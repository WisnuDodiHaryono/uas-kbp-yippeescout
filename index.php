<?php
session_start();

// ==========================================
// FITUR LOGIN & LOGOUT (AUTHENTICATION)
// ==========================================
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

if (isset($_POST['login'])) {
    if ($_POST['username'] === 'admin' && $_POST['password'] === 'uas123') {
        $_SESSION['is_logged_in'] = true;
        header("Location: index.php");
        exit();
    } else {
        $error_login = "Username atau Password salah!";
    }
}

if (!isset($_SESSION['is_logged_in'])) {
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login | Yippee DSS</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); width: 320px; text-align: center; }
        .login-box h2 { color: #2c3e50; margin-bottom: 20px; }
        .input-group { margin-bottom: 15px; text-align: left; }
        .input-group label { display: block; font-size: 13px; font-weight: bold; color: #555; margin-bottom: 5px; }
        .input-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; outline:none;}
        .input-group input:focus { border-color:#3498db; }
        .btn-login { background: #2c3e50; color: white; width: 100%; padding: 12px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .btn-login:hover { background: #1a252f; }
        .error { color: #e74c3c; font-size: 13px; margin-bottom: 15px; background: #fadbd8; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>🔒 Yippee Scout</h2>
        <p style="font-size: 13px; color: #777; margin-top: -15px; margin-bottom: 25px;">Managerial DSS Access</p>
        <?php if(isset($error_login)) echo "<div class='error'>$error_login</div>"; ?>
        <form method="POST" autocomplete="off">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Masukkan username" required autocomplete="off">
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required autocomplete="new-password">
            </div>
            <button type="submit" name="login" class="btn-login">Masuk Sistem</button>
        </form>
        <p style="font-size: 12px; color: #999; margin-top: 20px;">Hint: admin / uas123</p>
    </div>
</body>
</html>
<?php
    exit(); 
}

// ==========================================
// FITUR EXPORT EXCEL
// ==========================================
if (isset($_GET['export_excel'])) {
    header("Content-type: application/vnd-ms-excel");
    header("Content-Disposition: attachment; filename=Laporan_Evaluasi_Aset.xls");
    $conn = mysqli_connect("localhost", "root", "", "checkvaluethriftyippee");
    $res = mysqli_query($conn, "SELECT * FROM aset_baju ORDER BY id DESC");
    echo "<table border='1'><tr><th>ID</th><th>Nama Barang</th><th>Merk</th><th>Harga Thrift</th><th>Valuasi Wajar</th><th>Skor OVR</th><th>Keputusan</th><th>Status Stok</th><th>Tanggal</th></tr>";
    while($row = mysqli_fetch_assoc($res)){
        $status_stok = isset($row['status_stok']) ? $row['status_stok'] : 'Tersedia';
        echo "<tr><td>{$row['id']}</td><td>{$row['nama_barang']}</td><td>{$row['merk']}</td><td>{$row['harga_thrift']}</td><td>{$row['harga_wajar']}</td><td>{$row['skor_ovr']}</td><td>{$row['status_keputusan']}</td><td>{$status_stok}</td><td>{$row['tanggal']}</td></tr>";
    }
    echo "</table>";
    exit();
}

// ==========================================
// 1. CLASS DATABASE & OOP BACKEND
// ==========================================
class Database {
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $db = "checkvaluethriftyippee";
    public $conn;

    public function __construct() {
        $this->conn = mysqli_connect($this->host, $this->user, $this->pass, $this->db);
        if (!$this->conn) die("Koneksi gagal: " . mysqli_connect_error());
    }
}

class AtributPakaian {
    private $warna, $bahan, $kualitas, $gaya, $fit, $fungsi;
    public function __construct($w, $b, $k, $g, $fi, $fu) {
        $this->warna = $w; $this->bahan = $b; $this->kualitas = $k;
        $this->gaya = $g; $this->fit = $fi; $this->fungsi = $fu;
    }
    public function hitungOverallRating() {
        $ovr = ($this->warna * 0.15) + ($this->bahan * 0.20) + ($this->kualitas * 0.25) + 
               ($this->gaya * 0.20) + ($this->fit * 0.10) + ($this->fungsi * 0.10);
        return $ovr * 10; 
    }
    public function getSkorArray() {
        return [$this->warna, $this->bahan, $this->kualitas, $this->gaya, $this->fit, $this->fungsi];
    }
}

abstract class AsetDasar {
    protected $namaBarang, $merk, $hargaBaru;
    public function __construct($n, $m, $hb) {
        $this->namaBarang = $n; $this->merk = $m; $this->hargaBaru = $hb;
    }
}

class ScoutManager extends AsetDasar {
    private $hargaThrift, $atribut, $db;
    public function __construct($n, $m, $hb, $ht, AtributPakaian $attr, $dbConn) {
        parent::__construct($n, $m, $hb);
        $this->hargaThrift = $ht; $this->atribut = $attr; $this->db = $dbConn;
    }
    
    public function evaluasiDanSimpan($id_update = null) {
        $ovr = $this->atribut->hitungOverallRating();
        $hargaWajar = $this->hargaBaru * ($ovr / 100);
        list($sw, $sb, $sk, $sg, $sfi, $sfu) = $this->atribut->getSkorArray();

        if ($this->hargaThrift <= ($hargaWajar * 0.6)) { $status = "MUST BUY (Profit Tinggi)"; } 
        elseif ($this->hargaThrift <= $hargaWajar) { $status = "LAYAK (Sesuai Harga)"; } 
        else { $status = "REJECT (Overpriced)"; }

        if ($id_update) {
            $query = "UPDATE aset_baju SET 
                nama_barang='{$this->namaBarang}', merk='{$this->merk}', harga_thrift='{$this->hargaThrift}', 
                harga_baru='{$this->hargaBaru}', skor_ovr='$ovr', harga_wajar='$hargaWajar', status_keputusan='$status',
                skor_warna='$sw', skor_bahan='$sb', skor_kualitas='$sk', skor_gaya='$sg', skor_fit='$sfi', skor_fungsi='$sfu' 
                WHERE id=$id_update";
        } else {
            $tgl = date('Y-m-d');
            $query = "INSERT INTO aset_baju 
                (nama_barang, merk, harga_thrift, harga_baru, skor_ovr, harga_wajar, status_keputusan, tanggal, status_stok, skor_warna, skor_bahan, skor_kualitas, skor_gaya, skor_fit, skor_fungsi) 
                VALUES ('{$this->namaBarang}', '{$this->merk}', '{$this->hargaThrift}', '{$this->hargaBaru}', '$ovr', '$hargaWajar', '$status', '$tgl', 'Tersedia', '$sw', '$sb', '$sk', '$sg', '$sfi', '$sfu')";
        }
        mysqli_query($this->db, $query);
    }
}

// ==========================================
// 2. CONTROLLER & LOGIKA UI
// ==========================================
$dbObj = new Database();
$conn = $dbObj->conn;

if (isset($_POST['scout'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $merk = mysqli_real_escape_string($conn, $_POST['merk']);
    $ht = (int)str_replace('.', '', $_POST['harga_thrift']);
    $hb = (int)str_replace('.', '', $_POST['harga_baru']);
    $id_edit = !empty($_POST['id_edit']) ? (int)$_POST['id_edit'] : null;

    $objAtribut = new AtributPakaian($_POST['r_warna'], $_POST['r_bahan'], $_POST['r_kualitas'], $_POST['r_gaya'], $_POST['r_fit'], $_POST['r_fungsi']);
    $objScout = new ScoutManager($nama, $merk, $hb, $ht, $objAtribut, $conn);
    $objScout->evaluasiDanSimpan($id_edit);
    header("Location: index.php"); exit();
}

if (isset($_GET['terjual'])) {
    $id = (int)$_GET['terjual'];
    mysqli_query($conn, "UPDATE aset_baju SET status_stok='Terjual' WHERE id=$id");
    header("Location: index.php"); exit();
}
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM aset_baju WHERE id=$id");
    header("Location: index.php"); exit();
}

$id_form = ''; $val_nama = ''; $val_merk = ''; $val_ht = ''; $val_hb = '';
$vw=5; $vb=5; $vk=5; $vg=5; $vfi=5; $vfu=5;
if (isset($_GET['edit'])) {
    $id_form = (int)$_GET['edit'];
    $q_edit = mysqli_query($conn, "SELECT * FROM aset_baju WHERE id=$id_form");
    if ($d_edit = mysqli_fetch_assoc($q_edit)) {
        $val_nama = $d_edit['nama_barang']; $val_merk = $d_edit['merk'];
        $val_ht = $d_edit['harga_thrift']; $val_hb = $d_edit['harga_baru'];
        $vw = $d_edit['skor_warna'] ?? 5; $vb = $d_edit['skor_bahan'] ?? 5; $vk = $d_edit['skor_kualitas'] ?? 5;
        $vg = $d_edit['skor_gaya'] ?? 5; $vfi = $d_edit['skor_fit'] ?? 5; $vfu = $d_edit['skor_fungsi'] ?? 5;
    }
}

$q_acc = mysqli_query($conn, "SELECT COUNT(*) as total_aset, SUM(harga_wajar - harga_thrift) as potensi_margin FROM aset_baju WHERE status_keputusan NOT LIKE '%REJECT%' AND status_stok = 'Tersedia'");
$stats_acc = mysqli_fetch_assoc($q_acc);
$q_reject = mysqli_query($conn, "SELECT COUNT(*) as total_reject, SUM(harga_thrift - harga_wajar) as kerugian_dihindari FROM aset_baju WHERE status_keputusan LIKE '%REJECT%'");
$stats_reject = mysqli_fetch_assoc($q_reject);
$q_terjual = mysqli_query($conn, "SELECT COUNT(*) as total_terjual, SUM(harga_wajar - harga_thrift) as profit_nyata FROM aset_baju WHERE status_stok = 'Terjual'");
$stats_terjual = mysqli_fetch_assoc($q_terjual);

$search_query = "";
$search_value = "";
if (isset($_GET['cari'])) {
    $search_value = mysqli_real_escape_string($conn, $_GET['keyword']);
    if ($search_value != "") {
        $search_query = "WHERE nama_barang LIKE '%$search_value%' OR merk LIKE '%$search_value%'";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Yippee Scout | Managerial DSS</title>
    <style>
        :root { --primary: #2c3e50; --bg: #f4f7f6; --success: #27ae60; --accent: #3498db; --warning: #f39c12; --danger: #e74c3c; --purple: #8e44ad; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: var(--bg); padding: 30px 20px; color: #333; margin: 0; }
        .container { max-width: 1100px; margin: auto; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 8px 30px rgba(0,0,0,0.08); }
        .header-top { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; margin-bottom: 30px; }
        .header-logo { font-weight: bold; color: var(--primary); font-size: 24px; }
        .btn-logout { background: #e74c3c; color: white; text-decoration: none; padding: 8px 15px; border-radius: 6px; font-size: 13px; font-weight: bold; transition: 0.2s;}
        .btn-logout:hover { background: #c0392b; }
        
        .dashboard-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 30px; }
        .dash-card { padding: 20px; border-radius: 12px; color: white; box-shadow: 0 4px 10px rgba(0,0,0,0.1); display:flex; flex-direction: column; justify-content: center;}
        .dash-card h4 { margin: 0 0 10px 0; font-size: 13px; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.5px;}
        .dash-card h2 { margin: 0; font-size: 24px; }
        .bg-blue { background: linear-gradient(135deg, #2980b9, #3498db); }
        .bg-green { background: linear-gradient(135deg, #27ae60, #2ecc71); }
        .bg-red { background: linear-gradient(135deg, #c0392b, #e74c3c); }
        .bg-purple { background: linear-gradient(135deg, #8e44ad, #9b59b6); }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
        input[type="text"], input[type="number"] { padding: 14px 18px; border: 1px solid #ddd; border-radius: 10px; width: 100%; box-sizing: border-box; font-size: 14px;}
        input:focus { border-color: var(--accent); outline: none; }
        
        .slider-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; background: #f8f9fa; padding: 20px; border-radius: 10px; border-left: 4px solid var(--primary); margin-bottom: 20px; }
        .slider-box label { font-size: 13px; font-weight: bold; display: flex; align-items: center; gap: 8px; margin-bottom: 5px; color:#555;}
        .val-badge { background: var(--primary); color: white; padding: 2px 8px; border-radius: 5px; font-size:12px;}
        
        .btn-submit { background: var(--primary); color: white; padding: 16px; border: none; border-radius: 10px; font-weight: bold; cursor: pointer; width: 100%; font-size: 15px; transition: 0.3s;}
        .btn-update { background: var(--warning); color: white; } 
        
        .table-header-flex { display: flex; justify-content: space-between; align-items: center; margin-top: 40px; }
        .search-box { display: flex; gap: 10px; }
        .search-box input { padding: 10px 15px; border-radius: 8px; border: 1px solid #ddd; width: 250px; }
        .btn-search { background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: bold;}
        .btn-export { background: #10ac84; color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; font-size: 14px;}
        
        table { width: 100%; border-collapse: collapse; font-size: 14px; margin-top: 15px; }
        th { background: var(--primary); color: white; padding: 15px; text-align: left; }
        td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
        tr:hover td { background: #f9fdfc; }
        .status-badge { padding: 6px 10px; border-radius: 6px; font-weight: bold; color: white; font-size: 12px; }
        
        .btn-action { text-decoration:none; font-weight:bold; padding: 6px 12px; border-radius:5px; font-size:12px; display: inline-block; margin-bottom: 4px; cursor: pointer; border: none;}
        .btn-laku { background: var(--success); color: white; margin-right: 4px; }
        .btn-edit { background: var(--accent); color: white; margin-right: 4px;}
        .btn-hapus { background: var(--danger); color: white;}
        .btn-detail { background: #34495e; color: white; margin-right: 4px;}
        .btn-sold { background: #e0e0e0; color: #7f8c8d; cursor: not-allowed; margin-right: 4px; }

        /* MODAL POP-UP STYLES */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center; }
        .modal-content { background-color: white; padding: 30px; border-radius: 12px; width: 450px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); position: relative;}
        .close-btn { position: absolute; top: 15px; right: 20px; font-size: 24px; font-weight: bold; color: #aaa; cursor: pointer; }
        .close-btn:hover { color: #333; }
        .score-bar-container { display: flex; align-items: center; margin-bottom: 10px; }
        .score-label { width: 100px; font-size: 13px; font-weight: bold; color: #555; }
        .score-bar-bg { flex-grow: 1; background: #eee; height: 10px; border-radius: 5px; overflow: hidden; position: relative;}
        .score-bar-fill { background: var(--accent); height: 100%; border-radius: 5px; }
        .score-number { width: 30px; text-align: right; font-size: 14px; font-weight: bold; color: var(--primary); }
    </style>
</head>
<body>

<div class="container">
    <div class="header-top">
        <div class="header-logo">噫 CTVY <span>| DSS Organisasi </span></div>
        <a href="?logout=true" class="btn-logout">Keluar (Logout)</a>
    </div>

    <div class="dashboard-grid">
        <div class="dash-card bg-blue">
            <h4>Aset Gudang (ACC)</h4>
            <h2><?= $stats_acc['total_aset'] ?? 0 ?> <span style="font-size:14px;">Tersedia</span></h2>
        </div>
        <div class="dash-card bg-green">
            <h4>Potensi Profit Gudang</h4>
            <h2>Rp <?= number_format($stats_acc['potensi_margin'] ?? 0, 0, ',', '.') ?></h2>
        </div>
        <div class="dash-card bg-purple">
            <h4>Profit Nyata (Sold)</h4>
            <h2>Rp <?= number_format($stats_terjual['profit_nyata'] ?? 0, 0, ',', '.') ?></h2>
        </div>
        <div class="dash-card bg-red">
            <h4>Kerugian Dihindari</h4>
            <h2>Rp <?= number_format($stats_reject['kerugian_dihindari'] ?? 0, 0, ',', '.') ?></h2>
        </div>
    </div>

    <h3 style="color: var(--primary); border-bottom: 2px solid #eee; padding-bottom:10px;">
        <?= $id_form ? '駐 Edit Data Inventaris' : '装 Form Evaluasi Pengadaan' ?>
    </h3>
    
    <form method="POST" action="index.php">
        <input type="hidden" name="id_edit" value="<?= $id_form ?>">
        <div class="form-grid">
            <input type="text" name="nama_barang" placeholder="Nama Barang (Cth: Varsity Jacket)" value="<?= $val_nama ?>" required>
            <input type="text" name="merk" placeholder="Merk Brand" value="<?= $val_merk ?>" required>
            <input type="number" name="harga_thrift" placeholder="Harga Beli / Modal (Rp)" value="<?= $val_ht ?>" required>
            <input type="number" name="harga_baru" placeholder="Acuan Harga Retail (Rp)" value="<?= $val_hb ?>" required>
        </div>
        <div class="slider-grid">
            <div class="slider-box"><label>Warna <span class="val-badge" id="v1"><?= $vw ?></span></label><input type="range" name="r_warna" min="1" max="10" value="<?= $vw ?>" oninput="document.getElementById('v1').innerText = this.value"></div>
            <div class="slider-box"><label>Bahan <span class="val-badge" id="v2"><?= $vb ?></span></label><input type="range" name="r_bahan" min="1" max="10" value="<?= $vb ?>" oninput="document.getElementById('v2').innerText = this.value"></div>
            <div class="slider-box"><label>Kualitas <span class="val-badge" id="v3"><?= $vk ?></span></label><input type="range" name="r_kualitas" min="1" max="10" value="<?= $vk ?>" oninput="document.getElementById('v3').innerText = this.value"></div>
            <div class="slider-box"><label>Gaya/Tren <span class="val-badge" id="v4"><?= $vg ?></span></label><input type="range" name="r_gaya" min="1" max="10" value="<?= $vg ?>" oninput="document.getElementById('v4').innerText = this.value"></div>
            <div class="slider-box"><label>Fit/Ukuran <span class="val-badge" id="v5"><?= $vfi ?></span></label><input type="range" name="r_fit" min="1" max="10" value="<?= $vfi ?>" oninput="document.getElementById('v5').innerText = this.value"></div>
            <div class="slider-box"><label>Fungsi <span class="val-badge" id="v6"><?= $vfu ?></span></label><input type="range" name="r_fungsi" min="1" max="10" value="<?= $vfu ?>" oninput="document.getElementById('v6').innerText = this.value"></div>
        </div>
        <button type="submit" name="scout" class="btn-submit <?= $id_form ? 'btn-update' : '' ?>">
            <?= $id_form ? 'Simpan Perubahan Data' : 'Evaluasi Keputusan Pembelian' ?>
        </button>
    </form>

    <div class="table-header-flex">
        <h3 style="margin:0; color:var(--primary);">聴 Arsip Keputusan & Inventaris</h3>
        <form method="GET" class="search-box">
            <input type="text" name="keyword" placeholder="Cari nama atau merk..." value="<?= $search_value ?>">
            <button type="submit" name="cari" class="btn-search">Cari</button>
            <?php if(isset($_GET['cari'])) echo '<a href="index.php" style="line-height:38px; color:#e74c3c; text-decoration:none; font-weight:bold; font-size:14px;">Reset</a>'; ?>
        </form>
        <a href="?export_excel=true" class="btn-export">搦 Download Report (.xls)</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item & Merk</th>
                <th>Harga Transaksi</th>
                <th>Skor OVR</th>
                <th>Keputusan Sistem</th>
                <th style="width: 220px;">Aksi Gudang</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $res_tampil = mysqli_query($conn, "SELECT * FROM aset_baju $search_query ORDER BY id DESC");
            if ($res_tampil && mysqli_num_rows($res_tampil) > 0) {
                while ($row = mysqli_fetch_assoc($res_tampil)) {
                    $bg = strpos($row['status_keputusan'], 'REJECT') !== false ? '#e74c3c' : (strpos($row['status_keputusan'], 'MUST BUY') !== false ? '#27ae60' : '#f39c12');
                    $status_stok = isset($row['status_stok']) ? $row['status_stok'] : 'Tersedia';
                    
                    // Ambil nilai individual dari database (jika ada, jika tidak default 0)
                    $vw = $row['skor_warna'] ?? 0; $vb = $row['skor_bahan'] ?? 0; $vk = $row['skor_kualitas'] ?? 0;
                    $vg = $row['skor_gaya'] ?? 0; $vfi = $row['skor_fit'] ?? 0; $vfu = $row['skor_fungsi'] ?? 0;
            ?>
            <tr>
                <td><b><?= $row['nama_barang'] ?></b><br><span style="font-size:12px; color:#777;"><?= $row['merk'] ?></span></td>
                <td>
                    <span style="color:var(--danger); font-size:13px;">Beli: Rp <?= number_format($row['harga_thrift'], 0, ',', '.') ?></span><br>
                    <span style="color:var(--success); font-size:13px;">Wajar: Rp <?= number_format($row['harga_wajar'], 0, ',', '.') ?></span>
                </td>
                <td><b style="font-size:18px; color:var(--primary);"><?= $row['skor_ovr'] ?></b></td>
                <td><span class="status-badge" style="background:<?= $bg ?>"><?= $row['status_keputusan'] ?></span></td>
                <td>
                    <button class="btn-action btn-detail" onclick="bukaModal('<?= $row['nama_barang'] ?>', '<?= $row['merk'] ?>', '<?= $row['skor_ovr'] ?>', <?= $vw ?>, <?= $vb ?>, <?= $vk ?>, <?= $vg ?>, <?= $vfi ?>, <?= $vfu ?>)">Rincian</button>
                    
                    <?php if($status_stok == 'Tersedia' && strpos($row['status_keputusan'], 'REJECT') === false): ?>
                        <a href="?terjual=<?= $row['id'] ?>" class="btn-action btn-laku" onclick="return confirm('Tandai aset ini telah terjual?')">Laku</a>
                    <?php elseif($status_stok == 'Terjual'): ?>
                        <span class="btn-action btn-sold">Sold Out</span>
                    <?php endif; ?>
                    <a href="?edit=<?= $row['id'] ?>" class="btn-action btn-edit">Edit</a>
                    <a href="?hapus=<?= $row['id'] ?>" class="btn-action btn-hapus" onclick="return confirm('Hapus aset ini dari arsip?')">Drop</a>
                </td>
            </tr>
            <?php }} else { echo "<tr><td colspan='5' style='text-align:center; color:#999; padding:20px;'>Data tidak ditemukan.</td></tr>"; } ?>
        </tbody>
    </table>

    <footer style="margin-top: 40px; text-align: center; color: #95a5a6; font-size: 13px; padding-top: 20px; border-top: 1px solid #eee;">
        <p>&copy; 2026 <strong>Wisnu - Informatika UIN Sunan Kalijaga</strong></p>
    </footer>
</div>

<div id="modalReport" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="tutupModal()">&times;</span>
        <h3 style="margin-top:0; color:var(--primary); border-bottom:2px solid #eee; padding-bottom:10px;">Scout Report Detail</h3>
        <p style="font-size: 14px; font-weight: bold; color: #333;" id="m_nama">Nama Barang (Merk)</p>
        <p style="font-size: 24px; font-weight: bold; color: var(--primary); margin: 5px 0 20px 0;">OVR: <span id="m_ovr">0</span>/100</p>
        
        <div id="m_bars">
            </div>
    </div>
</div>

<script>
    const modal = document.getElementById("modalReport");
    
    function bukaModal(nama, merk, ovr, w, b, k, g, fi, fu) {
        document.getElementById('m_nama').innerText = nama + ' (' + merk + ')';
        document.getElementById('m_ovr').innerText = ovr;
        
        const labels = ['Warna', 'Bahan', 'Kualitas', 'Gaya/Tren', 'Fit/Ukuran', 'Fungsi'];
        const scores = [w, b, k, g, fi, fu];
        let barHtml = '';
        
        for(let i=0; i<6; i++) {
            let width = (scores[i] / 10) * 100;
            // Warna bar berubah sesuai skor
            let color = width <= 40 ? '#e74c3c' : (width <= 70 ? '#f39c12' : '#27ae60');
            
            barHtml += `
            <div class="score-bar-container">
                <div class="score-label">${labels[i]}</div>
                <div class="score-bar-bg">
                    <div class="score-bar-fill" style="width: ${width}%; background: ${color};"></div>
                </div>
                <div class="score-number">${scores[i]}/10</div>
            </div>`;
        }
        document.getElementById('m_bars').innerHTML = barHtml;
        modal.style.display = "flex";
    }

    function tutupModal() {
        modal.style.display = "none";
    }

    // Tutup modal jika user klik di luar area putih
    window.onclick = function(event) {
        if (event.target == modal) {
            tutupModal();
        }
    }
</script>

</body>
</html>