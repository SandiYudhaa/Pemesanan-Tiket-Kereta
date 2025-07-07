<?php
// Mendefinisikan variabel host server database
$host = "localhost";

// Mendefinisikan variabel username untuk akses database
$username = "root";

// Mendefinisikan variabel password untuk akses database
$password = "";

// Mendefinisikan variabel nama database yang akan digunakan
$database = "kasa_ticket";

// Membuat koneksi ke database menggunakan parameter yang telah didefinisikan
$conn = mysqli_connect($host, $username, $password, $database);

// Memeriksa apakah koneksi berhasil atau gagal
if (!$conn) {
    // Menghentikan eksekusi script dan menampilkan pesan error jika koneksi gagal
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>