<?php

namespace App\Http\Controllers;

use App\Jobs\CheckDomainSslJob;
use App\Models\AuditLog;
use App\Models\Domain;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
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
        $this->normalizeHostname($request);

        $validated = $request->validate([
            'hostname' => [
                'required',
                'string',
                'max:255',
                'regex:/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i',
                Rule::unique('domains', 'hostname')
                    ->where(fn ($q) => $q->where('user_id', $request->user()->id)),
                $this->blockPrivateHosts(),
            ],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
        ], $this->messages());

        $domain = Domain::create([
            'user_id' => $request->user()->id,
            'hostname' => $validated['hostname'],
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

        $this->normalizeHostname($request);

        $validated = $request->validate([
            'hostname' => [
                'required',
                'string',
                'max:255',
                'regex:/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i',
                Rule::unique('domains', 'hostname')
                    ->where(fn ($q) => $q->where('user_id', $domain->user_id))
                    ->ignore($domain->id),
                $this->blockPrivateHosts(),
            ],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'status' => ['required', 'in:active,inactive'],
        ], $this->messages());

        $domain->update([
            'hostname' => $validated['hostname'],
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

    /**
     * Rapikan input hostname sebelum divalidasi:
     * huruf kecil, tanpa spasi, tanpa http(s)://, tanpa path/port, tanpa titik di akhir.
     */
    private function normalizeHostname(Request $request): void
    {
        if (! $request->filled('hostname')) {
            return;
        }

        $host = strtolower(trim((string) $request->input('hostname')));
        $host = preg_replace('#^https?://#', '', $host);
        $host = preg_replace('#[/?\#].*$#', '', $host);
        $host = preg_replace('#:\d+$#', '', $host);
        $host = rtrim($host, '.');

        $request->merge(['hostname' => $host]);
    }

    /**
     * Proteksi SSRF: tolak hostname yang mengarah ke localhost / IP privat / IP khusus.
     * (localhost dan alamat IP mentah sudah ditolak oleh aturan regex.)
     */
    private function blockPrivateHosts(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) {
            if (! is_string($value) || $value === '') {
                return;
            }

            if (in_array($value, ['localhost', 'localhost.localdomain'], true) || str_ends_with($value, '.localhost')) {
                $fail('Hostname lokal tidak diizinkan.');

                return;
            }

            $ips = @gethostbynamel($value);
            if ($ips === false) {
                // Domain belum bisa di-resolve (DNS): dibiarkan, hasil cek SSL nanti akan berstatus error.
                return;
            }

            foreach ($ips as $ip) {
                $isPublic = filter_var(
                    $ip,
                    FILTER_VALIDATE_IP,
                    FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
                );

                if ($isPublic === false) {
                    $fail("Domain ini mengarah ke alamat IP privat/lokal ({$ip}) dan diblokir demi keamanan.");

                    return;
                }
            }
        };
    }

    /**
     * Pesan validasi dalam Bahasa Indonesia.
     */
    private function messages(): array
    {
        return [
            'hostname.required' => 'Nama domain wajib diisi.',
            'hostname.regex' => 'Format domain tidak valid (contoh: contoh.tasikmalayakota.go.id atau google.com).',
            'hostname.unique' => 'Domain ini sudah terdaftar di akun Anda.',
            'hostname.max' => 'Nama domain terlalu panjang (maksimal 255 karakter).',
            'port.integer' => 'Port harus berupa angka.',
            'port.min' => 'Port minimal 1.',
            'port.max' => 'Port maksimal 65535.',
            'port.required' => 'Port wajib diisi.',
            'status.required' => 'Status monitoring wajib dipilih.',
            'status.in' => 'Status monitoring tidak valid.',
        ];
    }
}   