<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View { return view('pages.home'); }
    public function about(): View { return view('pages.about'); }
    public function services(): View { return view('pages.services'); }
    public function gallery(): View { return view('pages.gallery'); }
    public function contact(): View { return view('pages.contact'); }

    public function submitContact(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        return to_route('contact')->with('success', 'Terima kasih. Pesan Anda telah diterima oleh tim kami.');
    }
}
