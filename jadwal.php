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

// Menangani parameter pencarian dari URL
$stasiun_asal = $_GET['stasiun_asal'] ?? ''; // Stasiun asal dari form
$stasiun_tujuan = $_GET['stasiun_tujuan'] ?? ''; // Stasiun tujuan dari form
$tanggal = $_GET['tanggal'] ?? date('Y-m-d'); // Tanggal dari form, default hari ini

// Query SQL untuk mendapatkan jadwal kereta
// Disesuaikan dengan menghapus k.gambar karena kolom tidak ada di database
$sql = "SELECT j.*, k.nama as nama_kereta, k.kelas,
        s1.nama as stasiun_asal, s2.nama as stasiun_tujuan
        FROM jadwal j
        JOIN kereta k ON j.kereta_id = k.id
        JOIN stasiun s1 ON j.stasiun_asal_id = s1.id
        JOIN stasiun s2 ON j.stasiun_tujuan_id = s2.id
        WHERE DATE(j.waktu_berangkat) = '$tanggal'";

// Menambahkan filter stasiun asal jika dipilih
if (!empty($stasiun_asal)) {
    $sql .= " AND j.stasiun_asal_id = $stasiun_asal";
}

// Menambahkan filter stasiun tujuan jika dipilih
if (!empty($stasiun_tujuan)) {
    $sql .= " AND j.stasiun_tujuan_id = $stasiun_tujuan";
}

// Mengurutkan berdasarkan waktu keberangkatan
$sql .= " ORDER BY j.waktu_berangkat ASC";

// Menjalankan query dan menyimpan hasil
$result = mysqli_query($conn, $sql);
$schedules = [];
while ($row = mysqli_fetch_assoc($result)) {
    $schedules[] = $row;
}

// Mengambil daftar stasiun untuk dropdown
$stations = [];
$stationResult = mysqli_query($conn, "SELECT * FROM stasiun ORDER BY nama ASC");
while ($row = mysqli_fetch_assoc($stationResult)) {
    $stations[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags dasar untuk halaman -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Kereta - PT KASA</title>
    
    <!-- Menyertakan CSS Bootstrap dan ikon -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Menyertakan library animasi -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- CSS kustom untuk halaman -->
    <style>
        /* Variabel warna untuk tema */
        :root {
            --kasa-red: #e31837;
            --kasa-dark-red: #c1122d;
            --kasa-dark: #1a1a2e;
            --kasa-light: #f8f9fa;
        }
        
        /* Styling dasar body */
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Styling sidebar */
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
        
        /* Link menu sidebar */
        .sidebar-menu .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 10px 20px;
            margin: 5px 0;
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }
        
        /* Efek hover pada menu sidebar */
        .sidebar-menu .nav-link:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        /* Menu aktif di sidebar */
        .sidebar-menu .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 3px solid var(--kasa-red);
        }
        
        /* Ikon di menu sidebar */
        .sidebar-menu .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* Area konten utama */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
            background: linear-gradient(rgba(245, 247, 250, 0.9), rgba(245, 247, 250, 0.9)), 
                        url('img/train-pattern.png');
            background-size: contain;
            background-attachment: fixed;
        }
        
        /* Navbar atas */
        .navbar-kasa {
            background-color: white;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 15px 20px;
        }
        
        /* Card pencarian */
        .search-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            padding: 25px;
            margin-bottom: 30px;
            border: none;
        }
        
        /* Card jadwal kereta */
        .schedule-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }
        
        /* Efek hover pada card jadwal */
        .schedule-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        /* Garis vertikal di sisi kiri card */
        .schedule-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(to bottom, var(--kasa-red), var(--kasa-dark-red));
        }
        
        /* Ikon kereta */
        .train-icon {
            font-size: 2rem;
            color: var(--kasa-red);
            margin-right: 15px;
        }
        
        /* Nama kereta */
        .train-name {
            font-weight: 700;
            color: var(--kasa-dark);
        }
        
        /* Kelas kereta */
        .train-class {
            background-color: var(--kasa-red);
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        /* Waktu keberangkatan/kedatangan */
        .time-display {
            font-weight: 600;
            font-size: 1.2rem;
            color: var(--kasa-dark);
        }
        
        /* Nama stasiun */
        .station-name {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        /* Durasi perjalanan */
        .duration-display {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Garis horizontal untuk durasi */
        .duration-display::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background-color: #dee2e6;
            z-index: 1;
        }
        
        /* Text durasi */
        .duration-text {
            background-color: white;
            padding: 0 10px;
            position: relative;
            z-index: 2;
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        /* Harga tiket */
        .price-display {
            font-weight: 700;
            font-size: 1.3rem;
            color: var(--kasa-red);
        }
        
        /* Tombol kustom */
        .btn-kasa {
            background: linear-gradient(135deg, var(--kasa-red), var(--kasa-dark-red));
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        /* Efek hover pada tombol */
        .btn-kasa:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(227, 24, 55, 0.3);
            color: white;
        }
        
        /* Tampilan saat tidak ada jadwal */
        .no-schedule {
            background-color: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }
        
        /* Ikon saat tidak ada jadwal */
        .no-schedule-icon {
            font-size: 3rem;
            color: var(--kasa-red);
            margin-bottom: 15px;
        }
        
        /* Responsive design untuk tablet */
        @media (max-width: 992px) {
            /* Sidebar disembunyikan di mobile */
            .sidebar {
                left: -250px;
            }
            
            /* Sidebar aktif saat ditoggle */
            .sidebar.active {
                left: 0;
            }
            
            /* Konten utama full width */
            .main-content {
                margin-left: 0;
            }
        }
        
        /* Responsive design untuk mobile */
        @media (max-width: 768px) {
            /* Penyesuaian layout kolom di mobile */
            .schedule-card .col-md-2,
            .schedule-card .col-md-3 {
                margin-bottom: 15px;
            }
            
            /* Penyesuaian tampilan durasi di mobile */
            .duration-display {
                margin: 15px 0;
            }
            
            /* Menghilangkan garis durasi di mobile */
            .duration-display::before {
                display: none;
            }
        }
        
        /* Animasi untuk card jadwal */
        .schedule-card {
            animation: fadeInUp 0.5s ease-out;
        }
        
        /* Keyframes animasi fade in up */
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
    </style>
</head>
<body>
    <!-- Sidebar Navigasi -->
    <div class="sidebar">
        <!-- Header Sidebar -->
        <div class="sidebar-header text-center">
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
                
                <!-- Menu Jadwal Kereta (aktif) -->
                <li class="nav-item">
                    <a class="nav-link active" href="jadwal.php">
                        <i class="bi bi-train-front"></i> Jadwal Kereta
                    </a>
                </li>
                
                <!-- Menu Riwayat Pemesanan -->
                <li class="nav-item">
                    <a class="nav-link" href="riwayat.php">
                        <i class="bi bi-clock-history"></i> Riwayat Pemesanan
                    </a>
                </li>
                
                <!-- Menu Tiket Saya -->
                <li class="nav-item">
                    <a class="nav-link" href="tiket.php">
                        <i class="bi bi-ticket-perforated"></i> Tiket Saya
                    </a>
                </li>
                
                <!-- Menu Profil -->
                <li class="nav-item">
                    <a class="nav-link" href="profile.php">
                        <i class="bi bi-person"></i> Profil Saya
                    </a>
                </li>
                
                <!-- Menu Logout -->
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
        <nav class="navbar navbar-expand-lg navbar-kasa mb-4">
            <div class="container-fluid">
                <!-- Brand dan toggle sidebar -->
                <div class="d-flex align-items-center">
                    <button class="btn btn-outline-secondary me-3 d-lg-none" id="sidebarToggle">
                        <i class="bi bi-list"></i>
                    </button>
                    <h5 class="mb-0 fw-bold"><i class="bi bi-train-front me-2"></i> Jadwal Kereta</h5>
                </div>
                
                <!-- Menu dropdown user -->
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownUser">
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i> Profil</a></li>
                            <li><a class="dropdown-item" href="riwayat.php"><i class="bi bi-clock-history me-2"></i> Riwayat</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Keluar</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Card Pencarian -->
        <div class="search-card animate__animated animate__fadeIn">
            <h5 class="mb-4 fw-bold text-kasa"><i class="bi bi-search me-2"></i> Cari Jadwal Kereta</h5>
            
            <!-- Form Pencarian -->
            <form method="GET">
                <div class="row g-3">
                    <!-- Input Stasiun Asal -->
                    <div class="col-md-4">
                        <label for="stasiun_asal" class="form-label fw-medium">Stasiun Asal</label>
                        <select class="form-select" id="stasiun_asal" name="stasiun_asal">
                            <option value="">Semua Stasiun</option>
                            <?php foreach ($stations as $station): ?>
                            <option value="<?= $station['id'] ?>" <?= $stasiun_asal == $station['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($station['nama']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Input Stasiun Tujuan -->
                    <div class="col-md-4">
                        <label for="stasiun_tujuan" class="form-label fw-medium">Stasiun Tujuan</label>
                        <select class="form-select" id="stasiun_tujuan" name="stasiun_tujuan">
                            <option value="">Semua Stasiun</option>
                            <?php foreach ($stations as $station): ?>
                            <option value="<?= $station['id'] ?>" <?= $stasiun_tujuan == $station['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($station['nama']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Input Tanggal -->
                    <div class="col-md-3">
                        <label for="tanggal" class="form-label fw-medium">Tanggal</label>
                        <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
                    </div>
                    
                    <!-- Tombol Pencarian -->
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-kasa w-100">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Section Hasil Pencarian -->
        <div class="row">
            <div class="col-12">
                <h5 class="mb-4 fw-bold"><i class="bi bi-list-task me-2"></i> Hasil Pencarian</h5>
                
                <?php if (empty($schedules)): ?>
                <!-- Tampilan jika tidak ada jadwal -->
                <div class="no-schedule animate__animated animate__fadeIn">
                    <div class="no-schedule-icon">
                        <i class="bi bi-train"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Tidak Ada Jadwal Tersedia</h5>
                    <p class="text-muted">Tidak ada jadwal kereta yang sesuai dengan kriteria pencarian Anda.</p>
                    <a href="jadwal.php" class="btn btn-kasa mt-3">
                        <i class="bi bi-arrow-clockwise me-2"></i>Coba Lagi
                    </a>
                </div>
                <?php else: ?>
                    <!-- Menampilkan daftar jadwal -->
                    <?php foreach ($schedules as $schedule): ?>
                    <div class="schedule-card card mb-3 animate__animated animate__fadeIn">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <!-- Info Kereta -->
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-train-front train-icon"></i>
                                        <div>
                                            <div class="train-name"><?= htmlspecialchars($schedule['nama_kereta']) ?></div>
                                            <span class="train-class"><?= htmlspecialchars($schedule['kelas']) ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Waktu Keberangkatan -->
                                <div class="col-md-2 text-center text-md-start">
                                    <div class="time-display"><?= date('H:i', strtotime($schedule['waktu_berangkat'])) ?></div>
                                    <div class="station-name"><?= htmlspecialchars($schedule['stasiun_asal']) ?></div>
                                </div>
                                
                                <!-- Durasi Perjalanan -->
                                <div class="col-md-2">
                                    <div class="duration-display">
                                        <span class="duration-text"><?= htmlspecialchars($schedule['durasi']) ?> jam</span>
                                    </div>
                                </div>
                                
                                <!-- Waktu Kedatangan -->
                                <div class="col-md-2 text-center text-md-start">
                                    <div class="time-display"><?= date('H:i', strtotime($schedule['waktu_tiba'])) ?></div>
                                    <div class="station-name"><?= htmlspecialchars($schedule['stasiun_tujuan']) ?></div>
                                </div>
                                
                                <!-- Harga Tiket -->
                                <div class="col-md-2 text-center text-md-start">
                                    <div class="price-display">Rp <?= number_format($schedule['harga'], 0, ',', '.') ?></div>
                                    <small class="text-muted">per orang</small>
                                </div>
                                
                                <!-- Tombol Pesan -->
                                <div class="col-md-1 text-center text-md-end">
                                    <a href="booking.php?jadwal_id=<?= $schedule['id'] ?>" class="btn btn-kasa">
                                        <i class="bi bi-ticket-perforated me-1"></i> Pesan
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Menyertakan JavaScript Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript Kustom -->
    <script>
        // Toggle sidebar di mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
        
        // Set tanggal minimum ke hari ini
        document.getElementById('tanggal').min = new Date().toISOString().split("T")[0];
        
        // Menambahkan delay animasi untuk card jadwal
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.schedule-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>
</body>
</html>