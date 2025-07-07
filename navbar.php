<?php
// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Data user default
$user = [
    'nama' => 'Pengguna',
    'role' => 'pengguna'
];

// Jika user sudah login, ambil data dari session
if (isset($_SESSION['user'])) {
    $user = array_merge($user, $_SESSION['user']);
}
?>

<!-- Sidebar Navigasi -->
<div class="sidebar p-3 bg-biru-tua text-white">
    <!-- Logo KASA -->
    <div class="text-center mb-4">
        <img src="/Pesan_Tiket_Sandi/Kasa-Logo.png" alt="Logo KAI" class="img-fluid" style="max-height: 50px;">
        <h5 class="mt-2 mb-0">KAI Indonesia</h5>
        <small class="text-muted">Sistem Pemesanan Tiket</small>
    </div>

    <!-- Menu Navigasi -->
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item mb-2">
            <a href="home.php" class="nav-link text-white">
                <i class="bi bi-house-door me-2"></i>
                Beranda
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="jadwal.php" class="nav-link text-white">
                <i class="bi bi-train-front me-2"></i>
                Jadwal Kereta
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="tiket.php" class="nav-link bg-merah-kai text-white">
                <i class="bi bi-ticket-perforated me-2"></i>
                Tiket Saya
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="riwayat.php" class="nav-link text-white">
                <i class="bi bi-clock-history me-2"></i>
                Riwayat Pemesanan
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="profile.php" class="nav-link text-white">
                <i class="bi bi-person me-2"></i>
                Profil Saya
            </a>
        </li>

<style>
    /* Variabel Warna */
    :root {
        --merah-kai: #e31837;
        --biru-tua: #1a1a2e;
        --abu-muda: #f8f9fa;
    }
    
    /* Style Sidebar */
    .sidebar {
        width: 250px;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        transition: all 0.3s;
        z-index: 1000;
    }
    
    .sidebar .nav-link {
        color: rgba(255, 255, 255, 0.8);
        border-radius: 5px;
        padding: 8px 12px;
        margin-bottom: 5px;
        transition: all 0.2s;
    }
    
    .sidebar .nav-link:hover {
        color: white;
        background-color: rgba(255, 255, 255, 0.1);
    }
    
    .sidebar .nav-link.active, 
    .sidebar .bg-merah-kai {
        background-color: var(--merah-kai) !important;
        color: white !important;
    }
    
    /* Responsif untuk mobile */
    @media (max-width: 992px) {
        .sidebar {
            transform: translateX(-100%);
        }
        
        .sidebar.tampil {
            transform: translateX(0);
        }
    }
</style>

<script>
    // Fungsi untuk toggle sidebar di mobile
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('tampil');
    }
</script>