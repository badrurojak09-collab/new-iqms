<?php

declare(strict_types=1);

$root = dirname(__DIR__) . '/app/Filament';
$replacements = [
    'Assessment Criterion' => 'Kriteria Penilaian', 'Canonical Criteria' => 'Kriteria Kanonik', 'Assessment Scales' => 'Skala Penilaian', 'Assessment Indicators' => 'Indikator Penilaian', 'Assessment Elements' => 'Elemen Penilaian', 'Instrument Node' => 'Simpul Instrumen', 'Name' => 'Nama', 'Node' => 'Simpul', 'Weight' => 'Bobot', 'Order' => 'Urutan', 'Min' => 'Minimum', 'Max' => 'Maksimum', 'Missing' => 'Tidak tersedia', 'Required' => 'Wajib', 'Yes' => 'Ya', 'No' => 'Tidak', 'Completed At' => 'Selesai Pada', 'Create RTL' => 'Buat RTL', 'Evidence completion berhasil ditautkan ke RTL.' => 'Bukti penyelesaian berhasil ditautkan ke RTL.', 'Aggregation Group' => 'Kelompok Agregasi', 'Minimum Passed' => 'Minimum Lulus', 'Accreditation Report' => 'Laporan Akreditasi', 'Accreditation Criteria' => 'Kriteria Akreditasi', 'Attach Existing Evidence' => 'Lampirkan Bukti yang Ada', 'Existing Evidence Cloud Link' => 'Tautan Bukti Cloud yang Ada', 'Check Link' => 'Periksa Tautan', 'Weighted Score' => 'Skor Berbobot', 'Fallback Response' => 'Respons Cadangan', 'RTL Code' => 'Kode RTL', 'Attach Completion Evidence' => 'Lampirkan Bukti Penyelesaian', 'RTL Action' => 'Tindakan RTL', 'Evidence Cloud Link' => 'Tautan Bukti Cloud', 'Create RTM Decision' => 'Buat Keputusan RTM', 'Outcome Evidence' => 'Bukti Dampak', 'Create Draft Review' => 'Buat Draf Tinjauan', 'Attach Evidence' => 'Lampirkan Bukti', 'Cloud Evidence' => 'Bukti Cloud', 'SPMI Indicator' => 'Indikator SPMI', 'SPMI Target' => 'Target SPMI', 'Assessment Element' => 'Elemen Penilaian', 'Assessment Indicator' => 'Indikator Penilaian', 'Assessment Scale' => 'Skala Penilaian', 'Assessment Rubric' => 'Rubrik Penilaian', 'Instrument Version' => 'Versi Instrumen', 'Instrument' => 'Instrumen', 'Accreditation' => 'Akreditasi', 'Collection Code' => 'Kode Koleksi', 'Collection Name' => 'Nama Koleksi', 'Collection' => 'Koleksi', 'Requirements' => 'Persyaratan', 'Root Folder URL' => 'URL Folder Utama', 'Root Folder ID' => 'ID Folder Utama', 'Root Folder' => 'Folder Utama', 'Evidence Expectation' => 'Ekspektasi Bukti', 'Evidence Links' => 'Tautan Bukti', 'Evidence Review' => 'Tinjauan Bukti', 'Mapping' => 'Pemetaan', 'Mappings' => 'Pemetaan', 'Thresholds' => 'Ambang Batas', 'Threshold' => 'Ambang Batas', 'Rubrics' => 'Rubrik', 'Rubric' => 'Rubrik', 'Approved By' => 'Disetujui Oleh', 'Approval Notes' => 'Catatan Persetujuan', 'Approve' => 'Setujui', 'Reject' => 'Tolak', 'Review' => 'Tinjau', 'Start' => 'Mulai', 'Complete' => 'Selesaikan', 'Verify' => 'Verifikasi', 'Create' => 'Buat', 'Edit' => 'Ubah', 'Delete' => 'Hapus', 'Search' => 'Cari', 'Filter' => 'Saring', 'Export' => 'Ekspor', 'Import' => 'Impor', 'Status' => 'Status', 'Code' => 'Kode', 'Title' => 'Judul', 'Notes' => 'Catatan', 'Year' => 'Tahun', 'Progress' => 'Kemajuan', 'Readiness' => 'Kesiapan', 'Run' => 'Proses', 'Tenant' => 'Tenant', 'User' => 'Pengguna', 'Users' => 'Pengguna', 'Role' => 'Peran', 'Permission' => 'Hak Akses', 'Dashboard' => 'Dasbor', 'Account' => 'Akun', 'Save' => 'Simpan', 'Submit' => 'Kirim', 'Action' => 'Tindakan', 'Actions' => 'Tindakan', 'Finding' => 'Temuan', 'Findings' => 'Temuan', 'Cycle' => 'Siklus', 'Decision' => 'Keputusan', 'Gap' => 'Kesenjangan', 'Program' => 'Program', 'Programs' => 'Program', 'Target' => 'Target', 'Indicator' => 'Indikator', 'Indicators' => 'Indikator', 'Version' => 'Versi', 'Element' => 'Elemen', 'Elements' => 'Elemen', 'Scale' => 'Skala', 'Scales' => 'Skala', 'Template' => 'Templat', 'Templates' => 'Templat', 'Section' => 'Bagian', 'Sections' => 'Bagian', 'Column' => 'Kolom', 'Columns' => 'Kolom', 'Report' => 'Laporan', 'Reports' => 'Laporan', 'Link' => 'Tautan', 'Document' => 'Dokumen', 'File' => 'Berkas', 'Owner' => 'Penanggung Jawab', 'Due Date' => 'Batas Waktu', 'Score' => 'Skor', 'Minimum' => 'Minimum', 'Maximum' => 'Maksimum', 'Passed' => 'Lulus', 'Result' => 'Hasil', 'Outcome' => 'Dampak', 'Recommendation' => 'Rekomendasi', 'Published' => 'Diterbitkan', 'Draft' => 'Draf', 'Active' => 'Aktif', 'Inactive' => 'Tidak Aktif', 'Pending' => 'Menunggu', 'History' => 'Riwayat', 'Check' => 'Periksa', 'Submission' => 'Pengajuan', 'Package' => 'Paket', 'Response' => 'Respons', 'Field' => 'Kolom', 'Source' => 'Sumber', 'Scope' => 'Lingkup', 'Period' => 'Periode', 'Coordinator' => 'Koordinator', 'Institution' => 'Institusi', 'Decision Code' => 'Kode Keputusan', 'RTM Meeting' => 'Rapat RTM', 'Resolve Gap' => 'Selesaikan Kesenjangan', 'Lock for Submission' => 'Kunci untuk Pengajuan', 'Lock Reason' => 'Alasan Penguncian', 'Completion Notes' => 'Catatan Penyelesaian', 'Readiness Job' => 'Proses Kesiapan', 'Re-eval Run' => 'Proses Evaluasi Ulang', 'Target Year' => 'Tahun Target', 'Min Score' => 'Skor Minimum',
];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') continue;
    $path = $file->getPathname();
    $source = file_get_contents($path);
    $tokens = token_get_all($source);
    $updated = '';
    foreach ($tokens as $token) {
        if (is_array($token) && $token[0] === T_CONSTANT_ENCAPSED_STRING) {
            $quote = $token[1][0];
            $value = stripcslashes(substr($token[1], 1, -1));
            if (isset($replacements[$value])) {
                $token[1] = $quote . addcslashes($replacements[$value], "\\$quote") . $quote;
            }
        }
        $updated .= is_array($token) ? $token[1] : $token;
    }
    $statusPatterns = [
        "TextColumn::make('status')->badge()" => "TextColumn::make('status')->badge()->formatStateUsing(fn (mixed \$state): string => \\App\\Support\\Ui\\StatusLabel::for(\$state))",
        "TextColumn::make('resolution_status')->label('Penyelesaian')->badge()" => "TextColumn::make('resolution_status')->label('Penyelesaian')->badge()->formatStateUsing(fn (mixed \$state): string => \\App\\Support\\Ui\\StatusLabel::for(\$state))",
        "TextColumn::make('re_evaluation_status')->label('Proses Kesiapan')->badge()" => "TextColumn::make('re_evaluation_status')->label('Proses Kesiapan')->badge()->formatStateUsing(fn (mixed \$state): string => \\App\\Support\\Ui\\StatusLabel::for(\$state))",
    ];
    $updated = str_replace(array_keys($statusPatterns), array_values($statusPatterns), $updated);
    if ($updated !== $source) file_put_contents($path, $updated);
}
