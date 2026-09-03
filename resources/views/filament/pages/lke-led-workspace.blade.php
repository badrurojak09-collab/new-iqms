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
            padding: 7px 12px;
            border-radius: 8px;
            font-size: 11px;
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
        .iqms-btn-success {
            background: #16a34a;
            color: #ffffff;
            border-color: #16a34a;
        }
        .iqms-btn-success:hover {
            background: #15803d;
            color: #ffffff;
        }
        .iqms-btn-warning {
            background: #d97706;
            color: #ffffff;
            border-color: #d97706;
        }
        .iqms-btn-warning:hover {
            background: #b45309;
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

        /* ── Workspace Main Tabs ── */
        .iqms-main-tabs {
            display: flex;
            gap: 8px;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 20px;
        }
        :is(.dark) .iqms-main-tabs {
            border-color: #1f2937;
        }
        .iqms-main-tab-btn {
            padding: 10px 18px;
            font-size: 13px;
            font-weight: 700;
            border: none;
            background: none;
            cursor: pointer;
            color: #64748b;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .iqms-main-tab-btn:hover {
            color: #0284c7;
        }
        .iqms-main-tab-btn.is-active {
            color: #0284c7;
            border-bottom-color: #0284c7;
        }
        :is(.dark) .iqms-main-tab-btn {
            color: #94a3b8;
        }
        :is(.dark) .iqms-main-tab-btn.is-active {
            color: #38bdf8;
            border-bottom-color: #38bdf8;
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

        /* ── LKPS Editable Data Grid ── */
        .iqms-grid-input {
            width: 100%;
            min-width: 120px;
            padding: 6px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 12px;
            background: #ffffff;
            color: #0f172a;
            transition: all 0.15s;
        }
        .iqms-grid-input:focus {
            outline: none;
            border-color: #0284c7;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.2);
            background: #f0f9ff;
        }
        .iqms-grid-input.has-error {
            border-color: #ef4444;
            background: #fef2f2;
        }
        :is(.dark) .iqms-grid-input {
            background: #1f2937;
            border-color: #374151;
            color: #f1f5f9;
        }

        /* ── Modal Overlay ── */
        .iqms-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .iqms-modal-content {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            max-width: 750px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15);
            padding: 24px;
        }
        :is(.dark) .iqms-modal-content {
            background: #111827;
            border-color: #1f2937;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
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

            {{-- Card 3: Kemajuan LKPS --}}
            <div class="iqms-card">
                <div class="iqms-metric-header">
                    <div>
                        <div class="iqms-metric-title">Kemajuan Tabel LKPS</div>
                        <div class="iqms-metric-value">{{ number_format($lkpsProgress, 1) }}%</div>
                    </div>
                    <div class="iqms-metric-icon" style="background: #f3e8ff; color: #9333ea;">
                        📊
                    </div>
                </div>
                <div class="iqms-progress-bg">
                    <div class="iqms-progress-fill" style="width: {{ $lkpsProgress }}%; background: #9333ea;"></div>
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 6px;">
                    {{ $lkpsTemplates->count() }} tabel instrumen aktif
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
                        🎯
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
                <p style="font-size: 12px; color: #64748b; margin: 0;">Pilih paket akreditasi aktif untuk memantau butir LKE, narasi LED, kelengkapan bukti, tabel data LKPS, dan ekspor dokumen.</p>
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
                            ✏️ Buka Form & Review
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

                {{-- Workspace Main Tabs Switcher --}}
                <div class="iqms-main-tabs">
                    <button
                        type="button"
                        wire:click="setWorkspaceTab('led')"
                        class="iqms-main-tab-btn {{ $activeWorkspaceTab === 'led' ? 'is-active' : '' }}"
                    >
                        📝 Butir & Narasi LED
                        <span class="iqms-badge {{ $activeWorkspaceTab === 'led' ? 'iqms-badge-blue' : 'iqms-badge-gray' }}">{{ $responseCount }}</span>
                    </button>
                    <button
                        type="button"
                        wire:click="setWorkspaceTab('lkps')"
                        class="iqms-main-tab-btn {{ $activeWorkspaceTab === 'lkps' ? 'is-active' : '' }}"
                    >
                        📊 Borang & Tabel LKPS
                        <span class="iqms-badge {{ $activeWorkspaceTab === 'lkps' ? 'iqms-badge-purple' : 'iqms-badge-gray' }}">{{ $lkpsTemplates->count() }}</span>
                    </button>
                </div>

                {{-- ── TAB 1: Butir & Narasi LED ──────────────────────── --}}
                @if ($activeWorkspaceTab === 'led')
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

                    {{-- LED Data Table with Response Workflow Actions --}}
                    <div class="iqms-table-wrap">
                        <table class="iqms-table">
                            <thead>
                                <tr>
                                    <th style="width: 110px;">Butir</th>
                                    <th style="width: 160px;">Kriteria / Bagian</th>
                                    <th>Narasi & Catatan Telaah</th>
                                    <th style="width: 140px;">Bukti & Sitasi</th>
                                    <th style="width: 110px;">Status</th>
                                    <th style="width: 220px; text-align: right;">Aksi Alur Kerja</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($filteredResponses as $response)
                                    @php
                                        $evCount = $response->evidenceLinks->count();
                                        $verCount = $response->evidenceLinks->where('evidence.status', 'verified')->count();
                                        $isLocked = $response->isLocked();
                                        $status = $response->status;
                                    @endphp
                                    <tr>
                                        {{-- Kunci Butir --}}
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap;">
                                                <span class="iqms-badge iqms-badge-gray" style="font-family: monospace; font-weight: 700;">
                                                    {{ $response->response_key }}
                                                </span>
                                                @if ($isLocked)
                                                    <span style="font-size: 12px;" title="Butir Terkunci">🔒</span>
                                                @endif
                                            </div>
                                            <button
                                                type="button"
                                                wire:click="openRevisionHistoryModal({{ $response->getKey() }})"
                                                style="background: none; border: none; padding: 0; font-size: 10px; color: #0284c7; margin-top: 4px; cursor: pointer; text-decoration: underline;"
                                            >
                                                Rev #{{ $response->revision_no ?: 1 }} (Riwayat 📜)
                                            </button>
                                        </td>

                                        {{-- Bagian / Kriteria --}}
                                        <td style="font-weight: 600; color: #0f172a;">
                                            {{ $response->section?->title ?: '—' }}
                                        </td>

                                        {{-- Narasi & Review Notes Preview --}}
                                        <td style="max-width: 300px; line-height: 1.4;">
                                            @if (filled($response->response_text))
                                                <div style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; color: #334155;">
                                                    {{ $response->response_text }}
                                                </div>
                                            @else
                                                <span style="font-style: italic; color: #94a3b8;">Belum ada narasi respons LED.</span>
                                            @endif

                                            {{-- Review Notes Callout --}}
                                            @if (filled($response->review_notes))
                                                <div style="margin-top: 6px; padding: 6px 8px; background: #fffbeb; border-left: 3px solid #f59e0b; border-radius: 4px; font-size: 10px; color: #92400e;">
                                                    <strong>Catatan Reviewer:</strong> {{ $response->review_notes }}
                                                </div>
                                            @endif
                                        </td>

                                        {{-- Evidence & Citation Link --}}
                                        <td>
                                            <button
                                                type="button"
                                                wire:click="openEvidenceLinkModal({{ $response->getKey() }})"
                                                class="iqms-badge {{ $evCount > 0 ? 'iqms-badge-blue' : 'iqms-badge-gray' }}"
                                                style="cursor: pointer; text-decoration: none;"
                                                title="Kelola sitasi bukti untuk butir ini"
                                            >
                                                📎 {{ $evCount }} Bukti ({{ $verCount }} Verif) ▾
                                            </button>
                                        </td>

                                        {{-- Status --}}
                                        <td>
                                            <span class="iqms-badge {{ match($status) {
                                                'approved' => 'iqms-badge-green',
                                                'locked' => 'iqms-badge-purple',
                                                'in_review', 'review' => 'iqms-badge-blue',
                                                'submitted' => 'iqms-badge-amber',
                                                'revision_required', 'rejected' => 'iqms-badge-red',
                                                default => 'iqms-badge-gray'
                                            } }}">
                                                {{ \App\Support\Ui\StatusLabel::for($status) }}
                                            </span>
                                        </td>

                                        {{-- Workflow Actions --}}
                                        <td style="text-align: right;">
                                            <div style="display: flex; gap: 4px; justify-content: flex-end; flex-wrap: wrap;">
                                                {{-- Edit Narrative Action --}}
                                                @if (! $isLocked && ! in_array($status, ['submitted', 'in_review', 'approved']))
                                                    <button
                                                        type="button"
                                                        wire:click="openEditResponseModal({{ $response->getKey() }})"
                                                        class="iqms-btn iqms-btn-primary"
                                                        style="padding: 4px 8px; font-size: 10px;"
                                                        title="Sunting narasi butir ini"
                                                    >
                                                        ✏️ Sunting
                                                    </button>
                                                @endif

                                                {{-- Submit Action (Author -> Reviewer) --}}
                                                @if (in_array($status, ['draft', 'revision_required', 'rejected']) && ! $isLocked)
                                                    <button
                                                        type="button"
                                                        wire:click="submitResponse({{ $response->getKey() }})"
                                                        class="iqms-btn iqms-btn-success"
                                                        style="padding: 4px 8px; font-size: 10px;"
                                                        title="Ajukan butir untuk ditinjau"
                                                    >
                                                        📤 Ajukan
                                                    </button>
                                                @endif

                                                {{-- Start Review Action (Reviewer) --}}
                                                @if ($status === 'submitted')
                                                    <button
                                                        type="button"
                                                        wire:click="startReviewResponse({{ $response->getKey() }})"
                                                        class="iqms-btn iqms-btn-primary"
                                                        style="padding: 4px 8px; font-size: 10px;"
                                                        title="Mulai proses telaah / review"
                                                    >
                                                        🔍 Review
                                                    </button>
                                                @endif

                                                {{-- Review in-progress actions --}}
                                                @if ($status === 'in_review')
                                                    <button
                                                        type="button"
                                                        wire:click="openRevisionModal({{ $response->getKey() }})"
                                                        class="iqms-btn iqms-btn-warning"
                                                        style="padding: 4px 8px; font-size: 10px;"
                                                        title="Minta perbaikan narasi/bukti"
                                                    >
                                                        ⚠️ Revisi
                                                    </button>

                                                    <button
                                                        type="button"
                                                        wire:click="approveResponse({{ $response->getKey() }})"
                                                        class="iqms-btn iqms-btn-success"
                                                        style="padding: 4px 8px; font-size: 10px;"
                                                        title="Setujui butir narasi ini"
                                                    >
                                                        ✅ Setujui
                                                    </button>
                                                @endif

                                                {{-- Lock Action (Approver) --}}
                                                @if ($status === 'approved' && ! $isLocked)
                                                    <button
                                                        type="button"
                                                        wire:click="lockResponse({{ $response->getKey() }})"
                                                        class="iqms-btn iqms-btn-secondary"
                                                        style="padding: 4px 8px; font-size: 10px;"
                                                        title="Kunci butir secara resmi"
                                                    >
                                                        🔒 Kunci
                                                    </button>
                                                @endif
                                            </div>
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
                @endif

                {{-- ── TAB 2: Borang & Tabel LKPS ─────────────────────── --}}
                @if ($activeWorkspaceTab === 'lkps')
                    @if ($lkpsTemplates->isEmpty())
                        <div style="padding: 40px; text-align: center; color: #64748b; border: 1px dashed #cbd5e1; border-radius: 12px;">
                            <div style="font-size: 28px; margin-bottom: 8px;">📊</div>
                            <div style="font-weight: 700; font-size: 14px;">Belum ada Template LKPS terdaftar</div>
                            <div style="font-size: 12px; margin-top: 4px;">Versi instrumen akreditasi ini belum memiliki konfigurasi tabel LKPS.</div>
                        </div>
                    @else
                        {{-- LKPS Table Selector Tabs --}}
                        <div class="iqms-tabs" style="border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px;">
                            @foreach ($lkpsTemplates as $tpl)
                                @php
                                    $ds = $tpl->datasets->first();
                                    $isFilled = $ds && ! empty($ds->rows_data) && empty($ds->validation_errors);
                                    $isSel = $selectedLkpsTemplateId === $tpl->getKey();
                                @endphp
                                <button
                                    type="button"
                                    wire:click="selectLkpsTemplate({{ $tpl->getKey() }})"
                                    class="iqms-tab-btn {{ $isSel ? 'is-active' : '' }}"
                                    style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px;"
                                >
                                    <span>{{ $tpl->code }}</span>
                                    @if ($isFilled)
                                        <span style="font-size: 10px;">✅</span>
                                    @elseif ($ds && ! empty($ds->validation_errors))
                                        <span style="font-size: 10px;">⚠️</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>

                        {{-- Current LKPS Table Info & Toolbar --}}
                        @if ($selectedLkpsTemplate)
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; margin-bottom: 16px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px;">
                                <div>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <h3 style="font-size: 14px; font-weight: 700; margin: 0;">{{ $selectedLkpsTemplate->name }}</h3>
                                        <span class="iqms-badge iqms-badge-gray" style="font-family: monospace;">{{ $selectedLkpsTemplate->code }}</span>
                                        @if ($selectedLkpsTemplate->is_required)
                                            <span class="iqms-badge iqms-badge-red">Wajib</span>
                                        @endif
                                    </div>
                                    @if ($selectedLkpsTemplate->description)
                                        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">{{ $selectedLkpsTemplate->description }}</div>
                                    @endif
                                </div>

                                {{-- Action Toolbar --}}
                                <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                                    <button
                                        type="button"
                                        wire:click="addLkpsRow"
                                        class="iqms-btn iqms-btn-secondary"
                                    >
                                        ➕ Tambah Baris
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="openImportModal"
                                        class="iqms-btn iqms-btn-secondary"
                                    >
                                        📥 Import Spreadsheet
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="exportLkpsTemplateCsv"
                                        class="iqms-btn iqms-btn-secondary"
                                        title="Unduh format template CSV kosong"
                                    >
                                        📄 Format CSV
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="saveLkpsDataset"
                                        class="iqms-btn iqms-btn-success"
                                    >
                                        💾 Simpan Perubahan
                                    </button>
                                </div>
                            </div>

                            {{-- Validation Errors Summary Alert --}}
                            @if (! empty($lkpsErrors))
                                <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 10px 14px; margin-bottom: 14px; font-size: 12px; color: #b91c1c; display: flex; align-items: center; gap: 8px;">
                                    <span>⚠️</span>
                                    <span>Terdapat <strong>{{ count($lkpsErrors) }}</strong> baris yang memiliki catatan validasi atau kolom wajib yang belum diisi.</span>
                                </div>
                            @endif

                            {{-- Editable Data Grid --}}
                            <div class="iqms-table-wrap">
                                <table class="iqms-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 45px; text-align: center;">#</th>
                                            @foreach ($selectedLkpsTemplate->columns->sortBy('sort_order') as $col)
                                                <th>
                                                    <div style="display: flex; align-items: center; gap: 4px;">
                                                        <span>{{ $col->label }}</span>
                                                        @if ($col->is_required)
                                                            <span style="color: #ef4444;">*</span>
                                                        @endif
                                                    </div>
                                                    @if ($col->unit)
                                                        <div style="font-size: 9px; font-weight: normal; color: #94a3b8; text-transform: none;">({{ $col->unit }})</div>
                                                    @endif
                                                </th>
                                            @endforeach
                                            <th style="width: 50px; text-align: center;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($lkpsRows as $rowIndex => $row)
                                            <tr>
                                                <td style="text-align: center; font-weight: 600; color: #64748b; vertical-align: middle;">
                                                    {{ $rowIndex + 1 }}
                                                </td>

                                                @foreach ($selectedLkpsTemplate->columns->sortBy('sort_order') as $col)
                                                    @php
                                                        $colKey = $col->column_key;
                                                        $cellError = $lkpsErrors[$rowIndex][$colKey] ?? null;
                                                        $isNumeric = in_array($col->data_type, ['integer', 'decimal', 'number', 'percent', 'ratio'], true);
                                                    @endphp
                                                    <td style="vertical-align: middle;">
                                                        @if (! empty($col->allowed_values) && is_array($col->allowed_values))
                                                            <select
                                                                wire:model="lkpsRows.{{ $rowIndex }}.{{ $colKey }}"
                                                                class="iqms-grid-input {{ $cellError ? 'has-error' : '' }}"
                                                            >
                                                                <option value="">-- Pilih --</option>
                                                                @foreach ($col->allowed_values as $opt)
                                                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                                                @endforeach
                                                            </select>
                                                        @else
                                                            <input
                                                                type="{{ $isNumeric ? 'number' : 'text' }}"
                                                                @if ($col->data_type === 'decimal' || $col->data_type === 'ratio' || $col->data_type === 'percent') step="any" @endif
                                                                wire:model="lkpsRows.{{ $rowIndex }}.{{ $colKey }}"
                                                                placeholder="{{ $col->label }}"
                                                                class="iqms-grid-input {{ $cellError ? 'has-error' : '' }}"
                                                                style="{{ $isNumeric ? 'text-align: right;' : '' }}"
                                                            />
                                                        @endif

                                                        @if ($cellError)
                                                            <div style="font-size: 10px; color: #ef4444; margin-top: 2px;">{{ $cellError }}</div>
                                                        @endif
                                                    </td>
                                                @endforeach

                                                <td style="text-align: center; vertical-align: middle;">
                                                    <button
                                                        type="button"
                                                        wire:click="removeLkpsRow({{ $rowIndex }})"
                                                        style="background: none; border: none; cursor: pointer; color: #ef4444; font-size: 14px; padding: 4px;"
                                                        title="Hapus baris ini"
                                                    >
                                                        🗑️
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ $selectedLkpsTemplate->columns->count() + 2 }}" style="padding: 24px; text-align: center; color: #94a3b8;">
                                                    Belum ada baris data. Klik <strong>"➕ Tambah Baris"</strong> atau <strong>"📥 Import Spreadsheet"</strong> untuk mulai mengisi data.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>

                                    {{-- Calculated Column Totals Footer --}}
                                    @if (! empty($lkpsSummary['column_totals']))
                                        <tfoot>
                                            <tr style="background: #f8fafc; font-weight: 700; border-top: 2px solid #e2e8f0;">
                                                <td style="text-align: center;">∑</td>
                                                @foreach ($selectedLkpsTemplate->columns->sortBy('sort_order') as $col)
                                                    @php
                                                        $total = $lkpsSummary['column_totals'][$col->column_key] ?? null;
                                                    @endphp
                                                    <td style="{{ in_array($col->data_type, ['integer', 'decimal', 'number', 'percent', 'ratio']) ? 'text-align: right;' : '' }}">
                                                        @if ($total !== null)
                                                            {{ $col->data_type === 'integer' ? number_format($total) : number_format($total, $col->decimal_scale ?: 2) }}
                                                            @if ($col->unit) <span style="font-size: 10px; font-weight: normal; color: #64748b;">{{ $col->unit }}</span> @endif
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                @endforeach
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>

                            {{-- Grid Quick Summary --}}
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px; font-size: 11px; color: #64748b;">
                                <div>
                                    Total <strong>{{ count($lkpsRows) }}</strong> baris data pada tabel ini.
                                </div>
                                <div>
                                    <button
                                        type="button"
                                        wire:click="saveLkpsDataset"
                                        class="iqms-btn iqms-btn-success"
                                        style="padding: 6px 12px;"
                                    >
                                        💾 Simpan & Validasi
                                    </button>
                                </div>
                            </div>
                        @endif
                    @endif
                @endif
            </div>
        @endif

        {{-- ── 4. Modal: Edit Narasi LED ────────────────────────────── --}}
        @if ($showEditResponseModal)
            <div class="iqms-modal-backdrop" wire:click.self="closeEditResponseModal">
                <div class="iqms-modal-content">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span class="iqms-badge iqms-badge-blue" style="font-family: monospace;">{{ $editingResponseKey }}</span>
                                <h3 style="font-size: 15px; font-weight: 700; margin: 0;">{{ $editingResponseTitle }}</h3>
                            </div>
                            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                Status: <strong>{{ strtoupper($editingResponseStatus) }}</strong> • Revisi aktif: <strong>Rev #{{ $editingRevisionNo }}</strong>
                            </div>
                        </div>
                        <button type="button" wire:click="closeEditResponseModal" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #94a3b8;">✕</button>
                    </div>

                    {{-- Guidance / Requirement Panel --}}
                    @if (filled($editingResponseRequirement) || filled($editingResponseGuidance))
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 16px; font-size: 11px; color: #475569; line-height: 1.5;">
                            @if (filled($editingResponseRequirement))
                                <div style="margin-bottom: 6px;"><strong>Syarat / Kebutuhan:</strong> {{ $editingResponseRequirement }}</div>
                            @endif
                            @if (filled($editingResponseGuidance))
                                <div><strong>Panduan Pengisian:</strong> {{ $editingResponseGuidance }}</div>
                            @endif
                        </div>
                    @endif

                    {{-- Narrative Textarea Editor --}}
                    <div style="margin-bottom: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <label style="font-size: 12px; font-weight: 700;">Narasi Respons LED</label>
                            <span style="font-size: 11px; color: #94a3b8;">{{ strlen($editingResponseText) }} karakter</span>
                        </div>
                        <textarea
                            wire:model="editingResponseText"
                            rows="8"
                            placeholder="Tuliskan narasi penjelasan capaian standar, siklus PPEPP, analisis evaluasi diri, dan tindak lanjut..."
                            style="width: 100%; font-size: 12px; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; line-height: 1.5; outline: none; background: #ffffff; color: #0f172a;"
                        ></textarea>
                    </div>

                    {{-- Modal Footer Actions --}}
                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 14px;">
                        <button
                            type="button"
                            wire:click="closeEditResponseModal"
                            class="iqms-btn iqms-btn-secondary"
                        >
                            Tutup
                        </button>
                        <button
                            type="button"
                            wire:click="saveResponseNarrative"
                            class="iqms-btn iqms-btn-success"
                        >
                            💾 Simpan Perubahan Narasi
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ── 5. Modal: Minta Catatan Revisi ────────────────────────── --}}
        @if ($showRevisionModal)
            <div class="iqms-modal-backdrop" wire:click.self="closeRevisionModal">
                <div class="iqms-modal-content" style="max-width: 550px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px;">
                        <div>
                            <h3 style="font-size: 15px; font-weight: 700; margin: 0; color: #b45309;">⚠️ Permintaan Revisi: {{ $revisionResponseKey }}</h3>
                            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Tuliskan catatan perbaikan atau bukti tambahan yang diperlukan</div>
                        </div>
                        <button type="button" wire:click="closeRevisionModal" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #94a3b8;">✕</button>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 6px;">Catatan / Alasan Revisi <span style="color: #ef4444;">*</span></label>
                        <textarea
                            wire:model="revisionNotes"
                            rows="4"
                            placeholder="Contoh: Narasi pada poin analisis belum menjelaskan tindak lanjut hasil AMI tahun 2025. Mohon lampirkan dokumen notula RTM..."
                            style="width: 100%; font-size: 12px; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; line-height: 1.4; outline: none; background: #ffffff; color: #0f172a;"
                        ></textarea>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 8px; border-top: 1px solid #e2e8f0; padding-top: 14px;">
                        <button
                            type="button"
                            wire:click="closeRevisionModal"
                            class="iqms-btn iqms-btn-secondary"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            wire:click="submitRevisionRequest"
                            class="iqms-btn iqms-btn-warning"
                        >
                            ⚠️ Kirim Permintaan Revisi
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ── 6. Modal: Riwayat Versi & Audit Trail ────────────────── --}}
        @if ($showHistoryModal)
            <div class="iqms-modal-backdrop" wire:click.self="closeRevisionHistoryModal">
                <div class="iqms-modal-content" style="max-width: 650px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px;">
                        <div>
                            <h3 style="font-size: 15px; font-weight: 700; margin: 0;">📜 Riwayat Versi: {{ $historyResponseKey }}</h3>
                            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Jejak audit seluruh perubahan narasi dan status butir</div>
                        </div>
                        <button type="button" wire:click="closeRevisionHistoryModal" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #94a3b8;">✕</button>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 12px; max-height: 450px; overflow-y: auto; padding-right: 4px;">
                        @forelse ($historyRevisions as $rev)
                            <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; background: #f8fafc;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <span class="iqms-badge iqms-badge-blue" style="font-weight: 700;">Rev #{{ $rev['revision_no'] }}</span>
                                        <span class="iqms-badge iqms-badge-gray">{{ strtoupper((string) $rev['status']) }}</span>
                                    </div>
                                    <div style="font-size: 11px; color: #64748b;">
                                        👤 {{ $rev['changed_by_name'] }} • 📅 {{ $rev['changed_at'] }}
                                    </div>
                                </div>
                                @if (filled($rev['change_reason']))
                                    <div style="font-size: 11px; color: #b45309; margin-bottom: 6px; font-style: italic;">
                                        Catatan: "{{ $rev['change_reason'] }}"
                                    </div>
                                @endif
                                <div style="font-size: 11px; color: #334155; line-height: 1.4; background: #ffffff; padding: 8px; border-radius: 6px; border: 1px solid #f1f5f9;">
                                    {{ $rev['response_text'] ?: '— (Tidak ada teks narasi) —' }}
                                </div>
                            </div>
                        @empty
                            <div style="padding: 24px; text-align: center; color: #94a3b8;">
                                Belum ada riwayat revisi yang tercatat untuk butir ini.
                            </div>
                        @endforelse
                    </div>

                    <div style="display: flex; justify-content: flex-end; border-top: 1px solid #e2e8f0; padding-top: 14px; margin-top: 14px;">
                        <button
                            type="button"
                            wire:click="closeRevisionHistoryModal"
                            class="iqms-btn iqms-btn-secondary"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ── 7. Modal: Tautkan Dokumen Bukti (Citation) ───────────── --}}
        @if ($showEvidenceModal && $activeResponseForEvidence)
            <div class="iqms-modal-backdrop" wire:click.self="closeEvidenceLinkModal">
                <div class="iqms-modal-content" style="max-width: 650px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px;">
                        <div>
                            <h3 style="font-size: 15px; font-weight: 700; margin: 0;">📎 Tautkan Bukti & Sitasi: {{ $evidenceModalResponseKey }}</h3>
                            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Kelola tautan dokumen bukti dari Evidence Center dan catatan sitasi halaman</div>
                        </div>
                        <button type="button" wire:click="closeEvidenceLinkModal" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #94a3b8;">✕</button>
                    </div>

                    {{-- Attached Evidences List --}}
                    <div style="margin-bottom: 18px;">
                        <div style="font-size: 12px; font-weight: 700; margin-bottom: 8px;">
                            Dokumen Bukti Tertaut ({{ $activeResponseForEvidence->evidenceLinks->count() }})
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 8px; max-height: 200px; overflow-y: auto;">
                            @forelse ($activeResponseForEvidence->evidenceLinks as $link)
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc;">
                                    <div>
                                        <div style="font-weight: 600; font-size: 12px; color: #0f172a;">
                                            {{ $link->evidence?->title ?? 'Dokumen Bukti' }}
                                        </div>
                                        <div style="font-size: 10px; color: #64748b; margin-top: 2px; display: flex; gap: 8px;">
                                            <span>Kode: <code>{{ $link->evidence?->code ?? '-' }}</code></span>
                                            <span>•</span>
                                            <span>Hal/Bagian: <strong>{{ $link->citation_page ?: '1' }}</strong></span>
                                            <span>•</span>
                                            <span style="color: {{ $link->evidence?->status === 'verified' ? '#16a34a' : '#d97706' }}; font-weight: 600;">
                                                {{ $link->evidence?->status === 'verified' ? 'Terverifikasi' : 'Draft' }}
                                            </span>
                                        </div>
                                        @if (filled($link->citation_note))
                                            <div style="font-size: 10px; color: #475569; font-style: italic; margin-top: 2px;">
                                                "{{ $link->citation_note }}"
                                            </div>
                                        @endif
                                    </div>
                                    <button
                                        type="button"
                                        wire:click="detachEvidenceLink({{ $link->getKey() }})"
                                        style="background: none; border: none; cursor: pointer; color: #ef4444; font-size: 14px; padding: 4px;"
                                        title="Lepas tautan bukti ini"
                                    >
                                        🗑️
                                    </button>
                                </div>
                            @empty
                                <div style="padding: 16px; text-align: center; color: #94a3b8; border: 1px dashed #cbd5e1; border-radius: 8px; font-size: 11px;">
                                    Belum ada dokumen bukti yang ditautkan ke butir ini.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Form Tautkan Bukti Baru --}}
                    <div style="border-top: 1px solid #e2e8f0; padding-top: 14px;">
                        <div style="font-size: 12px; font-weight: 700; margin-bottom: 10px;">➕ Tautkan Dokumen Bukti Baru</div>

                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <div>
                                <label style="font-size: 11px; font-weight: 600; display: block; margin-bottom: 4px;">Pilih Dokumen dari Evidence Center <span style="color: #ef4444;">*</span></label>
                                <select
                                    wire:model="selectedEvidenceId"
                                    class="iqms-grid-input"
                                >
                                    <option value="">-- Pilih Dokumen Bukti --</option>
                                    @foreach ($availableEvidences as $ev)
                                        <option value="{{ $ev->getKey() }}">
                                            [{{ $ev->code }}] {{ $ev->title }} ({{ $ev->status === 'verified' ? 'Terverifikasi' : 'Draft' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 10px;">
                                <div>
                                    <label style="font-size: 11px; font-weight: 600; display: block; margin-bottom: 4px;">Nomor Halaman / Bagian</label>
                                    <input
                                        type="number"
                                        wire:model="citationPage"
                                        placeholder="Contoh: 12"
                                        class="iqms-grid-input"
                                    />
                                </div>
                                <div>
                                    <label style="font-size: 11px; font-weight: 600; display: block; margin-bottom: 4px;">Catatan Sitasi / Relevansi</label>
                                    <input
                                        type="text"
                                        wire:model="citationNote"
                                        placeholder="Contoh: Lihat tabel 3.1 realisasi capaian standar"
                                        class="iqms-grid-input"
                                    />
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px;">
                            <button
                                type="button"
                                wire:click="closeEvidenceLinkModal"
                                class="iqms-btn iqms-btn-secondary"
                            >
                                Tutup
                            </button>
                            <button
                                type="button"
                                wire:click="attachEvidenceLink"
                                class="iqms-btn iqms-btn-primary"
                                @if (! $selectedEvidenceId) disabled style="opacity: 0.5; cursor: not-allowed;" @endif
                            >
                                📎 Tambahkan Tautan Bukti
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ── 8. Modal: Spreadsheet Import LKPS ────────────────────── --}}
        @if ($showImportModal && $selectedLkpsTemplate)
            <div class="iqms-modal-backdrop" wire:click.self="closeImportModal">
                <div class="iqms-modal-content">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px;">
                        <div>
                            <h3 style="font-size: 16px; font-weight: 700; margin: 0;">📥 Import Spreadsheet ke {{ $selectedLkpsTemplate->name }}</h3>
                            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Mendukung format file CSV dan Excel (.xlsx / .xls)</div>
                        </div>
                        <button type="button" wire:click="closeImportModal" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #94a3b8;">✕</button>
                    </div>

                    {{-- Upload Area --}}
                    <div style="margin-bottom: 16px;">
                        <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 6px;">Pilih File Spreadsheet</label>
                        <input
                            type="file"
                            wire:model="importFile"
                            accept=".csv, .xlsx, .xls, .txt"
                            style="width: 100%; font-size: 12px; padding: 10px; border: 1px dashed #cbd5e1; border-radius: 8px; background: #f8fafc;"
                        />
                        <div wire:loading wire:target="importFile" style="font-size: 11px; color: #0284c7; margin-top: 6px;">
                            ⏳ Memproses dan memvalidasi file spreadsheet...
                        </div>
                    </div>

                    {{-- Import Reconciliation & Preview --}}
                    @if ($importPreview)
                        <div style="margin-bottom: 16px;">
                            <div style="font-size: 12px; font-weight: 700; margin-bottom: 8px; display: flex; justify-content: space-between;">
                                <span>Hasil Pencocokan Kolom ({{ count($importPreview['preview_rows']) }} dari {{ $importPreview['raw_rows_count'] }} baris terbaca)</span>
                                <span class="iqms-badge {{ $importPreview['validation']['has_errors'] ? 'iqms-badge-amber' : 'iqms-badge-green' }}">
                                    {{ $importPreview['validation']['valid_rows'] }} / {{ count($importPreview['validation']['rows']) }} baris valid
                                </span>
                            </div>

                            {{-- Preview Table --}}
                            <div class="iqms-table-wrap" style="max-height: 220px; margin-top: 0;">
                                <table class="iqms-table" style="font-size: 11px;">
                                    <thead>
                                        <tr>
                                            <th style="width: 30px;">#</th>
                                            @foreach ($selectedLkpsTemplate->columns->sortBy('sort_order') as $col)
                                                <th>{{ $col->label }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($importPreview['preview_rows'] as $pIdx => $pRow)
                                            <tr>
                                                <td style="color: #64748b;">{{ $pIdx + 1 }}</td>
                                                @foreach ($selectedLkpsTemplate->columns->sortBy('sort_order') as $col)
                                                    <td>{{ $pRow[$col->column_key] ?? '—' }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- Modal Footer --}}
                    <div style="display: flex; justify-content: flex-end; gap: 8px; border-top: 1px solid #e2e8f0; padding-top: 14px;">
                        <button
                            type="button"
                            wire:click="closeImportModal"
                            class="iqms-btn iqms-btn-secondary"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            wire:click="commitImport"
                            class="iqms-btn iqms-btn-success"
                            @if (! $importPreview || empty($importPreview['validation']['rows'])) disabled style="opacity: 0.5; cursor: not-allowed;" @endif
                        >
                            ✓ Terapkan ke Tabel LKPS
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
