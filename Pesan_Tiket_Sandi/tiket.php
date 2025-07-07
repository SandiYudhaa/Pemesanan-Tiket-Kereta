<?php
// Memulai session untuk mengelola data pengguna
session_start();

// Menyertakan file konfigurasi dan fungsi pendukung
include 'config.php';
include 'functions.php';

// Memeriksa apakah pengguna sudah login, jika tidak redirect ke halaman index
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Mengambil data pengguna dari session
$user = $_SESSION['user'];

// Mendapatkan daftar pemesanan tiket pengguna
$bookings = getUserBookings($conn, $user['id']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <!-- Meta tags untuk pengaturan dokumen HTML -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiket Saya - PT KASA</title>
    
    <!-- Menyertakan CSS Bootstrap dan ikon -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Menyertakan font Poppins dari Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Gaya CSS khusus -->
    <style>
        /* Variabel warna untuk tema */
        :root {
            --kasa-red: #e31837;
            --kasa-dark-red: #c1122d;
            --kasa-dark: #1a1a2e;
            --kasa-light: #f8f9fa;
            --kasa-blue: #16213e;
            --kasa-gradient: linear-gradient(135deg, var(--kasa-red) 0%, var(--kasa-blue) 100%);
        }
        
        /* Gaya dasar body */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
        }
        
        /* Gaya sidebar */
        .sidebar {
            background-color: var(--kasa-dark);
            color: white;
            min-height: 100vh;
            position: fixed;
            width: 250px;
            transition: all 0.3s;
            z-index: 1000;
        }
        
        /* Header sidebar */
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Logo di sidebar */
        .sidebar-logo {
            width: 100%;
            max-width: 150px;
            margin-bottom: 10px;
        }
        
        /* Menu sidebar */
        .sidebar-menu {
            padding: 15px 0;
        }
        
        /* Tautan menu sidebar */
        .sidebar-menu .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 10px 20px;
            margin: 5px 0;
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }
        
        /* Efek hover pada tautan menu */
        .sidebar-menu .nav-link:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        /* Tautan menu aktif */
        .sidebar-menu .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 3px solid var(--kasa-red);
        }
        
        /* Ikon menu sidebar */
        .sidebar-menu .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* Gaya konten utama */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }
        
        /* Gaya navbar atas */
        .navbar-kasa {
            background-color: white;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
        }
        
        /* Gaya kartu tiket */
        .ticket-card {
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            border: none;
            margin-bottom: 20px;
        }
        
        /* Efek hover pada kartu tiket */
        .ticket-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        /* Header kartu tiket */
        .ticket-header {
            background: var(--kasa-gradient);
            color: white;
            padding: 15px;
            position: relative;
        }
        
        /* Status tiket */
        .ticket-status {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        /* Body kartu tiket */
        .ticket-body {
            padding: 20px;
            background-color: white;
        }
        
        /* Garis rute perjalanan */
        .route-line {
            display: flex;
            align-items: center;
            margin: 15px 0;
        }
        
        /* Titik rute */
        .route-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: var(--kasa-red);
        }
        
        /* Garis penghubung rute */
        .route-line-connector {
            flex-grow: 1;
            height: 2px;
            background-color: #ddd;
            margin: 0 10px;
        }
        
        /* Waktu stasiun */
        .station-time {
            font-weight: 600;
            font-size: 18px;
        }
        
        /* Nama stasiun */
        .station-name {
            font-size: 14px;
            color: #666;
        }
        
        /* Informasi kereta */
        .train-info {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        
        /* QR Code */
        .qr-code {
            width: 100%;
            max-width: 150px;
            margin: 0 auto;
            display: block;
        }
        
        /* Tombol dengan tema KASA */
        .btn-kasa {
            background: var(--kasa-gradient);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        /* Efek hover tombol KASA */
        .btn-kasa:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(227, 24, 55, 0.3);
            color: white;
        }
        
        /* Tombol outline dengan tema KASA */
        .btn-outline-kasa {
            border: 2px solid var(--kasa-red);
            color: var(--kasa-red);
            background: transparent;
            font-weight: 500;
        }
        
        /* Efek hover tombol outline KASA */
        .btn-outline-kasa:hover {
            background: var(--kasa-red);
            color: white;
        }
        
        /* Badge dengan tema KASA */
        .badge-kasa {
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            font-weight: 500;
        }
        
        /* Responsivitas untuk perangkat medium */
        @media (max-width: 992px) {
            .sidebar {
                left: -250px;
            }
            
            .sidebar.active {
                left: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
        
        /* Responsivitas untuk perangkat kecil */
        @media (max-width: 768px) {
            .ticket-card {
                margin-bottom: 15px;
            }
            
            .station-time {
                font-size: 16px;
            }
            
            .ticket-body {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Navigasi -->
    <div class="sidebar">
        <!-- Header Sidebar -->
        <div class="sidebar-header text-center">
            <!-- Logo Perusahaan -->
            <img src="/Pesan_Tiket_Sandi/img/Kasa-Logo.png" alt="PT KASA Logo" class="sidebar-logo">
            <h6 class="text-white">PT-KASA INDONESIA</h6>
        </div>
        
        <!-- Menu Sidebar -->
        <div class="sidebar-menu">
            <ul class="nav flex-column">
                <!-- Menu Beranda -->
                <li class="nav-item">
                    <a class="nav-link" href="home.php">
                        <i class="bi bi-house-door"></i> Beranda
                    </a>
                </li>
                
                <!-- Menu Jadwal Kereta -->
                <li class="nav-item">
                    <a class="nav-link" href="jadwal.php">
                        <i class="bi bi-train-front"></i> Jadwal Kereta
                    </a>
                </li>
                
                <!-- Menu Tiket Saya (aktif) -->
                <li class="nav-item">
                    <a class="nav-link active" href="tiket.php">
                        <i class="bi bi-ticket-perforated"></i> Tiket Saya
                    </a>
                </li>
                
                <!-- Menu Riwayat -->
                <li class="nav-item">
                    <a class="nav-link" href="riwayat.php">
                        <i class="bi bi-clock-history"></i> Riwayat
                    </a>
                </li>
                
                <!-- Menu Profil -->
                <li class="nav-item">
                    <a class="nav-link" href="profile.php">
                        <i class="bi bi-person"></i> Profil
                    </a>
                </li>
                
                <!-- Menu Keluar -->
                <li class="nav-item mt-3">
                    <a class="nav-link" href="logout.php">
                        <i class="bi bi-box-arrow-right"></i> Keluar
                    </a>
                </li>
            </ul>
        </div>
    </div>
    
    <!-- Konten Utama -->
    <div class="main-content">
        <!-- Navbar Atas -->
        <nav class="navbar navbar-expand-lg navbar-kasa">
            <div class="container-fluid">
                <div class="d-flex align-items-center">
                    <!-- Tombol Toggle Sidebar (mobile) -->
                    <button class="btn btn-outline-secondary me-3 d-lg-none" id="sidebarToggle">
                        <i class="bi bi-list"></i>
                    </button>
                    <h5 class="mb-0 fw-bold"><i class="bi bi-ticket-perforated me-2"></i> Tiket Saya</h5>
                </div>
            </div>
        </nav>
        
        <!-- Konten Tiket -->
        <div class="container py-4">
            <!-- Header dan Filter -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold"><i class="bi bi-ticket-perforated me-2"></i> Tiket Saya</h2>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                        <li><a class="dropdown-item" href="?filter=all">Semua Tiket</a></li>
                        <li><a class="dropdown-item" href="?filter=active">Aktif</a></li>
                        <li><a class="dropdown-item" href="?filter=completed">Selesai</a></li>
                        <li><a class="dropdown-item" href="?filter=canceled">Dibatalkan</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Pesan Sukses -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <!-- Daftar Tiket -->
            <?php if (count($bookings) > 0): ?>
                <div class="row">
                    <?php foreach ($bookings as $booking): ?>
                        <div class="col-lg-6">
                            <!-- Kartu Tiket -->
                            <div class="ticket-card">
                                <!-- Header Tiket -->
                                <div class="ticket-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-0"><?= htmlspecialchars($booking['nama_kereta']) ?></h5>
                                            <small><?= htmlspecialchars($booking['kelas']) ?></small>
                                        </div>
                                        <!-- Status Tiket -->
                                        <span class="ticket-status badge <?= $booking['status'] == 'selesai' ? 'bg-success' : ($booking['status'] == 'dibatalkan' ? 'bg-danger' : 'bg-warning') ?>">
                                            <?= ucfirst(htmlspecialchars($booking['status'])) ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Body Tiket -->
                                <div class="ticket-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <!-- Rute Perjalanan -->
                                            <div class="route-line">
                                                <div class="text-end">
                                                    <div class="station-time"><?= date('H:i', strtotime($booking['jam_berangkat'])) ?></div>
                                                    <div class="station-name"><?= htmlspecialchars($booking['stasiun_asal']) ?></div>
                                                </div>
                                                <div class="route-dot"></div>
                                                <div class="route-line-connector"></div>
                                                <div class="route-dot"></div>
                                                <div>
                                                    <div class="station-time"><?= date('H:i', strtotime($booking['jam_tiba'])) ?></div>
                                                    <div class="station-name"><?= htmlspecialchars($booking['stasiun_tujuan']) ?></div>
                                                </div>
                                            </div>
                                            
                                            <!-- Informasi Kereta -->
                                            <div class="train-info">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <small class="text-muted">Tanggal</small>
                                                        <div><?= date('d M Y', strtotime($booking['tanggal_berangkat'])) ?></div>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted">Durasi</small>
                                                        <div><?= calculateDuration($booking['jam_berangkat'], $booking['jam_tiba']) ?></div>
                                                    </div>
                                                    <div class="col-6 mt-2">
                                                        <small class="text-muted">No. Tempat Duduk</small>
                                                        <div><?= htmlspecialchars($booking['nomor_kursi']) ?></div>
                                                    </div>
                                                    <div class="col-6 mt-2">
                                                        <small class="text-muted">Kode Booking</small>
                                                        <div class="fw-bold"><?= htmlspecialchars($booking['kode_booking']) ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Tombol Aksi -->
                                            <div class="d-flex justify-content-between mt-3">
                                                <a href="cetak_tiket.php?id=<?= $booking['id'] ?>" class="btn btn-outline-kasa" target="_blank">
                                                    <i class="bi bi-printer"></i> Cetak
                                                </a>
                                                <?php if ($booking['status'] == 'aktif'): ?>
                                                    <a href="batalkan_tiket.php?id=<?= $booking['id'] ?>" class="btn btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin membatalkan tiket ini?')">
                                                        <i class="bi bi-x-circle"></i> Batalkan
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <!-- QR Code Tiket -->
                                        <div class="col-md-4 text-center mt-3 mt-md-0">
                                            <img src="generate_qr.php?code=<?= urlencode($booking['kode_booking']) ?>" alt="QR Code" class="qr-code">
                                            <small class="text-muted mt-2 d-block">Scan QR Code untuk verifikasi</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Tampilan jika tidak ada tiket -->
                <div class="card text-center py-5">
                    <div class="card-body">
                        <i class="bi bi-ticket-perforated display-4 text-muted mb-3"></i>
                        <h4 class="text-muted">Belum ada tiket</h4>
                        <p class="text-muted">Anda belum memiliki tiket aktif. Pesan tiket Anda sekarang!</p>
                        <a href="jadwal.php" class="btn btn-kasa px-4">
                            <i class="bi bi-search"></i> Cari Jadwal
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Menyertakan JavaScript Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Skrip untuk toggle sidebar pada perangkat mobile -->
    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>