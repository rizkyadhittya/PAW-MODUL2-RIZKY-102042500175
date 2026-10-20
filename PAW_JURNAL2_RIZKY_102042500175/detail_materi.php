<?php
require 'koneksi.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    exit('ID materi tidak valid. <a href="daftar_materi.php">Kembali ke daftar</a>');
}

// Ambil data materi dan nama mata kuliahnya dengan prepared statement
$stmt = $conn->prepare("SELECT m.*, k.kode AS kode_mk, k.nama AS nama_mk 
                        FROM materi m 
                        JOIN mata_kuliah k ON m.mata_kuliah_id = k.id 
                        WHERE m.id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$hasil = $stmt->get_result();
$materi = $hasil->fetch_assoc();

if (!$materi) {
    exit('Materi tidak ditemukan. <a href="daftar_materi.php">Kembali ke daftar</a>');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Materi - <?= e($materi['judul']) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main>
    <?php require 'navbar.php'; ?>

    <p class="label">Materi</p>
    <h1><?= e($materi['judul']) ?></h1>

    <div class="kartu-detail">
        <dl>
            <dt>Mata kuliah</dt>
            <dd><?= e($materi['kode_mk']) ?> - <?= e($materi['nama_mk']) ?></dd>

            <dt>Judul materi</dt>
            <dd><?= e($materi['judul']) ?></dd>

            <dt>Isi materi</dt>
            <dd style="white-space: pre-line; line-height: 1.7;"><?= e($materi['isi']) ?></dd>

            <dt>Tautan referensi</dt>
            <dd>
                <a href="<?= e($materi['tautan']) ?>" target="_blank" rel="noopener noreferrer">
                    <?= e($materi['tautan']) ?>
                </a>
            </dd>
        </dl>

        <div style="margin-top: 24px;">
            <a class="tombol" href="edit_materi.php?id=<?= $id ?>">Edit data</a>
            <a class="tombol tombol-outline" href="daftar_materi.php">Kembali</a>
            <a class="tombol bahaya" href="hapus_materi.php?id=<?= $id ?>">Hapus materi</a>
        </div>
    </div>
</main>
</body>
</html>
