<?php
require 'koneksi.php';

// Ambil daftar mata kuliah untuk opsi dropdown
$res_mk = mysqli_query($conn, "SELECT id, kode, nama FROM mata_kuliah ORDER BY kode ASC");
$daftar_mk = [];
while ($row = mysqli_fetch_assoc($res_mk)) {
    $daftar_mk[] = $row;
}

$mk_id = (int) ($_POST['mata_kuliah_id'] ?? ($_GET['mata_kuliah_id'] ?? 0));
$judul = trim($_POST['judul'] ?? '');
$instruksi = trim($_POST['instruksi'] ?? '');
$tenggat = trim($_POST['tenggat'] ?? '');
$status = trim($_POST['status'] ?? 'Draf');
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
        } else {
            // Simpan tugas dengan prepared statement
            $insert_stmt = $conn->prepare("INSERT INTO tugas (mata_kuliah_id, judul, instruksi, tenggat, status) VALUES (?, ?, ?, ?, ?)");
            $insert_stmt->bind_param('issss', $mk_id, $judul, $instruksi, $tenggat, $status);

            if ($insert_stmt->execute()) {
                $_SESSION['pesan'] = 'Tugas berhasil ditambahkan.';
                header('Location: daftar_tugas.php');
                exit;
            } else {
                $pesan = 'Gagal menyimpan data tugas: ' . $insert_stmt->error;
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
    <title>Tambah Tugas - LMS EAD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main>
    <?php require 'navbar.php'; ?>

    <p class="label">TUGAS / TAMBAH</p>
    <h1>Tambah tugas</h1>
    <p><a href="daftar_tugas.php">&larr; Kembali ke daftar tugas</a></p>

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
        <input type="text" id="judul" name="judul" maxlength="150" placeholder="Contoh: Latihan CRUD" value="<?= e($judul) ?>">

        <label for="instruksi">Instruksi tugas</label>
        <textarea id="instruksi" name="instruksi" maxlength="2000" placeholder="Jelaskan instruksi pengerjaan tugas..."><?= e($instruksi) ?></textarea>

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

        <button type="submit" name="simpan">Simpan data</button>
        <a href="daftar_tugas.php" class="tombol tombol-outline">Batal</a>
    </form>
</main>
</body>
</html>
