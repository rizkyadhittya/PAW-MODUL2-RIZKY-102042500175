<?php
require 'koneksi.php';

$cari = trim($_GET['cari'] ?? '');
$filter_mk = (int) ($_GET['mata_kuliah_id'] ?? 0);

// Ambil semua daftar mata kuliah untuk opsi filter
$res_mk = mysqli_query($conn, "SELECT id, kode, nama FROM mata_kuliah ORDER BY kode ASC");
$daftar_mk = [];
while ($row = mysqli_fetch_assoc($res_mk)) {
    $daftar_mk[] = $row;
}

// Susun query dengan prepared statement sesuai filter dan kata kunci
$sql = "SELECT m.*, k.kode AS kode_mk, k.nama AS nama_mk 
        FROM materi m 
        JOIN mata_kuliah k ON m.mata_kuliah_id = k.id 
        WHERE 1=1";
$params = [];
$types = '';

if ($cari !== '') {
    $sql .= " AND m.judul LIKE ?";
    $params[] = '%' . $cari . '%';
    $types .= 's';
}

if ($filter_mk > 0) {
    $sql .= " AND m.mata_kuliah_id = ?";
    $params[] = $filter_mk;
    $types .= 'i';
}

$sql .= " ORDER BY m.id DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$hasil = $stmt->get_result();
$total_data = $hasil->num_rows;

$pesan = $_SESSION['pesan'] ?? ($_GET['pesan'] ?? '');
unset($_SESSION['pesan']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materi - LMS EAD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main>
    <?php require 'navbar.php'; ?>

    <p class="label">PENGELOLAAN DATA</p>
    <h1>Materi</h1>
    <p>Kelola data materi pembelajaran yang terhubung dengan mata kuliah.</p>

    <?php if ($pesan): ?>
        <p class="pesan"><?= e($pesan) ?></p>
    <?php endif; ?>

    <p>
        <a class="tombol" href="tambah_materi.php">Tambah materi</a>
    </p>

    <!-- Form Pencarian dan Filter Mata Kuliah (Sesuai Gambar 7 & 8 Modul) -->
    <form method="get" class="pencarian">
        <input type="text" name="cari" placeholder="Cari judul materi..." value="<?= e($cari) ?>">
        
        <select name="mata_kuliah_id">
            <option value="">Semua mata kuliah</option>
            <?php foreach ($daftar_mk as $mk): ?>
                <option value="<?= (int)$mk['id'] ?>" <?= ($filter_mk === (int)$mk['id']) ? 'selected' : '' ?>>
                    <?= e($mk['kode']) ?> - <?= e($mk['nama']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Cari</button>
        <a href="daftar_materi.php">Reset</a>
    </form>

    <p>Menampilkan <?= $total_data ?> data.</p>

    <?php if ($total_data === 0): ?>
        <div class="kosong">
            <p>Data tidak ditemukan.</p>
            <p><a href="daftar_materi.php" class="tombol tombol-outline">Reset</a></p>
        </div>
    <?php else: ?>
        <div class="tabel-wadah">
            <table class="tabel-data">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Mata kuliah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($materi = $hasil->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?= e($materi['judul']) ?></strong>
                            </td>
                            <td>
                                <?= e($materi['kode_mk']) ?> - <?= e($materi['nama_mk']) ?>
                            </td>
                            <td>
                                <div class="aksi-grup">
                                    <a class="btn-aksi" href="detail_materi.php?id=<?= (int)$materi['id'] ?>">Detail</a>
                                    <a class="btn-aksi" href="edit_materi.php?id=<?= (int)$materi['id'] ?>">Edit</a>
                                    <a class="btn-aksi hapus" href="hapus_materi.php?id=<?= (int)$materi['id'] ?>">Hapus</a>
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
