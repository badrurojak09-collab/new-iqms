<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\MyProfile;
use App\Models\PerguruanTinggi;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Yayasan;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

final class MyProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_my_profile_page(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan Mutu', 'kode' => uniqid('YM')]);
        $pt = PerguruanTinggi::query()->create(['yayasan_id' => $yayasan->id, 'nama_pt' => 'PT Mutu', 'kode_pt' => uniqid('PTM')]);
        $user = User::factory()->create([
            'yayasan_id' => $yayasan->id,
            'perguruan_tinggi_id' => $pt->id,
            'name' => 'Budi Santoso',
            'email' => 'budi@mutu.ac.id',
        ]);
        $user->assignRole('pt_admin');

        Livewire::actingAs($user)
            ->test(MyProfile::class)
            ->assertSuccessful()
            ->assertSee('Profil Pengguna Mandiri');
    }

    public function test_user_can_update_profile_and_academic_data(): void
    {
        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan Mutu', 'kode' => uniqid('YM')]);
        $pt = PerguruanTinggi::query()->create(['yayasan_id' => $yayasan->id, 'nama_pt' => 'PT Mutu', 'kode_pt' => uniqid('PTM')]);
        $user = User::factory()->create([
            'yayasan_id' => $yayasan->id,
            'perguruan_tinggi_id' => $pt->id,
            'name' => 'Budi Santoso',
            'email' => 'budi@mutu.ac.id',
        ]);

        Livewire::actingAs($user)
            ->test(MyProfile::class)
            ->set('data.name', 'Prof. Budi Santoso, M.Kom.')
            ->set('data.email', 'budi.santoso@mutu.ac.id')
            ->set('data.title_prefix', 'Prof. Dr.')
            ->set('data.title_suffix', 'M.Kom., Ph.D.')
            ->set('data.nidn', '0412345678')
            ->set('data.nip', '198501012010121001')
            ->set('data.gender', 'male')
            ->set('data.phone', '081234567890')
            ->set('data.functional_position', 'Guru Besar / Profesor')
            ->set('data.structural_position', 'Ketua LPM')
            ->set('data.expertise', 'Sistem Informasi & Penjaminan Mutu')
            ->call('save')
            ->assertHasNoErrors();

        $user->refresh();
        self::assertSame('Prof. Budi Santoso, M.Kom.', $user->name);
        self::assertSame('budi.santoso@mutu.ac.id', $user->email);

        $profile = UserProfile::query()->where('user_id', $user->id)->first();
        self::assertNotNull($profile);
        self::assertSame('0412345678', $profile->nidn);
        self::assertSame('Guru Besar / Profesor', $profile->functional_position);
        self::assertSame('Ketua LPM', $profile->structural_position);
    }

    public function test_user_can_change_password_with_correct_current_password(): void
    {
        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan Mutu', 'kode' => uniqid('YM')]);
        $pt = PerguruanTinggi::query()->create(['yayasan_id' => $yayasan->id, 'nama_pt' => 'PT Mutu', 'kode_pt' => uniqid('PTM')]);
        $user = User::factory()->create([
            'yayasan_id' => $yayasan->id,
            'perguruan_tinggi_id' => $pt->id,
            'password' => Hash::make('oldpassword123'),
        ]);

        Livewire::actingAs($user)
            ->test(MyProfile::class)
            ->set('data.name', $user->name)
            ->set('data.email', $user->email)
            ->set('data.current_password', 'oldpassword123')
            ->set('data.new_password', 'newpassword456')
            ->set('data.new_password_confirmation', 'newpassword456')
            ->call('save')
            ->assertHasNoErrors();

        $user->refresh();
        self::assertTrue(Hash::check('newpassword456', $user->password));
    }
}
