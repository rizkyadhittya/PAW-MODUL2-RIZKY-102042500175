<?php
require 'koneksi.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    exit('ID mata kuliah tidak valid. <a href="daftar_mata_kuliah.php">Kembali ke daftar</a>');
}

// Ambil data mata kuliah saat ini
$stmt = $conn->prepare("SELECT * FROM mata_kuliah WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$hasil = $stmt->get_result();
$mk = $hasil->fetch_assoc();

if (!$mk) {
    exit('Mata kuliah tidak ditemukan. <a href="daftar_mata_kuliah.php">Kembali ke daftar</a>');
}

$kode = $mk['kode'];
$nama = $mk['nama'];
$dosen = $mk['dosen'];
$semester = $mk['semester'];
$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();

    $kode = trim($_POST['kode'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $dosen = trim($_POST['dosen'] ?? '');
    $semester = trim($_POST['semester'] ?? '');

    // Pemeriksaan isian
    if ($kode === '' || $nama === '' || $dosen === '' || $semester === '') {
        $pesan = 'Semua kolom wajib diisi.';
    } elseif (!preg_match('/^[A-Z0-9-]{3,20}$/', $kode)) {
        $pesan = 'Kode mata kuliah harus 3-20 karakter: huruf kapital, angka, atau tanda -.';
    } elseif (mb_strlen($nama) < 3 || mb_strlen($nama) > 100) {
        $pesan = 'Nama mata kuliah harus 3-100 karakter.';
    } elseif (mb_strlen($dosen) < 3 || mb_strlen($dosen) > 100) {
        $pesan = 'Nama dosen pengampu harus 3-100 karakter.';
    } elseif (!filter_var($semester, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 14]])) {
        $pesan = 'Semester harus berupa bilangan bulat antara 1 sampai 14.';
    } else {
        // Cek keunikan kode mata kuliah (kecuali ID ini sendiri)
        $cek_stmt = $conn->prepare("SELECT id FROM mata_kuliah WHERE kode = ? AND id <> ?");
        $cek_stmt->bind_param('si', $kode, $id);
        $cek_stmt->execute();
        $cek_res = $cek_stmt->get_result();

        if ($cek_res->num_rows > 0) {
            $pesan = 'Kode mata kuliah sudah digunakan.';
        } elseif ($kode === $mk['kode'] && $nama === $mk['nama'] && $dosen === $mk['dosen'] && (int)$semester === (int)$mk['semester']) {
            // Pengujian 5: Simpan edit tanpa perubahan -> Beri pesan yang wajar
            $pesan = 'Tidak ada perubahan data.';
        } else {
            // Update data dengan prepared statement
            $update_stmt = $conn->prepare("UPDATE mata_kuliah SET kode = ?, nama = ?, dosen = ?, semester = ? WHERE id = ?");
            $sem_int = (int)$semester;
            $update_stmt->bind_param('sssii', $kode, $nama, $dosen, $sem_int, $id);

            if ($update_stmt->execute()) {
                $_SESSION['pesan'] = 'Perubahan data berhasil disimpan.';
                header('Location: daftar_mata_kuliah.php');
                exit;
            } else {
                $pesan = 'Gagal memperbarui data: ' . $update_stmt->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Mata Kuliah - LMS EAD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main>
    <?php require 'navbar.php'; ?>

    <p class="label">MATA KULIAH / EDIT</p>
    <h1>Edit mata kuliah</h1>
    <p><a href="daftar_mata_kuliah.php">&larr; Kembali ke daftar</a></p>

    <?php if ($pesan !== ''): ?>
        <div class="kesalahan"><?= e($pesan) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="token" value="<?= e($_SESSION['token']) ?>">

        <label for="kode">Kode mata kuliah</label>
        <input type="text" id="kode" name="kode" maxlength="20" value="<?= e($kode) ?>">

        <label for="nama">Nama mata kuliah</label>
        <input type="text" id="nama" name="nama" maxlength="100" value="<?= e($nama) ?>">

        <label for="dosen">Dosen pengampu</label>
        <input type="text" id="dosen" name="dosen" maxlength="100" value="<?= e($dosen) ?>">

        <label for="semester">Semester</label>
        <input type="number" id="semester" name="semester" min="1" max="14" value="<?= e($semester) ?>">

        <button type="submit" name="simpan">Simpan perubahan</button>
        <a href="daftar_mata_kuliah.php" class="tombol tombol-outline">Batal</a>
    </form>
</main>
</body>
</html>
