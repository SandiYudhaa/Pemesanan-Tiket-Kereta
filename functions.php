<?php
/**
 * Fungsi untuk melakukan proses login user
 * 
 * @param string $email Email pengguna
 * @param string $password Password pengguna
 * @return array|false Mengembalikan data user jika berhasil, false jika gagal
 */
function loginUser($email, $password) {
    global $conn; // Menggunakan koneksi database global
    
    // Query untuk mencari user berdasarkan email
    $sql = "SELECT * FROM user WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    
    // Jika ditemukan tepat 1 user
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result); // Ambil data user
        
        // Verifikasi password dengan hash yang tersimpan
        if (password_verify($password, $user['password'])) {
            return $user; // Return data user jika password valid
        }
    }
    
    return false; // Return false jika login gagal
}

/**
 * Fungsi untuk mendapatkan jadwal kereta hari ini
 * 
 * @param mysqli $conn Koneksi database
 * @return array Array berisi jadwal kereta hari ini
 */
function getTodaySchedules($conn) {
    $today = date('Y-m-d'); // Ambil tanggal hari ini
    
    // Query untuk mendapatkan jadwal hari ini dengan detail kereta dan stasiun
    $sql = "SELECT j.*, k.nama as nama_kereta, k.kelas, 
            s1.nama as stasiun_asal, s2.nama as stasiun_tujuan
            FROM jadwal j
            JOIN kereta k ON j.kereta_id = k.id
            JOIN stasiun s1 ON j.stasiun_asal_id = s1.id
            JOIN stasiun s2 ON j.stasiun_tujuan_id = s2.id
            WHERE DATE(j.waktu_berangkat) = '$today'
            ORDER BY j.waktu_berangkat ASC";
    
    $result = mysqli_query($conn, $sql);
    
    $schedules = []; // Array untuk menyimpan hasil
    while ($row = mysqli_fetch_assoc($result)) {
        $schedules[] = $row; // Tambahkan setiap baris ke array
    }
    
    return $schedules; // Kembalikan array jadwal
}

/**
 * Fungsi untuk mendapatkan riwayat pemesanan user
 * 
 * @param mysqli $conn Koneksi database
 * @param int $user_id ID user
 * @return array Array berisi riwayat pemesanan
 */
function getUserBookings($conn, $user_id) {
    // Query untuk mendapatkan semua pemesanan user dengan detail lengkap
    $sql = "SELECT p.*, j.waktu_berangkat, k.nama as nama_kereta, 
                   s1.nama as stasiun_asal, s2.nama as stasiun_tujuan
            FROM pemesanan p
            JOIN jadwal j ON p.jadwal_id = j.id
            JOIN kereta k ON j.kereta_id = k.id
            JOIN stasiun s1 ON j.stasiun_asal_id = s1.id
            JOIN stasiun s2 ON j.stasiun_tujuan_id = s2.id
            WHERE p.user_id = $user_id
            ORDER BY j.waktu_berangkat DESC";
    
    $result = mysqli_query($conn, $sql);
    
    $bookings = []; // Array untuk menyimpan hasil
    while ($row = mysqli_fetch_assoc($result)) {
        $bookings[] = $row; // Tambahkan setiap baris ke array
    }
    
    return $bookings; // Kembalikan array pemesanan
}

/**
 * Fungsi untuk mendapatkan pemesanan aktif user
 * 
 * @param mysqli $conn Koneksi database
 * @param int $user_id ID user
 * @return array Array berisi pemesanan yang masih aktif
 */
function getActiveBookings($conn, $user_id) {
    // Query untuk mendapatkan pemesanan dengan status confirmed dan tanggal belum lewat
    $sql = "SELECT p.*, j.waktu_berangkat, k.nama as nama_kereta
            FROM pemesanan p
            JOIN jadwal j ON p.jadwal_id = j.id
            JOIN kereta k ON j.kereta_id = k.id
            WHERE p.user_id = $user_id AND p.status = 'confirmed'
            AND DATE(j.waktu_berangkat) >= CURDATE()
            ORDER BY j.waktu_berangkat ASC";
            
    $result = mysqli_query($conn, $sql);
    
    $bookings = []; // Array untuk menyimpan hasil
    while ($row = mysqli_fetch_assoc($result)) {
        $bookings[] = $row; // Tambahkan setiap baris ke array
    }
    
    return $bookings; // Kembalikan array pemesanan aktif
}

/**
 * Fungsi untuk mendapatkan testimoni user
 * 
 * @param mysqli $conn Koneksi database
 * @param int $user_id ID user
 * @return array Array berisi testimoni user
 */
function getUserTestimonies($conn, $user_id) {
    // Query untuk mendapatkan semua testimoni user
    $sql = "SELECT * FROM testimoni WHERE user_id = $user_id ORDER BY created_at DESC";
    $result = mysqli_query($conn, $sql);
    
    $testimonies = []; // Array untuk menyimpan hasil
    while ($row = mysqli_fetch_assoc($result)) {
        $testimonies[] = $row; // Tambahkan setiap baris ke array
    }
    
    return $testimonies; // Kembalikan array testimoni
}

/**
 * Fungsi untuk mendapatkan jadwal terbaru
 * 
 * @param mysqli $conn Koneksi database
 * @param int $limit Jumlah data yang akan diambil (default: 5)
 * @return array Array berisi jadwal terbaru
 */
function getLatestSchedules($conn, $limit = 5) {
    // Query untuk mendapatkan jadwal yang belum lewat
    $sql = "SELECT j.*, k.nama as nama_kereta, k.kelas, 
            s1.nama as stasiun_asal, s2.nama as stasiun_tujuan
            FROM jadwal j
            JOIN kereta k ON j.kereta_id = k.id
            JOIN stasiun s1 ON j.stasiun_asal_id = s1.id
            JOIN stasiun s2 ON j.stasiun_tujuan_id = s2.id
            WHERE DATE(j.waktu_berangkat) >= CURDATE()
            ORDER BY j.waktu_berangkat ASC
            LIMIT $limit";
            
    $result = mysqli_query($conn, $sql);
    
    $schedules = []; // Array untuk menyimpan hasil
    while ($row = mysqli_fetch_assoc($result)) {
        $schedules[] = $row; // Tambahkan setiap baris ke array
    }
    
    return $schedules; // Kembalikan array jadwal
}
?>