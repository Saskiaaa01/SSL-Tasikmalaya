<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Domain;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    /**
     * Nilai role yang dikenal sistem. Sesuaikan jika migration role memakai nilai lain.
     */
    public const ROLES = [
        'admin' => 'Administrator',
        'owner' => 'Owner (Pemilik Website)',
    ];

    public function index(Request $request): View
    {
        abort_unless($request->user()->isAdmin(), 403, 'Halaman ini khusus Administrator.');

        $users = User::orderBy('name')->paginate(20);

        $domainCounts = Domain::selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $roles = self::ROLES;

        return view('admin.users', compact('users', 'domainCounts', 'roles'));
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403, 'Halaman ini khusus Administrator.');

        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak dapat mengubah role akun Anda sendiri.');
        }

        $validated = $request->validate([
            'role' => ['required', Rule::in(array_keys(self::ROLES))],
        ]);

        $oldRole = $user->role;
        $user->forceFill(['role' => $validated['role']])->save();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'domain_id' => null,
            'action' => 'user_role_updated',
            'description' => "Role {$user->name} diubah dari {$oldRole} menjadi {$validated['role']} oleh {$request->user()->name}.",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('status', "Role {$user->name} berhasil diubah menjadi " . self::ROLES[$validated['role']] . '.');
    }
}