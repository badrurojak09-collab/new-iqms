<x-filament-panels::page>
    {{-- Scoped CSS for bulletproof layout across all Filament themes and environments --}}
    <style>
        .iqms-ws {
            display: flex;
            flex-direction: column;
            gap: 20px;
            font-family: inherit;
            color: #1e293b;
        }
        :is(.dark) .iqms-ws {
            color: #f1f5f9;
        }

        /* ── Grids ── */
        .iqms-grid-4 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }
        .iqms-grid-3 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 16px;
        }

        /* ── Card / Section ── */
        .iqms-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            transition: all 0.2s ease;
        }
        :is(.dark) .iqms-card {
            background: #111827;
            border-color: #1f2937;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        /* ── Metric Card ── */
        .iqms-metric-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .iqms-metric-title {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
        }
        :is(.dark) .iqms-metric-title {
            color: #94a3b8;
        }
        .iqms-metric-value {
            font-size: 26px;
            font-weight: 700;
            line-height: 1.2;
            color: #0f172a;
        }
        :is(.dark) .iqms-metric-value {
            color: #ffffff;
        }
        .iqms-metric-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        /* ── Badges ── */
        .iqms-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            line-height: 1.3;
        }
        .iqms-badge-purple { background: #f3e8ff; color: #7e22ce; }
        .iqms-badge-blue   { background: #e0f2fe; color: #0369a1; }
        .iqms-badge-green  { background: #dcfce7; color: #15803d; }
        .iqms-badge-amber  { background: #fef3c7; color: #b45309; }
        .iqms-badge-gray   { background: #f1f5f9; color: #475569; }
        .iqms-badge-red    { background: #fee2e2; color: #b91c1c; }

        :is(.dark) .iqms-badge-purple { background: rgba(126, 34, 206, 0.2); color: #d8b4fe; }
        :is(.dark) .iqms-badge-blue   { background: rgba(3, 105, 161, 0.2); color: #7dd3fc; }
        :is(.dark) .iqms-badge-green  { background: rgba(21, 128, 61, 0.2); color: #86efac; }
        :is(.dark) .iqms-badge-amber  { background: rgba(180, 83, 9, 0.2); color: #fde047; }
        :is(.dark) .iqms-badge-gray   { background: rgba(71, 85, 105, 0.2); color: #cbd5e1; }
        :is(.dark) .iqms-badge-red    { background: rgba(185, 28, 28, 0.2); color: #fca5a5; }

        /* ── Progress Bar ── */
        .iqms-progress-bg {
            width: 100%;
            height: 6px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
            margin-top: 8px;
        }
        :is(.dark) .iqms-progress-bg {
            background: #374151;
        }
        .iqms-progress-fill {
            height: 100%;
            background: #10b981;
            border-radius: 999px;
            transition: width 0.3s ease;
        }
        .iqms-progress-fill-primary {
            background: #0284c7;
        }

        /* ── Accreditation Item Card ── */
        .iqms-acc-item {
            cursor: pointer;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            background: #ffffff;
            text-align: left;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 12px;
            transition: all 0.2s ease;
        }
        .iqms-acc-item:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }
        .iqms-acc-item.is-selected {
            border-color: #0284c7;
            background: #f0f9ff;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        }
        :is(.dark) .iqms-acc-item {
            background: #111827;
            border-color: #1f2937;
        }
        :is(.dark) .iqms-acc-item:hover {
            background: #1f2937;
            border-color: #374151;
        }
        :is(.dark) .iqms-acc-item.is-selected {
            background: rgba(2, 132, 199, 0.1);
            border-color: #38bdf8;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2);
        }

        /* ── Buttons ── */
        .iqms-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .iqms-btn-primary {
            background: #0284c7;
            color: #ffffff;
            border-color: #0284c7;
        }
        .iqms-btn-primary:hover {
            background: #0369a1;
            color: #ffffff;
        }
        .iqms-btn-secondary {
            background: #ffffff;
            color: #334155;
            border-color: #cbd5e1;
        }
        .iqms-btn-secondary:hover {
            background: #f8fafc;
            border-color: #94a3b8;
        }
        :is(.dark) .iqms-btn-secondary {
            background: #1f2937;
            color: #f1f5f9;
            border-color: #374151;
        }
        :is(.dark) .iqms-btn-secondary:hover {
            background: #374151;
        }

        /* ── Filter Tabs ── */
        .iqms-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
        }
        .iqms-tab-btn {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            background: #f1f5f9;
            color: #475569;
            transition: all 0.15s ease;
        }
        .iqms-tab-btn:hover {
            background: #e2e8f0;
            color: #1e293b;
        }
        .iqms-tab-btn.is-active {
            background: #0284c7;
            color: #ffffff;
        }
        :is(.dark) .iqms-tab-btn {
            background: #1f2937;
            color: #94a3b8;
        }
        :is(.dark) .iqms-tab-btn:hover {
            background: #374151;
            color: #f1f5f9;
        }
        :is(.dark) .iqms-tab-btn.is-active {
            background: #0284c7;
            color: #ffffff;
        }

        /* ── Search Input ── */
        .iqms-search-box {
            position: relative;
            min-width: 220px;
        }
        .iqms-search-input {
            width: 100%;
            padding: 7px 12px 7px 32px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 12px;
            background: #ffffff;
            color: #1e293b;
            outline: none;
        }
        .iqms-search-input:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.2);
        }
        :is(.dark) .iqms-search-input {
            background: #1f2937;
            border-color: #374151;
            color: #f1f5f9;
        }

        /* ── Data Table ── */
        .iqms-table-wrap {
            overflow-x: auto;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #ffffff;
            margin-top: 16px;
        }
        :is(.dark) .iqms-table-wrap {
            border-color: #1f2937;
            background: #111827;
        }
        .iqms-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            text-align: left;
        }
        .iqms-table th {
            background: #f8fafc;
            padding: 10px 14px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }
        :is(.dark) .iqms-table th {
            background: #1f2937;
            color: #94a3b8;
            border-color: #374151;
        }
        .iqms-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: top;
        }
        :is(.dark) .iqms-table td {
            border-color: #1f2937;
            color: #cbd5e1;
        }
        .iqms-table tr:hover td {
            background: #f8fafc;
        }
        :is(.dark) .iqms-table tr:hover td {
            background: #1e293b;
        }

        /* ── Dropdown ── */
        .iqms-dropdown-menu {
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 6px;
            width: 250px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
            padding: 6px;
            z-index: 50;
        }
        :is(.dark) .iqms-dropdown-menu {
            background: #1f2937;
            border-color: #374151;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.4);
        }
        .iqms-dropdown-item {
            display: block;
            padding: 8px 10px;
            border-radius: 6px;
            font-size: 12px;
            color: #334155;
            text-decoration: none;
            transition: background 0.15s;
        }
        .iqms-dropdown-item:hover {
            background: #f1f5f9;
            color: #0284c7;
        }
        :is(.dark) .iqms-dropdown-item {
            color: #e2e8f0;
        }
        :is(.dark) .iqms-dropdown-item:hover {
            background: #374151;
            color: #38bdf8;
        }
    </style>

    <div class="iqms-ws">

        {{-- ── 1. Top Metrics Cards ────────────────────────────────── --}}
        <div class="iqms-grid-4">
            {{-- Card 1: Total Respons --}}
            <div class="iqms-card">
                <div class="iqms-metric-header">
                    <div>
                        <div class="iqms-metric-title">Total Butir LKE/LED</div>
                        <div class="iqms-metric-value">{{ $responseCount }}</div>
                    </div>
                    <div class="iqms-metric-icon" style="background: #e0f2fe; color: #0284c7;">
                        📋
                    </div>
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 6px; display: flex; gap: 8px; flex-wrap: wrap;">
                    <span style="color: #16a34a; font-weight: 600;">{{ $completedResponseCount }} Selesai</span>
                    <span>•</span>
                    <span style="color: #d97706; font-weight: 600;">{{ $inReviewCount }} Review</span>
                    <span>•</span>
                    <span>{{ $draftCount }} Draf</span>
                </div>
            </div>

            {{-- Card 2: Kelengkapan Respons --}}
            <div class="iqms-card">
                <div class="iqms-metric-header">
                    <div>
                        <div class="iqms-metric-title">Kelengkapan Respons</div>
                        <div class="iqms-metric-value">{{ $completenessPct }}%</div>
                    </div>
                    <div class="iqms-metric-icon" style="background: #dcfce7; color: #16a34a;">
                        ✅
                    </div>
                </div>
                <div class="iqms-progress-bg">
                    <div class="iqms-progress-fill" style="width: {{ $completenessPct }}%;"></div>
                </div>
            </div>

            {{-- Card 3: Bukti Tertaut --}}
            <div class="iqms-card">
                <div class="iqms-metric-header">
                    <div>
                        <div class="iqms-metric-title">Bukti Tertaut</div>
                        <div class="iqms-metric-value">{{ $evidenceLinksCount }}</div>
                    </div>
                    <div class="iqms-metric-icon" style="background: #f3e8ff; color: #9333ea;">
                        📎
                    </div>
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 6px;">
                    <strong style="color: #16a34a;">{{ $verifiedEvidenceCount }}</strong> terverifikasi dari {{ $evidenceLinksCount }} tautan
                </div>
            </div>

            {{-- Card 4: Kesiapan Dokumen --}}
            <div class="iqms-card">
                <div class="iqms-metric-header">
                    <div>
                        <div class="iqms-metric-title">Kesiapan Dokumen</div>
                        <div class="iqms-metric-value">
                            {{ $readinessScore !== null ? number_format($readinessScore, 1) . '%' : '—' }}
                        </div>
                    </div>
                    <div class="iqms-metric-icon" style="background: #fef3c7; color: #d97706;">
                        📊
                    </div>
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 6px; display: flex; justify-content: space-between; align-items: center;">
                    <span>Target: {{ $selectedAccreditation?->planned_submission_date?->format('d/m/Y') ?: 'Belum diset' }}</span>
                    @if ($selectedAccreditation)
                        <button type="button" wire:click="calculateReadiness" style="background: none; border: none; padding: 0; color: #0284c7; font-weight: 600; cursor: pointer; font-size: 11px;">
                            Hitung Ulang ↻
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── 2. Pemilihan Kegiatan Akreditasi ─────────────────────── --}}
        <div class="iqms-card">
            <div style="margin-bottom: 16px;">
                <h3 style="font-size: 15px; font-weight: 700; margin: 0 0 4px 0;">Pilih Kegiatan Akreditasi</h3>
                <p style="font-size: 12px; color: #64748b; margin: 0;">Pilih paket akreditasi aktif untuk memantau butir LKE, narasi LED, kelengkapan bukti, dan ekspor dokumen.</p>
            </div>

            <div class="iqms-grid-3">
                @forelse ($accreditations as $acc)
                    @php
                        $isSelected = $selectedAccreditation?->is($acc);
                        $bodyCode = $acc->instrumentVersion?->family?->accreditationBody?->code ?? 'BAN-PT';
                        $isInfokom = str_contains($bodyCode, 'INFOKOM');
                        $accRespCount = $acc->responses->count();
                        $accApprovedCount = $acc->responses->whereIn('status', ['approved', 'locked'])->count();
                        $accPct = $accRespCount > 0 ? (int) round(($accApprovedCount / $accRespCount) * 100) : 0;
                    @endphp
                    <div
                        wire:click="selectAccreditation({{ $acc->getKey() }})"
                        class="iqms-acc-item {{ $isSelected ? 'is-selected' : '' }}"
                    >
                        <div>
                            {{-- Badges Row --}}
                            <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 8px;">
                                <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                    <span class="iqms-badge {{ $isInfokom ? 'iqms-badge-purple' : 'iqms-badge-blue' }}">
                                        {{ $bodyCode }}
                                    </span>
                                    <span class="iqms-badge iqms-badge-gray">
                                        {{ $acc->scope_type === 'institution' ? 'Institusi' : 'Prodi' }}
                                    </span>
                                </div>
                                <span class="iqms-badge {{ match($acc->status) { 'completed', 'approved' => 'iqms-badge-green', 'ready', 'submitted' => 'iqms-badge-blue', 'in_progress', 'review' => 'iqms-badge-amber', default => 'iqms-badge-gray' } }}">
                                    {{ \App\Support\Ui\StatusLabel::for($acc->status) }}
                                </span>
                            </div>

                            {{-- Title & Code --}}
                            <div style="font-size: 13px; font-weight: 700; margin-bottom: 2px;">{{ $acc->title }}</div>
                            <div style="font-family: monospace; font-size: 11px; color: #64748b; margin-bottom: 8px;">{{ $acc->code }}</div>

                            {{-- Sub Details --}}
                            <div style="font-size: 11px; color: #64748b; line-height: 1.5;">
                                <div>🎓 {{ $acc->programStudi?->nama_prodi ?: ($acc->perguruanTinggi?->nama_pt ?: 'Lingkup Institusi') }}</div>
                                <div>📋 {{ $acc->instrumentVersion?->version_label ?: 'Versi belum ditentukan' }}</div>
                            </div>
                        </div>

                        {{-- Progress Bar --}}
                        <div style="border-top: 1px solid #f1f5f9; padding-top: 8px; font-size: 11px;">
                            <div style="display: flex; justify-content: space-between; color: #64748b; margin-bottom: 4px;">
                                <span>Progress Selesai</span>
                                <span style="font-weight: 700; color: #0f172a;">{{ $accApprovedCount }}/{{ $accRespCount }} ({{ $accPct }}%)</span>
                            </div>
                            <div class="iqms-progress-bg" style="margin-top: 0;">
                                <div class="iqms-progress-fill-primary" style="width: {{ $accPct }}%; height: 100%;"></div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1; padding: 32px; text-align: center; color: #64748b; border: 1px dashed #cbd5e1; border-radius: 12px;">
                        Belum ada kegiatan akreditasi pada lingkup Anda.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ── 3. Detail Workspace Akreditasi Terpilih ─────────────── --}}
        @if ($selectedAccreditation)
            <div class="iqms-card">
                {{-- Header Bar --}}
                <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 14px; border-bottom: 1px solid #e2e8f0; padding-bottom: 16px; margin-bottom: 16px;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <h2 style="font-size: 16px; font-weight: 700; margin: 0;">{{ $selectedAccreditation->title }}</h2>
                            <span class="iqms-badge iqms-badge-blue">{{ $selectedAccreditation->code }}</span>
                        </div>
                        <div style="font-size: 11px; color: #64748b; margin-top: 4px; display: flex; gap: 12px; flex-wrap: wrap;">
                            <span>🏛️ {{ $selectedAccreditation->perguruanTinggi?->nama_pt }}</span>
                            @if ($selectedAccreditation->programStudi)
                                <span>🎓 Prodi: {{ $selectedAccreditation->programStudi->nama_prodi }}</span>
                            @endif
                            <span>📋 {{ $selectedAccreditation->instrumentVersion?->version_label }}</span>
                            <span>📅 Submit: {{ $selectedAccreditation->planned_submission_date?->format('d/m/Y') ?: '-' }}</span>
                        </div>
                    </div>

                    {{-- Action Buttons Group --}}
                    <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                        <a
                            href="{{ \App\Filament\Resources\Accreditations\AccreditationResource::getUrl('edit', ['record' => $selectedAccreditation]) }}"
                            class="iqms-btn iqms-btn-primary"
                        >
                            ✏️ Buka Form Pengisian & Review
                        </a>

                        <button
                            type="button"
                            wire:click="calculateScore"
                            class="iqms-btn iqms-btn-secondary"
                        >
                            🧮 Simulasi Skor
                        </button>

                        {{-- Dropdown Ekspor Dokumen --}}
                        <div x-data="{ open: false }" style="position: relative; display: inline-block;">
                            <button
                                type="button"
                                @click="open = !open"
                                class="iqms-btn iqms-btn-secondary"
                            >
                                📥 Ekspor Dokumen ▾
                            </button>

                            <div
                                x-show="open"
                                @click.outside="open = false"
                                x-transition
                                class="iqms-dropdown-menu"
                                style="display: none;"
                            >
                                <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; color: #94a3b8; padding: 4px 8px;">Format Native</div>
                                <a href="{{ route('accreditations.export', ['accreditation' => $selectedAccreditation->getKey(), 'type' => 'led-docx']) }}" target="_blank" class="iqms-dropdown-item">
                                    📝 Draf LED (.docx Word)
                                </a>
                                <a href="{{ route('accreditations.export', ['accreditation' => $selectedAccreditation->getKey(), 'type' => 'lkps-xlsx']) }}" target="_blank" class="iqms-dropdown-item">
                                    📊 Borang LKPS (.xlsx Excel)
                                </a>

                                <div style="border-top: 1px solid #e2e8f0; margin: 4px 0;"></div>
                                <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; color: #94a3b8; padding: 4px 8px;">Format Web & Cetak</div>
                                <a href="{{ route('accreditations.export', ['accreditation' => $selectedAccreditation->getKey(), 'type' => 'led-html']) }}" target="_blank" class="iqms-dropdown-item">
                                    📄 Draf LED (HTML / PDF)
                                </a>
                                <a href="{{ route('accreditations.export', ['accreditation' => $selectedAccreditation->getKey(), 'type' => 'lkps-html']) }}" target="_blank" class="iqms-dropdown-item">
                                    🖥️ Tabel LKPS (HTML / Cetak)
                                </a>
                                <a href="{{ route('accreditations.export', ['accreditation' => $selectedAccreditation->getKey(), 'type' => 'score-simulation']) }}" target="_blank" class="iqms-dropdown-item">
                                    🏆 Matriks Skor & Syarat Perlu
                                </a>
                                <a href="{{ route('accreditations.export', ['accreditation' => $selectedAccreditation->getKey(), 'type' => 'evidence-matrix-html']) }}" target="_blank" class="iqms-dropdown-item">
                                    📎 Peta Bukti (Evidence Matrix)
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Filter & Search Toolbar --}}
                <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 12px;">
                    {{-- Status Tabs --}}
                    <div class="iqms-tabs">
                        @php
                            $statuses = [
                                'all' => 'Semua (' . $responseCount . ')',
                                'approved' => 'Disetujui (' . $completedResponseCount . ')',
                                'in_review' => 'Dalam Review (' . $inReviewCount . ')',
                                'draft' => 'Draf / Revisi (' . $draftCount . ')',
                            ];
                        @endphp
                        @foreach ($statuses as $stKey => $stLabel)
                            <button
                                type="button"
                                wire:click="filterByStatus('{{ $stKey }}')"
                                class="iqms-tab-btn {{ $selectedStatus === $stKey ? 'is-active' : '' }}"
                            >
                                {{ $stLabel }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Search Input --}}
                    <div class="iqms-search-box">
                        <span style="position: absolute; left: 10px; top: 7px; color: #94a3b8; font-size: 12px;">🔍</span>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Cari butir atau kriteria..."
                            class="iqms-search-input"
                        />
                    </div>
                </div>

                {{-- Criteria Filter Pills --}}
                @if ($sections->isNotEmpty())
                    <div style="display: flex; flex-wrap: wrap; gap: 4px; align-items: center; padding-top: 8px; border-top: 1px solid #f1f5f9;">
                        <span style="font-size: 11px; font-weight: 600; color: #64748b; margin-right: 4px;">Kriteria:</span>
                        <button
                            type="button"
                            wire:click="filterBySection(null)"
                            class="iqms-tab-btn {{ $selectedSectionId === null ? 'is-active' : '' }}"
                            style="padding: 3px 8px; font-size: 11px;"
                        >
                            Semua
                        </button>
                        @foreach ($sections as $sec)
                            <button
                                type="button"
                                wire:click="filterBySection({{ $sec->getKey() }})"
                                class="iqms-tab-btn {{ $selectedSectionId === $sec->getKey() ? 'is-active' : '' }}"
                                style="padding: 3px 8px; font-size: 11px;"
                            >
                                {{ $sec->title }}
                            </button>
                        @endforeach
                    </div>
                @endif

                {{-- Data Table --}}
                <div class="iqms-table-wrap">
                    <table class="iqms-table">
                        <thead>
                            <tr>
                                <th style="width: 110px;">Butir</th>
                                <th style="width: 180px;">Kriteria / Bagian</th>
                                <th>Ringkasan Narasi LED</th>
                                <th style="width: 130px;">Bukti</th>
                                <th style="width: 100px;">Status</th>
                                <th style="width: 80px; text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($filteredResponses as $response)
                                @php
                                    $evCount = $response->evidenceLinks->count();
                                    $verCount = $response->evidenceLinks->where('evidence.status', 'verified')->count();
                                @endphp
                                <tr>
                                    {{-- Kunci Butir --}}
                                    <td>
                                        <span class="iqms-badge iqms-badge-gray" style="font-family: monospace; font-weight: 700;">
                                            {{ $response->response_key }}
                                        </span>
                                        <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">
                                            Rev #{{ $response->revision_no ?: 1 }}
                                        </div>
                                    </td>

                                    {{-- Bagian / Kriteria --}}
                                    <td style="font-weight: 600; color: #0f172a;">
                                        {{ $response->section?->title ?: '—' }}
                                    </td>

                                    {{-- Narasi Preview --}}
                                    <td style="max-width: 320px; line-height: 1.4;">
                                        @if (filled($response->response_text))
                                            <div style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                {{ $response->response_text }}
                                            </div>
                                        @else
                                            <span style="font-style: italic; color: #94a3b8;">Belum ada narasi respons LED.</span>
                                        @endif
                                    </td>

                                    {{-- Evidence --}}
                                    <td>
                                        @if ($evCount > 0)
                                            <span class="iqms-badge iqms-badge-blue">
                                                📎 {{ $evCount }} Dok ({{ $verCount }} Verif)
                                            </span>
                                        @else
                                            <span class="iqms-badge iqms-badge-gray" style="color: #94a3b8;">
                                                0 Bukti
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Status --}}
                                    <td>
                                        <span class="iqms-badge {{ match($response->status) {
                                            'approved', 'locked' => 'iqms-badge-green',
                                            'in_review', 'review' => 'iqms-badge-blue',
                                            'revision_required', 'rejected' => 'iqms-badge-red',
                                            default => 'iqms-badge-gray'
                                        } }}">
                                            {{ \App\Support\Ui\StatusLabel::for($response->status) }}
                                        </span>
                                    </td>

                                    {{-- Action link --}}
                                    <td style="text-align: right;">
                                        <a
                                            href="{{ \App\Filament\Resources\Accreditations\AccreditationResource::getUrl('edit', ['record' => $selectedAccreditation]) }}"
                                            style="color: #0284c7; font-weight: 600; text-decoration: none;"
                                        >
                                            Sunting →
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="padding: 24px; text-align: center; color: #94a3b8;">
                                        Tidak ada butir LKE/LED yang sesuai dengan filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
