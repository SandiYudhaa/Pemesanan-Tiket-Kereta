<?php
// Memulai session untuk menyimpan data pengguna
session_start();

// Menyertakan file konfigurasi dan fungsi pendukung
include 'config.php';
include 'functions.php';

// Memeriksa apakah pengguna sudah login, jika tidak redirect ke halaman login
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Mengambil data user dari session
$user = $_SESSION['user'];

// Menangani proses pemesanan jika form dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Mengambil data dari form pemesanan
    $jadwal_id = $_POST['jadwal_id'];
    $nama_penumpang = $_POST['nama_penumpang'];
    $gerbong = $_POST['gerbong'];
    $kursi = $_POST['kursi'];
    $jumlah = $_POST['jumlah'];
    $total_harga = $_POST['total_harga'];
    $user_id = $user['id'];
    
    // Menyiapkan query untuk menyimpan pemesanan
    $stmt = $conn->prepare("INSERT INTO pemesanan (user_id, jadwal_id, nama_penumpang, no_ktp, no_hp, gerbong, kursi, jumlah, total_harga, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("iisssii", $user_id, $jadwal_id, $nama_penumpang, $gerbong, $kursi, $jumlah, $total_harga);

    // Menjalankan query dan menangani hasilnya
    if ($stmt->execute()) {
        // Jika berhasil, set session dan redirect ke halaman riwayat
        $_SESSION['booking_success'] = true;
        header("Location: riwayat.php");
        exit();
    } else {
        // Jika gagal, simpan pesan error
        $error = "Gagal menyimpan: " . $stmt->error;
    }
}

// Mengambil detail jadwal jika parameter jadwal_id ada
$jadwal = null;
if (isset($_GET['jadwal_id'])) {
    $jadwal_id = $_GET['jadwal_id'];
   
    // Query untuk mendapatkan detail jadwal berdasarkan ID
    $sql = "SELECT p.*, j.waktu_berangkat, j.waktu_tiba, s1.nama AS asal, s2.nama AS tujuan, k.nama AS nama_kereta
        FROM pemesanan p
        JOIN jadwal j ON p.jadwal_id = j.id
        JOIN kereta k ON j.kereta_id = k.id
        JOIN stasiun s1 ON j.stasiun_asal_id = s1.id
        JOIN stasiun s2 ON j.stasiun_tujuan_id = s2.id
        WHERE p.user_id = ?
        ORDER BY p.id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();

    // Mengambil data jadwal
    $jadwal = mysqli_fetch_assoc($result);
    
    // Jika jadwal tidak ditemukan, redirect ke halaman jadwal
    if (!$jadwal) {
        header("Location: jadwal.php");
        exit();
    }
    
    // Mengambil kursi yang tersedia untuk kereta ini
    $sql = "SELECT gerbong, no_kursi FROM kursi 
            WHERE kereta_id = " . $jadwal['kereta_id'] . "
            AND id NOT IN (
                SELECT kursi_id FROM pemesanan_kursi pk
                JOIN pemesanan p ON pk.pemesanan_id = p.id
                WHERE p.jadwal_id = $jadwal_id AND p.status = 'confirmed'
            )";
    $result = mysqli_query($conn, $sql);
    $available_seats = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $available_seats[$row['gerbong']][] = $row['no_kursi'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags dasar untuk halaman -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Tiket - KAI Indonesia</title>
    
    <!-- Menyertakan CSS Bootstrap dan ikon -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- CSS kustom untuk halaman booking -->
    <style>
        /* Variabel warna untuk tema */
        :root {
            --kai-red: #e31837;
            --kai-dark: #1a1a2e;
        }
        
        /* Styling dasar body */
        body {
            background-color: #f8f9fa;
        }
        
        /* Styling untuk sidebar */
        .sidebar {
            background-color: var(--kai-dark);
            color: white;
            min-height: 100vh;
            position: fixed;
            width: 250px;
        }
        
        /* Area konten utama */
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        
        /* Navbar kustom */
        .navbar-kai {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        /* Card kustom */
        .card-kai {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        /* Warna background dan text merah KAI */
        .bg-kai-red {
            background-color: var(--kai-red);
        }
        .text-kai-red {
            color: var(--kai-red);
        }
        
        /* Tombol kustom */
        .btn-kai {
            background-color: var(--kai-red);
            color: white;
        }
        .btn-kai:hover {
            background-color: #c1122d;
            color: white;
        }
        
        /* Styling untuk tempat duduk */
        .seat {
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 5px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .seat:hover {
            background-color: #f8f9fa;
        }
        .seat.selected {
            background-color: var(--kai-red);
            color: white;
            border-color: var(--kai-red);
        }
        .seat.booked {
            background-color: #6c757d;
            color: white;
            border-color: #6c757d;
            cursor: not-allowed;
        }
        .seat.available {
            background-color: #28a745;
            color: white;
            border-color: #28a745;
        }
    </style>
</head>
<body>
    <!-- Menyertakan sidebar -->
    <?php include 'sidebar.php'; ?>
    
    <!-- Konten utama -->
    <div class="main-content">
        <!-- Navbar atas -->
        <nav class="navbar navbar-expand-lg navbar-kai mb-4">
            <div class="container-fluid">
                <div class="d-flex align-items-center">
                    <!-- Tombol toggle sidebar untuk mobile -->
                    <button class="btn btn-outline-secondary me-3 d-lg-none" id="sidebarToggle">
                        <i class="bi bi-list"></i>
                    </button>
                    <h5 class="mb-0">Pemesanan Tiket Kereta</h5>
                </div>
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <!-- Menu dropdown user -->
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
        
        <!-- Menampilkan pesan error jika ada -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- Konten booking jika jadwal tersedia -->
        <?php if ($jadwal): ?>
        <div class="row">
            <!-- Card detail perjalanan -->
            <div class="col-md-12 mb-4">
                <div class="card card-kai">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <!-- Nama kereta dan kelas -->
                            <div class="col-md-3">
                                <h5><?= $jadwal['nama_kereta'] ?></h5>
                                <span class="badge bg-secondary"><?= $jadwal['kelas'] ?></span>
                            </div>
                            
                            <!-- Waktu dan stasiun keberangkatan -->
                            <div class="col-md-2">
                                <small>Keberangkatan</small>
                                <h6 class="mb-0"><?= date('H:i', strtotime($jadwal['waktu_berangkat'])) ?></h6>
                                <small><?= $jadwal['stasiun_asal'] ?></small>
                            </div>
                            
                            <!-- Durasi perjalanan -->
                            <div class="col-md-2 text-center">
                                <i class="bi bi-arrow-right text-muted"></i>
                                <div class="text-muted small"><?= $jadwal['durasi'] ?> jam</div>
                            </div> 
                            
                            <!-- Waktu dan stasiun kedatangan -->
                            <div class="col-md-2"> 
                                <small>Kedatangan</small> 
                                <h6 class="mb-0"><?= date('H:i', strtotime($jadwal['waktu_tiba'])) ?></h6>
                                <small><?= $jadwal['stasiun_tujuan'] ?></small>
                            </div>
                            
                            <!-- Harga tiket -->
                            <div class="col-md-3">
                                <h5 class="text-kai-red mb-0">Rp <?= number_format($jadwal['harga'], 0, ',', '.') ?></h5>
                                <small class="text-muted">per orang</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Form data penumpang -->
            <div class="col-md-6">
                <div class="card card-kai">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Data Penumpang</h5>
                        <form method="POST" id="bookingForm">
                            <!-- Input hidden untuk data penting -->
                            <input type="hidden" name="jadwal_id" value="<?= $jadwal['id'] ?>">
                            <input type="hidden" name="total_harga" id="totalHarga" value="<?= $jadwal['harga'] ?>">
                            
                            <!-- Input nama penumpang -->
                            <div class="mb-3">
                                <label for="nama_penumpang" class="form-label">Nama Lengkap Penumpang</label>
                                <input type="text" class="form-control" id="nama_penumpang" name="nama_penumpang" required>
                            </div>
                            
                            <!-- Input jumlah penumpang -->
                            <div class="mb-3">
                                <label for="jumlah" class="form-label">Jumlah Penumpang</label>
                                <select class="form-select" id="jumlah" name="jumlah" required>
                                    <option value="1">1 Penumpang</option>
                                    <option value="2">2 Penumpang</option>
                                    <option value="3">3 Penumpang</option>
                                    <option value="4">4 Penumpang</option>
                                </select>
                            </div>
                    </div>
                </div>
            </div>
            
            <!-- Pilihan tempat duduk -->
            <div class="col-md-6">
                <div class="card card-kai">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Pilih Tempat Duduk</h5>
                        
                        <!-- Pilihan gerbong -->
                        <div class="mb-3">
                            <label for="gerbong" class="form-label">Gerbong</label>
                            <select class="form-select" id="gerbong" name="gerbong" required>
                                <?php foreach ($available_seats as $gerbong => $seats): ?>
                                    <option value="<?= $gerbong ?>">Gerbong <?= $gerbong ?> (<?= count($seats) ?> kursi tersedia)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Pilihan kursi -->
                        <div class="mb-3">
                            <label class="form-label">Pilih Kursi</label>
                            <div id="seatSelection">
                                <?php 
                                $first_gerbong = array_key_first($available_seats);
                                foreach ($available_seats[$first_gerbong] as $seat): 
                                ?>
                                    <div class="seat available" data-seat="<?= $seat ?>"><?= $seat ?></div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" id="kursi" name="kursi" required>
                        </div>
                        
                        <!-- Total harga dan tombol pesan -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <h5>Total Harga: <span id="totalPrice">Rp <?= number_format($jadwal['harga'], 0, ',', '.') ?></span></h5>
                            <button type="submit" class="btn btn-kai btn-lg w-100">Pesan Sekarang</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <!-- Pesan jika tidak ada jadwal yang dipilih -->
        <div class="alert alert-warning">
            Silakan pilih jadwal kereta terlebih dahulu dari halaman <a href="jadwal.php">Jadwal Kereta</a>.
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Menyertakan JavaScript Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript kustom untuk halaman booking -->
    <script>
        // Fungsi untuk toggle sidebar di mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('d-none');
        });
        
        // Fungsi untuk menangani pemilihan tempat duduk
        document.addEventListener('DOMContentLoaded', function() {
            const gerbongSelect = document.getElementById('gerbong');
            const seatSelection = document.getElementById('seatSelection');
            const kursiInput = document.getElementById('kursi');
            const jumlahSelect = document.getElementById('jumlah');
            const totalHargaInput = document.getElementById('totalHarga');
            const totalPriceSpan = document.getElementById('totalPrice');
            const basePrice = <?= $jadwal ? $jadwal['harga'] : 0 ?>;
            
            // Event ketika gerbong diubah
            gerbongSelect.addEventListener('change', function() {
                const gerbong = this.value;
                kursiInput.value = '';
                
                // Mengosongkan pilihan kursi sebelumnya
                seatSelection.innerHTML = '';
                
                // Menambahkan kursi yang tersedia untuk gerbong yang dipilih
                <?php foreach ($available_seats as $gerbong => $seats): ?>
                    if (gerbong == '<?= $gerbong ?>') {
                        <?php foreach ($seats as $seat): ?>
                            const seatDiv = document.createElement('div');
                            seatDiv.className = 'seat available';
                            seatDiv.textContent = '<?= $seat ?>';
                            seatDiv.dataset.seat = '<?= $seat ?>';
                            seatDiv.addEventListener('click', selectSeat);
                            seatSelection.appendChild(seatDiv);
                        <?php endforeach; ?>
                    }
                <?php endforeach; ?>
            });
            
            // Fungsi untuk memilih kursi
            function selectSeat() {
                if (this.classList.contains('available')) {
                    // Menghapus seleksi sebelumnya
                    document.querySelectorAll('.seat.selected').forEach(seat => {
                        seat.classList.remove('selected');
                        seat.classList.add('available');
                    });
                    
                    // Memilih kursi ini
                    this.classList.remove('available');
                    this.classList.add('selected');
                    kursiInput.value = this.dataset.seat;
                }
            }
            
            // Menambahkan event listener ke kursi yang tersedia
            document.querySelectorAll('.seat.available').forEach(seat => {
                seat.addEventListener('click', selectSeat);
            });
            
            // Mengupdate total harga ketika jumlah penumpang berubah
            jumlahSelect.addEventListener('change', function() {
                const total = basePrice * parseInt(this.value);
                totalHargaInput.value = total;
                totalPriceSpan.textContent = 'Rp ' + total.toLocaleString('id-ID');
            });
        });
    </script>
</body>
</html>