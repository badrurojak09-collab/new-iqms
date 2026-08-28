<?php

declare(strict_types=1);

namespace App\Support\Ui;

final class StatusLabel
{
    public static function for(mixed $status): string
    {
        return match ((string) $status) {
            'planned' => 'Direncanakan',
            'in_progress' => 'Sedang Berjalan',
            'completed' => 'Selesai',
            'verified' => 'Terverifikasi',
            'open' => 'Terbuka',
            'closed' => 'Ditutup',
            'cancelled' => 'Dibatalkan',
            'draft' => 'Draf',
            'submitted' => 'Diajukan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'retired' => 'Tidak Berlaku',
            'pending' => 'Menunggu',
            'accepted' => 'Diterima',
            'review' => 'Ditinjau',
            'queued' => 'Dalam Antrean',
            'running' => 'Sedang Diproses',
            'failed' => 'Gagal',
            'resolved' => 'Terselesaikan',
            'unresolved' => 'Belum Terselesaikan',
            default => $status === null || $status === '' ? '—' : ucfirst(str_replace('_', ' ', (string) $status)),
        };
    }
}
