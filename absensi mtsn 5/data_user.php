<?php
session_start();
// Cek Login
if(!isset($_SESSION['status']) || $_SESSION['status'] != "sudah_login"){
    header("location:login.php"); exit;
}

include 'config/koneksi.php';
include 'layout/header.php';

// --- LOGIKA CRUD USER ---

// 1. Tambah User Baru
if(isset($_POST['btn_simpan'])){
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = md5($_POST['password']); // Enkripsi MD5 sesuai login
    $role     = $_POST['role'];

    // Cek Username Kembar
    $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");
    if(mysqli_num_rows($cek) > 0){
        echo "<script>alert('Gagal: Username sudah digunakan!');</script>";
    } else {
        $simpan = mysqli_query($koneksi, "INSERT INTO users (nama_lengkap, username, password, role) VALUES ('$nama', '$username', '$password', '$role')");
        if($simpan) echo "<script>alert('User Berhasil Ditambah'); window.location='data_user.php';</script>";
    }
}

// 2. Update User
if(isset($_POST['btn_update'])){
    $id       = $_POST['id_user'];
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $role     = $_POST['role'];
    
    // Logika Ganti Password
    // Jika kolom password diisi, maka update password. Jika kosong, pakai password lama.
    if(!empty($_POST['password'])){
        $password = md5($_POST['password']);
        $query_update = "UPDATE users SET nama_lengkap='$nama', username='$username', password='$password', role='$role' WHERE id_user='$id'";
    } else {
        $query_update = "UPDATE users SET nama_lengkap='$nama', username='$username', role='$role' WHERE id_user='$id'";
    }

    $update = mysqli_query($koneksi, $query_update);
    if($update) echo "<script>alert('Data User Berhasil Diupdate'); window.location='data_user.php';</script>";
}

// 3. Hapus User
if(isset($_GET['hapus'])){
    $id = $_GET['hapus'];
    
    // Mencegah hapus akun sendiri yang sedang login
    if($id == $_SESSION['id_user']){
        echo "<script>alert('Gagal: Anda tidak bisa menghapus akun yang sedang digunakan!'); window.location='data_user.php';</script>";
    } else {
        $hapus = mysqli_query($koneksi, "DELETE FROM users WHERE id_user='$id'");
        if($hapus) echo "<script>window.location='data_user.php';</script>";
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-success"><i class="bi bi-person-badge"></i> Data User (Guru & Admin)</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddUser">
            <i class="bi bi-plus-circle"></i> Tambah User
        </button>
    </div>

    <div class="card card-custom bg-white">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Status Walikelas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        // Query JOIN: Tampilkan data user + Nama Kelas jika dia Walikelas
                        $query = mysqli_query($koneksi, "
                            SELECT users.*, kelas.nama_kelas 
                            FROM users 
                            LEFT JOIN kelas ON users.id_user = kelas.id_walikelas 
                            ORDER BY users.role ASC, users.nama_lengkap ASC
                        ");
                        
                        while($row = mysqli_fetch_assoc($query)) :
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td class="fw-bold"><?php echo $row['nama_lengkap']; ?></td>
                            <td><?php echo $row['username']; ?></td>
                            <td>
                                <?php 
                                if($row['role'] == 'admin'){
                                    echo '<span class="badge bg-danger">Administrator</span>';
                                } else {
                                    echo '<span class="badge bg-success">Guru / Walikelas</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php 
                                if($row['nama_kelas']){
                                    echo '<span class="badge bg-info text-dark"><i class="bi bi-check-circle"></i> Mengampu Kelas '.$row['nama_kelas'].'</span>';
                                } else {
                                    if($row['role'] == 'walikelas'){
                                        echo '<span class="text-muted small fst-italic">- Belum ada kelas -</span>';
                                    } else {
                                        echo '<span class="text-muted small">-</span>';
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning text-white btn-edit" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalEditUser"
                                    data-id="<?php echo $row['id_user']; ?>"
                                    data-nama="<?php echo $row['nama_lengkap']; ?>"
                                    data-user="<?php echo $row['username']; ?>"
                                    data-role="<?php echo $row['role']; ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                
                                <a href="data_user.php?hapus=<?php echo $row['id_user']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Yakin ingin menghapus user <?php echo $row['nama_lengkap']; ?>?')">
                                   <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAddUser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Tambah User Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" required placeholder="Contoh: Budi S.Pd">
                    </div>
                    <div class="mb-3">
                        <label>Username (Login)</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Role (Hak Akses)</label>
                        <select name="role" class="form-select" required>
                            <option value="walikelas">Walikelas / Guru</option>
                            <option value="admin">Administrator</option>
                        </select>
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

<div class="modal fade" id="modalEditUser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">Edit Data User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_user" id="edit_id">
                    
                    <div class="mb-3">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" id="edit_nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" id="edit_user" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password Baru (Opsional)</label>
                        <input type="password" name="password" class="form-control" placeholder="Isi jika ingin mengganti password">
                        <small class="text-muted">Biarkan kosong jika tidak ingin mengubah password.</small>
                    </div>
                    <div class="mb-3">
                        <label>Role</label>
                        <select name="role" id="edit_role" class="form-select" required>
                            <option value="walikelas">Walikelas / Guru</option>
                            <option value="admin">Administrator</option>
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
    const editBtns = document.querySelectorAll('.btn-edit');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Isi data ke dalam modal edit
            document.getElementById('edit_id').value = this.getAttribute('data-id');
            document.getElementById('edit_nama').value = this.getAttribute('data-nama');
            document.getElementById('edit_user').value = this.getAttribute('data-user');
            document.getElementById('edit_role').value = this.getAttribute('data-role');
        });
    });
</script>

<?php include 'layout/footer.php'; ?>