<?php
// Pastikan session aktif di setiap request
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inisialisasi token CSRF jika belum ada
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

// Konfigurasi Database LMS
$host = '127.0.0.1';
$user = 'root';
$dbname = 'paw_jurnal2_lms';

// Coba koneksi dengan password '1234', jika gagal coba password kosong ''
$conn = @mysqli_connect($host, $user, '1234', $dbname);
if (!$conn) {
    $conn = @mysqli_connect($host, $user, '', $dbname);
}

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// Set karakter utf8mb4
mysqli_set_charset($conn, 'utf8mb4');

/**
 * Fungsi pembantu untuk sanitasi teks XSS
 */
function e($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

/**
 * Fungsi validasi token CSRF pada form POST
 */
function verifikasi_csrf() {
    $token_post = $_POST['token'] ?? '';
    $token_session = $_SESSION['token'] ?? '';
    if (!hash_equals($token_session, $token_post)) {
        http_response_code(403);
        die("Permintaan ditolak: Token keamanan (CSRF) tidak valid atau sesi berakhir. <a href='index.php'>Kembali</a>");
    }
}
