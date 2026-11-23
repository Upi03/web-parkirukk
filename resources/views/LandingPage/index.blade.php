<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmarPARK - Sistem Manajemen Parkir Internal</title>
    <meta name="description" content="SmarPARK adalah sistem manajemen parkir berbasis web khusus untuk Admin, Petugas, dan Owner.">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    @vite('resources/css/app.css')

    <!-- Alpine.js untuk interaktivitas -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 selection:bg-blue-200 selection:text-blue-900 overflow-x-hidden"
      x-data="{ isScrolled: false, mobileMenuOpen: false }"
      @scroll.window="isScrolled = (window.pageYOffset > 20)">

    <!-- 1. Navbar -->
    <header :class="{ 'bg-white/80 backdrop-blur-md shadow-sm py-3': isScrolled, 'bg-transparent py-5': !isScrolled }" 
            class="fixed top-0 w-full z-50 transition-all duration-300">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="bg-blue-600 text-white p-2 rounded-xl shadow-sm">
                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                </div>
                <span class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-700 to-cyan-500">
                    SmarPARK
                </span>
            </div>

            <!-- Desktop Nav -->
            <nav class="hidden md:flex items-center gap-8 font-medium text-slate-600">
                <a href="#beranda" class="hover:text-blue-600 transition-colors">Beranda</a>
                <a href="#fitur" class="hover:text-blue-600 transition-colors">Fitur</a>
                <a href="#peran" class="hover:text-blue-600 transition-colors">Peran</a>
                <a href="#cara-kerja" class="hover:text-blue-600 transition-colors">Cara Kerja</a>
                <a href="#faq" class="hover:text-blue-600 transition-colors">FAQ</a>
            </nav>

            <div class="hidden md:flex items-center">
                <a href="/login" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-full font-medium transition-all shadow-md shadow-blue-600/20 hover:shadow-lg hover:shadow-blue-600/40 active:scale-95 flex items-center gap-2">
                    Masuk <i data-lucide="log-in" class="w-4 h-4"></i>
                </a>
            </div>

            <!-- Mobile Menu Button -->
            <button class="md:hidden text-slate-600" @click="mobileMenuOpen = !mobileMenuOpen" aria-label="Toggle Menu">
                <i data-lucide="menu" class="w-6 h-6" x-show="!mobileMenuOpen"></i>
                <i data-lucide="x" class="w-6 h-6" x-show="mobileMenuOpen" style="display: none;"></i>
            </button>
        </div>

        <!-- Mobile Nav -->
        <div x-show="mobileMenuOpen" @click.away="mobileMenuOpen = false" style="display: none;" 
             class="md:hidden absolute top-full left-0 w-full bg-white shadow-xl border-t border-slate-100 py-4 px-4 flex flex-col gap-3">
            <a href="#beranda" @click="mobileMenuOpen = false" class="font-medium text-slate-700 p-3 hover:bg-slate-50 rounded-xl">Beranda</a>
            <a href="#fitur" @click="mobileMenuOpen = false" class="font-medium text-slate-700 p-3 hover:bg-slate-50 rounded-xl">Fitur</a>
            <a href="#peran" @click="mobileMenuOpen = false" class="font-medium text-slate-700 p-3 hover:bg-slate-50 rounded-xl">Peran</a>
            <a href="#cara-kerja" @click="mobileMenuOpen = false" class="font-medium text-slate-700 p-3 hover:bg-slate-50 rounded-xl">Cara Kerja</a>
            <a href="#faq" @click="mobileMenuOpen = false" class="font-medium text-slate-700 p-3 hover:bg-slate-50 rounded-xl">FAQ</a>
            <div class="h-px bg-slate-100 my-2"></div>
            <a href="/login" class="font-medium text-center text-white bg-blue-600 p-3 rounded-xl flex items-center justify-center gap-2">
                Masuk ke Sistem
            </a>
        </div>
    </header>

    <!-- 2. Hero Section -->
    <section id="beranda" class="relative pt-32 pb-20 lg:pt-40 lg:pb-28 overflow-hidden">
        <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3 w-[800px] h-[800px] rounded-full bg-gradient-to-br from-blue-100/60 to-cyan-50/50 blur-3xl -z-10"></div>
        <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3 w-[600px] h-[600px] rounded-full bg-blue-50/60 blur-3xl -z-10"></div>
        
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-8 items-center">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-100/80 border border-blue-200 text-blue-700 text-sm font-semibold mb-6">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                        Khusus Admin, Petugas & Owner
                    </div>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 leading-tight mb-6 tracking-tight">
                        Kelola Parkir Lebih <br/>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-500">
                            Cerdas, Cepat, & Terkontrol.
                        </span>
                    </h1>
                    <p class="text-lg sm:text-xl text-slate-600 mb-8 leading-relaxed">
                        Sistem manajemen parkir internal terpadu. Pastikan kelancaran operasional, akurasi pendapatan, dan pengawasan real-time dalam satu platform aman.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="/login" class="flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-8 py-3.5 rounded-full font-medium text-lg transition-all shadow-lg shadow-blue-600/30 hover:shadow-xl hover:shadow-blue-600/40 hover:-translate-y-1">
                            Masuk ke Sistem <i data-lucide="arrow-right" class="w-5 h-5"></i>
                        </a>
                        <a href="#fitur" class="flex items-center justify-center gap-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 px-8 py-3.5 rounded-full font-medium text-lg transition-all hover:shadow-md hover:-translate-y-1">
                            Lihat Fitur
                        </a>
                    </div>
                </div>
                
                <!-- Hero Image/Mockup -->
                <div class="relative mx-auto w-full max-w-lg lg:max-w-full group">
                    <div class="relative bg-white p-2 rounded-[2.5rem] shadow-2xl border border-slate-100 shadow-blue-900/10 z-10 aspect-[4/3] flex items-center justify-center overflow-hidden bg-gradient-to-br from-slate-800 to-blue-900 transition-transform duration-700 group-hover:scale-[1.02]">
                        <!-- Abstract Dashboard UI inside Hero -->
                        <div class="absolute inset-4 bg-slate-50 rounded-3xl overflow-hidden flex flex-col shadow-inner">
                            <div class="h-12 bg-white border-b border-slate-200 flex items-center px-4 justify-between">
                                <div class="flex gap-2">
                                    <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                    <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                                    <div class="w-3 h-3 rounded-full bg-green-400"></div>
                                </div>
                                <div class="h-4 w-24 bg-slate-200 rounded animate-pulse"></div>
                            </div>
                            <div class="flex-1 p-4 flex gap-4">
                                <div class="w-1/4 flex flex-col gap-3">
                                    <div class="h-8 bg-blue-100 rounded-lg w-full"></div>
                                    <div class="h-8 bg-slate-200 rounded-lg w-full opacity-60"></div>
                                    <div class="h-8 bg-slate-200 rounded-lg w-full opacity-60"></div>
                                </div>
                                <div class="w-3/4 flex flex-col gap-4">
                                    <div class="flex gap-4">
                                        <div class="h-24 bg-white border border-slate-100 shadow-sm rounded-xl flex-1 flex flex-col justify-center p-4">
                                            <div class="h-3 bg-slate-200 rounded w-1/2 mb-2"></div>
                                            <div class="h-6 bg-blue-600 rounded w-3/4"></div>
                                        </div>
                                        <div class="h-24 bg-white border border-slate-100 shadow-sm rounded-xl flex-1 flex flex-col justify-center p-4">
                                            <div class="h-3 bg-slate-200 rounded w-1/2 mb-2"></div>
                                            <div class="h-6 bg-cyan-500 rounded w-2/3"></div>
                                        </div>
                                    </div>
                                    <div class="flex-1 bg-white border border-slate-100 shadow-sm rounded-xl p-4">
                                        <div class="h-full w-full border-2 border-dashed border-slate-200 rounded-lg flex items-center justify-center">
                                            <i data-lucide="bar-chart-3" class="w-12 h-12 text-slate-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Floating cards -->
                    <div class="absolute -left-6 top-16 bg-white/90 backdrop-blur-md p-4 rounded-2xl shadow-xl border border-slate-100 z-20 flex items-center gap-3 animate-[bounce_3s_infinite]">
                        <div class="bg-blue-100 text-blue-600 p-2 rounded-lg"><i data-lucide="users" class="w-5 h-5"></i></div>
                        <div>
                            <p class="text-xs text-slate-500 font-medium">Sistem Role</p>
                            <p class="font-bold text-slate-800 text-sm">Aman & Terpusat</p>
                        </div>
                    </div>
                    <div class="absolute -right-6 bottom-16 bg-white/90 backdrop-blur-md p-4 rounded-2xl shadow-xl border border-slate-100 z-20 flex items-center gap-3 animate-[bounce_4s_infinite]">
                        <div class="bg-green-100 text-green-600 p-2 rounded-lg"><i data-lucide="activity" class="w-5 h-5"></i></div>
                        <div>
                            <p class="text-xs text-slate-500 font-medium">Monitoring</p>
                            <p class="font-bold text-slate-800 text-sm">Real-time</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. Fitur Unggulan -->
    <section id="fitur" class="py-24 bg-white border-y border-slate-200">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-blue-600 font-bold uppercase tracking-wider text-sm">Fitur Utama</span>
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mt-2 mb-4">Solusi Manajemen Parkir Terpadu</h2>
                <p class="text-lg text-slate-600">Dikembangkan khusus untuk menjawab tantangan operasional harian pengelola lahan parkir.</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Fitur 1 -->
                <div class="bg-slate-50 rounded-2xl p-8 border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all group">
                    <div class="w-12 h-12 bg-white shadow-sm text-blue-600 rounded-xl flex items-center justify-center mb-6 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <i data-lucide="log-in" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Pencatatan Keluar Masuk</h3>
                    <p class="text-slate-600 leading-relaxed text-sm">Catat detail kendaraan, nomor plat, dan waktu secara akurat untuk meminimalisir kehilangan.</p>
                </div>
                <!-- Fitur 2 -->
                <div class="bg-slate-50 rounded-2xl p-8 border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all group">
                    <div class="w-12 h-12 bg-white shadow-sm text-blue-600 rounded-xl flex items-center justify-center mb-6 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <i data-lucide="calculator" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Perhitungan Tarif Otomatis</h3>
                    <p class="text-slate-600 leading-relaxed text-sm">Sistem menghitung biaya parkir berdasarkan durasi dan jenis kendaraan secara presisi tanpa salah hitung.</p>
                </div>
                <!-- Fitur 3 -->
                <div class="bg-slate-50 rounded-2xl p-8 border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all group">
                    <div class="w-12 h-12 bg-white shadow-sm text-blue-600 rounded-xl flex items-center justify-center mb-6 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <i data-lucide="layout-grid" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Monitoring Slot Real-Time</h3>
                    <p class="text-slate-600 leading-relaxed text-sm">Pantau jumlah kendaraan yang sedang parkir dan sisa slot kosong kapan saja dari dashboard.</p>
                </div>
                <!-- Fitur 4 -->
                <div class="bg-slate-50 rounded-2xl p-8 border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all group">
                    <div class="w-12 h-12 bg-white shadow-sm text-blue-600 rounded-xl flex items-center justify-center mb-6 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <i data-lucide="user-cog" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Manajemen Data & Akun</h3>
                    <p class="text-slate-600 leading-relaxed text-sm">Admin dapat dengan mudah menambah, mengedit, atau menghapus akun petugas dan hak aksesnya.</p>
                </div>
                <!-- Fitur 5 -->
                <div class="bg-slate-50 rounded-2xl p-8 border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all group">
                    <div class="w-12 h-12 bg-white shadow-sm text-blue-600 rounded-xl flex items-center justify-center mb-6 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <i data-lucide="pie-chart" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Laporan Pendapatan</h3>
                    <p class="text-slate-600 leading-relaxed text-sm">Rekapitulasi pendapatan harian hingga bulanan yang dapat diekspor untuk kebutuhan audit.</p>
                </div>
                <!-- Fitur 6 -->
                <div class="bg-slate-50 rounded-2xl p-8 border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all group">
                    <div class="w-12 h-12 bg-white shadow-sm text-blue-600 rounded-xl flex items-center justify-center mb-6 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <i data-lucide="receipt" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Riwayat & Cetak Struk</h3>
                    <p class="text-slate-600 leading-relaxed text-sm">Setiap transaksi tersimpan secara permanen dan terhubung langsung dengan printer thermal.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. Peran Pengguna -->
    <section id="peran" class="py-24 bg-slate-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-blue-600 font-bold uppercase tracking-wider text-sm">Hak Akses</span>
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mt-2 mb-4">Sistem Berbasis Role</h2>
                <p class="text-lg text-slate-600">SmarPARK membagi hak akses dengan tegas demi menjaga keamanan data dan alur kerja yang terstruktur.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Admin -->
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200 hover:shadow-lg transition-all relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-bl-full -z-10 group-hover:bg-blue-100 transition-colors"></div>
                    <div class="w-16 h-16 bg-blue-600 text-white rounded-2xl flex items-center justify-center mb-6 shadow-md shadow-blue-600/20">
                        <i data-lucide="shield-alert" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-2">Admin</h3>
                    <p class="text-slate-500 mb-6 text-sm">Pemegang kendali penuh atas konfigurasi dan pengguna sistem.</p>
                    <ul class="space-y-3 mb-6">
                        <li class="flex gap-3 text-slate-700 text-sm">
                            <i data-lucide="check" class="w-5 h-5 text-blue-600 shrink-0"></i>
                            <span>Kelola akun Petugas & Owner</span>
                        </li>
                        <li class="flex gap-3 text-slate-700 text-sm">
                            <i data-lucide="check" class="w-5 h-5 text-blue-600 shrink-0"></i>
                            <span>Atur tarif dasar & denda parkir</span>
                        </li>
                        <li class="flex gap-3 text-slate-700 text-sm">
                            <i data-lucide="check" class="w-5 h-5 text-blue-600 shrink-0"></i>
                            <span>Manajemen kapasitas/area parkir</span>
                        </li>
                    </ul>
                </div>

                <!-- Petugas -->
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200 hover:shadow-lg transition-all relative overflow-hidden group transform md:-translate-y-4">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-50 rounded-bl-full -z-10 group-hover:bg-cyan-100 transition-colors"></div>
                    <div class="w-16 h-16 bg-cyan-500 text-white rounded-2xl flex items-center justify-center mb-6 shadow-md shadow-cyan-500/20">
                        <i data-lucide="user-check" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-2">Petugas</h3>
                    <p class="text-slate-500 mb-6 text-sm">Operator lapangan yang berhadapan langsung dengan transaksi.</p>
                    <ul class="space-y-3 mb-6">
                        <li class="flex gap-3 text-slate-700 text-sm">
                            <i data-lucide="check" class="w-5 h-5 text-cyan-500 shrink-0"></i>
                            <span>Input plat & jenis kendaraan</span>
                        </li>
                        <li class="flex gap-3 text-slate-700 text-sm">
                            <i data-lucide="check" class="w-5 h-5 text-cyan-500 shrink-0"></i>
                            <span>Proses pembayaran & kembalian</span>
                        </li>
                        <li class="flex gap-3 text-slate-700 text-sm">
                            <i data-lucide="check" class="w-5 h-5 text-cyan-500 shrink-0"></i>
                            <span>Cetak struk tiket & pembayaran</span>
                        </li>
                    </ul>
                </div>

                <!-- Owner -->
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200 hover:shadow-lg transition-all relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-amber-50 rounded-bl-full -z-10 group-hover:bg-amber-100 transition-colors"></div>
                    <div class="w-16 h-16 bg-amber-500 text-white rounded-2xl flex items-center justify-center mb-6 shadow-md shadow-amber-500/20">
                        <i data-lucide="briefcase" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-2">Owner</h3>
                    <p class="text-slate-500 mb-6 text-sm">Pemilik lahan yang memantau performa dan profitabilitas.</p>
                    <ul class="space-y-3 mb-6">
                        <li class="flex gap-3 text-slate-700 text-sm">
                            <i data-lucide="check" class="w-5 h-5 text-amber-500 shrink-0"></i>
                            <span>Pantau grafik pendapatan harian</span>
                        </li>
                        <li class="flex gap-3 text-slate-700 text-sm">
                            <i data-lucide="check" class="w-5 h-5 text-amber-500 shrink-0"></i>
                            <span>Melihat kinerja area & petugas</span>
                        </li>
                        <li class="flex gap-3 text-slate-700 text-sm">
                            <i data-lucide="check" class="w-5 h-5 text-amber-500 shrink-0"></i>
                            <span>Unduh laporan keuangan bulanan</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. Cara Kerja -->
    <section id="cara-kerja" class="py-24 bg-white relative overflow-hidden border-y border-slate-200">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-blue-600 font-bold uppercase tracking-wider text-sm">Alur Operasional</span>
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mt-2 mb-4">Sederhana dan Praktis</h2>
                <p class="text-lg text-slate-600">Alur kerja SmarPARK yang meminimalisir antrean dan kesalahan manusia.</p>
            </div>

            <div class="grid md:grid-cols-4 gap-8 relative">
                <!-- Connecting line (desktop only) -->
                <div class="hidden md:block absolute top-[3rem] left-[12%] right-[12%] h-0.5 bg-slate-200 -z-10"></div>
                
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto bg-white rounded-full shadow-md border-4 border-slate-50 flex items-center justify-center mb-6 relative">
                        <span class="text-xl font-bold text-blue-600">01</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Login Role</h3>
                    <p class="text-slate-600 text-sm">Masuk menggunakan akun yang telah dibuatkan oleh Admin.</p>
                </div>
                
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto bg-white rounded-full shadow-md border-4 border-slate-50 flex items-center justify-center mb-6 relative">
                        <span class="text-xl font-bold text-blue-600">02</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Input Kendaraan</h3>
                    <p class="text-slate-600 text-sm">Petugas mencatat plat nomor saat kendaraan masuk.</p>
                </div>

                <div class="text-center">
                    <div class="w-20 h-20 mx-auto bg-white rounded-full shadow-md border-4 border-slate-50 flex items-center justify-center mb-6 relative">
                        <span class="text-xl font-bold text-blue-600">03</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Hitung Otomatis</h3>
                    <p class="text-slate-600 text-sm">Sistem menghitung tarif otomatis saat kendaraan keluar area.</p>
                </div>

                <div class="text-center">
                    <div class="w-20 h-20 mx-auto bg-white rounded-full shadow-md border-4 border-slate-50 flex items-center justify-center mb-6 relative">
                        <span class="text-xl font-bold text-blue-600">04</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Tersimpan</h3>
                    <p class="text-slate-600 text-sm">Transaksi tersimpan dan langsung terlihat di laporan Owner.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 6 & 7. Preview & Keamanan (Combined Section) -->
    <section class="py-20 bg-slate-900 text-white overflow-hidden" x-data="{ tab: 'admin' }">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <!-- Text / Keamanan -->
                <div>
                    <span class="text-cyan-400 font-bold uppercase tracking-wider text-sm">Keamanan Terjamin</span>
                    <h2 class="text-3xl md:text-4xl font-bold mb-6 mt-2">Satu Sistem, Tampilan Berbeda</h2>
                    <p class="text-slate-300 text-lg mb-8 leading-relaxed">
                        SmarPARK dirancang agar setiap peran hanya dapat melihat data dan fitur yang menjadi haknya. Data keuangan tidak bocor ke petugas, dan fitur teknis terlindungi dari owner.
                    </p>
                    
                    <div class="flex flex-col gap-3 mb-8">
                        <button @click="tab = 'admin'" :class="tab === 'admin' ? 'bg-blue-600 border-blue-500' : 'bg-slate-800 border-slate-700 hover:bg-slate-700'" class="px-5 py-4 rounded-xl border text-left transition-colors flex justify-between items-center">
                            <div>
                                <div class="font-bold text-white">Dashboard Admin</div>
                                <div class="text-sm text-slate-400 mt-1">Akses semua menu pengaturan dan user.</div>
                            </div>
                            <i data-lucide="chevron-right" class="w-5 h-5 text-slate-400" x-show="tab !== 'admin'"></i>
                        </button>
                        <button @click="tab = 'petugas'" :class="tab === 'petugas' ? 'bg-blue-600 border-blue-500' : 'bg-slate-800 border-slate-700 hover:bg-slate-700'" class="px-5 py-4 rounded-xl border text-left transition-colors flex justify-between items-center">
                            <div>
                                <div class="font-bold text-white">Dashboard Petugas</div>
                                <div class="text-sm text-slate-400 mt-1">Fokus pada kasir dan pencatatan tiket.</div>
                            </div>
                            <i data-lucide="chevron-right" class="w-5 h-5 text-slate-400" x-show="tab !== 'petugas'"></i>
                        </button>
                        <button @click="tab = 'owner'" :class="tab === 'owner' ? 'bg-blue-600 border-blue-500' : 'bg-slate-800 border-slate-700 hover:bg-slate-700'" class="px-5 py-4 rounded-xl border text-left transition-colors flex justify-between items-center">
                            <div>
                                <div class="font-bold text-white">Dashboard Owner</div>
                                <div class="text-sm text-slate-400 mt-1">Fokus pada visualisasi pendapatan harian.</div>
                            </div>
                            <i data-lucide="chevron-right" class="w-5 h-5 text-slate-400" x-show="tab !== 'owner'"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Mockup Preview -->
                <div class="relative w-full max-w-lg mx-auto lg:mx-0">
                    <div class="absolute inset-0 bg-blue-600 blur-[100px] opacity-40 rounded-full"></div>
                    
                    <div class="relative bg-slate-800 border border-slate-700 rounded-2xl overflow-hidden shadow-2xl shadow-black/50 aspect-[4/3] flex flex-col">
                        <!-- Browser window top -->
                        <div class="h-10 bg-slate-900 border-b border-slate-700 flex items-center px-4 gap-2">
                            <div class="w-3 h-3 rounded-full bg-slate-700"></div>
                            <div class="w-3 h-3 rounded-full bg-slate-700"></div>
                            <div class="w-3 h-3 rounded-full bg-slate-700"></div>
                            <div class="ml-4 h-5 w-48 bg-slate-800 rounded-md"></div>
                        </div>
                        
                        <!-- Screen Content -->
                        <div class="flex-1 bg-slate-50 p-6 flex flex-col relative">
                            <!-- Header -->
                            <div class="flex justify-between items-center mb-6">
                                <div class="font-bold text-blue-700 text-lg flex items-center gap-2">
                                    <i data-lucide="shield-check" class="w-5 h-5"></i> SmarPARK
                                </div>
                                <div class="px-3 py-1 rounded-full text-xs font-bold uppercase"
                                     :class="{
                                        'bg-blue-100 text-blue-700': tab === 'admin',
                                        'bg-cyan-100 text-cyan-700': tab === 'petugas',
                                        'bg-amber-100 text-amber-700': tab === 'owner'
                                     }" x-text="tab">
                                </div>
                            </div>
                            
                            <!-- Tab Content: Admin -->
                            <div x-show="tab === 'admin'" class="flex-1 flex flex-col gap-4 animate-fade-in-up">
                                <div class="h-8 bg-slate-200 rounded w-1/3"></div>
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="bg-white border border-slate-200 rounded-lg p-3 shadow-sm h-16"></div>
                                    <div class="bg-white border border-slate-200 rounded-lg p-3 shadow-sm h-16"></div>
                                    <div class="bg-white border border-slate-200 rounded-lg p-3 shadow-sm h-16"></div>
                                </div>
                                <div class="flex-1 bg-white border border-slate-200 rounded-lg shadow-sm"></div>
                            </div>
                            
                            <!-- Tab Content: Petugas -->
                            <div x-show="tab === 'petugas'" class="flex-1 flex gap-4 animate-fade-in-up" style="display: none;">
                                <div class="w-2/3 bg-white border border-slate-200 rounded-lg shadow-sm p-4 flex flex-col gap-3">
                                    <div class="h-6 bg-slate-200 rounded w-1/2"></div>
                                    <div class="h-10 bg-slate-100 border border-slate-200 rounded"></div>
                                    <div class="h-10 bg-blue-600 rounded mt-auto"></div>
                                </div>
                                <div class="w-1/3 bg-white border border-slate-200 rounded-lg shadow-sm p-4">
                                    <div class="h-32 bg-slate-100 rounded-md border-2 border-dashed border-slate-200 mb-2"></div>
                                </div>
                            </div>

                            <!-- Tab Content: Owner -->
                            <div x-show="tab === 'owner'" class="flex-1 flex flex-col gap-4 animate-fade-in-up" style="display: none;">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-white border border-slate-200 rounded-lg p-4 shadow-sm h-24 flex flex-col justify-end">
                                        <div class="h-6 bg-amber-500 rounded w-3/4"></div>
                                    </div>
                                    <div class="bg-white border border-slate-200 rounded-lg p-4 shadow-sm h-24 flex flex-col justify-end">
                                        <div class="h-6 bg-green-500 rounded w-1/2"></div>
                                    </div>
                                </div>
                                <div class="flex-1 bg-white border border-slate-200 rounded-lg shadow-sm p-4 flex items-end justify-between px-8">
                                    <div class="w-8 bg-blue-200 rounded-t-sm h-1/3"></div>
                                    <div class="w-8 bg-blue-300 rounded-t-sm h-1/2"></div>
                                    <div class="w-8 bg-blue-400 rounded-t-sm h-2/3"></div>
                                    <div class="w-8 bg-blue-500 rounded-t-sm h-full"></div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 8. FAQ -->
    <section id="faq" class="py-24 bg-slate-50 border-t border-slate-200" x-data="{ activeFaq: null }">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-3xl">
            <div class="text-center mb-16">
                <span class="text-blue-600 font-bold uppercase tracking-wider text-sm">Bantuan Dasar</span>
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mt-2 mb-4">Pertanyaan Seputar Akses</h2>
            </div>

            <div class="space-y-4">
                <!-- FAQ 1 -->
                <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden transition-all shadow-sm">
                    <button class="w-full px-6 py-4 text-left flex justify-between items-center font-bold text-slate-900 hover:text-blue-600 focus:outline-none"
                            @click="activeFaq = activeFaq === 1 ? null : 1">
                        Bagaimana cara mendapatkan akun SmarPARK?
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="activeFaq === 1 ? 'rotate-180' : ''"></i>
                    </button>
                    <div class="px-6 overflow-hidden transition-all duration-300 ease-in-out"
                         :class="activeFaq === 1 ? 'max-h-40 pb-4 opacity-100' : 'max-h-0 opacity-0'">
                        <p class="text-slate-600">SmarPARK adalah sistem tertutup. Akun Anda (baik sebagai Petugas maupun Owner) hanya dapat dibuat dan diberikan oleh Administrator sistem di lokasi Anda.</p>
                    </div>
                </div>
                <!-- FAQ 2 -->
                <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden transition-all shadow-sm">
                    <button class="w-full px-6 py-4 text-left flex justify-between items-center font-bold text-slate-900 hover:text-blue-600 focus:outline-none"
                            @click="activeFaq = activeFaq === 2 ? null : 2">
                        Saya pengunjung parkir, di mana saya bisa mendaftar?
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="activeFaq === 2 ? 'rotate-180' : ''"></i>
                    </button>
                    <div class="px-6 overflow-hidden transition-all duration-300 ease-in-out"
                         :class="activeFaq === 2 ? 'max-h-40 pb-4 opacity-100' : 'max-h-0 opacity-0'">
                        <p class="text-slate-600">SmarPARK dikhususkan untuk alat bantu pengelola parkir. Pengunjung tidak perlu memiliki aplikasi atau mendaftar; cukup ambil tiket dari Petugas di gerbang.</p>
                    </div>
                </div>
                <!-- FAQ 3 -->
                <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden transition-all shadow-sm">
                    <button class="w-full px-6 py-4 text-left flex justify-between items-center font-bold text-slate-900 hover:text-blue-600 focus:outline-none"
                            @click="activeFaq = activeFaq === 3 ? null : 3">
                        Bagaimana jika saya lupa password akun saya?
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="activeFaq === 3 ? 'rotate-180' : ''"></i>
                    </button>
                    <div class="px-6 overflow-hidden transition-all duration-300 ease-in-out"
                         :class="activeFaq === 3 ? 'max-h-40 pb-4 opacity-100' : 'max-h-0 opacity-0'">
                        <p class="text-slate-600">Silakan hubungi Administrator sistem di tempat kerja Anda. Mereka memiliki hak akses untuk melakukan reset password akun Anda secara langsung.</p>
                    </div>
                </div>
                <!-- FAQ 4 -->
                <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden transition-all shadow-sm">
                    <button class="w-full px-6 py-4 text-left flex justify-between items-center font-bold text-slate-900 hover:text-blue-600 focus:outline-none"
                            @click="activeFaq = activeFaq === 4 ? null : 4">
                        Apakah sistem ini bisa diakses lewat HP?
                        <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="activeFaq === 4 ? 'rotate-180' : ''"></i>
                    </button>
                    <div class="px-6 overflow-hidden transition-all duration-300 ease-in-out"
                         :class="activeFaq === 4 ? 'max-h-40 pb-4 opacity-100' : 'max-h-0 opacity-0'">
                        <p class="text-slate-600">Ya, SmarPARK sangat responsif. Owner dapat memantau laporan pendapatan langsung dari peramban HP, dan Petugas dapat menggunakan tablet/HP untuk input data.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 9. CTA Akhir -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-gradient-to-br from-blue-700 to-blue-900 rounded-[2.5rem] p-10 md:p-16 text-center text-white relative overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 w-64 h-64 bg-cyan-500 rounded-full blur-3xl opacity-30"></div>
                <div class="absolute bottom-0 left-0 translate-y-1/2 -translate-x-1/2 w-64 h-64 bg-blue-400 rounded-full blur-3xl opacity-30"></div>
                
                <div class="relative z-10 max-w-2xl mx-auto">
                    <i data-lucide="lock-keyhole" class="w-12 h-12 mx-auto mb-6 text-blue-300 opacity-80"></i>
                    <h2 class="text-3xl md:text-5xl font-bold mb-6 leading-tight">Akses Portal Pekerja</h2>
                    <p class="text-blue-100 text-lg mb-10">
                        Silakan login untuk memulai shift Anda hari ini atau melihat rekap laporan performa.
                    </p>
                    <a href="/login" class="inline-flex items-center gap-2 bg-white text-blue-700 hover:bg-cyan-50 px-8 py-4 rounded-full font-bold text-lg transition-all shadow-lg hover:shadow-xl hover:-translate-y-1">
                        Masuk ke SmarPARK <i data-lucide="arrow-right" class="w-5 h-5"></i>
                    </a>
                    <p class="mt-8 text-sm text-blue-200">
                        Belum punya akun? <span class="font-bold underline cursor-pointer">Hubungi Admin Gedung.</span>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- 10. Footer -->
    <footer class="bg-slate-900 text-slate-400 py-12 border-t border-slate-800">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6 text-center md:text-left">
                
                <!-- Logo & Desc -->
                <div>
                    <div class="flex items-center justify-center md:justify-start gap-2 mb-2">
                        <div class="bg-blue-600 text-white p-1.5 rounded-lg shadow-sm">
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                        </div>
                        <span class="text-xl font-bold text-white">SmarPARK</span>
                    </div>
                    <p class="text-sm">Sistem Manajemen Parkir Internal Khusus Pengelola.</p>
                </div>
                
                <!-- Links -->
                <div class="flex gap-6 text-sm font-medium">
                    <a href="#fitur" class="hover:text-blue-400 transition-colors">Fitur</a>
                    <a href="#peran" class="hover:text-blue-400 transition-colors">Peran</a>
                    <a href="#faq" class="hover:text-blue-400 transition-colors">Bantuan</a>
                </div>
                
                <!-- Contact Admin -->
                <div class="text-sm">
                    <p class="mb-1 text-slate-500">Butuh Bantuan IT?</p>
                    <a href="mailto:admin@smarpark.local" class="text-white hover:text-cyan-400 font-medium transition-colors">admin@smarpark.local</a>
                </div>
            </div>
            
            <div class="pt-8 mt-8 border-t border-slate-800 text-center text-sm text-slate-500">
                <p>&copy; 2026 SmarPARK. Sistem Internal Terdaftar. Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </footer>

    <!-- Initialize Lucide Icons -->
    <script>
      lucide.createIcons();
    </script>
</body>
</html>
