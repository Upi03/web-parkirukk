<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk {{ $tipe === 'masuk' ? 'Masuk' : 'Keluar' }} - {{ $transaksi->kode_tiket }} - {{ $settings['app_name'] }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&display=swap');
        
        body {
            font-family: 'Courier Prime', monospace;
            background-color: #f1f5f9;
            color: #000;
            margin: 0;
            padding: 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Container Struk */
        .receipt-container {
            transition: width 0.2s ease;
        }

        .receipt-80mm {
            width: 80mm;
        }

        .receipt-58mm {
            width: 58mm;
        }

        .receipt {
            background: #fff;
            padding: 14px;
            box-shadow: 0 4px 12px -2px rgba(0, 0, 0, 0.12);
            border-radius: 4px;
            box-sizing: border-box;
        }

        .center {
            text-align: center;
        }

        h2 {
            margin: 0 0 3px;
            font-size: 1.15rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .receipt-58mm h2 {
            font-size: 1rem;
        }

        p {
            margin: 0;
            font-size: 0.8rem;
            line-height: 1.35;
        }

        .receipt-58mm p {
            font-size: 0.72rem;
            line-height: 1.25;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .divider-solid {
            border-top: 1px solid #000;
            margin: 8px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            margin-bottom: 3px;
        }

        .receipt-58mm .row {
            font-size: 0.72rem;
            margin-bottom: 2px;
        }

        .bold {
            font-weight: bold;
        }

        .big {
            font-size: 1.1rem;
        }

        .receipt-58mm .big {
            font-size: 0.95rem;
        }

        .badge-type {
            display: inline-block;
            border: 1px solid #000;
            padding: 2px 8px;
            font-weight: bold;
            font-size: 0.75rem;
            margin: 6px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Action Toolbar (Tidak dicetak) */
        .toolbar {
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            background: #ffffff;
            padding: 12px 18px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            font-family: sans-serif;
        }

        .btn {
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid transparent;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
        }

        .btn-primary {
            background-color: #2563eb;
            color: #fff;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
        }

        .btn-secondary {
            background-color: #f1f5f9;
            color: #334155;
            border-color: #cbd5e1;
        }
        .btn-secondary:hover {
            background-color: #e2e8f0;
        }

        .btn-active {
            background-color: #0f172a;
            color: #fff;
        }

        @media print {
            body {
                background-color: #fff;
                padding: 0;
                margin: 0;
                display: block;
            }
            .toolbar {
                display: none !important;
            }
            .receipt-container {
                width: 100% !important;
            }
            .receipt {
                width: 100% !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                padding: 4px !important;
            }
        }
    </style>
</head>
<body>

    <!-- Toolbar Pengaturan Cetak -->
    <div class="toolbar">
        <a href="{{ route('petugas.transaksi.index') }}" class="btn btn-secondary">&larr; Transaksi</a>
        <a href="{{ route('petugas.struk.masuk') }}" class="btn btn-secondary">Daftar Struk Masuk</a>
        <a href="{{ route('petugas.struk.keluar') }}" class="btn btn-secondary">Daftar Struk Keluar</a>

        <!-- Switch Tipe Struk: Masuk vs Keluar -->
        <div style="display: flex; gap: 4px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 2px;">
            <a href="{{ route('petugas.struk.show', ['transaksi' => $transaksi->id, 'type' => 'masuk']) }}" 
                class="btn {{ $tipe === 'masuk' ? 'btn-active' : 'btn-secondary' }}" style="padding: 6px 10px;">
                &#127915; Struk Masuk
            </a>
            <a href="{{ route('petugas.struk.show', ['transaksi' => $transaksi->id, 'type' => 'keluar']) }}" 
                class="btn {{ $tipe === 'keluar' ? 'btn-active' : 'btn-secondary' }}" style="padding: 6px 10px;">
                &#128196; Struk Keluar
            </a>
        </div>

        <!-- Switch Ukuran Kertas -->
        <div style="display: flex; gap: 4px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 2px;">
            <button type="button" onclick="setPaperSize('80mm')" id="btn-80mm" class="btn btn-active" style="padding: 6px 10px;">
                80mm
            </button>
            <button type="button" onclick="setPaperSize('58mm')" id="btn-58mm" class="btn btn-secondary" style="padding: 6px 10px;">
                58mm
            </button>
        </div>

        <button type="button" onclick="window.print()" class="btn btn-primary">
            &#128438; Cetak Sekarang
        </button>
    </div>

    <!-- Tampilan Struk Fisik -->
    <div class="receipt-container receipt-80mm" id="receipt-box">
        <div class="receipt">
            <!-- Kop Tempat Parkir Asli dari Database -->
            <div class="center">
                <h2>{{ $settings['company_name'] }}</h2>
                <p class="bold">{{ $settings['app_name'] }}</p>
                <p>{{ $settings['company_address'] }}</p>
                <p>Telp: {{ $settings['company_phone'] }}</p>
                <div class="badge-type">
                    {{ $tipe === 'masuk' ? 'STRUK PARKIR MASUK' : 'STRUK PARKIR KELUAR' }}
                </div>
            </div>

            <div class="divider"></div>

            <div class="row">
                <span>Kode Tiket:</span>
                <span class="bold">{{ $transaksi->kode_tiket }}</span>
            </div>
            <div class="row">
                <span>Plat Nomor:</span>
                <span class="bold big">{{ $transaksi->plat_nomor }}</span>
            </div>
            <div class="row">
                <span>Jenis:</span>
                <span>{{ $transaksi->jenis_kendaraan }}</span>
            </div>
            <div class="row">
                <span>Area Parkir:</span>
                <span>{{ $transaksi->areaParkir->nama_area ?? 'Area Umum' }}</span>
            </div>
            <div class="row">
                <span>Waktu Masuk:</span>
                <span>{{ $transaksi->waktu_masuk->format('d/m/Y H:i') }}</span>
            </div>

            @if($tipe === 'keluar')
                <div class="row">
                    <span>Waktu Keluar:</span>
                    <span>{{ $transaksi->waktu_keluar ? $transaksi->waktu_keluar->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</span>
                </div>
                <div class="row">
                    <span>Durasi:</span>
                    <span>
                        @php
                            $durasiMnt = $transaksi->durasi_menit ?? max(1, $transaksi->waktu_masuk->diffInMinutes($transaksi->waktu_keluar ?? now()));
                        @endphp
                        {{ floor($durasiMnt / 60) }} Jam {{ $durasiMnt % 60 }} Menit
                    </span>
                </div>

                <div class="divider-solid"></div>

                <div class="row bold big">
                    <span>TOTAL BAYAR:</span>
                    <span>Rp {{ number_format($transaksi->total_bayar ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="row">
                    <span>Metode Bayar:</span>
                    <span class="bold">{{ $transaksi->metode_bayar ?? 'Tunai' }}</span>
                </div>
                @if(($transaksi->metode_bayar ?? 'Tunai') === 'Tunai')
                    <div class="row">
                        <span>Uang Diterima:</span>
                        <span>Rp {{ number_format($transaksi->uang_diterima ?? $transaksi->total_bayar, 0, ',', '.') }}</span>
                    </div>
                    <div class="row">
                        <span>Kembalian:</span>
                        <span>Rp {{ number_format($transaksi->kembalian ?? 0, 0, ',', '.') }}</span>
                    </div>
                @endif
            @else
                <!-- Detail Khusus Struk Masuk -->
                <div class="divider"></div>
                <div class="row">
                    <span>Tarif per Jam:</span>
                    <span>Rp {{ number_format($transaksi->tarif->tarif_per_jam ?? 0, 0, ',', '.') }}</span>
                </div>
                @if($transaksi->tarif && $transaksi->tarif->tarif_pertama)
                    <div class="row">
                        <span>Tarif Jam Ke-1:</span>
                        <span>Rp {{ number_format($transaksi->tarif->tarif_pertama, 0, ',', '.') }}</span>
                    </div>
                @endif
                @if($transaksi->tarif && $transaksi->tarif->tarif_maksimal)
                    <div class="row">
                        <span>Tarif Maksimal:</span>
                        <span>Rp {{ number_format($transaksi->tarif->tarif_maksimal, 0, ',', '.') }}/hari</span>
                    </div>
                @endif
            @endif

            <div class="divider"></div>

            <div class="row">
                <span>Petugas:</span>
                <span>{{ $transaksi->petugas->name ?? auth()->user()->name }}</span>
            </div>
            <div class="row">
                <span>Dicetak:</span>
                <span>{{ now()->format('d/m/Y H:i:s') }}</span>
            </div>

            <div class="divider"></div>

            <div class="center" style="margin-top: 8px;">
                @if($tipe === 'masuk')
                    <p class="bold">SIMPAN STRUK INI DENGAN BAIK</p>
                    <p>Struk tiket diperlukan saat pembayaran keluar.</p>
                    <p>Kehilangan karcis tiket dikenakan denda administrasi.</p>
                @else
                    <p class="bold">{{ $settings['footer_text'] }}</p>
                    <p>Semoga Selamat Sampai Tujuan</p>
                @endif
            </div>
        </div>
    </div>

    <script>
        function setPaperSize(size) {
            const box = document.getElementById('receipt-box');
            const btn80 = document.getElementById('btn-80mm');
            const btn58 = document.getElementById('btn-58mm');

            if (size === '58mm') {
                box.classList.remove('receipt-80mm');
                box.classList.add('receipt-58mm');
                btn58.className = 'btn btn-active';
                btn80.className = 'btn btn-secondary';
            } else {
                box.classList.remove('receipt-58mm');
                box.classList.add('receipt-80mm');
                btn80.className = 'btn btn-active';
                btn58.className = 'btn btn-secondary';
            }
        }

        // Terapkan preferensi default dari database setting
        const defaultSize = "{{ $settings['default_paper_size'] ?? '80mm' }}";
        if (defaultSize === '58mm') {
            setPaperSize('58mm');
        }
    </script>
</body>
</html>
