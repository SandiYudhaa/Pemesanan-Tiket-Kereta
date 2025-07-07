<?php
// Memulai sesi jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Data pengguna default dengan peran sebagai tamu
$user = [
    'nama' => 'Pengguna',
    'role' => 'guest'
];

// Jika data pengguna tersedia dalam sesi, gabungkan dengan data default
if (isset($_SESSION['user'])) {
    $user = array_merge($user, $_SESSION['user']); 
}
?>
     <!-- Sidebar -->
    <div class="sidebar p-3">
        <!-- Kontainer Logo -->
        <div class="logo-container">
            <img src="/Pesan_Tiket_Sandi/img/Kasa-Logo.png" alt="KAI Logo" class="img-fluid">
        </div>
    
    <!-- Daftar menu navigasi -->
    <ul class="nav nav-pills flex-column mb-auto">
        <!-- Menu Beranda -->
        <li class="nav-item mb-2">
            <a href="home.php" class="nav-link text-white">
                <i class="bi bi-house-door me-2"></i>
                Beranda
            </a>
        </li>
        <!-- Menu Jadwal Kereta (aktif) -->
        <li class="nav-item mb-2">
            <a href="jadwal.php" class="nav-link active bg-kai-red text-white">
                <i class="bi bi-train-front me-2"></i>
                Jadwal Kereta
            </a>
        </li>
        <!-- Menu Riwayat Pemesanan -->
        <li class="nav-item mb-2">
            <a href="riwayat.php" class="nav-link text-white">
                <i class="bi bi-clock-history me-2"></i>
                Riwayat Pemesanan
            </a>
        </li>
        <!-- Menu Tiket Saya -->
        <li class="nav-item mb-2">
            <a href="tiket.php" class="nav-link text-white">
                <i class="bi bi-ticket-perforated me-2"></i>
                Tiket Saya
            </a>
        </li>
        <!-- Menu Profil Saya -->
        <li class="nav-item mb-2">
            <a href="profile.php" class="nav-link text-white">
                <i class="bi bi-person me-2"></i>
                Profil Saya
            </a>
        </li>
    </ul>
    
    <!-- Bagian footer sidebar -->
    <div class="border-top pt-3 mt-3">
                <!-- Menampilkan peran pengguna -->
                <small class="text-muted"><?= ucfirst($user['role']) ?></small>
            </div>
        </div>
        <!-- Tombol Keluar -->
        <div class="mt-3">
            <a href="logout.php" class="btn btn-outline-light btn-sm w-100">
                <i class="bi bi-box-arrow-right me-1"></i> Keluar
            </a>
        </div>
    </div>
</div>
<!-- Akhir Sidebar -->

<!-- Gaya CSS untuk sidebar -->
<style>
    /* Pemotongan teks yang panjang */
    .sidebar .text-truncate {
    max-width: 140px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    }

    /* Gaya utama sidebar */
    .sidebar {
        background-color: var(--kai-dark);
        color: white;
        min-height: 100vh;
        position: fixed;
        width: 250px;
        transition: all 0.3s;
        z-index: 1000;
    }
    
    /* Gaya tautan navigasi */
    .sidebar .nav-link {
        color: rgba(255, 255, 255, 0.8);
        border-radius: 5px;
        padding: 8px 12px;
    }
    
    /* Efek hover pada tautan */
    .sidebar .nav-link:hover {
        color: white;
        background-color: rgba(255, 255, 255, 0.1);
    }
    
    /* Tautan aktif */
    .sidebar .nav-link.active {
        color: white;
        background-color: var(--kai-red);
    }
    
    /* Gaya ikon */
    .sidebar .nav-link i {
        width: 20px;
        text-align: center;
    }
    
    /* Responsivitas untuk perangkat kecil */
    @media (max-width: 992px) {
        .sidebar {
            width: 0;
            overflow: hidden;
            padding: 0;
        }
        
        /* Sidebar yang ditampilkan */
        .sidebar.show {
            width: 250px;
            padding: 20px;
        }
        
        /* Konten utama */
        .main-content {
            margin-left: 0;
        }
    }
</style>