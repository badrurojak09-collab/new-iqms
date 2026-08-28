<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class ImpersonationController
{
    public function start(Request $request, User $user): RedirectResponse
    {
        $actor = $request->user();

        abort_unless($actor instanceof User && $actor->isSuperAdmin(), 403);
        abort_if($actor->getKey() === $user->getKey(), 422, 'Akun yang sama tidak perlu di-impersonate.');
        abort_if($user->isSuperAdmin(), 403, 'Impersonasi akun super_admin lain tidak diizinkan.');

        Gate::authorize('view', $user);

        $request->session()->put([
            'impersonation.original_user_id' => $actor->getKey(),
            'impersonation.target_user_id' => $user->getKey(),
            'impersonation.started_at' => now()->toIso8601String(),
        ]);

        $request->session()->regenerate();
        Auth::guard('web')->login($user);
        $request->setUserResolver(static fn (): User => $user);
        $request->session()->forget('password_hash_web');
        app(TenantContext::class)->clear();
        $request->session()->save();

        AuditLog::query()->create([
            'user_id' => $actor->getKey(),
            'event' => 'impersonation.started',
            'route' => $request->route()?->getName(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'context' => [
                'target_user_id' => $user->getKey(),
                'target_email' => $user->email,
            ],
        ]);

        return redirect()->route('filament.admin.pages.dashboard')
            ->with('status', 'Anda sedang melihat panel sebagai ' . $user->name . '.');
    }

    public function stop(Request $request): RedirectResponse
    {
        $originalUserId = $request->session()->get('impersonation.original_user_id');
        $targetUserId = $request->session()->get('impersonation.target_user_id');

        abort_unless($originalUserId !== null && $targetUserId !== null, 404);

        $target = $request->user();
        $originalUser = User::query()->findOrFail($originalUserId);
        Auth::guard('web')->login($originalUser);
        $request->setUserResolver(static fn (): User => $originalUser);
        $request->session()->forget('password_hash_web');
        $request->session()->forget([
            'impersonation.original_user_id',
            'impersonation.target_user_id',
            'impersonation.started_at',
        ]);
        $request->session()->regenerate();
        app(TenantContext::class)->clear();
        $request->session()->save();

        AuditLog::query()->create([
            'user_id' => $originalUserId,
            'event' => 'impersonation.stopped',
            'route' => $request->route()?->getName(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'context' => [
                'target_user_id' => $targetUserId,
                'target_email' => $target?->email,
            ],
        ]);

        return redirect()->route('filament.admin.pages.dashboard')
            ->with('status', 'Sesi impersonate telah dihentikan.');
    }
}
