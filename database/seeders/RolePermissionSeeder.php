<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class RolePermissionSeeder extends Seeder
{
    private const GUARD = 'web';

    /**
     * Kode matriks UAT:
     * L = lihat, K = kelola, R = review, V = verifikasi,
     * A = approval, S = submit.
     *
     * Permission granular ditambahkan untuk kebutuhan ekspansi policy.
     * Permission lama tetap dipertahankan karena sudah digunakan oleh
     * policy/resource yang berjalan.
     */
    public function run(): void
    {
        $permissions = [
            // Dashboard dan administrasi umum.
            'view dashboard',
            'view organization',
            'manage organization',
            'view security',
            'manage security',
            'view audit log',

            // SPMI.
            'view spmi',
            'manage spmi',
            'verify spmi improvement',

            // AMI.
            'view ami',
            'manage ami',
            'review ami',
            'verify ami',

            // Akreditasi legacy yang masih dipakai oleh policy Resource existing.
            'view accreditation',
            'manage accreditation',
            'review accreditation',
            'approve accreditation',

            // RTM dan RTL.
            'view rtm',
            'manage rtm',
            'view rtl',
            'manage rtl',
            'review rtl',
            'verify rtl',
            'close rtl',
            'review rtl effectiveness',
            'submit rtl effectiveness',
            'approve rtl effectiveness',

            // Evidence.
            'view evidence',
            'manage evidence',
            'review evidence',

            // Instrument Registry.
            'view instrument configuration',
            'manage instrument configuration',
            'review instrument configuration',
            'approve instrument configuration',

            // Template LED/LKPS.
            'view templates',
            'manage templates',
            'review templates',
            'approve templates',

            // Readiness, scoring, dan submission.
            'view readiness',
            'manage readiness',
            'review readiness',
            'verify readiness',
            'approve readiness',
            'view score snapshots',
            'view submission package',
            'manage submission package',
            'review submission package',
            'approve submission package',
            'submit submission package',
            'resolve readiness gap',

            // Reporting.
            'view reporting',
        ];

        $permissionModels = collect(array_values(array_unique($permissions)))
            ->mapWithKeys(fn (string $permission): array => [
                $permission => Permission::query()->firstOrCreate([
                    'name' => $permission,
                    'guard_name' => self::GUARD,
                ]),
            ]);

        $rolePermissions = [
            'super_admin' => [
                'view dashboard',
                'view organization', 'manage organization',
                'view security', 'manage security', 'view audit log',
                'view spmi', 'manage spmi', 'verify spmi improvement',
                'view ami', 'manage ami', 'review ami', 'verify ami',
                'view accreditation', 'manage accreditation', 'review accreditation', 'approve accreditation',
                'view rtm', 'manage rtm', 'view rtl', 'manage rtl', 'review rtl', 'verify rtl', 'close rtl',
                'review rtl effectiveness', 'submit rtl effectiveness', 'approve rtl effectiveness',
                'view evidence', 'manage evidence', 'review evidence',
                'view instrument configuration', 'manage instrument configuration', 'review instrument configuration', 'approve instrument configuration',
                'view templates', 'manage templates', 'review templates', 'approve templates',
                'view readiness', 'manage readiness', 'review readiness', 'verify readiness', 'approve readiness',
                'view score snapshots', 'view submission package', 'manage submission package', 'review submission package', 'approve submission package', 'submit submission package',
                'resolve readiness gap', 'view reporting',
            ],

            'security_admin' => [
                'view dashboard',
                'view security', 'manage security', 'view audit log',
            ],

            'yayasan_admin' => [
                'view dashboard',
                'view organization', 'manage organization',
                'view accreditation', 'approve accreditation',
                'view templates',
                'view readiness',
                'view score snapshots',
                'view submission package', 'approve submission package',
                'view reporting',
            ],

            'pt_admin' => [
                'view dashboard',
                'view organization', 'manage organization',
                'view spmi', 'manage spmi',
                'view ami', 'manage ami',
                'view accreditation', 'manage accreditation',
                'manage evidence',
                'view templates', 'manage templates',
                'view readiness',
                'view score snapshots',
                'manage submission package',
                'view reporting',
            ],

            'quality_manager' => [
                'view dashboard',
                'view spmi', 'manage spmi', 'verify spmi improvement',
                'view ami', 'review ami',
                'view accreditation', 'review accreditation',
                'view rtm', 'manage rtm',
                'view rtl', 'manage rtl', 'verify rtl', 'review rtl effectiveness', 'submit rtl effectiveness', 'approve rtl effectiveness',
                'view evidence', 'review evidence',
                'view instrument configuration', 'manage instrument configuration', 'review instrument configuration', 'approve instrument configuration',
                'view templates', 'review templates',
                'view readiness', 'review readiness', 'verify readiness',
                'view score snapshots',
                'view submission package', 'review submission package',
                'view reporting',
            ],

            'lpm' => [
                'view dashboard',
                'view spmi', 'manage spmi', 'verify spmi improvement',
                'view ami', 'review ami',
                'view accreditation', 'review accreditation',
                'view rtl', 'review rtl', 'verify rtl', 'review rtl effectiveness', 'submit rtl effectiveness',
                'view evidence', 'review evidence',
                'view templates', 'review templates',
                'view readiness', 'review readiness',
                'view score snapshots',
                'view submission package', 'review submission package',
                'view reporting',
            ],

            'auditor' => [
                'view dashboard',
                'view ami', 'manage ami',
                'view accreditation',
                'view evidence',
                'view reporting',
            ],

            'kaprodi' => [
                'view dashboard',
                'view spmi', 'manage spmi',
                'view ami', 'review ami',
                'view accreditation', 'manage accreditation',
                'manage rtl',
                'manage evidence',
                'manage templates',
                'view readiness', 'manage readiness',
                'view score snapshots',
                'manage submission package',
                'view reporting',
            ],

            'instrument_reviewer' => [
                'view dashboard',
                'view instrument configuration', 'review instrument configuration',
                'view accreditation', 'review accreditation',
                'view readiness', 'review readiness',
                'view score snapshots',
                'view submission package', 'review submission package',
                'view reporting',
            ],

            'instrument_approver' => [
                'view dashboard',
                'view instrument configuration', 'review instrument configuration', 'approve instrument configuration',
                'view accreditation', 'review accreditation',
                'view readiness', 'review readiness',
                'view score snapshots',
                'view submission package', 'review submission package',
                'view reporting',
            ],

            'reviewer' => [
                'view dashboard',
                'review ami',
                'review accreditation',
                'review evidence',
                'review instrument configuration',
                'review templates',
                'review readiness',
                'review submission package',
                'view reporting',
            ],
        ];

        foreach ($rolePermissions as $roleName => $rolePermissionNames) {
            $role = Role::query()->firstOrCreate([
                'name' => $roleName,
                'guard_name' => self::GUARD,
            ]);

            $role->syncPermissions(
                $permissionModels->only(array_values(array_unique($rolePermissionNames)))->values(),
            );
        }
    }
}
