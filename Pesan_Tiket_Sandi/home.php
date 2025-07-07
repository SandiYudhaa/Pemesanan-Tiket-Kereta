<?php
// Memulai session untuk menyimpan data pengguna
session_start();

// Menyertakan file konfigurasi database dan fungsi pendukung
include 'config.php';
include 'functions.php';

// Memeriksa apakah pengguna sudah login, jika tidak redirect ke halaman login
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Mengambil data user dari session
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags dasar untuk halaman -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - PT-KASA Indonesia</title>
    
    <!-- Menyertakan CSS Bootstrap dan ikon -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- CSS kustom untuk halaman -->
    <style>
        /* Variabel warna untuk tema */
        :root {
            --kai-red: #e31837;
            --kai-dark: #1a1a2e;
            --kai-light: #f8f9fa;
            --kai-gray: #6c757d;
        }
        
        /* Styling dasar body */
        body {
            background-color: var(--kai-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        /* Styling untuk sidebar */
        .sidebar {
            background-color: var(--kai-dark);
            color: white;
            min-height: 100vh;
            position: fixed;
            width: 250px;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        /* Container logo di sidebar */
        .sidebar .logo-container {
            padding: 20px 15px 15px;
            margin-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Styling untuk logo */
        .sidebar .logo-container img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        /* Card hero utama */
        .hero-card {
            border-radius: 15px;
            overflow: hidden;
            position: relative;
            color: white;
            min-height: 250px;
            display: flex;
            align-items: center;
        }

        /* Efek overlay untuk card hero */
        .hero-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('/Project-Web_UAS/img/Logo-Kasa.jfif') center/cover no-repeat;
            background-size: contain;
            opacity: 0.2;
            z-index: 0;
        }

        /* Konten dalam card hero */
        .hero-card .card-body {
            position: relative;
            z-index: 1;
        }

        /* Animasi ilustrasi kereta */
        .train-illustration {
            animation: float 3s ease-in-out infinite;
            filter: drop-shadow(0 5px 15px rgba(0,0,0,0.3));
            transition: all 0.5s ease;
        }

        /* Efek hover pada ilustrasi kereta */
        .train-illustration:hover {
            transform: scale(1.05) translateY(-10px);
            filter: drop-shadow(0 8px 25px rgba(0,0,0,0.4));
        }
        
        /* Class untuk sidebar yang collapsed */
        .sidebar-collapsed {
            transform: translateX(-250px);
        }
        
        /* Styling untuk link navigasi */
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 5px;
            border-radius: 5px;
            padding: 10px 15px;
            transition: all 0.2s;
            font-size: 0.95rem;
        }
        
        /* Efek hover dan active pada link navigasi */
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background-color: var(--kai-red);
            transform: translateX(5px);
        }
        
        /* Ikon di navigasi */
        .sidebar .nav-link i {
            margin-right: 10px;
            font-size: 1.1rem;
        }
        
        /* Area konten utama */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s ease;
            min-height: 100vh;
        }
        
        /* Class untuk konten utama yang expanded */
        .main-content-expanded {
            margin-left: 0;
        }
        
        /* Styling untuk navbar */
        .navbar-kai {
            background-color: white;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
        }
        
        /* Styling untuk card umum */
        .card-kai {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            height: 100%;
            margin-bottom: 1.5rem;
        }
        
        /* Efek hover pada card */
        .card-kai:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        /* Body card */
        .card-kai .card-body {
            padding: 1.25rem;
        }
        
        /* Warna background merah KAI */
        .bg-kai-red {
            background-color: var(--kai-red);
        }
        
        /* Warna text merah KAI */
        .text-kai-red {
            color: var(--kai-red);
        }
        
        /* Tombol kustom */
        .btn-kai {
            background-color: var(--kai-red);
            color: white;
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.2s;
            border: none;
        }
        
        /* Efek hover pada tombol */
        .btn-kai:hover {
            background-color: #c1122d;
            color: white;
            transform: translateY(-2px);
        }
        
        /* Tombol kecil */
        .btn-kai-sm {
            padding: 6px 15px;
            font-size: 0.9rem;
        }
        
        /* Avatar pengguna */
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--kai-red);
        }
        
        /* Card hero khusus */
        .hero-card {
            border-radius: 15px;
            overflow: hidden;
            position: relative;
            background: linear-gradient(135deg, var(--kai-red) 0%, var(--kai-dark) 100%);
            color: white;
        }
        
        /* Animasi mengambang untuk ilustrasi */
        .train-illustration {
            animation: float 3s ease-in-out infinite;
            max-width: 100%;
            height: auto;
        }
        
        /* Keyframes animasi mengambang */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        /* Card statistik */
        .stat-card {
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.05);
        }

        /* Efek hover pada card statistik */
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        /* Tombol outline dengan berbagai warna */
        .btn-outline-primary, .btn-outline-danger, .btn-outline-success {
            transition: all 0.3s ease;
            border-width: 2px;
            border-radius: 8px;
        }

        /* Efek hover tombol outline primary */
        .btn-outline-primary:hover {
            background-color: #0a2e6b;
            color: white;
        }

        /* Efek hover tombol outline danger */
        .btn-outline-danger:hover {
            background-color: #e31837;
            color: white;
        }

        /* Efek hover tombol outline success */
        .btn-outline-success:hover {
            background-color: #28a745;
            color: white;
        }
        
        /* Container tabel responsive */
        .table-responsive {
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 1rem;
        }
        
        /* Styling dasar tabel */
        .table {
            margin-bottom: 0;
            width: 100%;
        }
        
        /* Header tabel */
        .table th {
            background-color: var(--kai-red);
            color: white;
            font-weight: 500;
            padding: 12px 15px;
        }
        
        /* Sel tabel */
        .table td {
            vertical-align: middle;
            padding: 12px 15px;
        }
        
        /* Efek hover pada baris tabel */
        .table-hover tbody tr:hover {
            background-color: rgba(227, 24, 55, 0.05);
        }
        
        /* Badge kustom */
        .badge-kai {
            background-color: var(--kai-red);
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 20px;
        }
        
        /* Responsive design untuk tablet */
        @media (max-width: 992px) {
            /* Sidebar default hidden di mobile */
            .sidebar {
                transform: translateX(-250px);
            }
            
            /* Sidebar active saat ditoggle */
            .sidebar.active {
                transform: translateX(0);
            }
            
            /* Konten utama full width di mobile */
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            /* Ukuran font brand navbar di mobile */
            .navbar-brand {
                font-size: 1.1rem;
            }
            
            /* Layout kolom terbalik di mobile */
            .hero-card .row {
                flex-direction: column-reverse;
            }
            
            /* Penyesuaian teks di mobile */
            .hero-card .col-md-8 {
                text-align: center;
                margin-bottom: 1rem;
            }
            
            /* Tombol full width di mobile */
            .hero-card .btn {
                width: 100%;
            }
            
            /* Tabel scrollable di mobile */
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            /* Mencegah wrap text di sel tabel */
            .table td, .table th {
                white-space: nowrap;
            }
        }
        
        /* Responsive design untuk mobile kecil */
        @media (max-width: 576px) {
            /* Padding lebih kecil di mobile */
            .main-content {
                padding: 15px;
            }
            
            /* Padding card body lebih kecil */
            .card-kai .card-body {
                padding: 1rem;
            }
            
            /* Margin card statistik */
            .stat-card {
                margin-bottom: 1rem;
            }
            
            /* Ukuran sel tabel lebih kecil */
            .table td, .table th {
                padding: 8px 10px;
                font-size: 0.9rem;
            }
            
            /* Ukuran tombol lebih kecil */
            .btn-kai {
                padding: 6px 12px;
                font-size: 0.9rem;
            }
        }
        
        /* Animasi fade in up untuk card */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Class untuk animasi card */
        .animate-card {
            animation: fadeInUp 0.5s ease forwards;
            opacity: 0;
        }
        
        /* Delay animasi untuk card berurutan */
        .card-kai:nth-child(1) { animation-delay: 0.1s; }
        .card-kai:nth-child(2) { animation-delay: 0.2s; }
        .card-kai:nth-child(3) { animation-delay: 0.3s; }
        .card-kai:nth-child(4) { animation-delay: 0.4s; }
    </style>
</head>
<body>
    <!-- Sidebar Navigasi -->
    <div class="sidebar">
        <!-- Container Logo -->
        <div class="logo-container">
            <img src="/Pesan_Tiket_Sandi/img/Kasa-Logo.png" alt="KASA Logo" class="img-fluid">
        </div>
        
        <!-- Menu Navigasi -->
        <div class="p-3 pt-0">
            <ul class="nav flex-column">
                <!-- Item menu beranda -->
                <li class="nav-item">
                    <a class="nav-link active" href="home.php">
                        <i class="bi bi-house-door"></i> Beranda
                    </a>
                </li>
                <!-- Item menu jadwal kereta -->
                <li class="nav-item">
                    <a class="nav-link" href="jadwal.php">
                        <i class="bi bi-train-front"></i> Jadwal Kereta
                    </a>
                </li>
                <!-- Item menu booking tiket -->
                <li class="nav-item">
                    <a class="nav-link" href="booking.php">
                        <i class="bi bi-ticket-perforated"></i> Booking Tiket
                    </a>
                </li>
                <!-- Item menu riwayat -->
                <li class="nav-item">
                    <a class="nav-link" href="riwayat.php">
                        <i class="bi bi-clock-history"></i> Riwayat
                    </a>
                </li>
                <!-- Item menu testimoni -->
                <li class="nav-item">
                    <a class="nav-link" href="testimoni.php">
                        <i class="bi bi-chat-square-text"></i> Testimoni
                    </a>
                </li>
                <!-- Item menu profil -->
                <li class="nav-item">
                    <a class="nav-link" href="profile.php">
                        <i class="bi bi-person"></i> Profil Saya
                    </a>
                </li>
                <!-- Item menu logout -->
                <li class="nav-item mt-4">
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
        <nav class="navbar navbar-expand-lg navbar-kai mb-4">
            <div class="container-fluid">
                <!-- Brand dan toggle sidebar -->
                <div class="d-flex align-items-center">
                    <button class="btn btn-outline-secondary me-3 d-lg-none" id="sidebarToggle">
                        <i class="bi bi-list"></i>
                    </button>
                    <h4 class="mb-0 fw-bold">Selamat Datang, <span class="text-kai-red"><?= htmlspecialchars($user['nama']) ?></span></h4>
                </div>
                
                <!-- Menu dropdown user -->
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="img/default-avatar.jpg" alt="" class="">
                            <span class="fw-medium d-none d-sm-inline"><?= htmlspecialchars($user['nama']) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="dropdownUser">
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i> Profil</a></li>
                            <li><a class="dropdown-item" href="riwayat.php"><i class="bi bi-clock-history me-2"></i> Riwayat</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Keluar</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

       <!-- Hero Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card hero-card border-0 shadow-lg"
             style="
                <div class="card hero-card border-0 shadow-lg"
     style="
        background: 
            linear-gradient(135deg, rgba(10,46,107, 0.6), rgba(227,24,55, 0.6)),
            url('/Pesan_Tiket_Sandi/img/bg-Logo-Kasa.jfif') center/cover no-repeat;
        border-radius: 16px;
     ">
            <div class="card-body py-5 px-4">
                <div class="row">
                    <!-- Konten teks -->
                    <div class="col-md-12 text-white text-start">
                        <div class="d-flex align-items-center mb-3">
                            <img src="/Pesan_Tiket_Sandi/img/Kasa-Logo.png" alt="PT-KASA Logo"
                                 style="height: 50px; margin-right: 15px;">
                            <h2 class="fw-bold mb-0">PT-KASA INDONESIA</h2>
                        </div>
                        <h3 class="fw-bold mb-3">Pesan Tiket Perjalananmu Sekarang!</h3>
                        <p class="mb-4">
                            Nikmati pengalaman berkelas dengan layanan kereta api terbaik dari PT-KASA.
                            Perjalanan nyaman, harga terjangkau, dan pelayanan prima untuk petualangan tak terlupakan
                            di seluruh Indonesia.
                        </p>
                        <div class="d-flex gap-3 flex-wrap">
                            <a href="jadwal.php" class="btn btn-light btn-lg fw-bold shadow">
                                <i class="bi bi-search me-2"></i>Cari Jadwal
                            </a>
                            <a href="booking.php" class="btn btn-outline-light btn-lg fw-bold shadow">
                                <i class="bi bi-lightning-charge me-2"></i>Pesan Cepat
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
        <!-- Dashboard Admin (hanya tampil untuk admin) -->
        <?php if (isset($user['role']) && $user['role'] === 'admin'): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-kai">
                    <div class="card-body">
                        <h4 class="card-title fw-bold mb-4">
                            <i class="bi bi-speedometer2 me-2"></i>Admin Dashboard
                        </h4>
                        
                        <!-- Statistik Admin -->
                        <div class="row g-3">
                            <!-- Statistik Jadwal -->
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">Total Jadwal</h5>
                                        <p class="display-6 fw-bold text-kai-red"><?= countSchedules($conn) ?></p>
                                        <a href="admin/manage_schedules.php" class="btn btn-kai btn-sm">
                                            <i class="bi bi-calendar2-event me-1"></i> Kelola
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Section Statistik -->
        <div class="row g-4 mb-4">
            <!-- Card Statistik Jadwal Hari Ini -->
            <div class="col-md-4">
                <div class="card card-kai h-100 stat-card animate-card" style="border-left: 4px solid #0a2e6b; border-radius: 8px;">
                    <div class="card-body">
                        <div class="d-flex align-items-start mb-3">
                            <div class="bg-primary bg-opacity-10 p-2 rounded me-3">
                                <i class="bi bi-calendar-check text-primary" style="font-size: 1.5rem;"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-1 fw-semibold">Jadwal Hari Ini</h5>
                                <p class="text-muted small mb-2">Tersedia <?= count(getTodaySchedules($conn)) ?> jadwal</p>
                            </div>
                        </div>
                        <p class="card-text text-secondary mb-3">Temukan jadwal keberangkatan kereta PT-KASA hari ini untuk perjalanan Anda.</p>
                        <a href="jadwal.php" class="btn btn-outline-primary d-flex align-items-center justify-content-between">
                            <span>Lihat Jadwal</span>
                            <i class="bi bi-arrow-right-circle ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Card Statistik Pemesanan Aktif -->
            <div class="col-md-4">
                <div class="card card-kai h-100 stat-card animate-card" style="border-left: 4px solid #e31837; border-radius: 8px;">
                    <div class="card-body">
                        <div class="d-flex align-items-start mb-3">
                            <div class="bg-danger bg-opacity-10 p-2 rounded me-3">
                                <i class="bi bi-ticket-perforated text-danger" style="font-size: 1.5rem;"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-1 fw-semibold">Pemesanan Aktif</h5>
                                <p class="text-muted small mb-2"><?= count(getActiveBookings($conn, $user['id'])) ?> aktif</p>
                            </div>
                        </div>
                        <p class="card-text text-secondary mb-3">Kelola tiket perjalanan Anda yang masih aktif dengan mudah.</p>
                        <a href="riwayat.php" class="btn btn-outline-danger d-flex align-items-center justify-content-between">
                            <span>Lihat Riwayat</span>
                            <i class="bi bi-arrow-right-circle ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Card Statistik Testimoni -->
            <div class="col-md-4">
                <div class="card card-kai h-100 stat-card animate-card" style="border-left: 4px solid #28a745; border-radius: 8px;">
                    <div class="card-body">
                        <div class="d-flex align-items-start mb-3">
                            <div class="bg-success bg-opacity-10 p-2 rounded me-3">
                                <i class="bi bi-chat-square-text text-success" style="font-size: 1.5rem;"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-1 fw-semibold">Testimoni</h5>
                                <p class="text-muted small mb-2"><?= count(getUserTestimonies($conn, $user['id'])) ?> testimoni</p>
                            </div>
                        </div>
                        <p class="card-text text-secondary mb-3">Bagikan pengalaman perjalanan Anda dengan PT-KASA.</p>
                        <a href="testimoni.php" class="btn btn-outline-success d-flex align-items-center justify-content-between">
                            <span>Buat Testimoni</span>
                            <i class="bi bi-arrow-right-circle ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Section Jadwal Terbaru -->
        <div class="row">
            <div class="col-12">
                <div class="card card-kai animate-card">
                    <div class="card-body">
                        <!-- Header section dengan tombol lihat semua -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title mb-0 fw-bold">Jadwal Terbaru</h5>
                            <a href="jadwal.php" class="btn btn-kai btn-sm">Lihat Semua <i class="bi bi-arrow-right-short"></i></a>
                        </div>
                        
                        <!-- Tabel jadwal terbaru -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Kereta</th>
                                        <th>Rute</th>
                                        <th>Keberangkatan</th>
                                        <th>Kedatangan</th>
                                        <th>Harga</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (getLatestSchedules($conn, 5) as $schedule): ?>
                                    <tr>
                                        <!-- Kolom nama kereta -->
                                        <td>
                                            <strong><?= htmlspecialchars($schedule['nama_kereta']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($schedule['kelas']) ?></small>
                                        </td>
                                        <!-- Kolom rute perjalanan -->
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <div class="fw-medium"><?= htmlspecialchars($schedule['stasiun_asal']) ?></div>
                                                    <div class="text-muted small"><?= date('H:i', strtotime($schedule['waktu_berangkat'])) ?></div>
                                                </div>
                                                <i class="bi bi-arrow-right mx-2 text-kai-red"></i>
                                                <div>
                                                    <div class="fw-medium"><?= htmlspecialchars($schedule['stasiun_tujuan']) ?></div>
                                                    <div class="text-muted small"><?= date('H:i', strtotime($schedule['waktu_tiba'])) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <!-- Kolom tanggal keberangkatan -->
                                        <td>
                                            <?= date('d M Y', strtotime($schedule['waktu_berangkat'])) ?>
                                        </td>
                                        <!-- Kolom tanggal kedatangan -->
                                        <td>
                                            <?= date('d M Y', strtotime($schedule['waktu_tiba'])) ?>
                                        </td>
                                        <!-- Kolom harga -->
                                        <td class="fw-bold text-kai-red">
                                            Rp <?= number_format($schedule['harga'], 0, ',', '.') ?>
                                        </td>
                                        <!-- Kolom aksi -->
                                        <td>
                                            <a href="booking.php?jadwal_id=<?= $schedule['id'] ?>" class="btn btn-kai btn-sm">Pesan</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Menyertakan JavaScript Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript kustom -->
    <script>
        // Toggle sidebar di mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
        
        // Inisialisasi animasi
        document.addEventListener('DOMContentLoaded', function() {
            // Animasi card saat halaman dimuat
            const cards = document.querySelectorAll('.animate-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.style.opacity = 1;
            });
            
            // Penanganan responsivitas tabel
            const tables = document.querySelectorAll('.table-responsive');
            tables.forEach(table => {
                if (window.innerWidth < 992) {
                    table.classList.add('table-responsive-sm');
                }
            });
        });
        
        // Penanganan resize window
        window.addEventListener('resize', function() {
            const tables = document.querySelectorAll('.table-responsive');
            tables.forEach(table => {
                if (window.innerWidth < 992) {
                    table.classList.add('table-responsive-sm');
                } else {
                    table.classList.remove('table-responsive-sm');
                }
            });
        });
    </script>
</body>
</html>
