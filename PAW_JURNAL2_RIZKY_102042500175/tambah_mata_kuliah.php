<?php
require 'koneksi.php';

$kode = '';
$nama = '';
$dosen = '';
$semester = '';
$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();

    $kode = trim($_POST['kode'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $dosen = trim($_POST['dosen'] ?? '');
    $semester = trim($_POST['semester'] ?? '');

    // Pemeriksaan isian form
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
        // Cek keunikan kode mata kuliah menggunakan prepared statement
        $cek_stmt = $conn->prepare("SELECT id FROM mata_kuliah WHERE kode = ?");
        $cek_stmt->bind_param('s', $kode);
        $cek_stmt->execute();
        $cek_res = $cek_stmt->get_result();

        if ($cek_res->num_rows > 0) {
            $pesan = 'Kode mata kuliah sudah digunakan.';
        } else {
            // Simpan data dengan prepared statement
            $insert_stmt = $conn->prepare("INSERT INTO mata_kuliah (kode, nama, dosen, semester) VALUES (?, ?, ?, ?)");
            $sem_int = (int)$semester;
            $insert_stmt->bind_param('sssi', $kode, $nama, $dosen, $sem_int);

            if ($insert_stmt->execute()) {
                $_SESSION['pesan'] = 'Mata kuliah berhasil ditambahkan.';
                header('Location: daftar_mata_kuliah.php');
                exit;
            } else {
                $pesan = 'Gagal menyimpan data ke database: ' . $insert_stmt->error;
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
    <title>Tambah Mata Kuliah - LMS EAD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main>
    <?php require 'navbar.php'; ?>

    <p class="label">MATA KULIAH / TAMBAH</p>
    <h1>Tambah mata kuliah</h1>
    <p><a href="daftar_mata_kuliah.php">&larr; Kembali ke daftar mata kuliah</a></p>

    <?php if ($pesan !== ''): ?>
        <div class="kesalahan"><?= e($pesan) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="token" value="<?= e($_SESSION['token']) ?>">

        <label for="kode">Kode mata kuliah</label>
        <input type="text" id="kode" name="kode" maxlength="20" placeholder="Contoh: PAW-101" value="<?= e($kode) ?>">

        <label for="nama">Nama mata kuliah</label>
        <input type="text" id="nama" name="nama" maxlength="100" placeholder="Contoh: Pengembangan Aplikasi Website" value="<?= e($nama) ?>">

        <label for="dosen">Dosen pengampu</label>
        <input type="text" id="dosen" name="dosen" maxlength="100" placeholder="Contoh: Dosen PAW" value="<?= e($dosen) ?>">

        <label for="semester">Semester</label>
        <input type="number" id="semester" name="semester" min="1" max="14" placeholder="Contoh: 3" value="<?= e($semester) ?>">

        <button type="submit" name="simpan">Simpan data</button>
        <a href="daftar_mata_kuliah.php" class="tombol tombol-outline">Batal</a>
    </form>
</main>
</body>
</html>
