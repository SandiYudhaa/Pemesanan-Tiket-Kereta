<?php
// Memulai session untuk menyimpan data pengguna
session_start();

// Menyertakan file konfigurasi database dan fungsi-fungsi pendukung
include 'config.php';
include 'functions.php';

// Memeriksa apakah pengguna sudah login, jika tidak redirect ke halaman index
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Mengambil data user dari session
$user = $_SESSION['user'];

// Mengambil data booking/pemesanan user dari database
$bookings = getUserBookings($conn, $user['id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags untuk pengaturan dasar halaman -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pemesanan - PT-KASA Indonesia</title>
    
    <!-- Menyertakan CSS Bootstrap dan ikon -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Menyertakan font Poppins dari Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS kustom untuk halaman ini -->
    <style>
        /* Variabel warna kustom untuk tema */
        :root {
            --kai-red: #e31837;
            --kai-dark: #1a1a2e;
            --kai-light: #f8f9fa;
            --kai-blue: #16213e;
            --kai-gradient: linear-gradient(135deg, #e31837 0%, #16213e 100%);
        }
        
        /* Styling dasar untuk body dengan background gambar */
        body {
            background: url('https://images.unsplash.com/photo-1516054575922-f0b8eeadec1a?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Poppins', sans-serif;
            position: relative;
            min-height: 100vh;
        }
        
        /* Overlay gelap untuk background */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: -1;
        }
        
        /* Styling untuk card utama */
        .card-kai {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.9);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        /* Efek hover pada card */
        .card-kai:hover {
            transform: translateY(-5px);
        }
        
        /* Styling untuk header card */
        .header-kai {
            background: var(--kai-gradient);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        /* Styling untuk header tabel */
        .table th {
            background: var(--kai-gradient);
            color: white;
            position: sticky;
            top: 0;
        }
        
        /* Styling untuk tombol utama */
        .btn-kai {
            background: var(--kai-gradient);
            color: white;
            border-radius: 8px;
            border: none;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 0.5rem 1.25rem;
        }
        
        /* Efek hover pada tombol */
        .btn-kai:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(227, 24, 55, 0.3);
            color: white;
        }
        
        /* Styling untuk tombol outline */
        .btn-outline-kai {
            border: 2px solid var(--kai-red);
            color: var(--kai-red);
            background: transparent;
            font-weight: 500;
        }
        
        /* Efek hover tombol outline */
        .btn-outline-kai:hover {
            background: var(--kai-red);
            color: white;
        }
        
        /* Styling untuk badge */
        .badge-kai {
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            font-weight: 500;
        }
        
        /* Styling untuk ikon kereta */
        .train-icon {
            color: var(--kai-red);
            font-size: 1.5rem;
        }
        
        /* Styling untuk state kosong */
        .empty-state {
            padding: 3rem 0;
        }
        
        /* Styling untuk ikon state kosong */
        .empty-icon {
            font-size: 5rem;
            color: var(--kai-red);
            margin-bottom: 1.5rem;
        }
        
        /* Styling untuk card booking */
        .booking-card {
            border-left: 4px solid var(--kai-red);
            transition: all 0.3s ease;
        }
        
        /* Efek hover pada card booking */
        .booking-card:hover {
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        /* Responsive design untuk layar kecil */
        @media (max-width: 768px) {
            .table-responsive {
                border-radius: 15px;
                overflow: hidden;
            }
            
            /* Menyembunyikan header tabel di mobile */
            .table thead {
                display: none;
            }
            
            /* Mengubah tampilan baris tabel di mobile */
            .table tr {
                display: block;
                margin-bottom: 1.5rem;
                background: white;
                border-radius: 10px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            }
            
            /* Mengubah tampilan sel tabel di mobile */
            .table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.75rem 1rem;
                border-bottom: 1px solid #f0f0f0;
            }
            
            /* Menambahkan label untuk sel tabel di mobile */
            .table td::before {
                content: attr(data-label);
                font-weight: 600;
                margin-right: 1rem;
                color: var(--kai-blue);
            }
            
            /* Menghilangkan border bottom untuk sel terakhir */
            .table td:last-child {
                border-bottom: none;
            }
        }
    </style>
</head>
<body>
    <!-- Kontainer utama -->
    <div class="container py-4">
        <!-- Card utama untuk menampilkan riwayat pemesanan -->
        <div class="card card-kai mb-4">
            <!-- Header card -->
            <div class="header-kai">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="fw-bold mb-0"><i class="bi bi-train-front train-icon me-2"></i> Riwayat Pemesanan</h2>
                        <p class="mb-0">Semua perjalanan kereta Anda dalam satu tempat</p>
                    </div>
                    <!-- Tombol kembali -->
                    <a href="home.php" class="btn btn-outline-light">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            
            <!-- Body card -->
            <div class="card-body">
                <?php if (count($bookings) > 0): ?>
                <!-- Tabel riwayat pemesanan jika ada data -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Kode Booking</th>
                                <th>Kereta</th>
                                <th>Rute</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                            <!-- Baris data untuk setiap pemesanan -->
                            <tr class="booking-card">
                                <td data-label="Kode Booking">
                                    <span class="badge bg-light text-dark p-2"><?= htmlspecialchars($booking['kode_booking']) ?></span>
                                </td>
                                <td data-label="Kereta">
                                    <div>
                                        <strong><?= htmlspecialchars($booking['nama_kereta']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($booking['kelas']) ?></small>
                                    </div>
                                </td>
                                <td data-label="Rute">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <div><?= htmlspecialchars($booking['stasiun_asal']) ?></div>
                                            <div class="text-muted small"><?= date('H:i', strtotime($booking['jam_berangkat'])) ?></div>
                                        </div>
                                        <i class="bi bi-arrow-right mx-2 text-kai-red"></i>
                                        <div>
                                            <div><?= htmlspecialchars($booking['stasiun_tujuan']) ?></div>
                                            <div class="text-muted small"><?= date('H:i', strtotime($booking['jam_tiba'])) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Tanggal">
                                    <?= date('d M Y', strtotime($booking['tanggal_berangkat'])) ?>
                                </td>
                                <td data-label="Status">
                                    <!-- Badge status dengan warna berbeda sesuai status -->
                                    <span class="badge badge-kai <?= $booking['status'] == 'selesai' ? 'bg-success' : ($booking['status'] == 'dibatalkan' ? 'bg-danger' : 'bg-warning') ?>">
                                        <?= ucfirst(htmlspecialchars($booking['status'])) ?>
                                    </span>
                                </td>
                                <td data-label="Aksi">
                                    <!-- Tombol untuk melihat detail pemesanan -->
                                    <a href="booking_detail.php?id=<?= $booking['id'] ?>" class="btn btn-kai btn-sm">
                                        <i class="bi bi-eye-fill"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <!-- Tampilan jika tidak ada riwayat pemesanan -->
                <div class="empty-state text-center py-5">
                    <i class="bi bi-clock-history empty-icon"></i>
                    <h3 class="mb-3">Belum ada riwayat pemesanan</h3>
                    <p class="text-muted mb-4">Anda belum melakukan pemesanan tiket kereta. Mulai pesan tiket pertama Anda sekarang!</p>
                    <!-- Tombol untuk memesan tiket -->
                    <a href="booking.php" class="btn btn-kai px-4 py-2">
                        <i class="bi bi-ticket-perforated"></i> Pesan Tiket Sekarang
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="text-center text-white mt-4 mb-3">
            <p class="mb-0">© <?= date('Y') ?> PT-KASA Indonesia. All rights reserved.</p>
        </footer>
    </div>

    <!-- JavaScript Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script untuk menangani tampilan mobile -->
    <script>
        // Menambahkan formatting khusus untuk mobile
        document.addEventListener('DOMContentLoaded', function() {
            if (window.innerWidth <= 768) {
                const cells = document.querySelectorAll('td');
                cells.forEach(cell => {
                    const header = cell.parentNode.querySelector('th');
                    if (header) {
                        cell.setAttribute('data-label', header.textContent);
                    }
                });
            }
        });
    </script>
</body>
</html>