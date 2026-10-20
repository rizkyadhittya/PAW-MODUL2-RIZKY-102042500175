<?php
require 'koneksi.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    exit('ID mata kuliah tidak valid. <a href="daftar_mata_kuliah.php">Kembali ke daftar</a>');
}

// Ambil data mata kuliah dengan prepared statement
$stmt = $conn->prepare("SELECT * FROM mata_kuliah WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$hasil = $stmt->get_result();
$mk = $hasil->fetch_assoc();

if (!$mk) {
    exit('Mata kuliah tidak ditemukan. <a href="daftar_mata_kuliah.php">Kembali ke daftar</a>');
}

// Cek apakah mata kuliah masih memiliki materi atau tugas
$cek_mat = $conn->prepare("SELECT COUNT(*) FROM materi WHERE mata_kuliah_id = ?");
$cek_mat->bind_param('i', $id);
$cek_mat->execute();
$jml_materi = $cek_mat->get_result()->fetch_row()[0];

$cek_tug = $conn->prepare("SELECT COUNT(*) FROM tugas WHERE mata_kuliah_id = ?");
$cek_tug->bind_param('i', $id);
$cek_tug->execute();
$jml_tugas = $cek_tug->get_result()->fetch_row()[0];

$masih_dipakai = ($jml_materi > 0 || $jml_tugas > 0);
$pesan_kesalahan = '';

if ($masih_dipakai) {
    // ERROR J03 - Sesuai gambar modul jurnal halaman 17
    $pesan_kesalahan = 'Data belum tersimpan. Mata kuliah masih memiliki materi atau tugas. Hapus data terkait terlebih dahulu.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();

    if ($masih_dipakai) {
        $pesan_kesalahan = 'Data belum tersimpan. Mata kuliah masih memiliki materi atau tugas. Hapus data terkait terlebih dahulu.';
    } else {
        // Hapus dengan prepared statement
        $del_stmt = $conn->prepare("DELETE FROM mata_kuliah WHERE id = ?");
        $del_stmt->bind_param('i', $id);

        if ($del_stmt->execute()) {
            $_SESSION['pesan'] = 'Mata kuliah berhasil dihapus.';
            header('Location: daftar_mata_kuliah.php');
            exit;
        } else {
            $pesan_kesalahan = 'Gagal menghapus mata kuliah: ' . $del_stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus Mata Kuliah - LMS EAD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main>
    <?php require 'navbar.php'; ?>

    <p class="label">MATA KULIAH / KONFIRMASI</p>
    <h1>Hapus mata kuliah?</h1>

    <?php if ($pesan_kesalahan !== ''): ?>
        <div class="kesalahan"><?= e($pesan_kesalahan) ?></div>
    <?php endif; ?>

    <div class="kartu-detail">
        <p>Hapus <strong><?= e($mk['nama']) ?></strong> (<?= e($mk['kode']) ?>)?</p>
        <p>Data baru dihapus setelah tombol konfirmasi dikirim.</p>

        <?php if ($masih_dipakai): ?>
            <p style="color: #64748b; font-size: 14px;">
                <em>Mata kuliah ini memiliki <?= (int)$jml_materi ?> materi dan <?= (int)$jml_tugas ?> tugas terkait. Hapus materi dan tugas tersebut terlebih dahulu sebelum menghapus mata kuliah ini.</em>
            </p>
            <form method="post" style="margin-top: 20px;">
                <input type="hidden" name="token" value="<?= e($_SESSION['token']) ?>">
                <button type="submit" name="hapus" class="bahaya" disabled style="opacity: 0.5; cursor: not-allowed;">Ya, hapus data</button>
                <a href="daftar_mata_kuliah.php" class="tombol tombol-outline">Batal</a>
            </form>
        <?php else: ?>
            <p>Data yang sudah dihapus tidak bisa dikembalikan.</p>
            <form method="post" style="margin-top: 20px;">
                <input type="hidden" name="token" value="<?= e($_SESSION['token']) ?>">
                <button type="submit" name="hapus" class="bahaya">Ya, hapus data</button>
                <a href="daftar_mata_kuliah.php" class="tombol tombol-outline">Batal</a>
            </form>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
