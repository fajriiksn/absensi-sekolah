<?php
session_start();
// Cek Login
if(!isset($_SESSION['status']) || $_SESSION['status'] != "sudah_login"){
    header("location:login.php"); exit;
}

include 'config/koneksi.php';
include 'layout/header.php';

// --- LOGIKA CRUD KELAS ---

// 1. Tambah Kelas
if(isset($_POST['btn_simpan'])){
    $nama_kelas = $_POST['nama_kelas'];
    $id_walikelas = $_POST['id_walikelas']; // Bisa null jika belum ada wali kelas
    
    // Jika id_walikelas kosong (pilih opsi strip -), set NULL agar tidak error di database
    if($id_walikelas == "") {
        $query_simpan = "INSERT INTO kelas (nama_kelas, id_walikelas) VALUES ('$nama_kelas', NULL)";
    } else {
        $query_simpan = "INSERT INTO kelas (nama_kelas, id_walikelas) VALUES ('$nama_kelas', '$id_walikelas')";
    }

    $simpan = mysqli_query($koneksi, $query_simpan);
    if($simpan) {
        echo "<script>alert('Data Kelas Berhasil Disimpan'); window.location='data_kelas.php';</script>";
    } else {
        echo "<script>alert('Gagal: ".mysqli_error($koneksi)."');</script>";
    }
}

// 2. Edit Kelas
if(isset($_POST['btn_update'])){
    $id_kelas = $_POST['id_kelas'];
    $nama_kelas = $_POST['nama_kelas'];
    $id_walikelas = $_POST['id_walikelas'];

    if($id_walikelas == "") {
        $query_update = "UPDATE kelas SET nama_kelas='$nama_kelas', id_walikelas=NULL WHERE id_kelas='$id_kelas'";
    } else {
        $query_update = "UPDATE kelas SET nama_kelas='$nama_kelas', id_walikelas='$id_walikelas' WHERE id_kelas='$id_kelas'";
    }

    $update = mysqli_query($koneksi, $query_update);
    if($update) echo "<script>alert('Data Berhasil Diupdate'); window.location='data_kelas.php';</script>";
}

// 3. Hapus Kelas
if(isset($_GET['hapus'])){
    $id = $_GET['hapus'];
    // Cek apakah kelas dipakai oleh siswa?
    $cek_siswa = mysqli_query($koneksi, "SELECT * FROM siswa WHERE id_kelas='$id'");
    
    if(mysqli_num_rows($cek_siswa) > 0){
        echo "<script>alert('Gagal Hapus! Kelas ini masih memiliki siswa. Hapus/pindahkan siswa terlebih dahulu.'); window.location='data_kelas.php';</script>";
    } else {
        $hapus = mysqli_query($koneksi, "DELETE FROM kelas WHERE id_kelas='$id'");
        if($hapus) echo "<script>window.location='data_kelas.php';</script>";
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-success"><i class="bi bi-grid"></i> Data Kelas</h3>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus-circle"></i> Tambah Kelas
        </button>
    </div>

    <div class="card card-custom bg-white">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="10%">No</th>
                            <th>Nama Kelas</th>
                            <th>Wali Kelas</th>
                            <th>Jumlah Siswa</th>
                            <th width="20%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        // Query JOIN untuk menampilkan nama walikelas & hitung jumlah siswa
                        $query = mysqli_query($koneksi, "
                            SELECT kelas.*, users.nama_lengkap as nama_wali,
                            (SELECT COUNT(*) FROM siswa WHERE siswa.id_kelas = kelas.id_kelas) as jumlah_siswa
                            FROM kelas 
                            LEFT JOIN users ON kelas.id_walikelas = users.id_user
                            ORDER BY nama_kelas ASC
                        ");
                        
                        while($row = mysqli_fetch_assoc($query)) :
                        ?>
                        <tr>
                            <td class="text-center"><?php echo $no++; ?></td>
                            <td class="fw-bold text-center"><?php echo $row['nama_kelas']; ?></td>
                            <td>
                                <?php 
                                if($row['nama_wali']){
                                    echo '<i class="bi bi-person-badge text-muted me-1"></i> ' . $row['nama_wali']; 
                                } else {
                                    echo '<span class="text-muted fst-italic">- Belum diatur -</span>';
                                }
                                ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary rounded-pill"><?php echo $row['jumlah_siswa']; ?> Siswa</span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-warning text-white btn-edit" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalEdit" 
                                    data-id="<?php echo $row['id_kelas']; ?>"
                                    data-nama="<?php echo $row['nama_kelas']; ?>"
                                    data-wali="<?php echo $row['id_walikelas']; ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="data_kelas.php?hapus=<?php echo $row['id_kelas']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus kelas ini?')"><i class="bi bi-trash"></i></a>
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
                <h5 class="modal-title">Tambah Kelas Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nama Kelas</label>
                        <input type="text" name="nama_kelas" class="form-control" required placeholder="Contoh: 7A, 8B">
                    </div>
                    <div class="mb-3">
                        <label>Wali Kelas (Opsional)</label>
                        <select name="id_walikelas" class="form-select">
                            <option value="">-- Pilih Wali Kelas --</option>
                            <?php
                            // Ambil User yang role-nya Walikelas
                            $q_wali = mysqli_query($koneksi, "SELECT * FROM users WHERE role='walikelas'");
                            while($w = mysqli_fetch_assoc($q_wali)){
                                echo "<option value='".$w['id_user']."'>".$w['nama_lengkap']."</option>";
                            }
                            ?>
                        </select>
                        <small class="text-muted">Data diambil dari tabel Users (Role: Walikelas)</small>
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
                <h5 class="modal-title">Edit Data Kelas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_kelas" id="edit_id">
                    <div class="mb-3">
                        <label>Nama Kelas</label>
                        <input type="text" name="nama_kelas" id="edit_nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Wali Kelas</label>
                        <select name="id_walikelas" id="edit_wali" class="form-select">
                            <option value="">-- Tidak Ada --</option>
                            <?php
                            // Ambil ulang data wali untuk dropdown edit
                            $q_wali2 = mysqli_query($koneksi, "SELECT * FROM users WHERE role='walikelas'");
                            while($w2 = mysqli_fetch_assoc($q_wali2)){
                                echo "<option value='".$w2['id_user']."'>".$w2['nama_lengkap']."</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="btn_update" class="btn btn-warning text-white">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Isi Modal Edit secara otomatis saat tombol diklik
    const editBtns = document.querySelectorAll('.btn-edit');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.getAttribute('data-id');
            document.getElementById('edit_nama').value = this.getAttribute('data-nama');
            
            // Set dropdown value (jika kosong, set ke string kosong)
            let waliId = this.getAttribute('data-wali');
            document.getElementById('edit_wali').value = waliId ? waliId : "";
        });
    });
</script>

<?php include 'layout/footer.php'; ?>