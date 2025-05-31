<?php
session_start();

// Fungsi untuk memuat data produk dari file JSON
function loadProducts() {
    $dataFile = 'data/products.json';
    
    if (!file_exists($dataFile)) {
        return [];
    }
    
    $jsonData = file_get_contents($dataFile);
    $products = json_decode($jsonData, true);
    
    return is_array($products) ? $products : [];
}

// Fungsi untuk menyimpan produk ke file JSON
function saveProduct($product) {
    $dataDir = 'data';
    $dataFile = $dataDir . '/products.json';
    
    // Buat direktori data jika belum ada
    if (!file_exists($dataDir)) {
        if (!mkdir($dataDir, 0755, true)) {
            return false;
        }
    }
    
    // Muat produk yang sudah ada
    $products = loadProducts();
    
    // Tambah produk baru
    $products[] = $product;
    
    // Simpan ke file
    $jsonData = json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    return file_put_contents($dataFile, $jsonData) !== false;
}

// Fungsi untuk validasi form produk
function validateProductForm($data) {
    $errors = [];
    
    // Validasi nama produk
    if (empty($data['nama_produk'])) {
        $errors['nama_produk'] = "Nama produk harus diisi";
    } elseif (strlen(trim($data['nama_produk'])) < 3) {
        $errors['nama_produk'] = "Nama produk minimal 3 karakter";
    }
    
    // Validasi harga
    if (empty($data['harga'])) {
        $errors['harga'] = "Harga harus diisi";
    } elseif (!is_numeric($data['harga']) || $data['harga'] <= 0) {
        $errors['harga'] = "Harga harus berupa angka positif";
    }
    
    // Validasi kategori
    $valid_categories = ['elektronik', 'pakaian', 'makanan', 'buku', 'olahraga'];
    if (empty($data['kategori'])) {
        $errors['kategori'] = "Kategori harus dipilih";
    } elseif (!in_array($data['kategori'], $valid_categories)) {
        $errors['kategori'] = "Kategori tidak valid";
    }
    
    // Validasi deskripsi
    if (empty($data['deskripsi'])) {
        $errors['deskripsi'] = "Deskripsi harus diisi";
    } elseif (strlen(trim($data['deskripsi'])) < 10) {
        $errors['deskripsi'] = "Deskripsi minimal 10 karakter";
    }
    
    // Validasi stok
    if (!isset($data['stok']) || $data['stok'] === '') {
        $errors['stok'] = "Stok harus diisi";
    } elseif (!is_numeric($data['stok']) || $data['stok'] < 0) {
        $errors['stok'] = "Stok harus berupa angka non-negatif";
    }
    
    return [
        'errors' => $errors,
        'is_valid' => empty($errors)
    ];
}

// Inisialisasi variabel
$errors = [];
$success_message = '';
$products = loadProducts();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_product'])) {
    // Validasi data form
    $validation_result = validateProductForm($_POST);
    $errors = $validation_result['errors'];
    
    if ($validation_result['is_valid']) {
        // Simpan produk
        $product = [
            'id' => uniqid(),
            'nama_produk' => htmlspecialchars(trim($_POST['nama_produk'])),
            'harga' => (int)$_POST['harga'],
            'kategori' => $_POST['kategori'],
            'deskripsi' => htmlspecialchars(trim($_POST['deskripsi'])),
            'stok' => (int)$_POST['stok'],
            'tanggal' => date('Y-m-d H:i:s')
        ];
        
        if (saveProduct($product)) {
            $success_message = 'Produk berhasil ditambahkan!';
            $products = loadProducts(); // Reload products
            // Clear form data
            $_POST = [];
        } else {
            $errors['general'] = 'Gagal menyimpan produk. Silakan coba lagi.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tugas PHP - Form Input dan Validasi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            background: linear-gradient(45deg, #4a90e2, #7b68ee);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        h2 {
            color: #4a90e2;
            border-bottom: 2px solid #4a90e2;
            padding-bottom: 5px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }
        input:focus, textarea:focus, select:focus {
            border-color: #4a90e2;
            outline: none;
        }
        button {
            background: linear-gradient(45deg, #4a90e2, #7b68ee);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
        }
        button:hover {
            opacity: 0.9;
        }
        .error {
            color: #e74c3c;
            font-size: 12px;
            margin-top: 5px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .php-basics {
            background: #e8f4fd;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .code-example {
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
            overflow-x: auto;
        }
        .php-output {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            font-family: monospace;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background: #4a90e2;
            color: white;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        .form-errors {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Tugas PHP - Form Input dan Validasi</h1>

        <!-- Tugas 1: PHP Dasar -->
        <h2>1. Tugas Dasar PHP</h2>
        <div class="php-basics">
            <h3>Deklarasi Variabel:</h3>
            <div class="code-example">
&lt;?php
// Deklarasi variabel
$nama = "John Doe";
$umur = 25;
$tinggi = 175.5;
$menikah = false;

// Menampilkan variabel
echo "Nama: " . $nama . "&lt;br&gt;";
echo "Umur: " . $umur . " tahun&lt;br&gt;";
echo "Tinggi: " . $tinggi . " cm&lt;br&gt;";
echo "Status: " . ($menikah ? "Menikah" : "Belum Menikah");
?&gt;
            </div>
            <div class="php-output">
                <strong>Output:</strong><br>
                <?php
                    $nama = "John Doe";
                    $umur = 25;
                    $tinggi = 175.5;
                    $menikah = false;
                    
                    echo "Nama: " . $nama . "<br>";
                    echo "Umur: " . $umur . " tahun<br>";
                    echo "Tinggi: " . $tinggi . " cm<br>";
                    echo "Status: " . ($menikah ? "Menikah" : "Belum Menikah");
                ?>
            </div>

            <h3>Operator PHP:</h3>
            <div class="code-example">
&lt;?php
$a = 10;
$b = 3;

// Operator Aritmatika
echo "Penjumlahan: " . ($a + $b) . "&lt;br&gt;";
echo "Pengurangan: " . ($a - $b) . "&lt;br&gt;";
echo "Perkalian: " . ($a * $b) . "&lt;br&gt;";
echo "Pembagian: " . ($a / $b) . "&lt;br&gt;";

// Operator Perbandingan
echo "Apakah $a lebih besar dari $b? " . ($a > $b ? "Ya" : "Tidak");
?&gt;
            </div>
            <div class="php-output">
                <strong>Output:</strong><br>
                <?php
                    $a = 10;
                    $b = 3;
                    
                    echo "Penjumlahan: " . ($a + $b) . "<br>";
                    echo "Pengurangan: " . ($a - $b) . "<br>";
                    echo "Perkalian: " . ($a * $b) . "<br>";
                    echo "Pembagian: " . ($a / $b) . "<br>";
                    echo "Apakah $a lebih besar dari $b? " . ($a > $b ? "Ya" : "Tidak");
                ?>
            </div>

            <h3>Penggunaan If-Else:</h3>
            <div class="code-example">
&lt;?php
$nilai = 85;

if ($nilai >= 90) {
    echo "Grade: A";
} elseif ($nilai >= 80) {
    echo "Grade: B";
} elseif ($nilai >= 70) {
    echo "Grade: C";
} elseif ($nilai >= 60) {
    echo "Grade: D";
} else {
    echo "Grade: F";
}
?&gt;
            </div>
            <div class="php-output">
                <strong>Output:</strong><br>
                <?php
                    $nilai = 85;
                    
                    if ($nilai >= 90) {
                        echo "Grade: A";
                    } elseif ($nilai >= 80) {
                        echo "Grade: B";
                    } elseif ($nilai >= 70) {
                        echo "Grade: C";
                    } elseif ($nilai >= 60) {
                        echo "Grade: D";
                    } else {
                        echo "Grade: F";
                    }
                ?>
            </div>
        </div>

        <!-- Display errors if any -->
        <?php if (!empty($errors)): ?>
        <div class="form-errors">
            <strong>Error:</strong><br>
            <?php foreach ($errors as $error): ?>
                <div><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Display success message -->
        <?php if (!empty($success_message)): ?>
        <div class="success">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
        <?php endif; ?>

        <!-- Tugas 2: Form Input -->
        <h2>2. Form Input Produk</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="nama_produk">Nama Produk:</label>
                <input type="text" id="nama_produk" name="nama_produk" 
                       value="<?php echo isset($_POST['nama_produk']) ? htmlspecialchars($_POST['nama_produk']) : ''; ?>" required>
                <?php if (isset($errors['nama_produk'])): ?>
                    <div class="error"><?php echo htmlspecialchars($errors['nama_produk']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="harga">Harga (Rp):</label>
                <input type="number" id="harga" name="harga" min="0" step="1000" 
                       value="<?php echo isset($_POST['harga']) ? htmlspecialchars($_POST['harga']) : ''; ?>" required>
                <?php if (isset($errors['harga'])): ?>
                    <div class="error"><?php echo htmlspecialchars($errors['harga']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="kategori">Kategori:</label>
                <select id="kategori" name="kategori" required>
                    <option value="">Pilih Kategori</option>
                    <?php 
                    $categories = ['elektronik', 'pakaian', 'makanan', 'buku', 'olahraga'];
                    foreach ($categories as $cat): 
                    ?>
                        <option value="<?php echo $cat; ?>" 
                                <?php echo (isset($_POST['kategori']) && $_POST['kategori'] === $cat) ? 'selected' : ''; ?>>
                            <?php echo ucfirst($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['kategori'])): ?>
                    <div class="error"><?php echo htmlspecialchars($errors['kategori']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="deskripsi">Deskripsi:</label>
                <textarea id="deskripsi" name="deskripsi" rows="4" placeholder="Masukkan deskripsi produk..." required><?php echo isset($_POST['deskripsi']) ? htmlspecialchars($_POST['deskripsi']) : ''; ?></textarea>
                <?php if (isset($errors['deskripsi'])): ?>
                    <div class="error"><?php echo htmlspecialchars($errors['deskripsi']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="stok">Jumlah Stok:</label>
                <input type="number" id="stok" name="stok" min="0" 
                       value="<?php echo isset($_POST['stok']) ? htmlspecialchars($_POST['stok']) : ''; ?>" required>
                <?php if (isset($errors['stok'])): ?>
                    <div class="error"><?php echo htmlspecialchars($errors['stok']); ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" name="submit_product">Tambah Produk</button>
            <button type="button" onclick="window.location.href='<?php echo $_SERVER['PHP_SELF']; ?>'">Reset</button>
        </form>

        <!-- Tugas 3: Hasil Validasi dan Data -->
        <h2>3. Data Produk yang Tersimpan</h2>
        <?php if (!empty($products)): ?>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Produk</th>
                    <th>Harga</th>
                    <th>Kategori</th>
                    <th>Deskripsi</th>
                    <th>Stok</th>
                    <th>Tanggal Input</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $index => $product): ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td><?php echo htmlspecialchars($product['nama_produk']); ?></td>
                    <td>Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></td>
                    <td><?php echo ucfirst($product['kategori']); ?></td>
                    <td><?php echo htmlspecialchars(substr($product['deskripsi'], 0, 50)) . (strlen($product['deskripsi']) > 50 ? '...' : ''); ?></td>
                    <td><?php echo $product['stok']; ?></td>
                    <td><?php echo $product['tanggal']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>Belum ada produk yang tersimpan.</p>
        <?php endif; ?>

        <div class="php-basics">
            <h3>Contoh Kode PHP untuk Validasi:</h3>
            <div class="code-example">
&lt;?php
// Validasi form PHP
if ($_POST) {
    $errors = array();
    
    // Validasi nama produk
    if (empty($_POST['nama_produk'])) {
        $errors[] = "Nama produk harus diisi";
    } elseif (strlen($_POST['nama_produk']) < 3) {
        $errors[] = "Nama produk minimal 3 karakter";
    }
    
    // Validasi harga
    if (empty($_POST['harga'])) {
        $errors[] = "Harga harus diisi";
    } elseif (!is_numeric($_POST['harga']) || $_POST['harga'] <= 0) {
        $errors[] = "Harga harus berupa angka positif";
    }
    
    // Validasi kategori
    $valid_categories = ['elektronik', 'pakaian', 'makanan', 'buku', 'olahraga'];
    if (empty($_POST['kategori'])) {
        $errors[] = "Kategori harus dipilih";
    } elseif (!in_array($_POST['kategori'], $valid_categories)) {
        $errors[] = "Kategori tidak valid";
    }
    
    // Validasi deskripsi
    if (empty($_POST['deskripsi'])) {
        $errors[] = "Deskripsi harus diisi";
    } elseif (strlen($_POST['deskripsi']) < 10) {
        $errors[] = "Deskripsi minimal 10 karakter";
    }
    
    // Validasi stok
    if (!isset($_POST['stok']) || $_POST['stok'] < 0) {
        $errors[] = "Stok harus berupa angka non-negatif";
    }
    
    // Jika tidak ada error, simpan data
    if (empty($errors)) {
        // Simpan ke database atau file
        $data = array(
            'nama_produk' => htmlspecialchars($_POST['nama_produk']),
            'harga' => (int)$_POST['harga'],
            'kategori' => $_POST['kategori'],
            'deskripsi' => htmlspecialchars($_POST['deskripsi']),
            'stok' => (int)$_POST['stok'],
            'tanggal' => date('Y-m-d H:i:s')
        );
        
        echo "Data berhasil disimpan!";
    } else {
        // Tampilkan error
        foreach ($errors as $error) {
            echo "&lt;p style='color: red;'&gt;" . $error . "&lt;/p&gt;";
        }
    }
}
?&gt;
            </div>
        </div>
    </div>
</body>
</html>