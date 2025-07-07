<?php
// Memulai sesi
session_start();
// Menyertakan file konfigurasi dan fungsi
include 'config.php';
include 'functions.php';

// Memeriksa apakah pengguna sudah login, jika tidak redirect ke halaman index
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Mengambil data pengguna dari sesi
$user = $_SESSION['user'];

// Menangani pengiriman testimoni
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_testimoni'])) {
    // Mengambil data dari form
    $rating = $_POST['rating'];
    $komentar = $_POST['komentar'];
    $jadwal_id = $_POST['jadwal_id'] ?? null; // Opsional
    $user_id = $user['id'];

    // Menangani unggahan gambar
    $gambar = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
        $target_dir = "uploads/testimoni/";
        // Membuat direktori jika belum ada
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Memeriksa ekstensi file
        $file_ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_ext)) {
            // Membuat nama file unik
            $filename = uniqid() . '.' . $file_ext;
            $target_file = $target_dir . $filename;

            // Memindahkan file yang diunggah
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                $gambar = $target_file;
            }
        }
    }

    // Menyimpan testimoni ke database
    $stmt = $conn->prepare("INSERT INTO testimoni (user_id, jadwal_id, rating, komentar, gambar) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiss", $user_id, $jadwal_id, $rating, $komentar, $gambar);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Testimoni berhasil dikirim!";
        header("Location: testimoni.php");
        exit();
    } else {
        $error = "Gagal menyimpan testimoni: " . $stmt->error;
    }
}

// Menangani penghapusan testimoni
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $user_id = $user['id'];

    // Mengambil data gambar testimoni yang akan dihapus
    $sql = "SELECT gambar FROM testimoni WHERE id = $id AND user_id = $user_id";
    $result = mysqli_query($conn, $sql);
    $testimoni = mysqli_fetch_assoc($result);

    // Menghapus file gambar jika ada
    if ($testimoni && $testimoni['gambar']) {
        unlink($testimoni['gambar']);
    }

    // Menghapus testimoni dari database
    $sql = "DELETE FROM testimoni WHERE id = $id AND user_id = $user_id";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Testimoni berhasil dihapus!";
        header("Location: testimoni.php");
        exit();
    } else {
        $error = "Gagal menghapus testimoni: " . mysqli_error($conn);
    }
}

// Mengambil semua testimoni dari pengguna ini
$sql = "SELECT * FROM testimoni WHERE user_id = " . $user['id'] . " ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
$testimonies = [];
while ($row = mysqli_fetch_assoc($result)) {
    $testimonies[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags untuk pengaturan dokumen HTML -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testimoni - PT-KASA Indonesia</title>
    <!-- Menyertakan CSS Bootstrap dan ikon -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Gaya CSS khusus -->
    <style>
        /* Variabel warna */
        :root {
            --kai-red: #e31837;
            --kai-dark: #1a1a2e;
        }
        /* Gaya dasar body */
        body {
            background-color: #f8f9fa;
        }
        /* Gaya sidebar */
        .sidebar {
            background-color: var(--kai-dark);
            color: white;
            min-height: 100vh;
            position: fixed;
            width: 250px;
        }
        /* Gaya konten utama */
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        /* Gaya navbar */
        .navbar-kai {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        /* Gaya card */
        .card-kai {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        /* Kelas warna khusus */
        .bg-kai-red {
            background-color: var(--kai-red);
        }
        .text-kai-red {
            color: var(--kai-red);
        }
        /* Gaya tombol */
        .btn-kai {
            background-color: var(--kai-red);
            color: white;
        }
        .btn-kai:hover {
            background-color: #c1122d;
            color: white;
        }
        /* Gaya rating bintang */
        .star-rating {
            color: #ffc107;
            font-size: 1.5rem;
        }
        /* Gaya gambar testimoni */
        .testimoni-img {
            max-height: 200px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<!-- Menyertakan sidebar -->
<?php include 'sidebar.php'; ?>
<!-- Konten utama -->
<div class="main-content">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-kai mb-4">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <!-- Tombol toggle sidebar untuk perangkat mobile -->
                <button class="btn btn-outline-secondary me-3 d-lg-none" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <h5 class="mb-0">Testimoni Perjalanan</h5>
            </div>
        </div>
    </nav>

    <!-- Menampilkan pesan sukses jika ada -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- Menampilkan pesan error jika ada -->
    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Baris konten -->
    <div class="row">
        <!-- Kolom form testimoni -->
        <div class="col-md-6 mb-4">
            <div class="card card-kai">
                <div class="card-body">
                    <h5 class="card-title mb-4">Tambah Testimoni</h5>
                    <!-- Form testimoni -->
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="jadwal_id" value="1"> <!-- Ganti dengan jadwal_id dinamis jika diperlukan -->
                        <!-- Input rating -->
                        <div class="mb-3">
                            <label for="rating" class="form-label">Rating</label>
                            <select class="form-select" id="rating" name="rating" required>
                                <option value="">Pilih Rating</option>
                                <option value="5">5 - Sangat Baik</option>
                                <option value="4">4 - Baik</option>
                                <option value="3">3 - Cukup</option>
                                <option value="2">2 - Kurang</option>
                                <option value="1">1 - Sangat Kurang</option>
                            </select>
                        </div>
                        <!-- Input komentar -->
                        <div class="mb-3">
                            <label for="komentar" class="form-label">Komentar</label>
                            <textarea class="form-control" id="komentar" name="komentar" rows="3" required></textarea>
                        </div>
                        <!-- Input gambar -->
                        <div class="mb-3">
                            <label for="gambar" class="form-label">Unggah Gambar</label>
                            <input class="form-control" type="file" id="gambar" name="gambar" accept="image/*">
                        </div>
                        <!-- Tombol submit -->
                        <button type="submit" name="submit_testimoni" class="btn btn-kai">Kirim Testimoni</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kolom daftar testimoni -->
        <div class="col-md-6 mb-4">
            <div class="card card-kai">
                <div class="card-body">
                    <h5 class="card-title mb-4">Testimoni Saya</h5>
                    <!-- Jika tidak ada testimoni -->
                    <?php if (empty($testimonies)): ?>
                        <div class="alert alert-info">
                            Anda belum memberikan testimoni.
                        </div>
                    <?php else: ?>
                        <!-- Daftar testimoni -->
                        <div class="list-group">
                            <?php foreach ($testimonies as $testimoni): ?>
                                <div class="list-group-item mb-3 rounded">
                                    <!-- Header testimoni dengan rating dan tanggal -->
                                    <div class="d-flex justify-content-between">
                                        <div class="star-rating mb-2">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi bi-star<?= $i <= $testimoni['rating'] ? '-fill' : '' ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <small class="text-muted"><?= date('d M Y', strtotime($testimoni['created_at'])) ?></small>
                                    </div>
                                    <!-- Isi komentar -->
                                    <p><?= htmlspecialchars($testimoni['komentar']) ?></p>
                                    <!-- Gambar testimoni jika ada -->
                                    <?php if ($testimoni['gambar']): ?>
                                        <div class="mb-2">
                                            <img src="<?= $testimoni['gambar'] ?>" class="testimoni-img img-fluid" alt="Testimoni">
                                        </div>
                                    <?php endif; ?>
                                    <!-- Tombol hapus -->
                                    <div class="d-flex justify-content-end">
                                        <a href="testimoni.php?delete=<?= $testimoni['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus testimoni ini?')">
                                            <i class="bi bi-trash"></i> Hapus
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Menyertakan JavaScript Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Skrip untuk toggle sidebar -->
<script>
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('d-none');
    });
</script>
</body>
</html>