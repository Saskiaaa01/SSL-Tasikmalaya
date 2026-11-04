<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    /**
     * Show the admin dashboard.
     */
    public function index()
    {
        // Example stats – you can replace with real data as needed
        $domainsCount = \App\Models\Domain::count();
        $expiredCount = \App\Models\Domain::whereHas('latestSslCheck', fn($q) => $q->where('status', 'expired'))->count();

        return view('admin.dashboard', compact('domainsCount', 'expiredCount'));
    }
}

