<?php
require 'koneksi.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    exit('ID tugas tidak valid. <a href="daftar_tugas.php">Kembali ke daftar</a>');
}

// Ambil data tugas saat ini
$stmt = $conn->prepare("SELECT * FROM tugas WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$hasil = $stmt->get_result();
$tugas = $hasil->fetch_assoc();

if (!$tugas) {
    exit('Tugas tidak ditemukan. <a href="daftar_tugas.php">Kembali ke daftar</a>');
}

// Ambil daftar mata kuliah untuk opsi dropdown
$res_mk = mysqli_query($conn, "SELECT id, kode, nama FROM mata_kuliah ORDER BY kode ASC");
$daftar_mk = [];
while ($row = mysqli_fetch_assoc($res_mk)) {
    $daftar_mk[] = $row;
}

$mk_id = (int)$tugas['mata_kuliah_id'];
$judul = $tugas['judul'];
$instruksi = $tugas['instruksi'];
$tenggat = $tugas['tenggat'];
$status = $tugas['status'];
$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();

    $mk_id = (int) ($_POST['mata_kuliah_id'] ?? 0);
    $judul = trim($_POST['judul'] ?? '');
    $instruksi = trim($_POST['instruksi'] ?? '');
    $tenggat = trim($_POST['tenggat'] ?? '');
    $status = trim($_POST['status'] ?? '');

    // Pemeriksaan isian
    $tenggat_valid = false;
    if ($tenggat !== '') {
        $tenggat_parts = explode('-', $tenggat);
        if (count($tenggat_parts) === 3 && checkdate((int)$tenggat_parts[1], (int)$tenggat_parts[2], (int)$tenggat_parts[0])) {
            $tenggat_valid = true;
        }
    }

    if ($mk_id <= 0 || $judul === '' || $instruksi === '' || $tenggat === '' || $status === '') {
        $pesan = 'Semua kolom wajib diisi.';
    } elseif (mb_strlen($judul) < 3 || mb_strlen($judul) > 150) {
        $pesan = 'Judul tugas harus 3-150 karakter.';
    } elseif (mb_strlen($instruksi) < 3 || mb_strlen($instruksi) > 2000) {
        $pesan = 'Instruksi tugas harus 3-2000 karakter.';
    } elseif (!$tenggat_valid) {
        $pesan = 'Tenggat harus berupa tanggal yang valid.';
    } elseif (!in_array($status, ['Draf', 'Aktif', 'Selesai'], true)) {
        $pesan = 'Status harus berupa Draf, Aktif, atau Selesai.';
    } else {
        // Cek validitas mata kuliah
        $cek_mk = $conn->prepare("SELECT id FROM mata_kuliah WHERE id = ?");
        $cek_mk->bind_param('i', $mk_id);
        $cek_mk->execute();
        if ($cek_mk->get_result()->num_rows === 0) {
            $pesan = 'Mata kuliah yang dipilih tidak valid.';
        } elseif ($mk_id === (int)$tugas['mata_kuliah_id'] && $judul === $tugas['judul'] && $instruksi === $tugas['instruksi'] && $tenggat === $tugas['tenggat'] && $status === $tugas['status']) {
            // Pengujian 5: Simpan edit tanpa perubahan
            $pesan = 'Tidak ada perubahan data.';
        } else {
            // Update data tugas dengan prepared statement
            $update_stmt = $conn->prepare("UPDATE tugas SET mata_kuliah_id = ?, judul = ?, instruksi = ?, tenggat = ?, status = ? WHERE id = ?");
            $update_stmt->bind_param('issssi', $mk_id, $judul, $instruksi, $tenggat, $status, $id);

            if ($update_stmt->execute()) {
                $_SESSION['pesan'] = 'Perubahan data berhasil disimpan.';
                header('Location: daftar_tugas.php');
                exit;
            } else {
                $pesan = 'Gagal memperbarui data tugas: ' . $update_stmt->error;
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
    <title>Edit Tugas - LMS EAD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main>
    <?php require 'navbar.php'; ?>

    <p class="label">TUGAS / EDIT</p>
    <h1>Edit tugas</h1>
    <p><a href="daftar_tugas.php">&larr; Kembali ke daftar</a></p>

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

        <label for="judul">Judul tugas</label>
        <input type="text" id="judul" name="judul" maxlength="150" value="<?= e($judul) ?>">

        <label for="instruksi">Instruksi tugas</label>
        <textarea id="instruksi" name="instruksi" maxlength="2000"><?= e($instruksi) ?></textarea>

        <div style="display: flex; gap: 20px; flex-wrap: wrap; max-width: 600px;">
            <div style="flex: 1 1 200px;">
                <label for="tenggat">Tenggat</label>
                <input type="date" id="tenggat" name="tenggat" value="<?= e($tenggat) ?>">
            </div>
            <div style="flex: 1 1 200px;">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="Draf" <?= ($status === 'Draf') ? 'selected' : '' ?>>Draf</option>
                    <option value="Aktif" <?= ($status === 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                    <option value="Selesai" <?= ($status === 'Selesai') ? 'selected' : '' ?>>Selesai</option>
                </select>
            </div>
        </div>

        <button type="submit" name="simpan">Simpan perubahan</button>
        <a href="daftar_tugas.php" class="tombol tombol-outline">Batal</a>
    </form>
</main>
</body>
</html>
