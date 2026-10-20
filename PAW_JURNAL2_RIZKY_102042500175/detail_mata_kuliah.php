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

// Ambil materi terkait mata kuliah ini
$stmt_mat = $conn->prepare("SELECT * FROM materi WHERE mata_kuliah_id = ? ORDER BY id ASC");
$stmt_mat->bind_param('i', $id);
$stmt_mat->execute();
$hasil_mat = $stmt_mat->get_result();

// Ambil tugas terkait mata kuliah ini
$stmt_tug = $conn->prepare("SELECT * FROM tugas WHERE mata_kuliah_id = ? ORDER BY id ASC");
$stmt_tug->bind_param('i', $id);
$stmt_tug->execute();
$hasil_tug = $stmt_tug->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Mata Kuliah - <?= e($mk['nama']) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main>
    <?php require 'navbar.php'; ?>

    <p class="label">MATA KULIAH / DETAIL</p>
    <h1><?= e($mk['nama']) ?></h1>
    <p class="label"><?= e($mk['kode']) ?></p>

    <div class="kartu-detail">
        <dl>
            <dt>Kode Mata Kuliah</dt>
            <dd><?= e($mk['kode']) ?></dd>

            <dt>Nama Mata Kuliah</dt>
            <dd><?= e($mk['nama']) ?></dd>

            <dt>Dosen Pengampu</dt>
            <dd><?= e($mk['dosen']) ?></dd>

            <dt>Semester</dt>
            <dd>Semester <?= (int)$mk['semester'] ?></dd>
        </dl>

        <div style="margin-top: 20px;">
            <a class="tombol" href="edit_mata_kuliah.php?id=<?= $id ?>">Edit data</a>
            <a class="tombol tombol-outline" href="daftar_mata_kuliah.php">Kembali</a>
            <a class="tombol bahaya" href="hapus_mata_kuliah.php?id=<?= $id ?>">Hapus mata kuliah</a>
        </div>
    </div>

    <!-- Materi Terkait -->
    <div class="kartu-detail" style="margin-top: 24px;">
        <h2>Materi Pembelajaran (<?= $hasil_mat->num_rows ?>)</h2>
        <?php if ($hasil_mat->num_rows > 0): ?>
            <ul>
                <?php while ($mat = $hasil_mat->fetch_assoc()): ?>
                    <li style="margin-bottom: 8px;">
                        <a href="detail_materi.php?id=<?= $mat['id'] ?>">
                            <strong><?= e($mat['judul']) ?></strong>
                        </a>
                        <span style="font-size: 13px; color: #64748b;"> &bull; <a href="<?= e($mat['tautan']) ?>" target="_blank" rel="noopener noreferrer">Tautan</a></span>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p style="color: #64748b;">Belum ada materi untuk mata kuliah ini.</p>
        <?php endif; ?>
        <p><a href="tambah_materi.php?mata_kuliah_id=<?= $id ?>" class="btn-aksi">+ Tambah Materi</a></p>
    </div>

    <!-- Tugas Terkait -->
    <div class="kartu-detail" style="margin-top: 24px;">
        <h2>Tugas Praktikum (<?= $hasil_tug->num_rows ?>)</h2>
        <?php if ($hasil_tug->num_rows > 0): ?>
            <ul>
                <?php while ($tug = $hasil_tug->fetch_assoc()): ?>
                    <li style="margin-bottom: 8px;">
                        <a href="detail_tugas.php?id=<?= $tug['id'] ?>">
                            <strong><?= e($tug['judul']) ?></strong>
                        </a>
                        <span style="font-size: 13px; color: #64748b;"> (Tenggat: <?= e($tug['tenggat']) ?> &bull; Status: <?= e($tug['status']) ?>)</span>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p style="color: #64748b;">Belum ada tugas untuk mata kuliah ini.</p>
        <?php endif; ?>
        <p><a href="tambah_tugas.php?mata_kuliah_id=<?= $id ?>" class="btn-aksi">+ Tambah Tugas</a></p>
    </div>
</main>
</body>
</html>
