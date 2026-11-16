<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignWorkOrderRequest;
use App\Http\Requests\CancelWorkOrderRequest;
use App\Http\Requests\StoreWorkOrderRequest;
use App\Http\Requests\UpdateAdminNotesRequest;
use App\Http\Requests\UpdateWorkOrderRequest;
use App\Models\DetailPo;
use App\Models\DetailWorkOrder;
use App\Models\Petugas;
use App\Models\Po;
use App\Models\WorkOrder;
use App\Support\WorkOrderService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkOrderController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $status = (string) $request->input('status', '');
        $technician = (string) $request->input('idpetugas', '');

        $workOrders = WorkOrder::query()
            ->with(['customer', 'po', 'petugas'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('nomor_wo', 'like', "%{$search}%")
                        ->orWhere('pono', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn (Builder $customer) => $customer->where('nama_pt', 'like', "%{$search}%"));
                });
            })
            ->when(array_key_exists($status, WorkOrder::STATUSES), fn (Builder $query) => $query->where('status', $status))
            ->when($technician !== '', fn (Builder $query) => $query->where('idpetugas', $technician))
            ->latest('tanggal_terbit')
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.work-order.index', [
            'workOrders' => $workOrders,
            'statuses' => WorkOrder::STATUSES,
            'technicians' => Petugas::query()->where('role', 'teknisi')->orderBy('namapetugas')->get(),
        ]);
    }

    public function create(Po $po): View
    {
        abort_unless($po->canCreateWorkOrder(), 404);
        $po->load(['customer', 'details.barangJasa', 'workOrders.details']);

        return view('admin.work-order.create', [
            'po' => $po,
            'items' => $this->workOrderItems($po),
        ]);
    }

    public function store(StoreWorkOrderRequest $request, Po $po, WorkOrderService $workOrderService): RedirectResponse
    {
        $workOrder = $workOrderService->createFromPo($po, $request->validated());

        return to_route('admin.work-order.show', $workOrder)
            ->with('success', "Work Order {$workOrder->nomor_wo} berhasil dibuat.");
    }

    public function show(WorkOrder $workOrder): View
    {
        $workOrder->load(['customer', 'po.customer', 'petugas', 'details']);

        return view('admin.work-order.show', [
            'workOrder' => $workOrder,
            'technicians' => Petugas::query()
                ->where('role', 'teknisi')
                ->where('is_active', true)
                ->orderBy('namapetugas')
                ->get(),
        ]);
    }

    public function edit(WorkOrder $workOrder): View
    {
        abort_if($workOrder->isTerminal(), 404);
        $workOrder->load(['customer', 'po.customer', 'po.details.barangJasa', 'details']);

        return view('admin.work-order.edit', [
            'workOrder' => $workOrder,
            'po' => $workOrder->po,
            'items' => $this->workOrderItems($workOrder->po, $workOrder),
        ]);
    }

    public function update(UpdateWorkOrderRequest $request, WorkOrder $workOrder, WorkOrderService $workOrderService): RedirectResponse
    {
        $workOrderService->update($workOrder, $request->validated());

        return to_route('admin.work-order.show', $workOrder)
            ->with('success', "Work Order {$workOrder->nomor_wo} berhasil diperbarui.");
    }

    public function assign(AssignWorkOrderRequest $request, WorkOrder $workOrder, WorkOrderService $workOrderService): RedirectResponse
    {
        $workOrderService->assignTechnician($workOrder, (string) $request->validated('idpetugas'));

        return to_route('admin.work-order.show', $workOrder)
            ->with('success', 'Teknisi berhasil ditugaskan.');
    }

    public function cancel(CancelWorkOrderRequest $request, WorkOrder $workOrder, WorkOrderService $workOrderService): RedirectResponse
    {
        $workOrderService->cancel($workOrder, (string) $request->validated('alasan_batal'));

        return to_route('admin.work-order.show', $workOrder)
            ->with('success', 'Work Order dibatalkan.');
    }

    public function updateAdminNotes(UpdateAdminNotesRequest $request, WorkOrder $workOrder, WorkOrderService $workOrderService): RedirectResponse
    {
        $workOrderService->updateAdminNotes($workOrder, (string) ($request->validated('catatan_admin') ?? ''));

        return to_route('admin.work-order.show', $workOrder)
            ->with('success', 'Catatan admin berhasil disimpan.');
    }

    /**
     * @return array<int, array{detail: DetailPo, remaining_qty: int, selected_qty: int, available_qty: int}>
     */
    private function workOrderItems(Po $po, ?WorkOrder $currentWorkOrder = null): array
    {
        $detailIds = $po->details->pluck('id');
        $allocatedQuantities = DetailWorkOrder::query()
            ->whereIn('detail_po_id', $detailIds)
            ->whereHas('workOrder', function (Builder $query) use ($po, $currentWorkOrder): void {
                $query
                    ->where('pono', $po->pono)
                    ->where('status', '!=', WorkOrder::STATUS_DIBATALKAN)
                    ->when($currentWorkOrder !== null, fn (Builder $query): Builder => $query->where('nomor_wo', '!=', $currentWorkOrder->nomor_wo));
            })
            ->selectRaw('detail_po_id, SUM(qty) as allocated_qty')
            ->groupBy('detail_po_id')
            ->pluck('allocated_qty', 'detail_po_id');
        $selectedQuantities = $currentWorkOrder?->details->pluck('qty', 'detail_po_id') ?? collect();
        $items = [];

        foreach ($po->details as $detail) {
            if ($detail->barangJasa?->tipe !== 'jasa') {
                continue;
            }

            $allocatedQty = (int) ($allocatedQuantities[$detail->id] ?? 0);
            $selectedQty = (int) ($selectedQuantities[$detail->id] ?? 0);
            $remainingQty = max(0, (int) $detail->qty - $allocatedQty);

            if ($remainingQty < 1 && $selectedQty < 1) {
                continue;
            }

            $items[] = [
                'detail' => $detail,
                'remaining_qty' => $remainingQty,
                'selected_qty' => $selectedQty,
                'available_qty' => $remainingQty + $selectedQty,
            ];
        }

        return $items;
    }
}
