<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompleteWorkOrderRequest;
use App\Http\Requests\StartWorkOrderRequest;
use App\Http\Requests\UpdateTechnicianNotesRequest;
use App\Models\Petugas;
use App\Models\WorkOrder;
use App\Support\WorkOrderService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TechnicianWorkOrderController extends Controller
{
    public function index(Request $request): View
    {
        $petugas = $this->petugas();
        $search = trim((string) $request->input('q', ''));
        $status = (string) $request->input('status', '');

        $workOrders = WorkOrder::query()
            ->with(['customer', 'details'])
            ->where('idpetugas', $petugas->idpetugas)
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('nomor_wo', 'like', "%{$search}%")
                        ->orWhere('pono', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn (Builder $customer) => $customer->where('nama_pt', 'like', "%{$search}%"));
                });
            })
            ->when(array_key_exists($status, WorkOrder::STATUSES), fn (Builder $query) => $query->where('status', $status))
            ->latest('tanggal_terbit')
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('staff.work-order.index', [
            'workOrders' => $workOrders,
            'statuses' => WorkOrder::STATUSES,
            'petugas' => $petugas,
            'pageTitle' => 'Work Order Teknisi',
        ]);
    }

    public function show(WorkOrder $workOrder): View
    {
        $petugas = $this->petugas();
        abort_unless($workOrder->idpetugas === $petugas->idpetugas, 404);
        $workOrder->load(['customer', 'details']);

        return view('staff.work-order.show', [
            'workOrder' => $workOrder,
            'petugas' => $petugas,
            'pageTitle' => 'Detail Work Order',
        ]);
    }

    public function start(StartWorkOrderRequest $request, WorkOrder $workOrder, WorkOrderService $workOrderService): RedirectResponse
    {
        $petugas = $this->petugas();
        abort_unless($workOrder->idpetugas === $petugas->idpetugas, 404);
        $tanggalMulai = $request->validated('tanggal_mulai');
        $catatanTeknisi = $request->validated('catatan_teknisi');

        $workOrderService->start(
            $workOrder,
            $petugas->idpetugas,
            is_string($tanggalMulai) ? $tanggalMulai : null,
            is_string($catatanTeknisi) ? $catatanTeknisi : null,
        );

        return to_route('teknisi.work-order.show', $workOrder)
            ->with('success', 'Work Order dimulai.');
    }

    public function notes(UpdateTechnicianNotesRequest $request, WorkOrder $workOrder, WorkOrderService $workOrderService): RedirectResponse
    {
        $petugas = $this->petugas();
        abort_unless($workOrder->idpetugas === $petugas->idpetugas, 404);

        $workOrderService->updateTechnicianNotes($workOrder, $petugas->idpetugas, (string) $request->validated('catatan_teknisi'));

        return to_route('teknisi.work-order.show', $workOrder)
            ->with('success', 'Catatan teknisi berhasil disimpan.');
    }

    public function complete(CompleteWorkOrderRequest $request, WorkOrder $workOrder, WorkOrderService $workOrderService): RedirectResponse
    {
        $petugas = $this->petugas();
        abort_unless($workOrder->idpetugas === $petugas->idpetugas, 404);
        $tanggalSelesai = (string) $request->validated('tanggal_selesai');
        $tanggalBast = (string) $request->validated('tanggal_bast');
        $catatanTeknisi = $request->validated('catatan_teknisi');

        $workOrderService->complete(
            $workOrder,
            $petugas->idpetugas,
            $tanggalSelesai,
            $tanggalBast,
            is_string($catatanTeknisi) ? $catatanTeknisi : null,
        );

        return to_route('teknisi.work-order.show', $workOrder)
            ->with('success', 'Work Order ditandai selesai.');
    }

    private function petugas(): Petugas
    {
        /** @var Petugas $petugas */
        $petugas = Auth::user();

        return $petugas;
    }
}
