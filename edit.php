<?php
require 'koneksi.php';

// Ambil ID dari URL
$id = isset($_GET['id']) ? $_GET['id'] : null;
$id = filter_var($id, FILTER_VALIDATE_INT);

if ($id === false || $id === null || $id < 1) {
    header('Location: index.php');
    exit;
}

// Ambil data produk yang akan diedit
$stmt = mysqli_prepare($koneksi, 'SELECT * FROM produk WHERE id_produk = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$produk = mysqli_fetch_assoc($result);

if (!$produk) {
    header('Location: index.php');
    exit;
}

// Ambil daftar kategori
$kategori_result = mysqli_query($koneksi, 'SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori');

$error = '';

// Proses saat form dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = isset($_POST['nama_produk']) ? trim($_POST['nama_produk']) : '';
    $harga = isset($_POST['harga']) ? $_POST['harga'] : '';
    $stok = isset($_POST['stok']) ? $_POST['stok'] : '';
    $idKategori = isset($_POST['id_kategori']) ? $_POST['id_kategori'] : '';

    $harga = filter_var($harga, FILTER_VALIDATE_FLOAT);
    $stok = filter_var($stok, FILTER_VALIDATE_INT);
    $idKategori = filter_var($idKategori, FILTER_VALIDATE_INT);

    // Validasi input
    if ($nama === '' || $harga === false || $harga <= 0 || $stok === false || $stok < 0 || $idKategori === false || $idKategori < 1) {
        $error = 'Isi semua data dengan benar. Harga harus positif dan stok tidak boleh negatif.';
    } else {
        // Update data ke database
        $update = mysqli_prepare($koneksi, 'UPDATE produk SET id_kategori = ?, nama_produk = ?, harga = ?, stok = ? WHERE id_produk = ?');
        mysqli_stmt_bind_param($update, 'isdii', $idKategori, $nama, $harga, $stok, $id);
        mysqli_stmt_execute($update);
        mysqli_stmt_close($update);

        // Kembali ke daftar
        header('Location: index.php');
        exit;
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Edit Produk</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .error { color: red; padding: 10px; background: #ffebee; }
        form { max-width: 400px; }
        p { margin: 10px 0; }
        input, select { width: 100%; padding: 6px; margin-top: 4px; }
        button { padding: 8px 16px; background: #4CAF50; color: white; border: none; cursor: pointer; }
        a { color: #666; text-decoration: none; }
    </style>
</head>
<body>
    <h1>Edit Produk</h1>
    
    <?php if ($error !== ''): ?>
    <p class="error"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <p>
            Nama Produk:<br>
            <input type="text" name="nama_produk" value="<?= e($produk['nama_produk']) ?>" required>
        </p>
        <p>
            Harga:<br>
            <input type="number" name="harga" step="0.01" min="0.01" value="<?= e($produk['harga']) ?>" required>
        </p>
        <p>
            Stok:<br>
            <input type="number" name="stok" min="0" value="<?= e($produk['stok']) ?>" required>
        </p>
        <p>
            Kategori:<br>
            <select name="id_kategori" required>
                <option value="">Pilih Kategori</option>
                <?php while ($k = mysqli_fetch_assoc($kategori_result)): ?>
                <option value="<?= e($k['id_kategori']) ?>" 
                    <?= (int)$produk['id_kategori'] === (int)$k['id_kategori'] ? 'selected' : '' ?>>
                    <?= e($k['nama_kategori']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </p>
        <p>
            <button type="submit">💾 Simpan Perubahan</button>
        </p>
    </form>
    
    <p><a href="index.php">⬅️ Kembali ke Daftar Produk</a></p>
</body>
</html>