<?php
// cetak_invoice.php
require_once '../config/database.php';
check_login();

// Cek apakah parameter ID ada
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("❌ ID pesanan tidak valid");
}

$id_pesanan = clean_input($_GET['id']);

// Ambil data pesanan utama
try {
    $sql_pesanan = "SELECT p.*, 
                   pel.nama AS nama_pelanggan, 
                   pel.alamat AS alamat_pelanggan,
                   pel.no_hp AS telepon_pelanggan,
                   u.nama_lengkap AS nama_karyawan
            FROM data_pesanan p
            LEFT JOIN data_pelanggan pel ON p.id_pelanggan = pel.id_pelanggan
            LEFT JOIN users u ON p.id_karyawan = u.id_user
            WHERE p.id_pesanan = ?";
    
    $pesanan = getSingle($sql_pesanan, [$id_pesanan]);
    
    if (!$pesanan) {
        die("❌ Pesanan tidak ditemukan");
    }
    
} catch (PDOException $e) {
    die("❌ Gagal memuat data pesanan: " . $e->getMessage());
}

// Ambil data transaksi
try {
    $sql_transaksi = "SELECT * FROM data_transaksi WHERE id_pesanan = ? ORDER BY tgl_transaksi ASC";
    $transaksi = getAll($sql_transaksi, [$id_pesanan]);
} catch (PDOException $e) {
    $transaksi = [];
}

// Format status text
$status_text = '';
switch($pesanan['status_pesanan']) {
    case 'belum':
        $status_text = 'Belum Diproses';
        break;
    case 'dalam_proses':
        $status_text = 'Dalam Proses';
        break;
    case 'selesai':
        $status_text = 'Selesai';
        break;
    default:
        $status_text = $pesanan['status_pesanan'];
}

// Hitung total sudah dibayar
$total_sudah_bayar = 0;
foreach ($transaksi as $trx) {
    $total_sudah_bayar += $trx['jumlah_bayar'];
}

$sisa_bayar = $pesanan['total_harga'] - $total_sudah_bayar;

// Informasi perusahaan
$nama_perusahaan = "PARAPATAN TAILOR";
$alamat_perusahaan = "Jl. Baru Awirarangan No. 123, Kota Kuningan";
$telepon_perusahaan = "(+62) 876-1234-5678";
$email_perusahaan = "info@parapatantailor.com";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= $pesanan['id_pesanan']; ?> - <?= $nama_perusahaan; ?></title>
     <link rel="shortcut icon" href="../assets/images/logoterakhir.png" type="image/x-icon" />
    <style>
        /* Reset dan Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: #fff;
            padding: 20px;
        }
        
        /* Container Invoice */
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }
        
        /* Header Invoice */
        .invoice-header {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            padding: 25px 30px;
            text-align: center;
        }
        
        .company-info h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }
        
        .company-info .tagline {
            font-size: 12px;
            opacity: 0.9;
            margin-bottom: 10px;
        }
        
        .company-contact {
            font-size: 10px;
            opacity: 0.8;
            margin-top: 8px;
        }
        
        /* Invoice Title */
        .invoice-title {
            text-align: center;
            padding: 15px;
            background: #f8fafc;
            border-bottom: 2px solid #4f46e5;
        }
        
        .invoice-title h2 {
            font-size: 20px;
            color: #4f46e5;
            font-weight: bold;
        }
        
        .invoice-number {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        
        /* Invoice Body */
        .invoice-body {
            padding: 25px 30px;
        }
        
        /* Section Styles */
        .section {
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .section-title i {
            font-size: 12px;
        }
        
        /* Grid Layout */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .info-card {
            background: #f8fafc;
            padding: 12px;
            border-radius: 6px;
            border-left: 3px solid #4f46e5;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            padding: 2px 0;
        }
        
        .info-label {
            font-weight: 600;
            color: #374151;
            min-width: 120px;
        }
        
        .info-value {
            color: #6b7280;
            text-align: right;
            flex: 1;
        }
        
        .highlight {
            color: #4f46e5;
            font-weight: 600;
        }
        
        /* Order Details */
        .order-details {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 6px;
            padding: 15px;
            margin: 15px 0;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
        }
        
        .detail-label {
            font-weight: 600;
            color: #0c4a6e;
        }
        
        .detail-value {
            color: #0369a1;
            font-weight: 600;
        }
        
        /* Payment Summary */
        .payment-summary {
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .payment-header {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .payment-header h3 {
            color: #0c4a6e;
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .payment-amounts {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            text-align: center;
        }
        
        .amount-item {
            padding: 12px;
            border-radius: 6px;
            background: white;
        }
        
        .amount-label {
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .amount-value {
            font-size: 16px;
            font-weight: bold;
        }
        
        .amount-total { color: #059669; }
        .amount-paid { color: #2563eb; }
        .amount-remaining { color: #dc2626; }
        
        /* Transaction Table */
        .transaction-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10px;
            background: white;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .transaction-table th {
            background: #4f46e5;
            color: white;
            font-weight: 600;
            padding: 8px 10px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .transaction-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
            color: #374151;
        }
        
        .transaction-table tr:last-child td {
            border-bottom: none;
        }
        
        .transaction-table tr:nth-child(even) {
            background: #fafbff;
        }
        
        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-belanja { background: #fef3c7; color: #d97706; }
        .status-proses { background: #dbeafe; color: #1d4ed8; }
        .status-selesai { background: #dcfce7; color: #166534; }
        .status-lunas { background: #dcfce7; color: #166534; }
        .status-dp { background: #fef3c7; color: #d97706; }
        .status-belum { background: #fee2e2; color: #dc2626; }
        
        /* Footer */
        .invoice-footer {
            background: #f8fafc;
            padding: 20px 30px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
        }
        
        .terms {
            font-size: 9px;
            color: #6b7280;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        
        .thank-you {
            font-size: 11px;
            color: #4f46e5;
            font-weight: 600;
            margin-top: 10px;
        }
        
        .print-info {
            font-size: 8px;
            color: #9ca3af;
            margin-top: 8px;
        }
        
        /* Utility Classes */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .mb-10 { margin-bottom: 10px; }
        .mb-15 { margin-bottom: 15px; }
        .mt-10 { margin-top: 10px; }
        .mt-15 { margin-top: 15px; }
        
        /* Print Styles */
        @media print {
            body {
                padding: 0;
                background: white;
            }
            
            .invoice-container {
                border: none;
                box-shadow: none;
                margin: 0;
                max-width: 100%;
            }
            
            .no-print {
                display: none;
            }
            
            .invoice-header {
                background: #4f46e5 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .payment-summary {
                background: #f0f9ff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .transaction-table th {
                background: #4f46e5 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        
        /* No Print Section */
        .no-print {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: #f8fafc;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }
        
        .print-btn {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .print-btn:hover {
            background: #4338ca;
            transform: translateY(-1px);
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="company-info">
                <h1><?= $nama_perusahaan; ?></h1>
                <div class="tagline">Kualitas Terbaik untuk Penampilan Terbaik Anda</div>
                <div class="company-contact">
                    <?= $alamat_perusahaan; ?> | <?= $telepon_perusahaan; ?> | <?= $email_perusahaan; ?>
                </div>
            </div>
        </div>
        
        <!-- Invoice Title -->
        <div class="invoice-title">
            <h2>INVOICE</h2>
            <div class="invoice-number">No: INV/<?= date('Y/m/') . $pesanan['id_pesanan']; ?></div>
        </div>
        
        <!-- Invoice Body -->
        <div class="invoice-body">
            <!-- Informasi Dasar -->
            <div class="section">
                <div class="info-grid">
                    <div class="info-card">
                        <div class="section-title">
                            <i class="fas fa-user"></i> Informasi Pelanggan
                        </div>
                        <div class="info-row">
                            <span class="info-label">Nama:</span>
                            <span class="info-value highlight"><?= htmlspecialchars($pesanan['nama_pelanggan']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Alamat:</span>
                            <span class="info-value"><?= htmlspecialchars($pesanan['alamat_pelanggan'] ?? '-'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Telepon:</span>
                            <span class="info-value"><?= htmlspecialchars($pesanan['telepon_pelanggan'] ?? '-'); ?></span>
                        </div>
                    </div>
                    
                    <div class="info-card">
                        <div class="section-title">
                            <i class="fas fa-receipt"></i> Informasi Pesanan
                        </div>
                        <div class="info-row">
                            <span class="info-label">ID Pesanan:</span>
                            <span class="info-value highlight">#<?= $pesanan['id_pesanan']; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Tanggal Pesan:</span>
                            <span class="info-value"><?= date('d/m/Y', strtotime($pesanan['tgl_pesanan'])); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Status:</span>
                            <span class="info-value">
                                <span class="status-badge status-<?= $pesanan['status_pesanan']; ?>">
                                    <?= $status_text; ?>
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Detail Pesanan -->
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-tshirt"></i> Detail Pesanan
                </div>
                <div class="order-details">
                    <div class="detail-row">
                        <span class="detail-label">Jenis Pakaian:</span>
                        <span class="detail-value"><?= htmlspecialchars($pesanan['jenis_pakaian'] ?? '-'); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Bahan:</span>
                        <span class="detail-value"><?= htmlspecialchars($pesanan['bahan'] ?? '-'); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Karyawan:</span>
                        <span class="detail-value"><?= htmlspecialchars($pesanan['nama_karyawan'] ?? '-'); ?></span>
                    </div>
                    <?php if (!empty($pesanan['catatan'])): ?>
                    <div class="detail-row">
                        <span class="detail-label">Catatan:</span>
                        <span class="detail-value"><?= htmlspecialchars($pesanan['catatan']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Ringkasan Pembayaran -->
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-money-bill-wave"></i> Ringkasan Pembayaran
                </div>
                <div class="payment-summary">
                    <div class="payment-header">
                        <h3>TOTAL PEMBAYARAN</h3>
                    </div>
                    <div class="payment-amounts">
                        <div class="amount-item">
                            <div class="amount-label">Total Harga</div>
                            <div class="amount-value amount-total">Rp <?= number_format($pesanan['total_harga'], 0, ',', '.'); ?></div>
                        </div>
                        <div class="amount-item">
                            <div class="amount-label">Sudah Dibayar</div>
                            <div class="amount-value amount-paid">Rp <?= number_format($total_sudah_bayar, 0, ',', '.'); ?></div>
                        </div>
                        <div class="amount-item">
                            <div class="amount-label">Sisa Bayar</div>
                            <div class="amount-value amount-remaining">Rp <?= number_format($sisa_bayar, 0, ',', '.'); ?></div>
                        </div>
                    </div>
                    
                    <!-- Status Pembayaran -->
                    <div class="text-center mt-15">
                        <strong>Status Pembayaran: </strong>
                        <?php if ($sisa_bayar == 0): ?>
                            <span class="status-badge status-lunas">LUNAS</span>
                        <?php elseif ($total_sudah_bayar == 0): ?>
                            <span class="status-badge status-belum">BELUM BAYAR</span>
                        <?php else: ?>
                            <span class="status-badge status-dp">DP / CICILAN</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Riwayat Transaksi -->
            <?php if (!empty($transaksi)): ?>
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-history"></i> Riwayat Transaksi
                </div>
                <table class="transaction-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Jumlah Bayar</th>
                            <th>Metode</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transaksi as $index => $trx): ?>
                        <tr>
                            <td><?= $index + 1; ?></td>
                            <td><?= date('d/m/Y', strtotime($trx['tgl_transaksi'])); ?></td>
                            <td class="text-bold">Rp <?= number_format($trx['jumlah_bayar'], 0, ',', '.'); ?></td>
                            <td><?= htmlspecialchars($trx['metode_bayar'] ?? 'Cash'); ?></td>
                            <td><?= htmlspecialchars($trx['keterangan'] ?? '-'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Footer -->
        <div class="invoice-footer">
            <div class="terms">
                <strong>Ketentuan:</strong><br>
                1. Pembayaran dapat dilakukan via transfer atau tunai<br>
                2. Pesanan dapat diambil setelah pembayaran lunas<br>
                3. Garansi jahitan 30 hari dari tanggal pengambilan<br>
                4. Perubahan desain setelah produksi dikenakan biaya tambahan
            </div>
            <div class="thank-you">
                Terima kasih atas kepercayaan Anda kepada <?= $nama_perusahaan; ?>
            </div>
            <div class="print-info">
                Invoice dicetak pada: <?= date('d/m/Y H:i:s'); ?>
            </div>
        </div>
    </div>
    
    <!-- Print Button (Tidak akan tercetak) -->
    <div class="no-print">
        <button class="print-btn" onclick="window.print()">
            <i class="fas fa-print"></i> Cetak Invoice
        </button>
        <a href="detail_pesanan.php?id=<?= $pesanan['id_pesanan']; ?>" class="print-btn" style="background: #6b7280; margin-left: 10px;">
            <i class="fas fa-arrow-left"></i> Kembali ke Detail
        </a>
    </div>

    <script>
        // Auto print jika diinginkan
        document.addEventListener('DOMContentLoaded', function() {
            // Uncomment baris berikut untuk auto print
            // window.print();
            
            // Tambahkan event listener untuk tombol print
            document.querySelector('.print-btn').addEventListener('click', function() {
                window.print();
            });
        });
        
        // Keyboard shortcut untuk print (Ctrl + P)
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>