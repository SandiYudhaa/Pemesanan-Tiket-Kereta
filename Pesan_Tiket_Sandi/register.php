<?php
// Memulai session untuk menyimpan data pengguna
session_start();

// Menyertakan file konfigurasi database
include 'config.php';

// Jika pengguna sudah login, redirect ke halaman home
if (isset($_SESSION['user'])) {
    header("Location: home.php");
    exit();
}

// Menangani proses pendaftaran jika form dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Mengambil data dari form pendaftaran
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    // Mengenkripsi password sebelum disimpan
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Query untuk menyimpan data user baru
    $sql = "INSERT INTO user (nama, email, password) 
            VALUES ('$nama', '$email', '$password')";
    
    // Menjalankan query dan menangani hasilnya
    if (mysqli_query($conn, $sql)) {
        // Jika berhasil, set session dan redirect ke halaman login
        $_SESSION['register_success'] = true;
        header("Location: index.php");
        exit();
    } else {
        // Jika gagal, simpan pesan error
        $error = "Pendaftaran gagal: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags dasar untuk halaman -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - PT KASA</title>
    
    <!-- Menyertakan CSS Bootstrap dan ikon -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Menyertakan library animasi -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- CSS kustom untuk halaman pendaftaran -->
    <style>
        /* Variabel warna untuk tema */
        :root {
            --kasa-red: #e31837;
            --kasa-dark-red: #c1122d;
            --kasa-white: #ffffff;
            --kasa-light: #f8f9fa;
            --kasa-dark: #1a1a2e;
            --kasa-blue: #0056b3;
        }
        
        /* Styling dasar body dengan background gradient dan gambar */
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.8)), url('img/transport-bg.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Poppins', sans-serif;
        }
        
        /* Container utama untuk pendaftaran */
        .register-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* Card utama untuk pendaftaran */
        .register-card {
            background-color: rgba(255, 255, 255, 0.97);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: fadeInUp 0.8s;
        }
        
        /* Bagian kiri card (welcome section) */
        .register-left {
            background: linear-gradient(135deg, var(--kasa-red) 0%, var(--kasa-dark-red) 100%);
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        /* Efek dekoratif lingkaran */
        .register-left::before {
            content: "";
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }
        
        /* Efek dekoratif lingkaran kedua */
        .register-left::after {
            content: "";
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 250px;
            height: 250px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }
        
        /* Bagian kanan card (form pendaftaran) */
        .register-right {
            padding: 3rem;
        }
        
        /* Styling untuk logo */
        .logo {
            height: 60px;
            margin-bottom: 1.5rem;
            transition: transform 0.3s;
        }
        
        /* Efek hover pada logo */
        .logo:hover {
            transform: scale(1.05);
        }
        
        /* Judul welcome section */
        .welcome-title {
            font-weight: 700;
            font-size: 2.2rem;
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }
        
        /* Garis dekoratif di bawah judul */
        .welcome-title::after {
            content: "";
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: white;
        }
        
        /* Subjudul welcome section */
        .welcome-subtitle {
            font-weight: 300;
            opacity: 0.9;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        
        /* List fitur */
        .feature-list {
            list-style: none;
            padding: 0;
        }
        
        /* Item fitur */
        .feature-list li {
            margin-bottom: 1.2rem;
            display: flex;
            align-items: center;
            transition: transform 0.3s;
        }
        
        /* Efek hover pada item fitur */
        .feature-list li:hover {
            transform: translateX(5px);
        }
        
        /* Ikon untuk fitur */
        .feature-icon {
            background: rgba(255, 255, 255, 0.2);
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.1rem;
        }
        
        /* Label form */
        .form-label {
            font-weight: 600;
            color: var(--kasa-dark);
            margin-bottom: 0.5rem;
        }
        
        /* Input form */
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        /* Efek focus pada input */
        .form-control:focus {
            border-color: var(--kasa-red);
            box-shadow: 0 0 0 0.2rem rgba(227, 24, 55, 0.25);
        }
        
        /* Container untuk input password */
        .password-container {
            position: relative;
        }
        
        /* Tombol toggle password visibility */
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            transition: color 0.3s;
        }
        
        /* Efek hover pada toggle password */
        .toggle-password:hover {
            color: var(--kasa-dark);
        }
        
        /* Tombol daftar kustom */
        .btn-kasa {
            background: linear-gradient(135deg, var(--kasa-red) 0%, var(--kasa-dark-red) 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(227, 24, 55, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        /* Efek hover pada tombol daftar */
        .btn-kasa:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 20px rgba(227, 24, 55, 0.4);
            color: white;
        }
        
        /* Efek ripple saat tombol diklik */
        .btn-kasa::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.5);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }
        
        /* Animasi ripple */
        .btn-kasa:focus:not(:active)::after {
            animation: ripple 1s ease-out;
        }
        
        /* Keyframes untuk animasi ripple */
        @keyframes ripple {
            0% {
                transform: scale(0, 0);
                opacity: 0.5;
            }
            100% {
                transform: scale(20, 20);
                opacity: 0;
            }
        }
        
        /* Pembatas antara form dan opsi lain */
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: #6c757d;
            margin: 1.5rem 0;
        }
        
        /* Garis pembatas */
        .divider::before, .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid #dee2e6;
        }
        
        /* Spasi untuk garis pembatas kiri */
        .divider::before {
            margin-right: 1rem;
        }
        
        /* Spasi untuk garis pembatas kanan */
        .divider::after {
            margin-left: 1rem;
        }
        
        /* Footer untuk form pendaftaran */
        .register-footer {
            text-align: center;
            margin-top: 2rem;
            color: #6c757d;
        }
        
        /* Link di footer */
        .register-footer a {
            color: var(--kasa-red);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        /* Efek hover pada link footer */
        .register-footer a:hover {
            color: var(--kasa-dark-red);
            text-decoration: underline;
        }
        
        /* Container untuk statistik */
        .stats-container {
            display: flex;
            justify-content: space-around;
            margin-top: 2rem;
            text-align: center;
        }
        
        /* Item statistik */
        .stat-item {
            padding: 0 15px;
        }
        
        /* Angka statistik */
        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--kasa-red);
            margin-bottom: 0;
        }
        
        /* Label statistik */
        .stat-label {
            font-size: 0.9rem;
            color: white;
            opacity: 0.9;
        }
        
        /* Notifikasi floating */
        .floating-notice {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--kasa-red);
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            animation: bounceInRight 1s;
            z-index: 1000;
            display: flex;
            align-items: center;
            max-width: 300px;
        }
        
        /* Ikon notifikasi */
        .floating-notice i {
            margin-right: 10px;
            font-size: 1.5rem;
        }
        
        /* Tombol tutup notifikasi */
        .floating-notice .close-notice {
            margin-left: 15px;
            cursor: pointer;
            opacity: 0.8;
        }
        
        /* Efek hover pada tombol tutup */
        .floating-notice .close-notice:hover {
            opacity: 1;
        }
        
        /* Responsive design untuk layar menengah */
        @media (max-width: 992px) {
            /* Sembunyikan bagian kiri di tablet */
            .register-left {
                display: none;
            }
            
            /* Sesuaikan padding untuk tablet */
            .register-right {
                padding: 2rem;
            }
        }
        
        /* Responsive design untuk layar kecil */
        @media (max-width: 768px) {
            /* Sesuaikan margin dan border radius untuk mobile */
            .register-card {
                margin: 20px;
                border-radius: 10px;
            }
            
            /* Sesuaikan padding untuk mobile */
            .register-right {
                padding: 1.5rem;
            }
            
            /* Sesuaikan ukuran font judul untuk mobile */
            .welcome-title {
                font-size: 1.8rem;
            }
            
            /* Sesuaikan ukuran font fitur untuk mobile */
            .feature-list li {
                font-size: 0.9rem;
            }
            
            /* Sesuaikan ukuran tombol untuk mobile */
            .btn-kasa {
                padding: 10px;
                font-size: 1rem;
            }
            
            /* Sesuaikan notifikasi floating untuk mobile */
            .floating-notice {
                max-width: 90%;
                left: 5%;
                right: 5%;
                bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Container utama untuk halaman pendaftaran -->
    <div class="register-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <!-- Card pendaftaran utama -->
                    <div class="register-card">
                        <div class="row g-0">
                            <!-- Bagian Kiri - Welcome Section -->
                            <div class="col-lg-6 d-none d-lg-block">
                                <div class="register-left">
                                    <!-- Logo PT-KASA -->
                                    <img src="/Pesan_Tiket_Sandi/img/Kasa-Logo.png" alt="PT-KASA Logo" class="logo animate__animated animate__fadeIn">
                                    <!-- Judul dan subjudul -->
                                    <h1 class="welcome-title animate__animated animate__fadeIn">Bergabunglah Dengan Kami</h1>
                                    <p class="welcome-subtitle animate__animated animate__fadeIn animate__delay-1s">Daftar untuk menikmati semua layanan PT KASA</p>
                                    
                                    <!-- Daftar fitur -->
                                    <ul class="feature-list">
                                        <li class="animate__animated animate__fadeInLeft">
                                            <span class="feature-icon">
                                                <i class="bi bi-check-lg"></i>
                                            </span>
                                            Sistem pemesanan tiket terintegrasi
                                        </li>
                                        <li class="animate__animated animate__fadeInLeft animate__delay-1s">
                                            <span class="feature-icon">
                                                <i class="bi bi-check-lg"></i>
                                            </span>
                                            Notifikasi real-time perjalanan
                                        </li>
                                        <li class="animate__animated animate__fadeInLeft animate__delay-2s">
                                            <span class="feature-icon">
                                                <i class="bi bi-check-lg"></i>
                                            </span>
                                            Layanan pelanggan 24/7
                                        </li>
                                    </ul>
                                    
                                    <!-- Statistik perusahaan -->
                                    <div class="stats-container animate__animated animate__fadeInUp animate__delay-1s">
                                        <div class="stat-item">
                                            <div class="stat-number">10K+</div>
                                            <div class="stat-label">Pengguna</div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-number">500+</div>
                                            <div class="stat-label">Perjalanan</div>
                                        </div>
                                        <div class="stat-item">
                                            <div class="stat-number">99%</div>
                                            <div class="stat-label">Kepuasan</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Bagian Kanan - Form Pendaftaran -->
                            <div class="col-lg-6">
                                <div class="register-right">
                                    <!-- Logo untuk tampilan mobile -->
                                    <div class="text-center d-lg-none mb-4">
                                        <img src="/Project-Web_UAS/img/Kasa-Logo.png" alt="PT KASA Logo" class="logo" style="width: 140px;">
                                    </div>
                                    
                                    <!-- Judul form pendaftaran -->
                                    <h2 class="text-center mb-4" style="color: var(--kasa-red);">Buat Akun Baru</h2>
                                    
                                    <!-- Menampilkan pesan error jika ada -->
                                    <?php if (isset($error)): ?>
                                        <div class="alert alert-danger alert-dismissible fade show">
                                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                            <?= htmlspecialchars($error) ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Form pendaftaran -->
                                    <form method="POST">
                                        <!-- Input nama lengkap -->
                                        <div class="mb-3">
                                            <label for="nama" class="form-label">Nama Lengkap</label>
                                            <input type="text" class="form-control" id="nama" name="nama" required placeholder="Masukkan nama lengkap Anda">
                                        </div>
                                        
                                        <!-- Input email -->
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Alamat Email</label>
                                            <input type="email" class="form-control" id="email" name="email" required placeholder="email@contoh.com">
                                        </div>
                                        
                                        <!-- Input password dengan toggle visibility -->
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Kata Sandi</label>
                                            <div class="password-container">
                                                <input type="password" class="form-control" id="password" name="password" required placeholder="Buat kata sandi">
                                                <i class="bi bi-eye-fill toggle-password" id="togglePassword"></i>
                                            </div>
                                        </div>
                                        
                                        <!-- Input konfirmasi password -->
                                        <div class="mb-3">
                                            <label for="confirm_password" class="form-label">Konfirmasi Kata Sandi</label>
                                            <div class="password-container">
                                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="Ulangi kata sandi">
                                                <i class="bi bi-eye-fill toggle-password" id="toggleConfirmPassword"></i>
                                            </div>
                                        </div>
                                        
                                        <!-- Checkbox syarat dan ketentuan -->
                                        <div class="form-check mb-4">
                                            <input class="form-check-input" type="checkbox" id="terms" required>
                                            <label class="form-check-label" for="terms">
                                                Saya menyetujui <a href="#" style="color: var(--kasa-red);">Syarat dan Ketentuan</a> serta <a href="#" style="color: var(--kasa-red);">Kebijakan Privasi</a>
                                            </label>
                                        </div>
                                        
                                        <!-- Tombol submit -->
                                        <div class="d-grid mb-3">
                                            <button type="submit" class="btn btn-kasa">
                                                <i class="bi bi-person-plus-fill me-2"></i> DAFTAR SEKARANG
                                            </button>
                                        </div>
                                        
                                        <!-- Link ke halaman login -->
                                        <div class="register-footer">
                                            Sudah punya akun? <a href="index.php">Masuk disini</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Notifikasi floating promo -->
    <div class="floating-notice animate__animated animate__bounceInRight animate__delay-1s">
        <i class="bi bi-megaphone-fill"></i>
        <div>
            <strong>Promo Spesial!</strong> Nikmati diskon 30% untuk pemesanan tiket pertama Anda.
        </div>
        <span class="close-notice">&times;</span>
    </div>
    
    <!-- Menyertakan JavaScript Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript kustom -->
    <script>
        // Fungsi untuk toggle visibility password
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
        const confirmPassword = document.querySelector('#confirm_password');
        
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('bi-eye-slash-fill');
            this.classList.toggle('bi-eye-fill');
        });
        
        toggleConfirmPassword.addEventListener('click', function() {
            const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPassword.setAttribute('type', type);
            this.classList.toggle('bi-eye-slash-fill');
            this.classList.toggle('bi-eye-fill');
        });
        
        // Fungsi untuk menutup notifikasi floating
        document.querySelector('.close-notice').addEventListener('click', function() {
            document.querySelector('.floating-notice').style.display = 'none';
        });
        
        // Validasi form sebelum submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Menvalidasi kecocokan password
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Kata sandi dan konfirmasi kata sandi tidak cocok!');
                return false;
            }
            
            // Memvalidasi panjangnya password
            if (password.length < 8) {
                e.preventDefault();
                alert('Kata sandi harus minimal 8 karakter!');
                return false;
            }
            
            // Menvalidasi persetujuan syarat dan ketentuan
            if (!document.getElementById('terms').checked) {
                e.preventDefault();
                alert('Anda harus menyetujui Syarat dan Ketentuan!');
                return false;
            }
        });
        
        // Tampilan animasi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.register-card, .feature-list li');
            elements.forEach((el, index) => {
                setTimeout(() => {
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>