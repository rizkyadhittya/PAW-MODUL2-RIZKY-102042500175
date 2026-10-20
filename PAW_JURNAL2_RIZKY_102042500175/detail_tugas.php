<?php
require 'koneksi.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    exit('ID tugas tidak valid. <a href="daftar_tugas.php">Kembali ke daftar</a>');
}

// Ambil data tugas dan nama mata kuliahnya dengan prepared statement
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

$badge_class = 'badge-draf';
if ($tugas['status'] === 'Aktif') $badge_class = 'badge-aktif';
if ($tugas['status'] === 'Selesai') $badge_class = 'badge-selesai';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Tugas - <?= e($tugas['judul']) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main>
    <?php require 'navbar.php'; ?>

    <p class="label">Tugas</p>
    <h1><?= e($tugas['judul']) ?></h1>

    <div class="kartu-detail">
        <dl>
            <dt>Mata kuliah</dt>
            <dd><?= e($tugas['kode_mk']) ?> - <?= e($tugas['nama_mk']) ?></dd>

            <dt>Judul tugas</dt>
            <dd><?= e($tugas['judul']) ?></dd>

            <dt>Instruksi tugas</dt>
            <dd style="white-space: pre-line; line-height: 1.7;"><?= e($tugas['instruksi']) ?></dd>

            <dt>Tenggat pengumpulan</dt>
            <dd><?= date('d F Y', strtotime($tugas['tenggat'])) ?> (<?= e($tugas['tenggat']) ?>)</dd>

            <dt>Status</dt>
            <dd><span class="badge <?= $badge_class ?>"><?= e($tugas['status']) ?></span></dd>
        </dl>

        <div style="margin-top: 24px;">
            <a class="tombol" href="edit_tugas.php?id=<?= $id ?>">Edit data</a>
            <a class="tombol tombol-outline" href="daftar_tugas.php">Kembali</a>
            <a class="tombol bahaya" href="hapus_tugas.php?id=<?= $id ?>">Hapus tugas</a>
        </div>
    </div>
</main>
</body>
</html>
