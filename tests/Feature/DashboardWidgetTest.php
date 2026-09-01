<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Widgets\AccreditationProgress;
use App\Filament\Widgets\QualityOverview;
use App\Models\PerguruanTinggi;
use App\Models\User;
use App\Models\Yayasan;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class DashboardWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_quality_overview_and_accreditation_progress_widgets_render_correctly(): void
    {
        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan Pendidikan']);
        $pt = PerguruanTinggi::query()->create([
            'yayasan_id' => $yayasan->id,
            'nama_pt' => 'Universitas Contoh',
            'kode_pt' => 'PT-001',
            'jenis' => 'universitas',
            'status' => 'active',
        ]);
        $user = User::factory()->create([
            'yayasan_id' => $yayasan->id,
            'perguruan_tinggi_id' => $pt->id,
        ]);

        $this->actingAs($user);
        app(TenantContext::class)->set($user, $pt->id);

        Livewire::test(QualityOverview::class)
            ->assertSuccessful()
            ->assertSee('SPMI Met Rate')
            ->assertSee('Temuan AMI Terbuka')
            ->assertSee('RTL Overdue')
            ->assertSee('Readiness Akreditasi');

        Livewire::test(AccreditationProgress::class)
            ->assertSuccessful()
            ->assertSee('Progress LED')
            ->assertSee('Progress LKPS')
            ->assertSee('Response Completion')
            ->assertSee('Readiness Item');
    }
}
