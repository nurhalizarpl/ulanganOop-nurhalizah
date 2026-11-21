
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Pasien - KlinikNuri</title>

    <link rel="stylesheet" href="assets/style.css">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

<div class="container pasien-container">

    <h1>Data Pasien</h1>
    <p>Manajemen Data Pasien KlinikNuri</p>

    <div class="navigation">

        <a href="index.php?module=pasien" class="active">
            Data Pasien
        </a>

        <a href="index.php?module=kunjungan">
            Data Kunjungan
        </a>

    </div>

    <div class="toolbar">

        <a href="index.php?module=pasien&action=create"
           class="btn btn-primary">
            <i class="fa-solid fa-plus"></i>
            Tambah Pasien
        </a>

    </div>

    <div class="table-responsive">

        <table>

            <thead>
                <tr>
                    <th>No</th>
                    <th>Foto</th>
                    <th>Nama Pasien</th>
                    <th>Username</th>
                    <th>Usia</th>
                    <th>Jenis Kelamin</th>
                    <th>No. HP</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>

                <?php $no = 1; ?>

                <?php if ($dataPasien && $dataPasien->num_rows > 0): ?>

                    <?php while ($row = $dataPasien->fetch_assoc()): ?>

                    <tr>

                        <td><?= $no++; ?></td>

                        <!-- FOTO PASIEN -->
                        <td>

                            <?php if (!empty($row['foto'])): ?>

                                <img
                                    src="assets/pasien/<?= rawurlencode(basename($row['foto'])); ?>"
                                    class="foto-tabel"
                                    alt="Foto pasien"
                                    onerror="this.outerHTML='<span>Foto tidak ditemukan</span>';"
                                >

                            <?php else: ?>

                                <span>Belum ada foto</span>

                            <?php endif; ?>

                        </td>

                        <td>
                            <?= htmlspecialchars($row['namapasien'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars((string) ($row['usia'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['jeniskelamin'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['nohp'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </td>

                        <!-- TOMBOL AKSI -->
                        <td class="aksi">

                            <a
                                href="index.php?module=pasien&action=show&id=<?= (int) $row['idpasien']; ?>"
                                class="btn btn-info"
                                title="Detail">
                                <i class="fa-solid fa-eye"></i>
                            </a>

                            <a
                                href="index.php?module=pasien&action=edit&id=<?= (int) $row['idpasien']; ?>"
                                class="btn btn-warning"
                                title="Edit">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>

                            <a
                                href="index.php?module=pasien&action=delete&id=<?= (int) $row['idpasien']; ?>"
                                class="btn btn-danger"
                                title="Hapus"
                                onclick="return confirm('Yakin ingin menghapus pasien ini?')">

                                <i class="fa-solid fa-trash"></i>

                            </a>

                        </td>

                    </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="8" style="text-align:center;">
                            Belum ada data pasien.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>
