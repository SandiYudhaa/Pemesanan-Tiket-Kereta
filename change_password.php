<?php
// Memulai sesi untuk manajemen pengguna
session_start();

// Menyertakan konfigurasi database dan fungsi bantuan
include 'config.php';
include 'functions.php';

// Mengarahkan ke halaman login jika pengguna belum terautentikasi
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Mendapatkan data pengguna dari sesi
$user = $_SESSION['user'];
$error = '';
$success = '';

// Menangani pengiriman formulir perubahan password
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Memvalidasi password saat ini
    if (!password_verify($current_password, $user['password'])) {
        $error = "Password saat ini tidak valid";
    } 
    // Memvalidasi kekuatan password
    elseif (strlen($new_password) < 8) {
        $error = "Password baru harus minimal 8 karakter";
    }
    elseif (!preg_match('/[A-Z]/', $new_password)) {
        $error = "Password baru harus mengandung huruf besar";
    }
    elseif (!preg_match('/[0-9]/', $new_password)) {
        $error = "Password baru harus mengandung angka";
    }
    elseif (!preg_match('/[^A-Za-z0-9]/', $new_password)) {
        $error = "Password baru harus mengandung karakter khusus";
    }
    // Memeriksa kesesuaian password
    elseif ($new_password !== $confirm_password) {
        $error = "Password baru dan konfirmasi tidak cocok";
    }
    // Memperbarui password jika semua validasi terpenuhi
    else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE user SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $hashed_password, $user['id']);
        
        if ($stmt->execute()) {
            $success = "Password berhasil diperbarui!";
            // Memperbarui sesi dengan hash password baru
            $_SESSION['user']['password'] = $hashed_password;
        } else {
            $error = "Gagal memperbarui password: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <!-- Deklarasi metadata dasar -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keamanan Akun - PT KASA INDONESIA</title>
    
    <!-- Menyertakan CSS Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- CSS Kustom -->
    <style>
        /* Mendefinisikan variabel warna untuk tema */
        :root {
            --kasa-primary: #0056b3;
            --kasa-secondary: #003366;
            --kasa-accent: #ff6600;
            --kasa-light: #f8f9fa;
            --kasa-dark: #1a1a2e;
            --kasa-success: #28a745;
            --kasa-danger: #dc3545;
            --kasa-warning: #ffc107;
            --kasa-info: #17a2b8;
        }
        
        /* Gaya dasar body dengan background gradient */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            background-image: url('https://images.unsplash.com/photo-1550751827-4bd374c3f58b?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            position: relative;
            min-height: 100vh;
            color: #333;
        }
        
        /* Overlay semi-transparan untuk body */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0, 86, 179, 0.9) 0%, rgba(0, 51, 102, 0.9) 100%);
            z-index: -1;
        }
        
        /* Container utama untuk konten keamanan */
        .security-container {
            max-width: 600px;
            margin: 2rem auto;
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        /* Efek hover pada container */
        .security-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }
        
        /* Gaya header bagian keamanan */
        .security-header {
            background: linear-gradient(135deg, var(--kasa-primary) 0%, var(--kasa-secondary) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
            border-bottom: 4px solid var(--kasa-accent);
        }
        
        /* Gaya ikon keamanan */
        .security-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: white;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        /* Gaya body konten keamanan */
        .security-body {
            padding: 2rem;
        }
        
        /* Gaya dasar untuk alert */
        .security-alert {
            border-radius: 8px;
            border-left: 4px solid transparent;
        }
        
        /* Variasi alert danger */
        .alert-danger {
            border-left-color: var(--kasa-danger);
        }
        
        /* Variasi alert success */
        .alert-success {
            border-left-color: var(--kasa-success);
        }
        
        /* Gaya label formulir */
        .form-label {
            font-weight: 600;
            color: var(--kasa-secondary);
            margin-bottom: 0.5rem;
        }
        
        /* Gaya kontrol formulir */
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #ced4da;
            transition: all 0.3s ease;
        }
        
        /* Efek fokus pada kontrol formulir */
        .form-control:focus {
            border-color: var(--kasa-primary);
            box-shadow: 0 0 0 0.25rem rgba(0, 86, 179, 0.25);
        }
        
        /* Gaya teks input group */
        .input-group-text {
            background-color: #f8f9fa;
            border-radius: 0 8px 8px 0;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        /* Efek hover pada input group */
        .input-group-text:hover {
            background-color: #e9ecef;
        }
        
        /* Indikator kekuatan password */
        .password-strength {
            height: 8px;
            margin-top: 10px;
            border-radius: 4px;
            background-color: #e9ecef;
            overflow: hidden;
        }
        
        /* Bar indikator kekuatan password */
        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: width 0.5s ease, background-color 0.5s ease;
            border-radius: 4px;
        }
        
        /* Daftar persyaratan password */
        .password-requirements {
            background-color: #f8fafc;
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
            border: 1px dashed #cbd5e1;
        }
        
        /* Item persyaratan password */
        .requirement-item {
            margin-bottom: 8px;
            position: relative;
            padding-left: 25px;
            color: #64748b;
            transition: all 0.3s ease;
        }
        
        /* Ikon untuk item persyaratan */
        .requirement-item:before {
            content: '\f28a';
            font-family: 'bootstrap-icons';
            position: absolute;
            left: 0;
            color: #cbd5e1;
        }
        
        /* Gaya untuk item persyaratan yang terpenuhi */
        .requirement-item.valid {
            color: var(--kasa-success);
        }
        
        /* Ikon untuk item persyaratan yang terpenuhi */
        .requirement-item.valid:before {
            content: '\f26e';
            color: var(--kasa-success);
        }
        
        /* Gaya tombol keamanan */
        .btn-security {
            background: linear-gradient(135deg, var(--kasa-primary) 0%, var(--kasa-secondary) 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 14px 28px;
            font-weight: 600;
            letter-spacing: 0.5px;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 86, 179, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        /* Efek hover pada tombol keamanan */
        .btn-security:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 86, 179, 0.4);
            color: white;
        }
        
        /* Efek aktif pada tombol keamanan */
        .btn-security:active {
            transform: translateY(1px);
        }
        
        /* Efek animasi pada tombol keamanan */
        .btn-security::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }
        
        /* Animasi hover pada tombol keamanan */
        .btn-security:hover::before {
            left: 100%;
        }
        
        /* Gaya tip keamanan */
        .security-tip {
            background-color: rgba(255, 102, 0, 0.1);
            border-left: 4px solid var(--kasa-accent);
            padding: 12px 15px;
            border-radius: 8px;
            margin-top: 20px;
            color: var(--kasa-secondary);
        }
        
        /* Gaya tombol kembali */
        .btn-back {
            color: var(--kasa-primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
        }
        
        /* Efek hover pada tombol kembali */
        .btn-back:hover {
            color: var(--kasa-secondary);
            text-decoration: underline;
        }
        
        /* Gaya tombol profil */
        .profile-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 15px;
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        /* Efek hover pada tombol profil */
        .profile-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        /* Gaya avatar pengguna */
        .profile-btn .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }
        
        /* Gaya nama pengguna */
        .profile-btn .user-name {
            font-weight: 500;
        }
        
        /* Gaya menu dropdown */
        .dropdown-menu {
            border-radius: 10px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        /* Gaya item dropdown */
        .dropdown-item {
            padding: 8px 16px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        
        /* Efek hover pada item dropdown */
        .dropdown-item:hover {
            background-color: #f8f9fa;
            color: var(--kasa-primary);
        }

        /* Efek hover pada tombol outline primary */
        a.btn-outline-primary:hover {
            background-color: #0d6efd;
            color: white !important;
            transition: all 0.3s ease-in-out;
        }

        /* Penyesuaian vertikal ikon dalam tombol */
        .btn i {
            vertical-align: middle;
        }

        /* Responsivitas untuk perangkat mobile */
        @media (max-width: 768px) {
            .security-container {
                margin: 1rem;
            }
            
            .security-header {
                padding: 1.5rem;
            }
            
            .security-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigasi Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: rgba(0, 0, 0, 0.2); backdrop-filter: blur(5px);">
        <div class="container">
            <!-- Brand/logo -->
            <a class="navbar-brand fw-bold" href="#">
                <i class="bi bi-train-front me-2"></i>PT-KASA INDONESIA
            </a>
            
            <!-- Menu profil pengguna -->
            <div class="d-flex align-items-center">
                <!-- Tombol profil dengan dropdown -->
                <div class="dropdown">
                    <a href="#" class="profile-btn dropdown-toggle" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['nama']) ?>&background=0056b3&color=fff" alt="User Avatar" class="user-avatar">
                        <span class="user-name d-none d-sm-inline"><?= htmlspecialchars($user['nama']) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end mt-2" aria-labelledby="profileDropdown">
                        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i> Profil</a></li>
                        <li><a class="dropdown-item" href="home.php"><i class="bi bi-shield-lock me-2"></i> Beranda</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Konten Utama Keamanan -->
    <div class="container py-5">
        <div class="security-container">
            <!-- Header keamanan -->
            <div class="security-header">
                <div class="security-icon">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <h3 class="mb-2">Keamanan Akun</h3>
                <p class="mb-0 text-white-50">Kelola keamanan akun Anda</p>
            </div>
            
            <!-- Body keamanan -->
            <div class="security-body">
                <!-- Menampilkan pesan error jika ada -->
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger security-alert alert-dismissible fade show mb-4">
                        <i class="bi bi-exclamation-octagon-fill me-2"></i>
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Menampilkan pesan sukses jika ada -->
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success security-alert alert-dismissible fade show mb-4">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Formulir perubahan password -->
                <form method="POST" id="passwordForm" class="mb-4">
                    <!-- Input password saat ini -->
                    <div class="mb-4">
                        <label for="current_password" class="form-label">Password Saat Ini</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                            <span class="input-group-text toggle-password" data-target="current_password">
                                <i class="bi bi-eye-slash"></i>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Input password baru -->
                    <div class="mb-4">
                        <label for="new_password" class="form-label">Password Baru</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <span class="input-group-text toggle-password" data-target="new_password">
                                <i class="bi bi-eye-slash"></i>
                            </span>
                        </div>
                        <!-- Indikator kekuatan password -->
                        <div class="password-strength mt-2">
                            <div class="password-strength-bar" id="passwordStrengthBar"></div>
                        </div>
                        <!-- Daftar persyaratan password -->
                        <div class="password-requirements mt-3">
                            <div class="requirement-item" id="reqLength">Minimal 8 karakter</div>
                            <div class="requirement-item" id="reqUpper">Mengandung huruf besar (A-Z)</div>
                            <div class="requirement-item" id="reqNumber">Mengandung angka (0-9)</div>
                            <div class="requirement-item" id="reqSpecial">Mengandung karakter khusus (!@#$%^&*)</div>
                        </div>
                    </div>
                    
                    <!-- Input konfirmasi password baru -->
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            <span class="input-group-text toggle-password" data-target="confirm_password">
                                <i class="bi bi-eye-slash"></i>
                            </span>
                        </div>
                        <!-- Pesan error konfirmasi password -->
                        <div class="invalid-feedback d-block" id="confirmError" style="display: none;">Password harus sesuai</div>
                    </div>
                    
                    <!-- Tombol submit -->
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-security">
                            <i class="bi bi-shield-check me-2"></i> Perbarui Password
                        </button>
                    </div>
                </form>
                
                <!-- Tip keamanan -->
                <div class="security-tip">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>Tips Keamanan:</strong> Gunakan password yang unik dan tidak digunakan di akun lain.
                </div>
                
                <!-- Tombol kembali -->
               <div class="text-center mt-4">
             <a href="profile.php" class="btn btn-outline-primary btn-lg fw-semibold shadow-sm rounded-pill px-4 py-2">
                    <i class="bi bi-arrow-left me-2 fs-5"></i> Kembali ke Profil
            </a>
            </div>

            </div>
        </div>
    </div>

    <!-- Menyertakan JavaScript Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript Kustom -->
    <script>
        // Fungsi untuk menampilkan/menyembunyikan password
        document.querySelectorAll('.toggle-password').forEach(item => {
            item.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                }
            });
        });
        
        // Fungsi untuk mengecek kekuatan password
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        const passwordStrengthBar = document.getElementById('passwordStrengthBar');
        const form = document.getElementById('passwordForm');
        
        // Event listener untuk input password baru
        newPassword.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Pengecekan panjang password
            if (password.length >= 8) {
                strength += 1;
                document.getElementById('reqLength').classList.add('valid');
            } else {
                document.getElementById('reqLength').classList.remove('valid');
            }
            
            // Pengecekan huruf besar
            if (/[A-Z]/.test(password)) {
                strength += 1;
                document.getElementById('reqUpper').classList.add('valid');
            } else {
                document.getElementById('reqUpper').classList.remove('valid');
            }
            
            // Pengecekan angka
            if (/[0-9]/.test(password)) {
                strength += 1;
                document.getElementById('reqNumber').classList.add('valid');
            } else {
                document.getElementById('reqNumber').classList.remove('valid');
            }
            
            // Pengecekan karakter khusus
            if (/[^A-Za-z0-9]/.test(password)) {
                strength += 1;
                document.getElementById('reqSpecial').classList.add('valid');
            } else {
                document.getElementById('reqSpecial').classList.remove('valid');
            }
            
            // Memperbarui indikator kekuatan password
            let width = 0;
            let color = '#dc3545';
            
            if (strength === 0) {
                width = 0;
            } else if (strength === 1) {
                width = 25;
                color = '#dc3545';
            } else if (strength === 2) {
                width = 50;
                color = '#fd7e14';
            } else if (strength === 3) {
                width = 75;
                color = '#ffc107';
            } else {
                width = 100;
                color = '#28a745';
            }
            
            passwordStrengthBar.style.width = width + '%';
            passwordStrengthBar.style.backgroundColor = color;
        });
        
        // Menvalidasi konfirmasi password
        confirmPassword.addEventListener('input', function() {
            if (this.value !== newPassword.value) {
                this.classList.add('is-invalid');
                document.getElementById('confirmError').style.display = 'block';
            } else {
                this.classList.remove('is-invalid');
                document.getElementById('confirmError').style.display = 'none';
            }
        });
        
        // Menvalidasi formulir sebelum submit
        form.addEventListener('submit', function(e) {
            if (newPassword.value !== confirmPassword.value) {
                e.preventDefault();
                confirmPassword.classList.add('is-invalid');
                document.getElementById('confirmError').style.display = 'block';
                confirmPassword.focus();
            }
        });
    </script>
</body>
</html>