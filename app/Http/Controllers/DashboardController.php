<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\Notification;
use App\Models\SslCheck;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $domainsQuery = Domain::with('latestSslCheck');
        if (! $user->isAdmin()) {
            $domainsQuery->where('user_id', $user->id);
        }

        $domains = $domainsQuery->latest()->get();
        $domainIds = $domains->pluck('id');

        $totalDomains = $domains->count();

        // Count statuses based on latest SSL checks
        $activeSslCount = 0;
        $expiringCount = 0;
        $expiredCount = 0;
        $errorCount = 0;

        foreach ($domains as $domain) {
            $latest = $domain->latestSslCheck;
            if (! $latest) {
                continue;
            }

            match ($latest->status) {
                'valid' => $activeSslCount++,
                'expiring' => $expiringCount++,
                'expired' => $expiredCount++,
                'error' => $errorCount++,
                default => null,
            };
        }

        $recentChecks = SslCheck::whereIn('domain_id', $domainIds)
            ->with('domain')
            ->latest('checked_at')
            ->take(10)
            ->get();

        $recentNotifications = Notification::whereIn('domain_id', $domainIds)
            ->with('domain')
            ->latest('sent_at')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'domains',
            'totalDomains',
            'activeSslCount',
            'expiringCount',
            'expiredCount',
            'errorCount',
            'recentChecks',
            'recentNotifications'
        ));
    }
}

