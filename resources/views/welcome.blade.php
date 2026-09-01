<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="I-QMS - Platform Penjaminan Mutu & Akreditasi Terintegrasi Perguruan Tinggi (SPMI, AMI, dan Akreditasi BAN-PT & Multi-LAM).">
    <title>{{ config('app.name', 'I-QMS') }} - Sistem Penjaminan Mutu & Akreditasi Terintegrasi</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN for instant, pristine light theme rendering -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        display: ['"Outfit"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f7ff',
                            100: '#e0effe',
                            200: '#bae0fd',
                            300: '#7cc5fb',
                            400: '#38a5f6',
                            500: '#0e87e9',
                            600: '#026bc7',
                            700: '#0355a1',
                            800: '#074884',
                            900: '#0c3d6e',
                            950: '#082749',
                        },
                        spmi: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                        },
                        ami: {
                            50: '#faf5ff',
                            100: '#f3e8ff',
                            200: '#e9d5ff',
                            600: '#7c3aed',
                            700: '#6d28d9',
                            800: '#5b21b6',
                        },
                        akreditasi: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #ffffff;
            color: #1e293b;
            overflow-x: hidden;
        }

        .font-display {
            font-family: 'Outfit', sans-serif;
        }

        /* Light Dignified Glass / Card styles */
        .academic-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .academic-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
            transform: translateY(-2px);
        }

        .light-nav {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid #e2e8f0;
        }

        .ambient-bg {
            background: radial-gradient(circle at 10% 10%, rgba(224, 242, 254, 0.6) 0%, transparent 40%),
                        radial-gradient(circle at 90% 15%, rgba(243, 232, 255, 0.5) 0%, transparent 45%),
                        radial-gradient(circle at 50% 90%, rgba(220, 252, 231, 0.5) 0%, transparent 50%);
        }

        .subtle-dot-grid {
            background-size: 28px 28px;
            background-image: radial-gradient(#e2e8f0 1.2px, transparent 1.2px);
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f8fafc;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="antialiased selection:bg-brand-600 selection:text-white min-h-screen flex flex-col justify-between bg-white text-slate-800 relative">

    <!-- Subtle Ambient Glow Background -->
    <div class="fixed inset-0 pointer-events-none z-0 ambient-bg"></div>
    <div class="fixed inset-0 pointer-events-none z-0 subtle-dot-grid opacity-60"></div>

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-50 light-nav transition-all duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo & Brand -->
                <a href="#" class="flex items-center gap-3.5 group">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-brand-700 via-brand-600 to-sky-500 p-[1.5px] shadow-md shadow-brand-700/10 group-hover:shadow-brand-700/25 transition-all duration-300">
                        <div class="w-full h-full bg-white rounded-[10px] flex items-center justify-center">
                            <svg class="w-6 h-6 text-brand-700 group-hover:scale-105 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                <path d="m9 12 2 2 4-4"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-display font-black text-xl tracking-tight text-slate-900 group-hover:text-brand-700 transition-colors">I-QMS</span>
                            <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full bg-brand-50 text-brand-700 border border-brand-200">PT Enterprise</span>
                        </div>
                        <p class="text-xs text-slate-500 hidden sm:block">SPMI &bull; AMI &bull; Akreditasi Terintegrasi</p>
                    </div>
                </a>

                <!-- Nav Menu Desktop -->
                <nav class="hidden lg:flex items-center gap-8 text-sm font-semibold text-slate-600">
                    <a href="#triad" class="hover:text-brand-700 transition-colors">Trinitas Mutu</a>
                    <a href="#ppepp" class="hover:text-brand-700 transition-colors">Siklus PPEPP</a>
                    <a href="#simulator" class="hover:text-brand-700 transition-colors flex items-center gap-1.5">
                        <span>Simulasi Kesiapan</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    </a>
                    <a href="#features" class="hover:text-brand-700 transition-colors">Fitur Unggulan</a>
                    <a href="#roles" class="hover:text-brand-700 transition-colors">Peran Pengguna</a>
                    <a href="#faq" class="hover:text-brand-700 transition-colors">FAQ</a>
                </nav>

                <!-- Action CTA Buttons -->
                <div class="flex items-center gap-3">
                    <a href="{{ url('/admin') }}" 
                       class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm text-white bg-slate-900 hover:bg-brand-700 shadow-sm hover:shadow-md transition-all duration-200">
                        <svg class="w-4 h-4 text-sky-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        <span>Masuk Portal</span>
                    </a>

                    <!-- Mobile Menu Button -->
                    <button id="mobileMenuBtn" class="lg:hidden p-2.5 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 hover:text-slate-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div id="mobileMenu" class="hidden lg:hidden border-t border-slate-200 bg-white/98 px-6 py-6 space-y-4 shadow-xl">
            <a href="#triad" class="block text-slate-700 font-medium hover:text-brand-700 py-1">Trinitas Mutu (SPMI, AMI, Akreditasi)</a>
            <a href="#ppepp" class="block text-slate-700 font-medium hover:text-brand-700 py-1">Siklus PPEPP</a>
            <a href="#simulator" class="block text-slate-700 font-medium hover:text-brand-700 py-1">Simulasi Kesiapan</a>
            <a href="#features" class="block text-slate-700 font-medium hover:text-brand-700 py-1">Fitur Unggulan</a>
            <a href="#roles" class="block text-slate-700 font-medium hover:text-brand-700 py-1">Peran Pengguna</a>
            <a href="#faq" class="block text-slate-700 font-medium hover:text-brand-700 py-1">FAQ & Regulasi</a>
            <div class="pt-4 border-t border-slate-200">
                <a href="{{ url('/admin') }}" class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-slate-900 hover:bg-brand-700 font-semibold text-white">
                    Masuk Portal Sistem
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="relative z-10 flex-grow">

        <!-- ================= HERO SECTION ================= -->
        <section class="relative pt-12 pb-16 md:pt-20 md:pb-28 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Tagline Badge -->
                <div class="flex justify-center mb-6">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white border border-slate-200 text-xs sm:text-sm font-semibold text-slate-700 shadow-sm">
                        <span class="flex h-2 w-2 rounded-full bg-brand-600"></span>
                        <span class="text-brand-700">Regulasi SN-Dikti & BAN-PT / LAM Ready</span>
                        <span class="text-slate-300">&bull;</span>
                        <span class="text-slate-600">Continuous Quality Improvement</span>
                    </div>
                </div>

                <!-- Main Title & Subtitle -->
                <div class="text-center max-w-4xl mx-auto">
                    <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-[1.18] mb-6">
                        Satu Ekosistem Mutu Terpadu: <br class="hidden sm:inline">
                        <span class="text-sky-700">SPMI</span>, 
                        <span class="text-purple-700">AMI</span>, & 
                        <span class="text-emerald-700">Akreditasi</span>
                    </h1>
                    <p class="text-lg sm:text-xl text-slate-600 leading-relaxed mb-10 max-w-3xl mx-auto font-normal">
                        Hubungkan hulu penetapan standar mutu, pengujian audit objektif, hingga pemenuhan borang LED & LKPS dalam satu sistem terpadu. Bebas duplikasi dokumen, siap audit kapan saja.
                    </p>

                    <!-- Hero CTA Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-16">
                        <a href="{{ url('/admin') }}" 
                           class="w-full sm:w-auto inline-flex items-center justify-center gap-3 px-8 py-4 rounded-xl font-bold text-white bg-brand-700 hover:bg-brand-800 shadow-lg shadow-brand-700/20 hover:-translate-y-0.5 transition-all duration-200 text-base">
                            <span>Akses Portal SPMI & AMI</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>
                        <a href="#triad" 
                           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-4 rounded-xl font-semibold text-slate-700 hover:text-slate-900 bg-white hover:bg-slate-50 border border-slate-300 shadow-sm transition-all duration-200 text-base">
                            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Eksplorasi Alur Sinergi</span>
                        </a>
                    </div>
                </div>

                <!-- Hero Interactive Ecosystem Visual Display (Clean Light Theme) -->
                <div class="relative max-w-5xl mx-auto">
                    <div class="academic-card rounded-2xl p-6 sm:p-8 bg-white border-slate-200/90 shadow-xl overflow-hidden">
                        
                        <!-- Top Bar Mockup -->
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-rose-400"></span>
                                <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                                <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
                                <span class="ml-2 text-xs font-mono text-slate-500 font-medium">i-qms.live-ecosystem.state</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-2 h-2 rounded-full bg-emerald-600 mr-1.5 animate-pulse"></span>
                                    Siklus PPEPP Aktif (Tersinkronisasi)
                                </span>
                            </div>
                        </div>

                        <!-- 3-Pillar Interactive Live Sync Pipeline -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative">
                            
                            <!-- Card 1: SPMI -->
                            <div class="relative rounded-xl p-5 bg-sky-50/50 border border-sky-200 shadow-sm">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-9 h-9 rounded-lg bg-sky-100 border border-sky-300 flex items-center justify-center text-sky-800 font-bold">
                                            01
                                        </div>
                                        <div>
                                            <h2 class="font-bold text-slate-900 text-sm">SPMI (Hulu)</h2>
                                            <p class="text-[11px] text-sky-700 font-semibold">Penetapan & Realisasi</p>
                                        </div>
                                    </div>
                                    <span class="text-[10px] px-2 py-0.5 rounded font-semibold bg-sky-100 text-sky-800 border border-sky-200">SN-Dikti</span>
                                </div>
                                <div class="space-y-2.5 text-xs text-slate-700 pt-2 border-t border-sky-200/70">
                                    <div class="flex justify-between items-center bg-white p-2 rounded-lg border border-sky-100 shadow-2xs">
                                        <span class="text-slate-500">Standar & IKU/IKT:</span>
                                        <span class="font-bold text-sky-800">45 Indikator</span>
                                    </div>
                                    <div class="flex justify-between items-center bg-white p-2 rounded-lg border border-sky-100 shadow-2xs">
                                        <span class="text-slate-500">Capaian Target:</span>
                                        <span class="font-bold text-emerald-700">92.4% Tercapai</span>
                                    </div>
                                    <div class="flex justify-between items-center bg-white p-2 rounded-lg border border-sky-100 shadow-2xs">
                                        <span class="text-slate-500">Polymorphic Evidence:</span>
                                        <span class="font-mono text-[11px] font-semibold text-slate-700">128 Dokumen Sah</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 2: AMI -->
                            <div class="relative rounded-xl p-5 bg-purple-50/50 border border-purple-200 shadow-sm">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-9 h-9 rounded-lg bg-purple-100 border border-purple-300 flex items-center justify-center text-purple-800 font-bold">
                                            02
                                        </div>
                                        <div>
                                            <h2 class="font-bold text-slate-900 text-sm">AMI (Verifikasi)</h2>
                                            <p class="text-[11px] text-purple-700 font-semibold">Audit Mutu Internal</p>
                                        </div>
                                    </div>
                                    <span class="text-[10px] px-2 py-0.5 rounded font-semibold bg-purple-100 text-purple-800 border border-purple-200">Auditor</span>
                                </div>
                                <div class="space-y-2.5 text-xs text-slate-700 pt-2 border-t border-purple-200/70">
                                    <div class="flex justify-between items-center bg-white p-2 rounded-lg border border-purple-100 shadow-2xs">
                                        <span class="text-slate-500">Siklus Periode:</span>
                                        <span class="font-bold text-purple-800">Genap 2025/2026</span>
                                    </div>
                                    <div class="flex justify-between items-center bg-white p-2 rounded-lg border border-purple-100 shadow-2xs">
                                        <span class="text-slate-500">Temuan KTS / Ob:</span>
                                        <span class="font-bold text-amber-700">3 KTS (Ada RTL)</span>
                                    </div>
                                    <div class="flex justify-between items-center bg-white p-2 rounded-lg border border-purple-100 shadow-2xs">
                                        <span class="text-slate-500">Status RTM:</span>
                                        <span class="font-bold text-emerald-700">Resolved & Selesai</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 3: Akreditasi -->
                            <div class="relative rounded-xl p-5 bg-emerald-50/50 border border-emerald-200 shadow-sm">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-9 h-9 rounded-lg bg-emerald-100 border border-emerald-300 flex items-center justify-center text-emerald-800 font-bold">
                                            03
                                        </div>
                                        <div>
                                            <h2 class="font-bold text-slate-900 text-sm">Akreditasi (Muara)</h2>
                                            <p class="text-[11px] text-emerald-700 font-semibold">BAN-PT & Multi-LAM</p>
                                        </div>
                                    </div>
                                    <span class="text-[10px] px-2 py-0.5 rounded font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">SPME</span>
                                </div>
                                <div class="space-y-2.5 text-xs text-slate-700 pt-2 border-t border-emerald-200/70">
                                    <div class="flex justify-between items-center bg-white p-2 rounded-lg border border-emerald-100 shadow-2xs">
                                        <span class="text-slate-500">Mapping Kriteria:</span>
                                        <span class="font-bold text-emerald-800">100% Terpetakan</span>
                                    </div>
                                    <div class="flex justify-between items-center bg-white p-2 rounded-lg border border-emerald-100 shadow-2xs">
                                        <span class="text-slate-500">Readiness Score:</span>
                                        <span class="font-extrabold text-amber-700">3.78 / 4.00</span>
                                    </div>
                                    <div class="flex justify-between items-center bg-white p-2 rounded-lg border border-emerald-100 shadow-2xs">
                                        <span class="text-slate-500">Prediksi Kualifikasi:</span>
                                        <span class="font-extrabold text-emerald-700 uppercase tracking-wide">Unggul ★★★</span>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Footer Pipeline Info -->
                        <div class="mt-6 pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-3">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                <span>Single Source of Truth: Eviden SPMI & AMI otomatis mengalir ke Butir Penilaian Akreditasi</span>
                            </div>
                            <span class="text-slate-500 font-mono text-[11px] font-medium bg-slate-100 px-2.5 py-0.5 rounded">Immutable Snapshot Active</span>
                        </div>

                    </div>
                </div>

                <!-- Trust Metrics Strip -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 max-w-5xl mx-auto mt-10">
                    <div class="academic-card p-4 sm:p-5 rounded-xl text-center bg-white">
                        <div class="font-display text-2xl sm:text-3xl font-extrabold text-sky-700 mb-1">PPEPP</div>
                        <div class="text-xs font-semibold text-slate-500">Siklus Mutu Berkelanjutan</div>
                    </div>
                    <div class="academic-card p-4 sm:p-5 rounded-xl text-center bg-white">
                        <div class="font-display text-2xl sm:text-3xl font-extrabold text-purple-700 mb-1">100% Link</div>
                        <div class="text-xs font-semibold text-slate-500">Polymorphic Evidence Center</div>
                    </div>
                    <div class="academic-card p-4 sm:p-5 rounded-xl text-center bg-white">
                        <div class="font-display text-2xl sm:text-3xl font-extrabold text-emerald-700 mb-1">Multi-LAM</div>
                        <div class="text-xs font-semibold text-slate-500">BAN-PT & Seluruh LAM-PT</div>
                    </div>
                    <div class="academic-card p-4 sm:p-5 rounded-xl text-center bg-white">
                        <div class="font-display text-2xl sm:text-3xl font-extrabold text-amber-700 mb-1">Realtime</div>
                        <div class="text-xs font-semibold text-slate-500">Simulasi Skor Akreditasi</div>
                    </div>
                </div>

            </div>
        </section>


        <!-- ================= THE TRIAD PHILOSOPHY SECTION ================= -->
        <section id="triad" class="py-16 md:py-24 relative border-t border-slate-200/80 bg-slate-50/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <span class="text-xs font-bold uppercase tracking-wider text-brand-700 px-3 py-1 rounded-full bg-brand-50 border border-brand-200 inline-block mb-3">
                        Filosofi Trinitas Mutu
                    </span>
                    <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mb-4">
                        Makna & Keterkaitan 3 Pilar Mutu
                    </h2>
                    <p class="text-slate-600 text-base sm:text-lg">
                        Penjaminan mutu bukan sekadar pengisian dokumen terpisah, melainkan sebuah tata kelola komprehensif dari hulu ke muara.
                    </p>
                </div>

                <!-- 3 Pillars Cards with In-depth Meaning -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-14">
                    
                    <!-- Pillar 1: SPMI -->
                    <div class="academic-card rounded-2xl p-8 bg-white border-slate-200 relative group">
                        <div class="w-14 h-14 rounded-xl bg-sky-50 border border-sky-200 flex items-center justify-center text-sky-700 mb-6">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>

                        <div class="inline-block text-xs font-bold text-sky-700 uppercase tracking-wider mb-2">Hulu Penjaminan Mutu</div>
                        <h3 class="font-display text-2xl font-bold text-slate-900 mb-3">1. SPMI</h3>
                        <p class="text-sm text-slate-600 leading-relaxed mb-6">
                            Fondasi internal tempat Perguruan Tinggi merumuskan komitmen mutunya. Berisi penetapan standar mutu, IKU (Indikator Kinerja Utama), IKT (Indikator Kinerja Tambahan), target tahunan, serta pencatatan realisasi capaian setiap Program Studi & Unit.
                        </p>

                        <div class="space-y-3 border-t border-slate-100 pt-5 text-xs text-slate-700 font-medium">
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-sky-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Standar Pendidikan, Penelitian, & Pengabdian</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-sky-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Target & Realisasi Semesteran/Tahunan</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-sky-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Pusat Eviden Bukti Sahih (Link-Only Cloud)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Pillar 2: AMI -->
                    <div class="academic-card rounded-2xl p-8 bg-white border-slate-200 relative group">
                        <div class="w-14 h-14 rounded-xl bg-purple-50 border border-purple-200 flex items-center justify-center text-purple-700 mb-6">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>

                        <div class="inline-block text-xs font-bold text-purple-700 uppercase tracking-wider mb-2">Jantung Pengujian & Kontrol</div>
                        <h3 class="font-display text-2xl font-bold text-slate-900 mb-3">2. AMI & Tindak Lanjut</h3>
                        <p class="text-sm text-slate-600 leading-relaxed mb-6">
                            Audit independen yang memverifikasi apakah pelaksanaan di lapangan telah sesuai dengan standar SPMI yang ditetapkan. Mencatat temuan KTS (Mayor/Minor/Observasi), menggelar RTM, dan mengawal RTL hingga gap mutu tertutup.
                        </p>

                        <div class="space-y-3 border-t border-slate-100 pt-5 text-xs text-slate-700 font-medium">
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-purple-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Checklist & Penugasan Auditor Terverifikasi</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-purple-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Temuan KTS & Rekomendasi Auditor</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-purple-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>RTM (Rapat Tinjauan) & RTL Efektivitas</span>
                            </div>
                        </div>
                    </div>

                    <!-- Pillar 3: Akreditasi -->
                    <div class="academic-card rounded-2xl p-8 bg-white border-slate-200 relative group">
                        <div class="w-14 h-14 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-700 mb-6">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>

                        <div class="inline-block text-xs font-bold text-emerald-700 uppercase tracking-wider mb-2">Muara Pengakuan Eksternal</div>
                        <h3 class="font-display text-2xl font-bold text-slate-900 mb-3">3. Akreditasi & SPME</h3>
                        <p class="text-sm text-slate-600 leading-relaxed mb-6">
                            Pemanenan hasil kerja SPMI & evaluasi AMI. Data dan eviden yang telah terverifikasi secara cerdas dipetakan (*smart-mapped*) ke Kriteria 9 BAN-PT atau LAM (INFOKOM, LAMEMBA, dll) untuk menyusun LED, LKPS, dan simulasi skor Unggul.
                        </p>

                        <div class="space-y-3 border-t border-slate-100 pt-5 text-xs text-slate-700 font-medium">
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Pemetaan Dinamis Standar ke Butir LAM</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Penyusunan LED & LKPS Tanpa Duplikasi</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Simulasi Skor & Immutable Snapshot</span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Closed-Loop Flow Diagram (Light Academic Style) -->
                <div class="academic-card rounded-2xl p-6 sm:p-8 bg-white border-slate-200">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-6">
                        <div>
                            <h4 class="text-xl font-bold text-slate-900 mb-1">Siklus Tertutup (Closed-Loop Quality Flow)</h4>
                            <p class="text-sm text-slate-500">Bagaimana ketiga modul saling menyuplai data secara berkesinambungan tanpa terputus.</p>
                        </div>
                        <div class="px-3.5 py-1.5 rounded-lg bg-slate-100 border border-slate-200 text-xs font-mono font-semibold text-slate-700">
                            Automated Data Provenance
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 relative">
                        <div class="p-4 rounded-xl bg-sky-50/50 border border-sky-200 text-center">
                            <div class="text-xs font-bold text-sky-800 mb-1">LANGKAH 1</div>
                            <div class="text-sm font-bold text-slate-900 mb-1">Penetapan Target SPMI</div>
                            <div class="text-xs text-slate-600">LPM & Prodi menentukan IKU, IKT, dan unggah bukti awal.</div>
                        </div>
                        <div class="p-4 rounded-xl bg-purple-50/50 border border-purple-200 text-center">
                            <div class="text-xs font-bold text-purple-800 mb-1">LANGKAH 2</div>
                            <div class="text-sm font-bold text-slate-900 mb-1">Audit Independen AMI</div>
                            <div class="text-xs text-slate-600">Auditor memvalidasi eviden, mencatat KTS & rekomendasi RTM.</div>
                        </div>
                        <div class="p-4 rounded-xl bg-amber-50/50 border border-amber-200 text-center">
                            <div class="text-xs font-bold text-amber-800 mb-1">LANGKAH 3</div>
                            <div class="text-sm font-bold text-slate-900 mb-1">RTL Menutup Celah</div>
                            <div class="text-xs text-slate-600">Tindakan perbaikan dieksekusi, dokumen diperbarui dan diverifikasi.</div>
                        </div>
                        <div class="p-4 rounded-xl bg-emerald-50/50 border border-emerald-200 text-center">
                            <div class="text-xs font-bold text-emerald-800 mb-1">LANGKAH 4</div>
                            <div class="text-sm font-bold text-slate-900 mb-1">Akreditasi Siap Unggul</div>
                            <div class="text-xs text-slate-600">Data valid otomatis mengisi LED & LKPS dengan bukti sahih.</div>
                        </div>
                    </div>
                </div>

            </div>
        </section>


        <!-- ================= PPEPP STEP-BY-STEP WORKFLOW ================= -->
        <section id="ppepp" class="py-16 md:py-24 relative bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <span class="text-xs font-bold uppercase tracking-wider text-brand-700 px-3 py-1 rounded-full bg-brand-50 border border-brand-200 inline-block mb-3">
                        Siklus Standar Nasional Dikti
                    </span>
                    <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mb-4">
                        Penerapan Komprehensif Siklus PPEPP
                    </h2>
                    <p class="text-slate-600 text-base sm:text-lg">
                        Setiap tahapan PPEPP terakomodasi secara terstruktur di dalam I-QMS dengan alur persetujuan dan riwayat audit yang transparan.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 sm:gap-6">
                    
                    <!-- P1 -->
                    <div class="academic-card p-6 rounded-2xl border-slate-200 bg-white flex flex-col justify-between">
                        <div>
                            <div class="w-10 h-10 rounded-lg bg-sky-100 border border-sky-200 flex items-center justify-center font-display font-extrabold text-sky-800 mb-4 text-lg">
                                P
                            </div>
                            <h3 class="font-bold text-slate-900 text-base mb-2">Penetapan</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Formulasi standar mutu, manual mutu, formulir, dan indikator target institusi.
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] font-bold text-sky-700">
                            Modul: Standar SPMI
                        </div>
                    </div>

                    <!-- P2 -->
                    <div class="academic-card p-6 rounded-2xl border-slate-200 bg-white flex flex-col justify-between">
                        <div>
                            <div class="w-10 h-10 rounded-lg bg-blue-100 border border-blue-200 flex items-center justify-center font-display font-extrabold text-blue-800 mb-4 text-lg">
                                P
                            </div>
                            <h3 class="font-bold text-slate-900 text-base mb-2">Pelaksanaan</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Operasional tridharma perguruan tinggi, pencatatan realisasi dan pengumpulan eviden.
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] font-bold text-blue-700">
                            Modul: Realisasi SPMI
                        </div>
                    </div>

                    <!-- E -->
                    <div class="academic-card p-6 rounded-2xl border-slate-200 bg-white flex flex-col justify-between">
                        <div>
                            <div class="w-10 h-10 rounded-lg bg-purple-100 border border-purple-200 flex items-center justify-center font-display font-extrabold text-purple-800 mb-4 text-lg">
                                E
                            </div>
                            <h3 class="font-bold text-slate-900 text-base mb-2">Evaluasi</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Audit Mutu Internal (AMI) berkala dan evaluasi diri berbasis kriteria instrumen.
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] font-bold text-purple-700">
                            Modul: Siklus AMI & Audit
                        </div>
                    </div>

                    <!-- P4 -->
                    <div class="academic-card p-6 rounded-2xl border-slate-200 bg-white flex flex-col justify-between">
                        <div>
                            <div class="w-10 h-10 rounded-lg bg-amber-100 border border-amber-200 flex items-center justify-center font-display font-extrabold text-amber-800 mb-4 text-lg">
                                P
                            </div>
                            <h3 class="font-bold text-slate-900 text-base mb-2">Pengendalian</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Rapat Tinjauan Manajemen (RTM) dan eksekusi Rencana Tindak Lanjut (RTL).
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] font-bold text-amber-700">
                            Modul: RTM & Aksi RTL
                        </div>
                    </div>

                    <!-- P5 -->
                    <div class="academic-card p-6 rounded-2xl border-slate-200 bg-white flex flex-col justify-between">
                        <div>
                            <div class="w-10 h-10 rounded-lg bg-emerald-100 border border-emerald-200 flex items-center justify-center font-display font-extrabold text-emerald-800 mb-4 text-lg">
                                P
                            </div>
                            <h3 class="font-bold text-slate-900 text-base mb-2">Peningkatan</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Peningkatan standar mutu melampaui SN-Dikti (*Kaizen*) untuk meraih akreditasi Unggul.
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] font-bold text-emerald-700">
                            Modul: Akreditasi & SPME
                        </div>
                    </div>

                </div>

            </div>
        </section>


        <!-- ================= INTERACTIVE READINESS SIMULATOR ================= -->
        <section id="simulator" class="py-16 md:py-24 relative border-t border-slate-200/80 bg-slate-50/70">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-12">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-700 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 inline-block mb-3">
                        Simulasi Interaktif
                    </span>
                    <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mb-4">
                        Simulasi Skor Kesiapan Akreditasi
                    </h2>
                    <p class="text-slate-600 text-base sm:text-lg">
                        Lihat bagaimana pemenuhan IKU SPMI dan penutupan temuan AMI mendongkrak skor readiness akreditasi prodi secara real-time.
                    </p>
                </div>

                <div class="max-w-4xl mx-auto academic-card rounded-2xl p-6 sm:p-10 bg-white border-slate-200 shadow-lg">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                        
                        <!-- Sliders Controls (7 Cols) -->
                        <div class="lg:col-span-7 space-y-6">
                            
                            <!-- Control 1: SPMI Target Realization -->
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-sky-600"></span>
                                        Capaian Indikator SPMI
                                    </label>
                                    <span id="spmiValText" class="text-sm font-mono font-bold text-sky-700">85%</span>
                                </div>
                                <input id="spmiSlider" type="range" min="30" max="100" value="85" 
                                       class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-sky-600">
                                <p class="text-[11px] text-slate-500 mt-1">Tingkat realisasi pemenuhan target IKU & IKT SPMI.</p>
                            </div>

                            <!-- Control 2: AMI RTL Completion -->
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span>
                                        Penyelesaian RTL & Temuan AMI
                                    </label>
                                    <span id="amiValText" class="text-sm font-mono font-bold text-purple-700">90%</span>
                                </div>
                                <input id="amiSlider" type="range" min="20" max="100" value="90" 
                                       class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-purple-600">
                                <p class="text-[11px] text-slate-500 mt-1">Efektivitas penutupan temuan KTS hasil audit internal.</p>
                            </div>

                            <!-- Control 3: Valid Evidence Ratio -->
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                                        Kelengkapan Eviden Sah
                                    </label>
                                    <span id="evidenValText" class="text-sm font-mono font-bold text-emerald-700">95%</span>
                                </div>
                                <input id="evidenSlider" type="range" min="30" max="100" value="95" 
                                       class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-emerald-600">
                                <p class="text-[11px] text-slate-500 mt-1">Dokumen sah di Evidence Center yang masih berlaku & terverifikasi.</p>
                            </div>

                        </div>

                        <!-- Live Result Card (5 Cols) -->
                        <div class="lg:col-span-5 bg-gradient-to-b from-slate-900 to-slate-950 p-6 rounded-2xl text-center text-white relative shadow-md">
                            <div class="text-xs uppercase font-bold text-slate-300 tracking-wider mb-2">
                                Proyeksi Skor Akreditasi
                            </div>
                            
                            <!-- Big Score -->
                            <div class="my-3">
                                <span id="simScoreText" class="font-display text-5xl font-black text-amber-400 tracking-tight">3.72</span>
                                <span class="text-sm text-slate-400 font-mono"> / 4.00</span>
                            </div>

                            <!-- Rating Badge -->
                            <div id="simRatingBadge" class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-400/40 mb-3">
                                UNGGUL (TERAKREDITASI)
                            </div>

                            <p id="simDescText" class="text-xs text-slate-300 leading-relaxed">
                                Seluruh syarat perlu terpenuhi dengan profil SPMI dan audit AMI yang matang.
                            </p>

                            <div class="mt-5 pt-3 border-t border-slate-800 text-[10px] text-slate-400 font-medium">
                                <span>Rule Evaluator: BAN-PT / LAM INFOKOM / LAMEMBA</span>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </section>


        <!-- ================= KEY FEATURES MATRIX ================= -->
        <section id="features" class="py-16 md:py-24 relative bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <span class="text-xs font-bold uppercase tracking-wider text-brand-700 px-3 py-1 rounded-full bg-brand-50 border border-brand-200 inline-block mb-3">
                        Arsitektur Modern
                    </span>
                    <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mb-4">
                        Fitur Unggulan Sistem Mutu Terpadu
                    </h2>
                    <p class="text-slate-600 text-base sm:text-lg">
                        Dirancang khusus untuk memenuhi standar tata kelola perguruan tinggi modern dengan skalabilitas dan keamanan data tingkat tinggi.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    <!-- Feature 1 -->
                    <div class="academic-card p-6 sm:p-7 rounded-2xl bg-white border-slate-200">
                        <div class="w-12 h-12 rounded-xl bg-sky-50 border border-sky-200 flex items-center justify-center text-sky-700 mb-5">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-slate-900 text-lg mb-2">Polymorphic Evidence Center</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Simpan dokumen satu kali dalam cloud link. Hubungkan secara fleksibel ke puluhan butir SPMI dan Kriteria Akreditasi tanpa upload berulang.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="academic-card p-6 sm:p-7 rounded-2xl bg-white border-slate-200">
                        <div class="w-12 h-12 rounded-xl bg-purple-50 border border-purple-200 flex items-center justify-center text-purple-700 mb-5">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-slate-900 text-lg mb-2">Immutable Score Snapshot</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Kunci skor hasil audit dan simulasi akreditasi pada momen tertentu. Menjamin integritas data saat asesmen lapangan atau audit eksternal.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="academic-card p-6 sm:p-7 rounded-2xl bg-white border-slate-200">
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-700 mb-5">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-slate-900 text-lg mb-2">Multi-Instrument Versioning</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Mendukung template BAN-PT (9 Kriteria) dan berbagai Lembaga Akreditasi Mandiri (LAM INFOKOM, LAMEMBA, LAMDIK, LAM-PTKes).
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="academic-card p-6 sm:p-7 rounded-2xl bg-white border-slate-200">
                        <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-700 mb-5">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-slate-900 text-lg mb-2">Multi-Tenant (PT, Fak, Prodi)</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Struktur organisasi berlapis dengan kontrol kewenangan ketat antara pimpinan institusi, fakultas, prodi, dan auditor internal/eksternal.
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="academic-card p-6 sm:p-7 rounded-2xl bg-white border-slate-200">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-700 mb-5">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-slate-900 text-lg mb-2">Automasi LED & LKPS</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Generate draf Laporan Evaluasi Diri dan Laporan Kinerja Program Studi langsung dari data SPMI & temuan audit yang telah tervalidasi.
                        </p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="academic-card p-6 sm:p-7 rounded-2xl bg-white border-slate-200">
                        <div class="w-12 h-12 rounded-xl bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-700 mb-5">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-slate-900 text-lg mb-2">Real-Time Audit Trail</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Setiap perubahan target, pengunggahan eviden, review auditor, hingga approval terekam dalam log aktivitas yang transparan dan akuntabel.
                        </p>
                    </div>

                </div>

            </div>
        </section>


        <!-- ================= USER ROLES & CAPABILITIES ================= -->
        <section id="roles" class="py-16 md:py-24 relative border-t border-slate-200/80 bg-slate-50/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <span class="text-xs font-bold uppercase tracking-wider text-purple-700 px-3 py-1 rounded-full bg-purple-50 border border-purple-200 inline-block mb-3">
                        Kewenangan Terarah
                    </span>
                    <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mb-4">
                        Didesain Untuk Setiap Pemangku Kepentingan
                    </h2>
                    <p class="text-slate-600 text-base sm:text-lg">
                        Pemisahan peran yang tegas antara pengelola standar, auditor independen, dan pimpinan pengambil keputusan.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    
                    <!-- Role 1: Pimpinan & LPM -->
                    <div class="academic-card rounded-2xl p-7 bg-white border-slate-200">
                        <div class="inline-block px-3 py-1 rounded-full bg-sky-50 text-sky-800 text-xs font-bold mb-4 border border-sky-200">
                            Pimpinan PT & Lembaga Mutu (LPM)
                        </div>
                        <h3 class="font-bold text-xl text-slate-900 mb-3">Pemantauan Eksekutif & Kebijakan</h3>
                        <ul class="space-y-3 text-xs text-slate-600 font-medium">
                            <li class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-sky-600"></span>
                                Dashboard capaian mutu institusi & seluruh prodi
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-sky-600"></span>
                                Monitoring pelaksanaan siklus PPEPP menyeluruh
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-sky-600"></span>
                                Pengambilan keputusan strategis pada forum RTM
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-sky-600"></span>
                                Rekapitulasi kesiapan akreditasi institusi (LKPT/LED-PT)
                            </li>
                        </ul>
                    </div>

                    <!-- Role 2: Auditor AMI -->
                    <div class="academic-card rounded-2xl p-7 bg-white border-slate-200">
                        <div class="inline-block px-3 py-1 rounded-full bg-purple-50 text-purple-800 text-xs font-bold mb-4 border border-purple-200">
                            Auditor Mutu Internal (AMI)
                        </div>
                        <h3 class="font-bold text-xl text-slate-900 mb-3">Audit Objektif & Rekomendasi</h3>
                        <ul class="space-y-3 text-xs text-slate-600 font-medium">
                            <li class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-600"></span>
                                Akses checklist audit sesuai periode penugasan
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-600"></span>
                                Pemeriksaan keabsahan dokumen eviden langsung di sistem
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-600"></span>
                                Penerbitan temuan KTS Mayor, Minor, & Rekomendasi
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-600"></span>
                                Verifikasi efektivitas pelaksanaan RTL oleh auditee
                            </li>
                        </ul>
                    </div>

                    <!-- Role 3: Prodi & Taskforce -->
                    <div class="academic-card rounded-2xl p-7 bg-white border-slate-200">
                        <div class="inline-block px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 text-xs font-bold mb-4 border border-emerald-200">
                            Program Studi & Taskforce
                        </div>
                        <h3 class="font-bold text-xl text-slate-900 mb-3">Pengisian Capaian & Borang</h3>
                        <ul class="space-y-3 text-xs text-slate-600 font-medium">
                            <li class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                Input target & realisasi capaian IKU/IKT periodik
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                Manajemen repositori eviden prodi terstruktur
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                Tindak lanjut temuan audit melalui form RTL prodi
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                Simulasi skor dan export LED & LKPS otomatis
                            </li>
                        </ul>
                    </div>

                </div>

            </div>
        </section>


        <!-- ================= FAQ SECTION ================= -->
        <section id="faq" class="py-16 md:py-24 relative bg-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center mb-14">
                    <span class="text-xs font-bold uppercase tracking-wider text-brand-700 px-3 py-1 rounded-full bg-brand-50 border border-brand-200 inline-block mb-3">
                        Pertanyaan Umum
                    </span>
                    <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mb-4">
                        Hal yang Sering Ditanyakan
                    </h2>
                    <p class="text-slate-600 text-base">
                        Penjelasan mendasar mengenai integrasi SPMI, AMI, dan Akreditasi dalam platform I-QMS.
                    </p>
                </div>

                <div class="space-y-4">
                    
                    <!-- FAQ 1 -->
                    <div class="academic-card rounded-xl p-5 bg-white border-slate-200">
                        <h4 class="font-bold text-slate-900 text-base mb-2 flex items-center justify-between">
                            <span>Mengapa SPMI, AMI, dan Akreditasi harus terintegrasi dalam satu platform?</span>
                            <span class="text-brand-600 text-xl font-bold">+</span>
                        </h4>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Di banyak kampus, ketiga proses ini berjalan terisolasi (*data silo*). Akibatnya, saat akreditasi tiba, tim taskforce harus mengumpulkan dokumen dari nol. Dengan I-QMS, data realisasi SPMI dan temuan audit AMI otomatis menjadi sumber eviden yang siap pakai untuk kriteria akreditasi BAN-PT maupun LAM.
                        </p>
                    </div>

                    <!-- FAQ 2 -->
                    <div class="academic-card rounded-xl p-5 bg-white border-slate-200">
                        <h4 class="font-bold text-slate-900 text-base mb-2 flex items-center justify-between">
                            <span>Apakah SPMI harus mengikuti struktur 9 Kriteria BAN-PT?</span>
                            <span class="text-brand-600 text-xl font-bold">+</span>
                        </h4>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Tidak. SPMI dirancang fleksibel sesuai kekhasan dan visi perguruan tinggi Anda (SN-Dikti, Renstra, Statuta). I-QMS menyediakan fitur *Smart Instrument Mapping*, sehingga satu indikator SPMI internal dapat dipetakan ke berbagai elemen kriteria BAN-PT maupun LAM yang berbeda.
                        </p>
                    </div>

                    <!-- FAQ 3 -->
                    <div class="academic-card rounded-xl p-5 bg-white border-slate-200">
                        <h4 class="font-bold text-slate-900 text-base mb-2 flex items-center justify-between">
                            <span>Bagaimana Evidence Center mencegah penumpukan file server?</span>
                            <span class="text-brand-600 text-xl font-bold">+</span>
                        </h4>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            I-QMS menerapkan arsitektur *Polymorphic Link-Only Evidence*. Dokumen disimpan di repositori cloud resmi kampus (Google Drive / OneDrive / Nextcloud), dan sistem mengelola metadata tautan, masa berlaku, dan relasi ke indikator. Dokumen yang sama bisa dipakai di ratusan butir tanpa menduplikasi file.
                        </p>
                    </div>

                    <!-- FAQ 4 -->
                    <div class="academic-card rounded-xl p-5 bg-white border-slate-200">
                        <h4 class="font-bold text-slate-900 text-base mb-2 flex items-center justify-between">
                            <span>Lembaga Akreditasi Mandiri (LAM) mana saja yang didukung?</span>
                            <span class="text-brand-600 text-xl font-bold">+</span>
                        </h4>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Sistem memiliki modul instrumen dinamis dan versioned yang mendukung BAN-PT, LAM INFOKOM, LAMEMBA, LAMDIK, LAM-PTKes, dan LAM Teknik. Aturan kualifikasi seperti Rule Unggul terintegrasi langsung dalam evaluasi skor.
                        </p>
                    </div>

                </div>

            </div>
        </section>


        <!-- ================= CTA BANNER ================= -->
        <section class="py-16 relative bg-slate-50/70 border-t border-slate-200/80">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative rounded-3xl p-8 sm:p-12 overflow-hidden bg-gradient-to-r from-slate-900 via-brand-950 to-slate-900 border border-slate-800 shadow-2xl text-center text-white">
                    
                    <div class="relative z-10 max-w-2xl mx-auto">
                        <span class="text-xs font-bold uppercase tracking-wider text-sky-300 px-3 py-1 rounded-full bg-sky-950 border border-sky-800 inline-block mb-4">
                            Mulai Transformasi Mutu Perguruan Tinggi
                        </span>
                        <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-white tracking-tight mb-4">
                            Wujudkan Budaya Mutu Berkelanjutan & Akreditasi Unggul
                        </h2>
                        <p class="text-slate-300 text-sm sm:text-base mb-8 leading-relaxed">
                            Masuk ke portal administrasi untuk mengelola standar SPMI, menjalankan siklus AMI, dan memantau kesiapan akreditasi sekarang.
                        </p>

                        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                            <a href="{{ url('/admin') }}" 
                               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl font-bold text-white bg-brand-600 hover:bg-brand-500 shadow-lg shadow-brand-600/30 transition-all text-base">
                                <span>Masuk ke Dashboard Sistem</span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </section>

    </main>

    <!-- ================= FOOTER ================= -->
    <footer class="relative z-10 border-t border-slate-200 bg-white pt-12 pb-8 text-slate-600 text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
                
                <!-- Col 1 -->
                <div class="md:col-span-2 space-y-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-brand-700 flex items-center justify-center text-white font-bold text-sm">
                            Q
                        </div>
                        <span class="font-display font-bold text-lg text-slate-900">I-QMS Enterprise</span>
                    </div>
                    <p class="text-slate-600 max-w-md leading-relaxed">
                        Integrated Quality Management System &bull; Platform Penjaminan Mutu & Akreditasi Terpadu Perguruan Tinggi yang menghubungkan SPMI, AMI, dan Akreditasi BAN-PT & Seluruh LAM.
                    </p>
                    <div class="flex items-center gap-3 pt-2 text-slate-500 font-medium">
                        <span>Standar SN-Dikti</span>
                        <span>&bull;</span>
                        <span>Permendikbudristek 53/2023</span>
                        <span>&bull;</span>
                        <span>BAN-PT & LAM</span>
                    </div>
                </div>

                <!-- Col 2 -->
                <div>
                    <h5 class="font-bold text-slate-900 text-sm mb-3">Modul Terintegrasi</h5>
                    <ul class="space-y-2">
                        <li><a href="#triad" class="hover:text-brand-700 transition-colors">Sistem SPMI (Hulu Standar)</a></li>
                        <li><a href="#triad" class="hover:text-brand-700 transition-colors">Audit Mutu Internal (AMI)</a></li>
                        <li><a href="#triad" class="hover:text-brand-700 transition-colors">RTM & Rencana Tindak Lanjut</a></li>
                        <li><a href="#triad" class="hover:text-brand-700 transition-colors">Akreditasi & SPME (Muara)</a></li>
                        <li><a href="#features" class="hover:text-brand-700 transition-colors">Polymorphic Evidence Center</a></li>
                    </ul>
                </div>

                <!-- Col 3 -->
                <div>
                    <h5 class="font-bold text-slate-900 text-sm mb-3">Tautan Cepat</h5>
                    <ul class="space-y-2">
                        <li><a href="{{ url('/admin') }}" class="text-brand-700 font-semibold hover:underline">Masuk Portal Admin</a></li>
                        <li><a href="#ppepp" class="hover:text-brand-700 transition-colors">Alur Siklus PPEPP</a></li>
                        <li><a href="#simulator" class="hover:text-brand-700 transition-colors">Simulasi Kesiapan Skor</a></li>
                        <li><a href="#roles" class="hover:text-brand-700 transition-colors">Struktur Hak Akses</a></li>
                        <li><a href="#faq" class="hover:text-brand-700 transition-colors">FAQ & Bantuan</a></li>
                    </ul>
                </div>

            </div>

            <!-- Bottom Copyright -->
            <div class="pt-8 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between text-slate-500 gap-4">
                <div>
                    &copy; {{ date('Y') }} {{ config('app.name', 'I-QMS') }}. Hak Cipta Dilindungi Undang-Undang.
                </div>
                <div class="flex items-center gap-4">
                    <span class="inline-flex items-center gap-1.5 font-medium text-slate-600">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Status Sistem: Normal & Terkoneksi
                    </span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Interactive Scripts for Simulator & Mobile Nav -->
    <script>
        // Mobile Menu Toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });
        }

        // Live Accreditation Readiness Simulator Logic
        const spmiSlider = document.getElementById('spmiSlider');
        const amiSlider = document.getElementById('amiSlider');
        const evidenSlider = document.getElementById('evidenSlider');
        
        const spmiValText = document.getElementById('spmiValText');
        const amiValText = document.getElementById('amiValText');
        const evidenValText = document.getElementById('evidenValText');

        const simScoreText = document.getElementById('simScoreText');
        const simRatingBadge = document.getElementById('simRatingBadge');
        const simDescText = document.getElementById('simDescText');

        function updateSimulator() {
            const spmi = parseInt(spmiSlider.value, 10);
            const ami = parseInt(amiSlider.value, 10);
            const eviden = parseInt(evidenSlider.value, 10);

            spmiValText.innerText = spmi + '%';
            amiValText.innerText = ami + '%';
            evidenValText.innerText = eviden + '%';

            // Calculate weighted score (scale 4.00)
            // SPMI weight 35%, AMI weight 35%, Evidence weight 30%
            const calculatedScore = ((spmi * 0.35 + ami * 0.35 + eviden * 0.30) / 100) * 4.0;
            const formattedScore = calculatedScore.toFixed(2);
            simScoreText.innerText = formattedScore;

            if (calculatedScore >= 3.60) {
                simRatingBadge.className = "inline-flex items-center px-4 py-1.5 rounded-full text-xs font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-400/40 mb-3";
                simRatingBadge.innerText = "UNGGUL (TERAKREDITASI)";
                simDescText.innerText = "Sangat Memuaskan! Seluruh syarat perlu terpenuhi dengan profil SPMI dan audit AMI yang matang.";
            } else if (calculatedScore >= 3.00) {
                simRatingBadge.className = "inline-flex items-center px-4 py-1.5 rounded-full text-xs font-extrabold bg-sky-500/20 text-sky-300 border border-sky-400/40 mb-3";
                simRatingBadge.innerText = "BAIK SEKALI";
                simDescText.innerText = "Bagus. Tingkatkan penyelesaian temuan AMI dan kelengkapan eviden untuk meraih predikat Unggul.";
            } else if (calculatedScore >= 2.00) {
                simRatingBadge.className = "inline-flex items-center px-4 py-1.5 rounded-full text-xs font-extrabold bg-amber-500/20 text-amber-300 border border-amber-400/40 mb-3";
                simRatingBadge.innerText = "BAIK";
                simDescText.innerText = "Memenuhi standar minimal. Diperlukan percepatan tindak lanjut RTL dan pemenuhan target IKU.";
            } else {
                simRatingBadge.className = "inline-flex items-center px-4 py-1.5 rounded-full text-xs font-extrabold bg-rose-500/20 text-rose-300 border border-rose-400/40 mb-3";
                simRatingBadge.innerText = "PERLU PERBAIKAN";
                simDescText.innerText = "Capaian belum memenuhi ambang batas minimum akreditasi. Segera lakukan audit komprehensif.";
            }
        }

        if (spmiSlider && amiSlider && evidenSlider) {
            spmiSlider.addEventListener('input', updateSimulator);
            amiSlider.addEventListener('input', updateSimulator);
            evidenSlider.addEventListener('input', updateSimulator);
            updateSimulator();
        }
    </script>
</body>
</html>
