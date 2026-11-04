<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\Notification;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhatsAppLogController extends Controller
{
    /**
     * Daftar riwayat notifikasi WhatsApp.
     * Admin melihat semua, user biasa hanya notifikasi untuk domain miliknya.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Notification::with('domain');
        if (! $user->isAdmin()) {
            $query->whereIn('domain_id', Domain::where('user_id', $user->id)->pluck('id'));
        }

        $statsBase = clone $query;
        $stats = [
            'total' => (clone $statsBase)->count(),
            'sent' => (clone $statsBase)->where('status', 'sent')->count(),
            'not_sent' => (clone $statsBase)->where(fn ($q) => $q->where('status', '!=', 'sent')->orWhereNull('status'))->count(),
            'today' => (clone $statsBase)->whereDate('sent_at', now()->toDateString())->count(),
        ];

        $notifications = $query->latest('sent_at')->paginate(20);

        $waConfigured = filled(config('services.whatsapp.token')) && filled(config('services.whatsapp.phone_number_id'));

        return view('whatsapp.log', compact('notifications', 'stats', 'waConfigured'));
    }

    /**
     * Kirim pesan uji coba ke nomor WhatsApp milik user yang sedang login.
     */
    public function test(Request $request, WhatsAppService $whatsApp): RedirectResponse
    {
        $user = $request->user();

        if (blank($user->phone_number)) {
            return back()->with('error', 'Isi nomor WhatsApp di halaman Profil terlebih dahulu.');
        }

        $message = "✅ *Tes Notifikasi SSLGuard*\n\n"
            . "Halo {$user->name}, ini pesan uji coba dari SSLGuard (KOMINFO Monitoring).\n"
            . 'Waktu kirim: ' . now()->timezone('Asia/Jakarta')->format('d/m/Y H:i') . " WIB\n\n"
            . 'Jika pesan ini sampai, berarti integrasi WhatsApp Cloud API sudah berfungsi.';

        $result = $whatsApp->sendMessage($user->phone_number, $message);

        return match ($result['status'] ?? null) {
            'success' => back()->with('status', 'Pesan uji coba dikirim ke ' . $user->phone_number . '. Cek WhatsApp Anda.'),
            'simulated' => back()->with('error', 'Mode simulasi: token/Phone Number ID WhatsApp belum diisi di .env. Pesan hanya dicatat di log.'),
            default => back()->with('error', 'Gagal mengirim pesan uji coba. Lihat storage/logs/laravel.log untuk detailnya.'),
        };
    }
}