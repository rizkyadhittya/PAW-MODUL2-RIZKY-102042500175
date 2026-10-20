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

// Susun query dengan prepared statement
$sql = "SELECT t.*, k.kode AS kode_mk, k.nama AS nama_mk 
        FROM tugas t 
        JOIN mata_kuliah k ON t.mata_kuliah_id = k.id 
        WHERE 1=1";
$params = [];
$types = '';

if ($cari !== '') {
    $sql .= " AND t.judul LIKE ?";
    $params[] = '%' . $cari . '%';
    $types .= 's';
}

if ($filter_mk > 0) {
    $sql .= " AND t.mata_kuliah_id = ?";
    $params[] = $filter_mk;
    $types .= 'i';
}

$sql .= " ORDER BY t.id DESC";

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
    <title>Tugas - LMS EAD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main>
    <?php require 'navbar.php'; ?>

    <p class="label">PENGELOLAAN DATA</p>
    <h1>Tugas</h1>
    <p>Kelola data penugasan praktikum yang terhubung dengan mata kuliah.</p>

    <?php if ($pesan): ?>
        <p class="pesan"><?= e($pesan) ?></p>
    <?php endif; ?>

    <p>
        <a class="tombol" href="tambah_tugas.php">Tambah tugas</a>
    </p>

    <!-- Form Pencarian dan Filter Mata Kuliah -->
    <form method="get" class="pencarian">
        <input type="text" name="cari" placeholder="Cari judul tugas..." value="<?= e($cari) ?>">
        
        <select name="mata_kuliah_id">
            <option value="">Semua mata kuliah</option>
            <?php foreach ($daftar_mk as $mk): ?>
                <option value="<?= (int)$mk['id'] ?>" <?= ($filter_mk === (int)$mk['id']) ? 'selected' : '' ?>>
                    <?= e($mk['kode']) ?> - <?= e($mk['nama']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Cari</button>
        <a href="daftar_tugas.php">Reset</a>
    </form>

    <p>Menampilkan <?= $total_data ?> data.</p>

    <?php if ($total_data === 0): ?>
        <div class="kosong">
            <p>Data tidak ditemukan.</p>
            <p><a href="daftar_tugas.php" class="tombol tombol-outline">Reset</a></p>
        </div>
    <?php else: ?>
        <div class="tabel-wadah">
            <table class="tabel-data">
                <thead>
                    <tr>
                        <th>Judul tugas</th>
                        <th>Mata kuliah</th>
                        <th>Tenggat</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($tugas = $hasil->fetch_assoc()): ?>
                        <?php
                        $badge_class = 'badge-draf';
                        if ($tugas['status'] === 'Aktif') $badge_class = 'badge-aktif';
                        if ($tugas['status'] === 'Selesai') $badge_class = 'badge-selesai';
                        ?>
                        <tr>
                            <td>
                                <strong><?= e($tugas['judul']) ?></strong>
                            </td>
                            <td>
                                <?= e($tugas['kode_mk']) ?> - <?= e($tugas['nama_mk']) ?>
                            </td>
                            <td>
                                <?= date('d M Y', strtotime($tugas['tenggat'])) ?>
                            </td>
                            <td>
                                <span class="badge <?= $badge_class ?>"><?= e($tugas['status']) ?></span>
                            </td>
                            <td>
                                <div class="aksi-grup">
                                    <a class="btn-aksi" href="detail_tugas.php?id=<?= (int)$tugas['id'] ?>">Detail</a>
                                    <a class="btn-aksi" href="edit_tugas.php?id=<?= (int)$tugas['id'] ?>">Edit</a>
                                    <a class="btn-aksi hapus" href="hapus_tugas.php?id=<?= (int)$tugas['id'] ?>">Hapus</a>
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
