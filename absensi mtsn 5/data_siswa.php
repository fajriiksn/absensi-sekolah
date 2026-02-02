<?php
session_start();
if(!isset($_SESSION['status']) || $_SESSION['status'] != "sudah_login"){
    header("location:login.php"); exit;
}

include 'config/koneksi.php';
include 'layout/header.php';

// --- FUNGSI HELPER UPLOAD FOTO ---
function uploadFoto($input_name){
    $namaFile   = $_FILES[$input_name]['name'];
    $ukuranFile = $_FILES[$input_name]['size'];
    $error      = $_FILES[$input_name]['error'];
    $tmpName    = $_FILES[$input_name]['tmp_name'];

    // Jika tidak ada foto yang diupload (Error 4), pakai default
    if( $error === 4 ) {
        return 'default.jpg';
    }

    // Cek ekstensi file
    $ekstensiGambarValid = ['jpg', 'jpeg', 'png'];
    $ekstensiGambar = explode('.', $namaFile);
    $ekstensiGambar = strtolower(end($ekstensiGambar));
    
    if( !in_array($ekstensiGambar, $ekstensiGambarValid) ) {
        echo "<script>alert('Gagal: Format file harus JPG, JPEG, atau PNG!');</script>";
        return false;
    }

    // Cek ukuran file (Maks 2MB)
    if( $ukuranFile > 2000000 ) {
        echo "<script>alert('Gagal: Ukuran foto terlalu besar! Maksimal 2MB.');</script>";
        return false;
    }

    // Generate nama unik
    $namaFileBaru = uniqid();
    $namaFileBaru .= '.';
    $namaFileBaru .= $ekstensiGambar;

    // Pastikan folder ada
    $target_dir = "assets/img/siswa/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true); // Buat folder jika belum ada
    }

    // Pindahkan file
    if(move_uploaded_file($tmpName, $target_dir . $namaFileBaru)){
        return $namaFileBaru;
    } else {
        echo "<script>alert('Gagal mengupload file ke folder assets. Cek permission folder!');</script>";
        return false;
    }
}

// --- LOGIKA CRUD (CREATE, UPDATE, DELETE) ---

// 1. TAMBAH SISWA
if(isset($_POST['btn_simpan'])){
    $nis   = mysqli_real_escape_string($koneksi, $_POST['nis']);
    $nama  = mysqli_real_escape_string($koneksi, $_POST['nama_siswa']);
    $kelas = mysqli_real_escape_string($koneksi, $_POST['id_kelas']);
    $jk    = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']);
    $hp    = mysqli_real_escape_string($koneksi, $_POST['no_hp_ortu']);
    
    $cek_nis = mysqli_query($koneksi, "SELECT nis FROM siswa WHERE nis = '$nis'");
    if(mysqli_num_rows($cek_nis) > 0){
        echo "<script>alert('GAGAL: NIS $nis sudah terdaftar! Gunakan NIS lain.'); window.location='data_siswa.php';</script>";
    } else {
        $foto = uploadFoto('foto_siswa');
        if($foto !== false) {
            $query_simpan = "INSERT INTO siswa (nis, nama_siswa, id_kelas, jenis_kelamin, no_hp_ortu, foto_siswa) VALUES ('$nis', '$nama', '$kelas', '$jk', '$hp', '$foto')";
            if(mysqli_query($koneksi, $query_simpan)){
                echo "<script>alert('Data Berhasil Disimpan!'); window.location='data_siswa.php';</script>";
            } else {
                echo "<script>alert('Database Error: ".mysqli_error($koneksi)."');</script>";
            }
        }
    }
}

// 2. EDIT SISWA
if(isset($_POST['btn_update'])){
    $id    = $_POST['id_siswa'];
    $nama  = mysqli_real_escape_string($koneksi, $_POST['nama_siswa']);
    $kelas = $_POST['id_kelas'];
    $hp    = mysqli_real_escape_string($koneksi, $_POST['no_hp_ortu']);
    $foto_lama = $_POST['foto_lama'];

    if($_FILES['foto_siswa']['error'] === 4) {
        $foto = $foto_lama; 
    } else {
        $foto = uploadFoto('foto_siswa');
        if($foto === false){ $foto = $foto_lama; }
    }

    $query_update = "UPDATE siswa SET nama_siswa='$nama', id_kelas='$kelas', no_hp_ortu='$hp', foto_siswa='$foto' WHERE id_siswa='$id'";
    if(mysqli_query($koneksi, $query_update)){
        echo "<script>alert('Data Berhasil Diupdate'); window.location='data_siswa.php';</script>";
    } else {
        echo "<script>alert('Gagal Update: ".mysqli_error($koneksi)."');</script>";
    }
}

// 3. HAPUS SISWA
if(isset($_GET['hapus'])){
    $id = $_GET['hapus'];
    $q_foto = mysqli_query($koneksi, "SELECT foto_siswa FROM siswa WHERE id_siswa='$id'");
    $d_foto = mysqli_fetch_assoc($q_foto);
    $foto_hapus = $d_foto['foto_siswa'];
    
    if($foto_hapus != 'default.jpg' && file_exists('assets/img/siswa/'.$foto_hapus)){
        unlink('assets/img/siswa/'.$foto_hapus);
    }

    $hapus = mysqli_query($koneksi, "DELETE FROM siswa WHERE id_siswa='$id'");
    if($hapus) echo "<script>window.location='data_siswa.php';</script>";
}
?>

<style>
    .id-card-container { width: 320px; height: 480px; background: #fff; border: 1px solid #ddd; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.1); margin: auto; position: relative; font-family: 'Poppins', sans-serif; }
    .card-header-mts { background: linear-gradient(135deg, #064e3b 0%, #10b981 100%); color: white; padding: 15px; text-align: center; border-bottom: 3px solid #fcd34d; }
    .school-name { font-size: 1.1rem; font-weight: 700; margin: 0; text-transform: uppercase; }
    .card-title-sub { font-size: 0.8rem; opacity: 0.9; margin: 0; }
    .card-body-mts { padding: 20px; text-align: center; }
    .student-photo-frame { width: 120px; height: 150px; margin: 0 auto 15px auto; border: 3px solid #10b981; border-radius: 8px; overflow: hidden; background: #f0f0f0; }
    .student-photo { width: 100%; height: 100%; object-fit: cover; }
    .student-name { font-size: 1.2rem; font-weight: 700; color: #064e3b; margin-bottom: 5px; }
    .student-nis { font-size: 1rem; color: #555; background: #e0f2f1; display: inline-block; padding: 2px 10px; border-radius: 5px; margin-bottom: 15px; }
    .qr-code-area { margin-top: 10px; border-top: 2px dashed #ddd; padding-top: 15px; }
    .qr-img { width: 100px; height: 100px; }
    @media print { body { -webkit-print-color-adjust: exact; } }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-success"><i class="bi bi-people"></i> Data Siswa</h3>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus-circle"></i> Tambah Siswa
        </button>
    </div>

    <div class="card card-custom bg-white mb-4 border-start border-4 border-primary">
        <div class="card-body">
            <h5 class="fw-bold mb-3"><i class="bi bi-printer-fill"></i> Cetak Kartu Massal</h5>
            <form action="cetak_massal.php" method="GET" target="_blank" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Pilih Kelas</label>
                    <select name="id_kelas" class="form-select">
                        <option value="semua">-- Cetak Semua Siswa --</option>
                        <?php
                        $q_kelas_print = mysqli_query($koneksi, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
                        while($kp = mysqli_fetch_assoc($q_kelas_print)){
                            echo "<option value='".$kp['id_kelas']."'>Kelas ".$kp['nama_kelas']."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-printer"></i> Proses Cetak
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-custom bg-white mb-3">
        <div class="card-body py-3">
            <form action="" method="GET">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                            <input type="text" name="keyword" class="form-control" placeholder="Cari Nama / NIS..." value="<?php echo isset($_GET['keyword']) ? $_GET['keyword'] : ''; ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="filter_kelas" class="form-select">
                            <option value="">-- Semua Kelas --</option>
                            <?php
                            // Reset pointer query kelas agar bisa dipakai lagi
                            mysqli_data_seek($q_kelas_print, 0); 
                            while($kf = mysqli_fetch_assoc($q_kelas_print)){
                                // Cek agar opsi terpilih tetap 'selected' setelah submit
                                $selected = (isset($_GET['filter_kelas']) && $_GET['filter_kelas'] == $kf['id_kelas']) ? 'selected' : '';
                                echo "<option value='".$kf['id_kelas']."' $selected>Kelas ".$kf['nama_kelas']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100">Filter</button>
                    </div>
                    <div class="col-md-2">
                        <a href="data_siswa.php" class="btn btn-outline-danger w-100">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card card-custom bg-white">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th>No</th>
                            <th>Foto</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>No HP Ortu</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // --- LOGIKA QUERY PENCARIAN & FILTER ---
                        
                        // 1. Ambil nilai dari URL (GET)
                        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : "";
                        $filter_kelas = isset($_GET['filter_kelas']) ? $_GET['filter_kelas'] : "";

                        // 2. Mulai susun query dasar
                        $query_str = "SELECT siswa.*, kelas.nama_kelas 
                                      FROM siswa 
                                      JOIN kelas ON siswa.id_kelas = kelas.id_kelas 
                                      WHERE 1=1"; // Teknik '1=1' agar mudah menyambung 'AND'

                        // 3. Jika ada Filter Kelas
                        if($filter_kelas != ""){
                            $query_str .= " AND siswa.id_kelas = '$filter_kelas'";
                        }

                        // 4. Jika ada Keyword Pencarian
                        if($keyword != ""){
                            $query_str .= " AND (siswa.nama_siswa LIKE '%$keyword%' OR siswa.nis LIKE '%$keyword%')";
                        }

                        // 5. Tambahkan Order By
                        $query_str .= " ORDER BY id_siswa DESC";

                        // 6. Eksekusi Query
                        $query = mysqli_query($koneksi, $query_str);
                        $no = 1;

                        // Cek jika data kosong
                        if(mysqli_num_rows($query) == 0){
                            echo "<tr><td colspan='7' class='text-center py-4 text-muted'>Data tidak ditemukan. Silakan reset filter.</td></tr>";
                        }

                        while($row = mysqli_fetch_assoc($query)) :
                        ?>
                        <tr>
                            <td class="text-center"><?php echo $no++; ?></td>
                            <td class="text-center">
                                <img src="assets/img/siswa/<?php echo $row['foto_siswa']; ?>" alt="Foto" width="40" height="40" class="rounded-circle object-fit-cover" onerror="this.src='https://via.placeholder.com/40'">
                            </td>
                            <td class="text-center fw-bold"><?php echo $row['nis']; ?></td>
                            <td><?php echo $row['nama_siswa']; ?></td>
                            <td class="text-center"><?php echo $row['nama_kelas']; ?></td>
                            <td><?php echo $row['no_hp_ortu']; ?></td>
                            <td class="text-center" style="min-width: 150px;">
                                <button class="btn btn-sm btn-warning text-white btn-edit" 
                                    data-bs-toggle="modal" data-bs-target="#modalEdit" 
                                    data-id="<?php echo $row['id_siswa']; ?>"
                                    data-nama="<?php echo $row['nama_siswa']; ?>"
                                    data-kelas="<?php echo $row['id_kelas']; ?>"
                                    data-hp="<?php echo $row['no_hp_ortu']; ?>"
                                    data-foto="<?php echo $row['foto_siswa']; ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="data_siswa.php?hapus=<?php echo $row['id_siswa']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')"><i class="bi bi-trash"></i></a>
                                <button class="btn btn-sm btn-info text-white" onclick="showKartuPelajar('<?php echo $row['nis']; ?>', '<?php echo htmlspecialchars($row['nama_siswa']); ?>', '<?php echo $row['nama_kelas']; ?>', '<?php echo $row['foto_siswa']; ?>')">
                                    <i class="bi bi-person-badge"></i> Kartu
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Tambah Siswa Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="data_siswa.php" method="POST" enctype="multipart/form-data"> 
                <div class="modal-body">
                    <div class="mb-3">
                        <label>NIS</label>
                        <input type="number" name="nis" class="form-control" required placeholder="Cth: 12345">
                    </div>
                    <div class="mb-3">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_siswa" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select" required>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Kelas</label>
                            <select name="id_kelas" class="form-select" required>
                                <option value="">-Pilih-</option>
                                <?php
                                $q_kelas = mysqli_query($koneksi, "SELECT * FROM kelas");
                                while($k = mysqli_fetch_assoc($q_kelas)){ echo "<option value='".$k['id_kelas']."'>".$k['nama_kelas']."</option>"; }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>No HP Orang Tua</label>
                        <input type="text" name="no_hp_ortu" class="form-control" required placeholder="628xxxxxxxxxx">
                    </div>
                    <div class="mb-3">
                        <label>Foto Siswa</label>
                        <input type="file" name="foto_siswa" class="form-control" accept=".jpg, .jpeg, .png">
                        <small class="text-muted">Opsional. Maks 2MB.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="btn_simpan" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">Edit Data Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="data_siswa.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id_siswa" id="edit_id">
                    <input type="hidden" name="foto_lama" id="edit_foto_lama">
                    
                    <div class="text-center mb-3">
                         <img src="" id="preview_foto" class="rounded-circle" width="80" height="80" style="object-fit: cover;">
                    </div>

                    <div class="mb-3">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_siswa" id="edit_nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Kelas</label>
                        <select name="id_kelas" id="edit_kelas" class="form-select" required>
                            <?php
                            $q_kelas2 = mysqli_query($koneksi, "SELECT * FROM kelas");
                            while($k2 = mysqli_fetch_assoc($q_kelas2)){ echo "<option value='".$k2['id_kelas']."'>".$k2['nama_kelas']."</option>"; }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>No HP Orang Tua</label>
                        <input type="text" name="no_hp_ortu" id="edit_hp" class="form-control" required>
                    </div>
                     <div class="mb-3">
                        <label>Ganti Foto</label>
                        <input type="file" name="foto_siswa" class="form-control" accept=".jpg, .jpeg, .png">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="btn_update" class="btn btn-warning text-white">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalKartu" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0 shadow-none"> 
            <div class="modal-body p-0" id="printAreaKartu">
                <div class="id-card-container">
                    <div class="card-header-mts">
                        <h4 class="school-name">MTs NEGERI DIGITAL</h4>
                        <p class="card-title-sub">Kartu Tanda Pelajar</p>
                    </div>
                    <div class="card-body-mts">
                        <div class="student-photo-frame">
                            <img id="kartu_foto" src="" alt="Foto" class="student-photo" onerror="this.src='https://via.placeholder.com/120x150?text=No+Photo'">
                        </div>
                        <h5 id="kartu_nama" class="student-name">Nama</h5>
                        <div class="student-nis">NIS: <span id="kartu_nis"></span> | Kelas: <span id="kartu_kelas"></span></div>
                        <div class="qr-code-area">
                            <img id="kartu_qr" src="" class="qr-img">
                            <p class="text-muted extra-small mb-0 mt-1" style="font-size: 0.7rem;">Scan untuk absensi.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-center border-0 mt-3">
                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Tutup</button>
                <button onclick="printKartu()" class="btn btn-primary"><i class="bi bi-printer"></i> Cetak Kartu</button>
            </div>
        </div>
    </div>
</div>

<script>
    const editBtns = document.querySelectorAll('.btn-edit');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.getAttribute('data-id');
            document.getElementById('edit_nama').value = this.getAttribute('data-nama');
            document.getElementById('edit_kelas').value = this.getAttribute('data-kelas');
            document.getElementById('edit_hp').value = this.getAttribute('data-hp');
            document.getElementById('edit_foto_lama').value = this.getAttribute('data-foto');
            document.getElementById('preview_foto').src = 'assets/img/siswa/' + this.getAttribute('data-foto');
        });
    });

    function showKartuPelajar(nis, nama, kelas, fotoFilename) {
        document.getElementById('kartu_nama').innerText = nama;
        document.getElementById('kartu_nis').innerText = nis;
        document.getElementById('kartu_kelas').innerText = kelas;
        document.getElementById('kartu_foto').src = 'assets/img/siswa/' + fotoFilename;
        const urlQR = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${nis}&bgcolor=ffffff`;
        document.getElementById('kartu_qr').src = urlQR;
        var myModal = new bootstrap.Modal(document.getElementById('modalKartu'));
        myModal.show();
    }

    function printKartu() {
        var printContents = document.getElementById('printAreaKartu').innerHTML;
        var printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Cetak</title>');
        printWindow.document.write('<style>' + document.getElementsByTagName('style')[0].innerHTML + '</style>'); 
        printWindow.document.write('</head><body>');
        printWindow.document.write(printContents);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    }
</script>

<?php include 'layout/footer.php'; ?>