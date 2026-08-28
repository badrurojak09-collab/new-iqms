@if (session()->has('impersonation.original_user_id'))
    <div class="flex items-center gap-3 bg-warning-50 px-4 py-2 text-sm text-warning-800 dark:bg-warning-950 dark:text-warning-200">
        <span>
            Mode impersonate aktif sebagai <strong>{{ auth()->user()?->name }}</strong>.
            Aktivitas tetap dicatat atas nama super_admin.
        </span>
        <form method="POST" action="{{ route('impersonation.stop') }}" class="ml-auto">
            @csrf
            <button type="submit" class="rounded-lg bg-warning-600 px-3 py-1.5 font-medium text-white hover:bg-warning-700">
                Kembali ke Akun Super Admin
            </button>
        </form>
    </div>
@endif
