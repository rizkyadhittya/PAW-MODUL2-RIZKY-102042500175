<?php
require 'koneksi.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    exit('ID materi tidak valid. <a href="daftar_materi.php">Kembali ke daftar</a>');
}

// Ambil data materi saat ini
$stmt = $conn->prepare("SELECT * FROM materi WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$hasil = $stmt->get_result();
$materi = $hasil->fetch_assoc();

if (!$materi) {
    exit('Materi tidak ditemukan. <a href="daftar_materi.php">Kembali ke daftar</a>');
}

// Ambil daftar mata kuliah untuk opsi dropdown
$res_mk = mysqli_query($conn, "SELECT id, kode, nama FROM mata_kuliah ORDER BY kode ASC");
$daftar_mk = [];
while ($row = mysqli_fetch_assoc($res_mk)) {
    $daftar_mk[] = $row;
}

$mk_id = (int)$materi['mata_kuliah_id'];
$judul = $materi['judul'];
$isi = $materi['isi'];
$tautan = $materi['tautan'];
$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();

    $mk_id = (int) ($_POST['mata_kuliah_id'] ?? 0);
    $judul = trim($_POST['judul'] ?? '');
    $isi = trim($_POST['isi'] ?? '');
    $tautan = trim($_POST['tautan'] ?? '');

    // Pemeriksaan isian
    if ($mk_id <= 0 || $judul === '' || $isi === '' || $tautan === '') {
        $pesan = 'Semua kolom wajib diisi.';
    } elseif (mb_strlen($judul) < 3 || mb_strlen($judul) > 150) {
        $pesan = 'Judul materi harus 3-150 karakter.';
    } elseif (mb_strlen($isi) < 3 || mb_strlen($isi) > 2000) {
        $pesan = 'Isi materi harus 3-2000 karakter.';
    } elseif (mb_strlen($tautan) > 255 || !preg_match('/^https?:\/\//i', $tautan) || !filter_var($tautan, FILTER_VALIDATE_URL)) {
        // ERROR J01
        $pesan = 'Gunakan tautan http/https yang valid.';
    } else {
        // Cek validitas mata kuliah
        $cek_mk = $conn->prepare("SELECT id FROM mata_kuliah WHERE id = ?");
        $cek_mk->bind_param('i', $mk_id);
        $cek_mk->execute();
        if ($cek_mk->get_result()->num_rows === 0) {
            $pesan = 'Mata kuliah yang dipilih tidak valid.';
        } elseif ($mk_id === (int)$materi['mata_kuliah_id'] && $judul === $materi['judul'] && $isi === $materi['isi'] && $tautan === $materi['tautan']) {
            // Pengujian 5: Simpan edit tanpa perubahan
            $pesan = 'Tidak ada perubahan data.';
        } else {
            // Update data dengan prepared statement
            $update_stmt = $conn->prepare("UPDATE materi SET mata_kuliah_id = ?, judul = ?, isi = ?, tautan = ? WHERE id = ?");
            $update_stmt->bind_param('isssi', $mk_id, $judul, $isi, $tautan, $id);

            if ($update_stmt->execute()) {
                $_SESSION['pesan'] = 'Perubahan data berhasil disimpan.';
                header('Location: daftar_materi.php');
                exit;
            } else {
                $pesan = 'Gagal memperbarui materi: ' . $update_stmt->error;
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
    <title>Edit Materi - LMS EAD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main>
    <?php require 'navbar.php'; ?>

    <p class="label">MATERI / EDIT</p>
    <h1>Edit materi</h1>
    <p><a href="daftar_materi.php">&larr; Kembali ke daftar</a></p>

    <?php if ($pesan !== ''): ?>
        <div class="kesalahan"><?= e($pesan) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="token" value="<?= e($_SESSION['token']) ?>">

        <label for="mata_kuliah">Mata kuliah</label>
        <select id="mata_kuliah" name="mata_kuliah_id">
            <option value="">Pilih Mata kuliah</option>
            <?php foreach ($daftar_mk as $mk): ?>
                <option value="<?= (int)$mk['id'] ?>" <?= ($mk_id === (int)$mk['id']) ? 'selected' : '' ?>>
                    <?= e($mk['kode']) ?> - <?= e($mk['nama']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="judul">Judul materi</label>
        <input type="text" id="judul" name="judul" maxlength="150" value="<?= e($judul) ?>">

        <label for="isi">Isi materi</label>
        <textarea id="isi" name="isi" maxlength="2000"><?= e($isi) ?></textarea>

        <label for="tautan">Tautan referensi</label>
        <input type="text" id="tautan" name="tautan" maxlength="255" value="<?= e($tautan) ?>">

        <button type="submit" name="simpan">Simpan perubahan</button>
        <a href="daftar_materi.php" class="tombol tombol-outline">Batal</a>
    </form>
</main>
</body>
</html>
