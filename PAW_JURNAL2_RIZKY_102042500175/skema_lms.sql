CREATE DATABASE IF NOT EXISTS paw_jurnal2_lms
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE paw_jurnal2_lms;

-- Hapus tabel lama jika ada (urutan child dulu baru parent)
DROP TABLE IF EXISTS tugas;
DROP TABLE IF EXISTS materi;
DROP TABLE IF EXISTS mata_kuliah;

-- 1. Tabel mata_kuliah
CREATE TABLE mata_kuliah (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    dosen VARCHAR(100) NOT NULL,
    semester TINYINT UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabel materi (relasi 1-N ke mata_kuliah, ON DELETE RESTRICT)
CREATE TABLE materi (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mata_kuliah_id INT UNSIGNED NOT NULL,
    judul VARCHAR(150) NOT NULL,
    isi TEXT NOT NULL,
    tautan VARCHAR(255) NOT NULL,
    FOREIGN KEY (mata_kuliah_id)
        REFERENCES mata_kuliah(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabel tugas (relasi 1-N ke mata_kuliah, ON DELETE RESTRICT)
CREATE TABLE tugas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mata_kuliah_id INT UNSIGNED NOT NULL,
    judul VARCHAR(150) NOT NULL,
    instruksi TEXT NOT NULL,
    tenggat DATE NOT NULL,
    status VARCHAR(20) NOT NULL,
    FOREIGN KEY (mata_kuliah_id)
        REFERENCES mata_kuliah(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data awal (masing-masing 2 data)
INSERT INTO mata_kuliah (id, kode, nama, dosen, semester) VALUES
(1, 'ALP-101', 'Algoritma dan Pemrograman', 'Dosen ALP', 1),
(2, 'PAW-101', 'Pengembangan Aplikasi Website', 'Dosen PAW', 3);

INSERT INTO materi (id, mata_kuliah_id, judul, isi, tautan) VALUES
(1, 1, 'Dasar Algoritma dan Flowchart', 'Materi pengantar dasar logika algoritma, notasi pseudocode, dan penyusunan flowchart.', 'https://telkomuniversity.ac.id'),
(2, 2, 'Transaksi database', 'Materi membahas pengelolaan perubahan data pada database aplikasi website.', 'https://www.php.net/manual/en/book.mysqli.php');

INSERT INTO tugas (id, mata_kuliah_id, judul, instruksi, tenggat, status) VALUES
(1, 1, 'Tugas Logika Percabangan', 'Buatlah flowchart dan program percabangan menggunakan bahasa pemrograman C/Python.', '2026-10-30', 'Aktif'),
(2, 2, 'Latihan CRUD', 'Kerjakan latihan CRUD database showroom dan kembangkan menjadi modul LMS.', '2026-11-05', 'Draf');
