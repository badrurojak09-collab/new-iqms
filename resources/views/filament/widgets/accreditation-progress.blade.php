<x-filament-widgets::widget>
    {{-- Scoped CSS matching Workspace LKE/LED widget styling --}}
    <style>
        .iqms-dash-grid-4 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }

        .iqms-dash-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        :is(.dark) .iqms-dash-card {
            background: #111827;
            border-color: #1f2937;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .iqms-dash-metric-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .iqms-dash-metric-title {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
        }
        :is(.dark) .iqms-dash-metric-title {
            color: #94a3b8;
        }
        .iqms-dash-metric-value {
            font-size: 26px;
            font-weight: 700;
            line-height: 1.2;
            color: #0f172a;
        }
        :is(.dark) .iqms-dash-metric-value {
            color: #ffffff;
        }
        .iqms-dash-metric-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .iqms-dash-progress-bg {
            width: 100%;
            height: 6px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
            margin-top: 8px;
        }
        :is(.dark) .iqms-dash-progress-bg {
            background: #374151;
        }
        .iqms-dash-progress-fill {
            height: 100%;
            background: #10b981;
            border-radius: 999px;
            transition: width 0.3s ease;
        }
        .iqms-dash-progress-fill-blue {
            background: #0284c7;
        }
        .iqms-dash-progress-fill-purple {
            background: #9333ea;
        }
        .iqms-dash-progress-fill-amber {
            background: #d97706;
        }
    </style>

    @if (! $hasTenant)
        <div class="iqms-dash-card" style="padding: 24px; text-align: center; color: #64748b;">
            Pilih Perguruan Tinggi aktif untuk melihat kemajuan LED & LKPS.
        </div>
    @else
        <div class="iqms-dash-grid-4">
            {{-- Card 1: Progress LED --}}
            <div class="iqms-dash-card">
                <div>
                    <div class="iqms-dash-metric-header">
                        <div>
                            <div class="iqms-dash-metric-title">Progress LED</div>
                            <div class="iqms-dash-metric-value">{{ number_format((float) ($metrics['led_progress'] ?? 0), 1) }}%</div>
                        </div>
                        <div class="iqms-dash-metric-icon" style="background: #e0f2fe; color: #0284c7;">
                            📝
                        </div>
                    </div>
                </div>
                <div>
                    <div class="iqms-dash-progress-bg">
                        <div class="iqms-dash-progress-fill-blue" style="width: {{ min(100, (float) ($metrics['led_progress'] ?? 0)) }}%; height: 100%;"></div>
                    </div>
                    <div style="font-size: 11px; color: #64748b; margin-top: 6px;">
                        {{ $metrics['sections'] ?? 0 }} section akreditasi
                    </div>
                </div>
            </div>

            {{-- Card 2: Progress LKPS --}}
            <div class="iqms-dash-card">
                <div>
                    <div class="iqms-dash-metric-header">
                        <div>
                            <div class="iqms-dash-metric-title">Progress LKPS</div>
                            <div class="iqms-dash-metric-value">{{ number_format((float) ($metrics['lkps_progress'] ?? 0), 1) }}%</div>
                        </div>
                        <div class="iqms-dash-metric-icon" style="background: #dcfce7; color: #16a34a;">
                            📊
                        </div>
                    </div>
                </div>
                <div>
                    <div class="iqms-dash-progress-bg">
                        <div class="iqms-dash-progress-fill" style="width: {{ min(100, (float) ($metrics['lkps_progress'] ?? 0)) }}%;"></div>
                    </div>
                    <div style="font-size: 11px; color: #64748b; margin-top: 6px;">
                        Readiness section LKPS
                    </div>
                </div>
            </div>

            {{-- Card 3: Response Completion --}}
            <div class="iqms-dash-card">
                <div>
                    <div class="iqms-dash-metric-header">
                        <div>
                            <div class="iqms-dash-metric-title">Response Completion</div>
                            <div class="iqms-dash-metric-value">{{ number_format((float) ($metrics['response_completion_rate'] ?? 0), 1) }}%</div>
                        </div>
                        <div class="iqms-dash-metric-icon" style="background: #f3e8ff; color: #9333ea;">
                            ✅
                        </div>
                    </div>
                </div>
                <div>
                    <div class="iqms-dash-progress-bg">
                        <div class="iqms-dash-progress-fill-purple" style="width: {{ min(100, (float) ($metrics['response_completion_rate'] ?? 0)) }}%; height: 100%;"></div>
                    </div>
                    <div style="font-size: 11px; color: #64748b; margin-top: 6px;">
                        Respons disubmit / diverifikasi
                    </div>
                </div>
            </div>

            {{-- Card 4: Readiness Item --}}
            <div class="iqms-dash-card">
                <div>
                    <div class="iqms-dash-metric-header">
                        <div>
                            <div class="iqms-dash-metric-title">Readiness Item</div>
                            <div class="iqms-dash-metric-value">{{ number_format((float) ($metrics['readiness_item_rate'] ?? 0), 1) }}%</div>
                        </div>
                        <div class="iqms-dash-metric-icon" style="background: #fef3c7; color: #d97706;">
                            📌
                        </div>
                    </div>
                </div>
                <div>
                    <div class="iqms-dash-progress-bg">
                        <div class="iqms-dash-progress-fill-amber" style="width: {{ min(100, (float) ($metrics['readiness_item_rate'] ?? 0)) }}%; height: 100%;"></div>
                    </div>
                    <div style="font-size: 11px; color: #64748b; margin-top: 6px;">
                        {{ $metrics['mapping_count'] ?? 0 }} mapping instrumen
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament-widgets::widget>
