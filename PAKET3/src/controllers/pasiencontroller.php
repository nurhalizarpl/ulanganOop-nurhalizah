<?php
namespace App\Controllers;

use App\Models\Pasien;

class PasienController
{
    private $pasien;
    private $folderFoto;

    public function __construct($koneksi)
    {
        $this->pasien = new Pasien($koneksi);

        // Lokasi folder foto berdasarkan lokasi file controller
        $this->folderFoto = dirname(__DIR__, 2) . '/assets/images/pasien/';

        if (!is_dir($this->folderFoto)) {
            mkdir($this->folderFoto, 0777, true);
        }
    }

    // Menampilkan seluruh data pasien
    public function index()
    {
        $dataPasien = $this->pasien->tampil();
        require 'views/Pasien/index.php';
    }

    // Form tambah pasien
    public function create()
    {
        require 'views/Pasien/create.php';
    }

    // Menyimpan data pasien
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die("Akses tidak diizinkan.");
        }

        if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            die("Foto pasien wajib diunggah.");
        }

        $file = $_FILES['foto'];

        $ekstensi = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $ekstensiDiizinkan = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($ekstensi, $ekstensiDiizinkan)) {
            die("Format foto harus JPG, JPEG, PNG, atau WEBP.");
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            die("Ukuran foto maksimal 2 MB.");
        }

        $mime = mime_content_type($file['tmp_name']);

        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'])) {
            die("File yang diunggah bukan gambar yang valid.");
        }

        $namaFoto = uniqid('pasien_', true) . '.' . $ekstensi;
        $lokasiFoto = $this->folderFoto . $namaFoto;

        if (!move_uploaded_file($file['tmp_name'], $lokasiFoto)) {
            die("Gagal mengunggah foto. Periksa folder penyimpanan.");
        }

        $data = [
            'namapasien' => trim($_POST['namapasien']),
            'username' => trim($_POST['username']),
            'password' => $_POST['password'],
            'usia' => (int) $_POST['usia'],
            'alergi' => trim($_POST['alergi']),
            'jeniskelamin' => $_POST['jeniskelamin'],
            'alamat' => trim($_POST['alamat']),
            'nohp' => trim($_POST['nohp']),
            'foto' => $namaFoto
        ];

        try {
            $hasil = $this->pasien->tambah($data);

            if ($hasil) {
                header("Location: index.php?module=pasien&action=index");
                exit;
            }

            if (file_exists($lokasiFoto)) {
                unlink($lokasiFoto);
            }

            die("Gagal menyimpan data pasien.");

        } catch (\Throwable $e) {
            if (file_exists($lokasiFoto)) {
                unlink($lokasiFoto);
            }

            die("Terjadi kesalahan: " . $e->getMessage());
        }
    }

    // Form edit pasien
    public function edit($id)
    {
        $pasien = $this->pasien->ambil($id);

        if (!$pasien) {
            die("Data pasien tidak ditemukan.");
        }

        require 'views/Pasien/edit.php';
    }

    // Memperbarui data pasien
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die("Akses tidak diizinkan.");
        }

        $pasienLama = $this->pasien->ambil($id);

        if (!$pasienLama) {
            die("Data pasien tidak ditemukan.");
        }

        $namaFoto = $pasienLama['foto'];
        $fotoBaru = false;
        $lokasiFotoBaru = null;

        if (
            isset($_FILES['foto']) &&
            $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE
        ) {
            if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                die("Gagal mengunggah foto.");
            }

            $file = $_FILES['foto'];
            $ekstensi = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($ekstensi, ['jpg', 'jpeg', 'png', 'webp'])) {
                die("Format foto harus JPG, JPEG, PNG, atau WEBP.");
            }

            if ($file['size'] > 2 * 1024 * 1024) {
                die("Ukuran foto maksimal 2 MB.");
            }

            $mime = mime_content_type($file['tmp_name']);

            if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'])) {
                die("File yang diunggah bukan gambar yang valid.");
            }

            $namaFoto = uniqid('pasien_', true) . '.' . $ekstensi;
            $lokasiFotoBaru = $this->folderFoto . $namaFoto;

            if (!move_uploaded_file($file['tmp_name'], $lokasiFotoBaru)) {
                die("Gagal mengunggah foto baru.");
            }

            $fotoBaru = true;
        }

        $data = [
            'namapasien' => trim($_POST['namapasien']),
            'username' => trim($_POST['username']),
            'password' => $_POST['password'],
            'usia' => (int) $_POST['usia'],
            'alergi' => trim($_POST['alergi']),
            'jeniskelamin' => $_POST['jeniskelamin'],
            'alamat' => trim($_POST['alamat']),
            'nohp' => trim($_POST['nohp']),
            'foto' => $namaFoto
        ];

        try {
            $hasil = $this->pasien->ubah($id, $data);

            if ($hasil) {
                if ($fotoBaru) {
                    $fotoLama = $this->folderFoto . $pasienLama['foto'];

                    if (file_exists($fotoLama)) {
                        unlink($fotoLama);
                    }
                }

                header("Location: index.php?module=pasien&action=index");
                exit;
            }

            if ($fotoBaru && file_exists($lokasiFotoBaru)) {
                unlink($lokasiFotoBaru);
            }

            die("Gagal memperbarui data pasien.");

        } catch (\Throwable $e) {
            if ($fotoBaru && file_exists($lokasiFotoBaru)) {
                unlink($lokasiFotoBaru);
            }

            die("Terjadi kesalahan: " . $e->getMessage());
        }
    }

    // Detail pasien
    public function show($id)
    {
        $pasien = $this->pasien->ambil($id);

        if (!$pasien) {
            die("Data pasien tidak ditemukan.");
        }

        require 'views/Pasien/show.php';
    }

    // Menghapus pasien
    public function delete($id)
    {
        $pasien = $this->pasien->ambil($id);

        if (!$pasien) {
            die("Data pasien tidak ditemukan.");
        }

        try {
            $hasil = $this->pasien->hapus($id);

            if ($hasil) {
                $lokasiFoto = $this->folderFoto . $pasien['foto'];

                if (file_exists($lokasiFoto)) {
                    unlink($lokasiFoto);
                }

                header("Location: index.php?module=pasien&action=index");
                exit;
            }

            die("Gagal menghapus data pasien.");

        } catch (\mysqli_sql_exception $e) {
            die("Pasien tidak dapat dihapus karena masih memiliki data kunjungan. Hapus data kunjungan terlebih dahulu.");
        }
    }
}
