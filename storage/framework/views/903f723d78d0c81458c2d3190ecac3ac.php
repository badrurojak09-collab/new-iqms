<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('impersonation.original_user_id')): ?>
    <div class="flex items-center gap-3 bg-warning-50 px-4 py-2 text-sm text-warning-800 dark:bg-warning-950 dark:text-warning-200">
        <span>
            Mode impersonate aktif sebagai <strong><?php echo e(auth()->user()?->name); ?></strong>.
            Aktivitas tetap dicatat atas nama super_admin.
        </span>
        <form method="POST" action="<?php echo e(route('impersonation.stop')); ?>" class="ml-auto">
            <?php echo csrf_field(); ?>
            <button type="submit" class="rounded-lg bg-warning-600 px-3 py-1.5 font-medium text-white hover:bg-warning-700">
                Kembali ke Akun Super Admin
            </button>
        </form>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH D:\laragon\www\new-qms\resources\views/filament/components/impersonation-banner.blade.php ENDPATH**/ ?>