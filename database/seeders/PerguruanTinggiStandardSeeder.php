<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PerguruanTinggi;
use App\Models\PerguruanTinggiStandard;
use App\Models\SpmiStandard;
use Illuminate\Database\Seeder;

final class PerguruanTinggiStandardSeeder extends Seeder
{
    public function run(): void
    {
        PerguruanTinggi::query()->each(function (PerguruanTinggi $perguruanTinggi): void {
            $standards = [
                ['code' => 'PT-AKD-01', 'name' => 'Standar Pendidikan', 'category' => 'academic', 'statement' => 'Perguruan tinggi menjamin penyelenggaraan pendidikan yang bermutu dan berkelanjutan.', 'sort_order' => 10],
                ['code' => 'PT-AKD-02', 'name' => 'Standar Penelitian', 'category' => 'academic', 'statement' => 'Perguruan tinggi menjamin penelitian yang relevan, bermutu, dan berdampak.', 'sort_order' => 20],
                ['code' => 'PT-AKD-03', 'name' => 'Standar Pengabdian kepada Masyarakat', 'category' => 'academic', 'statement' => 'Perguruan tinggi menjamin pengabdian kepada masyarakat yang berbasis kebutuhan dan hasil penelitian.', 'sort_order' => 30],
                ['code' => 'PT-NON-01', 'name' => 'Standar Tata Kelola dan Sumber Daya', 'category' => 'non_academic', 'statement' => 'Perguruan tinggi menjalankan tata kelola, sumber daya, dan layanan pendukung secara akuntabel.', 'sort_order' => 40],
            ];

            foreach ($standards as $data) {
                $standard = PerguruanTinggiStandard::query()->withTrashed()->updateOrCreate(
                    ['perguruan_tinggi_id' => $perguruanTinggi->getKey(), 'code' => $data['code']],
                    $data + ['status' => 'active'],
                );

                if ($standard->trashed()) {
                    $standard->restore();
                }

                SpmiStandard::query()
                    ->where('perguruan_tinggi_id', $perguruanTinggi->getKey())
                    ->whereNull('perguruan_tinggi_standard_id')
                    ->where(function ($query) use ($data): void {
                        $query->where('name', 'like', '%'.str_replace('Standar ', '', $data['name']).'%')
                            ->orWhere('code', 'like', '%'.substr($data['code'], -2).'%');
                    })
                    ->limit(1)
                    ->update(['perguruan_tinggi_standard_id' => $standard->getKey()]);
            }
        });
    }
}
