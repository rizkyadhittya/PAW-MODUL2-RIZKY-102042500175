<?php
require 'koneksi.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    exit('ID tugas tidak valid. <a href="daftar_tugas.php">Kembali ke daftar</a>');
}

// Ambil data tugas dengan prepared statement
$stmt = $conn->prepare("SELECT t.*, k.kode AS kode_mk, k.nama AS nama_mk 
                        FROM tugas t 
                        JOIN mata_kuliah k ON t.mata_kuliah_id = k.id 
                        WHERE t.id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$hasil = $stmt->get_result();
$tugas = $hasil->fetch_assoc();

if (!$tugas) {
    exit('Tugas tidak ditemukan. <a href="daftar_tugas.php">Kembali ke daftar</a>');
}

$pesan_kesalahan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();

    $del_stmt = $conn->prepare("DELETE FROM tugas WHERE id = ?");
    $del_stmt->bind_param('i', $id);

    if ($del_stmt->execute()) {
        $_SESSION['pesan'] = 'Tugas berhasil dihapus.';
        header('Location: daftar_tugas.php');
        exit;
    } else {
        $pesan_kesalahan = 'Gagal menghapus tugas: ' . $del_stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus Tugas - LMS EAD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main>
    <?php require 'navbar.php'; ?>

    <p class="label">TUGAS / KONFIRMASI</p>
    <h1>Hapus tugas?</h1>

    <?php if ($pesan_kesalahan !== ''): ?>
        <div class="kesalahan"><?= e($pesan_kesalahan) ?></div>
    <?php endif; ?>

    <div class="kartu-detail">
        <p>Hapus tugas <strong><?= e($tugas['judul']) ?></strong> (Mata kuliah: <?= e($tugas['kode_mk']) ?> - <?= e($tugas['nama_mk']) ?>)?</p>
        <p>Data baru dihapus setelah tombol konfirmasi dikirim.</p>
        <p>Data yang sudah dihapus tidak bisa dikembalikan.</p>

        <form method="post" style="margin-top: 20px;">
            <input type="hidden" name="token" value="<?= e($_SESSION['token']) ?>">
            <button type="submit" name="hapus" class="bahaya">Ya, hapus data</button>
            <a href="daftar_tugas.php" class="tombol tombol-outline">Batal</a>
        </form>
    </div>
</main>
</body>
</html>
