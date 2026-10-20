<?php
require 'koneksi.php';

$hasil_mk = mysqli_query($conn, 'SELECT COUNT(*) AS jumlah FROM mata_kuliah');
$hasil_mat = mysqli_query($conn, 'SELECT COUNT(*) AS jumlah FROM materi');
$hasil_tug = mysqli_query($conn, 'SELECT COUNT(*) AS jumlah FROM tugas');

$row_mk = mysqli_fetch_assoc($hasil_mk);
$row_mat = mysqli_fetch_assoc($hasil_mat);
$row_tug = mysqli_fetch_assoc($hasil_tug);

echo 'Koneksi berhasil. Jumlah mata kuliah: ' . $row_mk['jumlah'] . ', materi: ' . $row_mat['jumlah'] . ', tugas: ' . $row_tug['jumlah'];
