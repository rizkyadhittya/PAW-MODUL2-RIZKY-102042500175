<?php
require 'koneksi.php';

$cari = trim($_GET['cari'] ?? '');

if ($cari !== '') {
    $stmt = $conn->prepare("SELECT * FROM mata_kuliah WHERE kode LIKE ? OR nama LIKE ? ORDER BY kode ASC");
    $param_cari = '%' . $cari . '%';
    $stmt->bind_param('ss', $param_cari, $param_cari);
    $stmt->execute();
    $hasil = $stmt->get_result();
} else {
    $stmt = $conn->prepare("SELECT * FROM mata_kuliah ORDER BY kode ASC");
    $stmt->execute();
    $hasil = $stmt->get_result();
}

$total_data = $hasil->num_rows;
$pesan = $_SESSION['pesan'] ?? ($_GET['pesan'] ?? '');
unset($_SESSION['pesan']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mata Kuliah - LMS EAD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main>
    <?php require 'navbar.php'; ?>

    <p class="label">PENGELOLAAN DATA</p>
    <h1>Mata Kuliah</h1>
    <p>Kelola data mata kuliah yang terdaftar di sistem LMS.</p>

    <?php if ($pesan): ?>
        <p class="pesan"><?= e($pesan) ?></p>
    <?php endif; ?>

    <p>
        <a class="tombol" href="tambah_mata_kuliah.php">Tambah mata kuliah</a>
    </p>

    <!-- Form Pencarian -->
    <form method="get" class="pencarian">
        <input type="text" name="cari" placeholder="Cari kode atau nama mata kuliah..." value="<?= e($cari) ?>">
        <button type="submit">Cari</button>
        <a href="daftar_mata_kuliah.php">Reset</a>
    </form>

    <p>Menampilkan <?= $total_data ?> data.</p>

    <?php if ($total_data === 0): ?>
        <div class="kosong">
            <p>Data tidak ditemukan. Coba kata kunci lain.</p>
            <p><a href="daftar_mata_kuliah.php" class="tombol tombol-outline">Reset Pencarian</a></p>
        </div>
    <?php else: ?>
        <div class="tabel-wadah">
            <table class="tabel-data">
                <thead>
                    <tr>
                        <th>Mata kuliah</th>
                        <th>Dosen / semester</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($mk = $hasil->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?= e($mk['kode']) ?></strong> - <?= e($mk['nama']) ?>
                            </td>
                            <td>
                                <?= e($mk['dosen']) ?> / Semester <?= (int)$mk['semester'] ?>
                            </td>
                            <td>
                                <div class="aksi-grup">
                                    <a class="btn-aksi" href="detail_mata_kuliah.php?id=<?= (int)$mk['id'] ?>">Detail</a>
                                    <a class="btn-aksi" href="edit_mata_kuliah.php?id=<?= (int)$mk['id'] ?>">Edit</a>
                                    <a class="btn-aksi hapus" href="hapus_mata_kuliah.php?id=<?= (int)$mk['id'] ?>">Hapus</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>
</body>
</html>
