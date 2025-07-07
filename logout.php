<?php
// Memulai session PHP
session_start();

// Mengosongkan semua data session yang tersimpan
$_SESSION = array();

// Menghapus cookie session jika digunakan
if (ini_get("session.use_cookies")) {
    // Mengambil parameter cookie session saat ini
    $params = session_get_cookie_params();
    
    // Mengatur cookie session dengan waktu kadaluarsa di masa lalu
    setcookie(
        session_name(),       // Nama session
        '',                  // Nilai kosong
        time() - 42000,      // Waktu kadaluarsa (masa lalu)
        $params["path"],     // Path cookie
        $params["domain"],   // Domain cookie
        $params["secure"],   // Koneksi secure
        $params["httponly"]  // Hanya HTTP
    );
}

// Menghancurkan session sepenuhnya
session_destroy();

// Mengarahkan pengguna kembali ke halaman login
header("Location: index.php");

// Menghentikan eksekusi script lebih lanjut
exit();
?>