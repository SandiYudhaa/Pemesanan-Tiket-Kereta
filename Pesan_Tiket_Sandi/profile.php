<?php
session_start();
include 'config.php';
include 'functions.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];   
    // Perbaikan query SQL
    $sql = "UPDATE user SET nama = ?, email = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("ssi", $nama, $email, $user['id']);
        
        if ($stmt->execute()) {
            $_SESSION['user']['nama'] = $nama;
            $_SESSION['user']['email'] = $email;
            $success = "Profil berhasil diperbarui!";
        } else {
            $error = "Gagal memperbarui profil: " . $conn->error;
        }
        
        $stmt->close();
    } else {
        $error = "Error dalam persiapan statement: " . $conn->error;
    }
}

// Ambil data tiket aktif pengguna
$active_tickets = [];
$sql_active = "SELECT COUNT(*) as count FROM pemesanan 
               WHERE user_id = ? AND status = 'confirmed' 
               AND jadwal_id IN (SELECT id FROM jadwal WHERE waktu_berangkat >= NOW())";
$stmt_active = $conn->prepare($sql_active);
if ($stmt_active) {
    $stmt_active->bind_param("i", $user['id']);
    $stmt_active->execute();
    $result_active = $stmt_active->get_result();
    $active_tickets = $result_active->fetch_assoc();
    $stmt_active->close();
}

// Ambil total pesanan pengguna
$total_orders = [];
$sql_total = "SELECT COUNT(*) as count FROM pemesanan WHERE user_id = ?";
$stmt_total = $conn->prepare($sql_total);
if ($stmt_total) {
    $stmt_total->bind_param("i", $user['id']);
    $stmt_total->execute();
    $result_total = $stmt_total->get_result();
    $total_orders = $result_total->fetch_assoc();
    $stmt_total->close();
}

// Ambil riwayat aktivitas pengguna
$activity_history = [];
$sql_history = "SELECT 
                'Pemesanan Tiket' as activity, 
                CONCAT(s1.nama, ' - ', s2.nama) as description, 
                p.created_at as date,
                p.status
                FROM pemesanan p
                JOIN jadwal j ON p.jadwal_id = j.id
                JOIN stasiun s1 ON j.stasiun_asal_id = s1.id
                JOIN stasiun s2 ON j.stasiun_tujuan_id = s2.id
                WHERE p.user_id = ?
                
                UNION ALL
                
                SELECT 
                'Pembaruan Profil' as activity,
                'Perubahan data profil' as description,
                updated_at as date,
                'updated' as status
                FROM user
                WHERE id = ? AND updated_at IS NOT NULL
                
                ORDER BY date DESC
                LIMIT 5";
$stmt_history = $conn->prepare($sql_history);
if ($stmt_history) {
    $stmt_history->bind_param("ii", $user['id'], $user['id']);
    $stmt_history->execute();
    $result_history = $stmt_history->get_result();
    while ($row = $result_history->fetch_assoc()) {
        $activity_history[] = $row;
    }
    $stmt_history->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - PT KASA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --kasa-primary: #0056b3;
            --kasa-secondary: #003366;
            --kasa-accent: #ff6600;
            --kasa-light: #f8f9fa;
            --kasa-dark: #1a1a2e;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }
        
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .profile-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .profile-card:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
        }
        
        .profile-header {
            background: linear-gradient(135deg, var(--kasa-primary) 0%, var(--kasa-secondary) 100%);
            color: white;
            padding: 1.5rem;
            text-align: center;
            position: relative;
        }
        
        .profile-header h2 {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .user-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .user-avatar:hover {
            transform: scale(1.05);
        }
        
        .member-since {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .profile-sidebar {
            background-color: white;
            padding: 2rem;
            height: 100%;
            border-right: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .profile-content {
            background-color: white;
            padding: 2rem;
        }
        
        .section-title {
            color: var(--kasa-primary);
            font-weight: 600;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--kasa-accent);
        }
        
        .form-label {
            font-weight: 500;
            color: var(--kasa-secondary);
        }
        
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--kasa-primary);
            box-shadow: 0 0 0 0.2rem rgba(0, 86, 179, 0.1);
        }
        
        .btn-kasa {
            background: linear-gradient(135deg, var(--kasa-primary) 0%, var(--kasa-secondary) 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .btn-kasa:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(0, 86, 179, 0.2);
            color: white;
        }
        
        .btn-outline-kasa {
            border: 2px solid var(--kasa-primary);
            color: var(--kasa-primary);
            background: transparent;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-outline-kasa:hover {
            background: var(--kasa-primary);
            color: white;
        }
        
        .divider {
            border-top: 1px dashed #e0e0e0;
            margin: 1.5rem 0;
        }
        
        .profile-stat {
            text-align: center;
            padding: 1rem;
            border-radius: 8px;
            background-color: rgba(0, 86, 179, 0.05);
            transition: all 0.3s ease;
        }
        
        .profile-stat:hover {
            background-color: rgba(0, 86, 179, 0.1);
            transform: translateY(-3px);
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--kasa-primary);
        }
        
        .stat-label {
            font-size: 0.85rem;
            color: #666;
        }
        
        .activity-status {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .status-confirmed {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .status-canceled {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        
        .status-updated {
            background-color: rgba(0, 86, 179, 0.1);
            color: #0056b3;
        }
        
        @media (max-width: 768px) {
            .profile-sidebar {
                border-right: none;
                border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            }
            
            .user-avatar {
                width: 100px;
                height: 100px;
            }
            
            .profile-stat {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="container py-4">
            <div class="profile-card">
                <div class="profile-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2>Profil Saya</h2>
                            <p class="member-since mb-0">Member sejak <?= date('d M Y', strtotime($user['created_at'])) ?></p>
                        </div>
                        <a href="home.php" class="btn btn-outline-light">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                
                <div class="row g-0">
                    <!-- Sidebar Profil -->
                    <div class="col-lg-4">
                        <div class="profile-sidebar text-center">
                            <img src="img/<?= htmlspecialchars($user['foto'] ?? 'default-avatar.jpg') ?>" 
                                 alt="Foto Profil" 
                                 class="user-avatar mb-3"
                                 onerror="this.src='img/default-avatar.jpg'">
                            <h4><?= htmlspecialchars($user['nama']) ?></h4>
                            <p class="text-muted mb-4"><?= htmlspecialchars($user['email']) ?></p>
                            
                            <div class="row mt-4">
                                <div class="col-md-6 col-6">
                                    <div class="profile-stat">
                                        <div class="stat-value"><?= $active_tickets['count'] ?? 0 ?></div>
                                        <div class="stat-label">Tiket Aktif</div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-6">
                                    <div class="profile-stat">
                                        <div class="stat-value"><?= $total_orders['count'] ?? 0 ?></div>
                                        <div class="stat-label">Total Pesanan</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 mt-4">
                                <a href="change_password.php" class="btn btn-outline-kasa">
                                    <i class="bi bi-shield-lock"></i> Keamanan Akun
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Konten Utama -->
                    <div class="col-lg-8">
                        <div class="profile-content">
                            <?php if (!empty($success)): ?>
                                <div class="alert alert-success alert-dismissible fade show">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    <?= htmlspecialchars($success) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <?= htmlspecialchars($error) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <h5 class="section-title">Informasi Pribadi</h5>
                            
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control" name="nama" 
                                               value="<?= htmlspecialchars($user['nama']) ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" 
                                               value="<?= htmlspecialchars($user['email']) ?>" required>
                                    </div>
                                </div>
                                
                                
                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-kasa">
                                        <i class="bi bi-save"></i> Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                            
                            <div class="divider"></div>
                            
                            <h5 class="section-title">Riwayat Aktivitas Terakhir</h5>
                            
                            <div class="list-group">
                                <?php if (!empty($activity_history)): ?>
                                    <?php foreach ($activity_history as $activity): ?>
                                        <div class="list-group-item border-0 py-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1"><?= htmlspecialchars($activity['activity']) ?></h6>
                                                    <small class="text-muted">
                                                        <?= htmlspecialchars($activity['description']) ?> - 
                                                        <?= date('d M Y H:i', strtotime($activity['date'])) ?>
                                                    </small>
                                                </div>
                                                <span class="activity-status status-<?= 
                                                    $activity['status'] === 'confirmed' ? 'confirmed' : 
                                                    ($activity['status'] === 'canceled' ? 'canceled' : 'updated')
                                                ?>">
                                                    <?= ucfirst($activity['status']) ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-4 text-muted">
                                        <i class="bi bi-clock-history display-6 mb-3"></i>
                                        <p>Belum ada aktivitas terakhir</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Menampilkan animasi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.profile-stat, .user-avatar');
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