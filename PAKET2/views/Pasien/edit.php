<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pasien - KlinikNuri</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="container form-container">

    <h1>Edit Data Pasien</h1>
    <p>Perbarui Informasi Pasien KlinikNuri</p>

    <div class="navigation">
        <a href="index.php?module=pasien" class="active">
            Data Pasien
        </a>

        <a href="index.php?module=kunjungan">
            Data Kunjungan
        </a>
    </div>

    <form action="index.php?module=pasien&action=update&id=<?= (int) $pasien['idpasien']; ?>"
          method="POST"
          enctype="multipart/form-data">

        <label>Nama Pasien</label>
        <input
            type="text"
            name="namapasien"
            maxlength="100"
            value="<?= htmlspecialchars($pasien['namapasien'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
            required
        >

        <label>Username</label>
        <input
            type="text"
            name="username"
            maxlength="100"
            value="<?= htmlspecialchars($pasien['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
            required
        >

        <label>Password Baru</label>
        <input
            type="password"
            name="password"
            maxlength="255"
            placeholder="Masukkan password baru"
            required
        >

        <label>Usia</label>
        <input
            type="number"
            name="usia"
            min="1"
            value="<?= htmlspecialchars((string) ($pasien['usia'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
            required
        >

        <label>Alergi Obat</label>
        <input
            type="text"
            name="alergi"
            maxlength="255"
            placeholder="Contoh: Mual, Gatal, Bengkak, Sesak Napas"
            value="<?= htmlspecialchars($pasien['alergi'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
            required
        >

        <label>Jenis Kelamin</label>
        <select name="jeniskelamin" required>

            <option value="">-- Pilih Jenis Kelamin --</option>

            <option value="Laki-laki"
                <?= ($pasien['jeniskelamin'] ?? '') == 'Laki-laki' ? 'selected' : ''; ?>>
                Laki-laki
            </option>

            <option value="Perempuan"
                <?= ($pasien['jeniskelamin'] ?? '') == 'Perempuan' ? 'selected' : ''; ?>>
                Perempuan
            </option>

        </select>

        <label>Alamat</label>
        <textarea
            name="alamat"
            maxlength="255"
        ><?= htmlspecialchars($pasien['alamat'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>

        <label>Nomor HP</label>
        <input
            type="text"
            name="nohp"
            maxlength="15"
            value="<?= htmlspecialchars($pasien['nohp'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
        >

        <label>Foto Pasien Saat Ini</label>

        <div class="foto-preview">

            <?php if (!empty($pasien['foto'])): ?>

                <img
                    src="assets/pasien/<?= rawurlencode(basename($pasien['foto'])); ?>"
                    class="foto-detail"
                    alt="Foto pasien saat ini"
                    onerror="this.style.display='none'; document.getElementById('foto-error').style.display='block';"
                >

                <p id="foto-error" style="display:none; color:red;">
                    Foto tidak ditemukan. Periksa folder assets/pasien/.
                </p>

            <?php else: ?>

                <p>Belum ada foto pasien.</p>

            <?php endif; ?>

        </div>

        <label>Ganti Foto Pasien</label>

        <input
            type="file"
            name="foto"
            accept=".jpg,.jpeg,.png,.webp"
        >

        <small>
            Kosongkan jika tidak ingin mengganti foto pasien.
        </small>

        <div class="form-actions">

            <button type="submit" class="btn btn-primary">
                Perbarui
            </button>

            <a href="index.php?module=pasien"
               class="btn btn-secondary">
                Kembali
            </a>

        </div>

    </form>

</div>

</body>
</html>
