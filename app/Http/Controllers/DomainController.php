<?php

namespace App\Http\Controllers;

use App\Jobs\CheckDomainSslJob;
use App\Models\AuditLog;
use App\Models\Domain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DomainController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Domain::with('latestSslCheck');
        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        $domains = $query->latest()->paginate(15);

        return view('domains.index', compact('domains'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('domains.create');
    }

    /**
     * Display the specified resource.
     */
    public function show(Domain $domain): View
    {
        Gate::authorize('view', $domain);

        $domain->load([
            'latestSslCheck',
            'sslChecks' => fn ($q) => $q->latest('checked_at')->take(10),
            'notifications' => fn ($q) => $q->latest('sent_at')->take(10),
        ]);

        return view('domains.show', compact('domain'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hostname' => [
                'required',
                'string',
                'max:255',
                'regex:/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i',
            ],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
        ], [
            'hostname.regex' => 'Format domain tidak valid (contoh: contoh.tasikmalayakota.go.id atau google.com).',
        ]);

        $domain = Domain::create([
            'user_id' => $request->user()->id,
            'hostname' => strtolower($validated['hostname']),
            'port' => $validated['port'] ?? 443,
            'status' => 'active',
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'domain_id' => $domain->id,
            'action' => 'domain_created',
            'description' => "Domain {$domain->hostname} ditambahkan oleh {$request->user()->name}.",
            'ip_address' => $request->ip(),
        ]);

        // Dispatch initial SSL check job immediately
        CheckDomainSslJob::dispatch($domain);

        return redirect()->route('domains.index')->with('status', "Domain {$domain->hostname} berhasil ditambahkan dan pemeriksaan SSL sedang berlangsung.");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Domain $domain): View
    {
        Gate::authorize('update', $domain);

        return view('domains.edit', compact('domain'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Domain $domain): RedirectResponse
    {
        Gate::authorize('update', $domain);

        $validated = $request->validate([
            'hostname' => [
                'required',
                'string',
                'max:255',
                'regex:/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i',
            ],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $domain->update([
            'hostname' => strtolower($validated['hostname']),
            'port' => $validated['port'],
            'status' => $validated['status'],
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'domain_id' => $domain->id,
            'action' => 'domain_updated',
            'description' => "Domain {$domain->hostname} diperbarui oleh {$request->user()->name}.",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('domains.index')->with('status', "Domain {$domain->hostname} berhasil diperbarui.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Domain $domain): RedirectResponse
    {
        Gate::authorize('delete', $domain);

        $hostname = $domain->hostname;
        $domain->delete();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'domain_id' => null,
            'action' => 'domain_deleted',
            'description' => "Domain {$hostname} dihapus oleh {$request->user()->name}.",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('domains.index')->with('status', "Domain {$hostname} berhasil dihapus.");
    }

    /**
     * Trigger immediate SSL check.
     */
    public function checkNow(Domain $domain): RedirectResponse
    {
        Gate::authorize('check', $domain);

        CheckDomainSslJob::dispatch($domain);

        return back()->with('status', "Pemeriksaan SSL untuk {$domain->hostname} telah dijadwalkan ke antrian.");
    }
}

