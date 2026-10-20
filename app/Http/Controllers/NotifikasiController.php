<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $notifikasi = $request->user()->notifications()->paginate(20);

        return view('notifikasi.index', compact('notifikasi'));
    }

    public function bacaSemua(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    // Tandai dibaca lalu arahkan ke halaman terkait
    public function buka(Request $request, string $id)
    {
        $notif = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $notif->markAsRead();

        $url = $notif->data['url'] ?? null;

        // Hanya alamat dalam aplikasi (diawali satu "/") yang boleh dituju
        $aman = is_string($url) && str_starts_with($url, '/') && !str_starts_with($url, '//');

        return redirect($aman ? $url : route('notifikasi.index'));
    }
}