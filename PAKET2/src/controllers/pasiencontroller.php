<?php
namespace App\Controllers;

use App\Models\Pasien;

class PasienController
{
    private $pasien;
    private $projectRoot;

    public function __construct($koneksi)
    {
        $this->pasien = new Pasien($koneksi);
        $this->projectRoot = dirname(__DIR__, 2);
    }

    // Menentukan folder penyimpanan foto
    private function folderFoto()
    {
        $folder = $this->projectRoot . '/assets/pasien/';

        if (!is_dir($folder)) {
            if (!mkdir($folder, 0777, true) && !is_dir($folder)) {
                throw new \RuntimeException(
                    'Folder foto pasien gagal dibuat.'
                );
            }
        }

        if (!is_writable($folder)) {
            throw new \RuntimeException(
                'Folder foto pasien tidak dapat ditulis.'
            );
        }

        return $folder;
    }

    // Upload dan validasi foto pasien
    private function uploadFoto($file)
    {
        if (!isset($file['error']) ||
            $file['error'] === UPLOAD_ERR_NO_FILE) {
            throw new \RuntimeException(
                'Foto pasien wajib diunggah.'
            );
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException(
                'Terjadi kesalahan saat mengunggah foto.'
            );
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new \RuntimeException(
                'File upload tidak valid.'
            );
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            throw new \RuntimeException(
                'Ukuran foto maksimal 2 MB.'
            );
        }

        $info = @getimagesize($file['tmp_name']);

        if ($info === false) {
            throw new \RuntimeException(
                'File yang diunggah bukan gambar.'
            );
        }

        $format = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        if (!isset($format[$info['mime']])) {
            throw new \RuntimeException(
                'Format foto harus JPG, JPEG, PNG, atau WEBP.'
            );
        }

        $ekstensi = $format[$info['mime']];

        $namaFoto = 'pasien_' .
            bin2hex(random_bytes(8)) . '.' . $ekstensi;

        $tujuan = $this->folderFoto() . $namaFoto;

        if (!move_uploaded_file($file['tmp_name'], $tujuan)) {
            throw new \RuntimeException(
                'Gagal mengunggah foto pasien.'
            );
        }

        return $namaFoto;
    }

    // Menghapus foto pasien
    private function hapusFoto($namaFoto)
    {
        if (empty($namaFoto)) {
            return;
        }

        $lokasi = $this->folderFoto() . basename($namaFoto);

        if (is_file($lokasi)) {
            if (!unlink($lokasi)) {
                throw new \RuntimeException(
                    'Foto pasien gagal dihapus.'
                );
            }
        }
    }

    // Menampilkan seluruh data pasien
    public function index()
    {
        $dataPasien = $this->pasien->tampil();

        require $this->projectRoot . '/views/Pasien/index.php';
    }

    // Menampilkan formulir tambah pasien
    public function create()
    {
        require $this->projectRoot . '/views/Pasien/create.php';
    }

    // Menyimpan data pasien
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?module=pasien');
            exit;
        }

        $foto = '';

        try {
            $foto = $this->uploadFoto($_FILES['foto'] ?? []);

            $data = [
                'namapasien' => trim($_POST['namapasien'] ?? ''),
                'username' => trim($_POST['username'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'usia' => (int) ($_POST['usia'] ?? 0),
                'alergi' => trim($_POST['alergi'] ?? ''),
                'jeniskelamin' => $_POST['jeniskelamin'] ?? '',
                'alamat' => trim($_POST['alamat'] ?? ''),
                'nohp' => trim($_POST['nohp'] ?? ''),
                'foto' => $foto
            ];

            $hasil = $this->pasien->tambah($data);

            if ($hasil) {
                header('Location: index.php?module=pasien');
                exit;
            }

            $this->hapusFoto($foto);

            die('Gagal menyimpan data pasien.');

        } catch (\Throwable $e) {
            if (!empty($foto)) {
                try {
                    $this->hapusFoto($foto);
                } catch (\Throwable $hapusError) {
                    // Kegagalan penghapusan tidak menutupi error utama
                }
            }

            die('Error: ' . htmlspecialchars(
                $e->getMessage(),
                ENT_QUOTES,
                'UTF-8'
            ));
        }
    }

    // Menampilkan formulir edit pasien
    public function edit($id)
    {
        $pasien = $this->pasien->ambil($id);

        if (!$pasien) {
            die('Data pasien tidak ditemukan.');
        }

        require $this->projectRoot . '/views/Pasien/edit.php';
    }

    // Memperbarui data pasien
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?module=pasien');
            exit;
        }

        $pasienLama = $this->pasien->ambil($id);

        if (!$pasienLama) {
            die('Data pasien tidak ditemukan.');
        }

        $foto = $pasienLama['foto'] ?? '';
        $fotoBaru = false;

        try {
            if (
                isset($_FILES['foto']) &&
                $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE
            ) {
                $foto = $this->uploadFoto($_FILES['foto']);
                $fotoBaru = true;
            }

            $data = [
                'namapasien' => trim($_POST['namapasien'] ?? ''),
                'username' => trim($_POST['username'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'usia' => (int) ($_POST['usia'] ?? 0),
                'alergi' => trim($_POST['alergi'] ?? ''),
                'jeniskelamin' => $_POST['jeniskelamin'] ?? '',
                'alamat' => trim($_POST['alamat'] ?? ''),
                'nohp' => trim($_POST['nohp'] ?? ''),
                'foto' => $foto
            ];

            $hasil = $this->pasien->ubah($id, $data);

            if ($hasil) {
                if ($fotoBaru && !empty($pasienLama['foto'])) {
                    $this->hapusFoto($pasienLama['foto']);
                }

                header('Location: index.php?module=pasien');
                exit;
            }

            if ($fotoBaru) {
                $this->hapusFoto($foto);
            }

            die('Gagal memperbarui data pasien.');

        } catch (\Throwable $e) {
            if ($fotoBaru) {
                try {
                    $this->hapusFoto($foto);
                } catch (\Throwable $hapusError) {
                    // Menjaga pesan error utama
                }
            }

            die('Error: ' . htmlspecialchars(
                $e->getMessage(),
                ENT_QUOTES,
                'UTF-8'
            ));
        }
    }

    // Menampilkan detail pasien
    public function show($id)
    {
        $pasien = $this->pasien->ambil($id);

        if (!$pasien) {
            die('Data pasien tidak ditemukan.');
        }

        require $this->projectRoot . '/views/Pasien/show.php';
    }

    // Menghapus data pasien
    public function delete($id)
    {
        try {
            $pasien = $this->pasien->ambil($id);

            if (!$pasien) {
                die('Data pasien tidak ditemukan.');
            }

            $hasil = $this->pasien->hapus($id);

            if ($hasil) {
                if (!empty($pasien['foto'])) {
                    $this->hapusFoto($pasien['foto']);
                }

                header('Location: index.php?module=pasien');
                exit;
            }

            die('Gagal menghapus data pasien.');

        } catch (\mysqli_sql_exception $e) {
            die(
                'Data pasien tidak dapat dihapus karena masih memiliki ' .
                'riwayat kunjungan. Hapus data kunjungan terlebih dahulu.'
            );
        }
    }
}
