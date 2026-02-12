<?php
require_once '../config.php';
require_once '../includes/auth_check.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$message = '';

// Handle Delete
if ($action == 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    header('Location: students.php?msg=deleted');
    exit;
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $father_name = $_POST['father_name'];
    $age = $_POST['age'];
    $institute = $_POST['institute'];
    $qualification = $_POST['qualification'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $class_id = $_POST['class_id'];
    
    $photo = $_POST['existing_photo'] ?? '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $target_dir = "../uploads/";
        $file_ext = pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION);
        $photo = time() . "_" . rand(1000, 9999) . "." . $file_ext;
        move_uploaded_file($_FILES["photo"]["tmp_name"], $target_dir . $photo);
    }

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $stmt = $pdo->prepare("UPDATE students SET name=?, father_name=?, age=?, institute=?, qualification=?, email=?, mobile=?, photo=?, class_id=? WHERE id=?");
        $stmt->execute([$name, $father_name, $age, $institute, $qualification, $email, $mobile, $photo, $class_id, $_POST['id']]);
        $message = "Student profile updated successfully!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO students (name, father_name, age, institute, qualification, email, mobile, photo, class_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $father_name, $age, $institute, $qualification, $email, $mobile, $photo, $class_id]);
        $message = "New student enrolled successfully!";
    }
    $action = 'list';
}

include '../includes/header.php';
?>

<style>
    /* Consistent Stylish Elements */
    .stylish-header-bar {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 20px 35px;
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }

    .black-title { 
        color: #000; 
        font-weight: 800; 
        font-size: 2.2rem; 
        letter-spacing: -1.8px; 
        margin: 0; 
    }

    .main-card { 
        background: #fff; 
        border-radius: 28px; 
        overflow: hidden; 
        box-shadow: 0 15px 40px rgba(0,0,0,0.4); 
        border: none;
    }

    /* Table Styling */
    .table thead th { 
        background: #000; 
        color: #fff; 
        padding: 18px; 
        border: none; 
        font-size: 0.75rem; 
        text-transform: uppercase; 
        letter-spacing: 1px;
    }

    .student-avatar {
        width: 45px;
        height: 45px;
        object-fit: cover;
        border: 2px solid #00d4ff;
        padding: 2px;
    }

    /* Buttons */
    .btn-add-new {
        background: #00d4ff;
        color: #000;
        font-weight: 700;
        border-radius: 50px;
        padding: 10px 25px;
        border: none;
        transition: 0.3s;
    }
    .btn-add-new:hover { background: #000; color: #fff; transform: translateY(-2px); }

    .btn-action-edit { background: #f0f0f0; color: #000; border-radius: 10px; transition: 0.2s; }
    .btn-action-edit:hover { background: #00d4ff; color: #000; }

    .btn-action-delete { background: #fff1f0; color: #f5222d; border-radius: 10px; transition: 0.2s; }
    .btn-action-delete:hover { background: #f5222d; color: #fff; }

    /* Form Styling */
    .form-label { font-weight: 700; font-size: 0.85rem; color: #444; text-transform: uppercase; margin-bottom: 8px; }
    .form-control, .form-select {
        border-radius: 12px;
        padding: 12px;
        border: 1px solid #e0e0e0;
        background: #fcfcfc;
    }
    .form-control:focus { box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.2); border-color: #00d4ff; }
</style>

<div class="container py-4">
    <?php if ($message): ?>
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if ($action == 'list'): ?>
        <div class="stylish-header-bar">
            <div>
                <h2 class="black-title">Student Directory</h2>
                <p class="text-muted small mb-0">Manage enrollments and student profiles</p>
            </div>
            <a href="students.php?action=add" class="btn btn-add-new shadow-sm">
                <i class="bi bi-plus-lg me-2"></i> Add New Student
            </a>
        </div>

        <div class="main-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Profile</th>
                            <th>Student Name</th>
                            <th>Class / Track</th>
                            <th>Contact</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody style="color: #000;">
                        <?php
                        $students = $pdo->query("SELECT s.*, c.class_name FROM students s LEFT JOIN classes c ON s.class_id = c.id ORDER BY s.id DESC")->fetchAll();
                        foreach ($students as $s):
                        ?>
                        <tr>
                            <td class="ps-4">
                                <?php if ($s['photo']): ?>
                                    <img src="../uploads/<?php echo $s['photo']; ?>" class="rounded-circle student-avatar">
                                <?php else: ?>
                                    <div class="bg-light rounded-circle student-avatar d-flex align-items-center justify-content-center text-muted fw-bold">?</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($s['name']); ?></div>
                                <small class="text-muted">ID: #STU-<?php echo $s['id']; ?></small>
                            </td>
                            <td><span class="badge bg-light text-dark border px-3"><?php echo htmlspecialchars($s['class_name']); ?></span></td>
                            <td>
                                <div class="small"><i class="bi bi-telephone me-1"></i> <?php echo htmlspecialchars($s['mobile']); ?></div>
                                <div class="small text-muted"><i class="bi bi-envelope me-1"></i> <?php echo htmlspecialchars($s['email']); ?></div>
                            </td>
                            <td class="text-center">
                                <a href="students.php?action=edit&id=<?php echo $s['id']; ?>" class="btn btn-sm btn-action-edit me-1" title="Edit Profile">
                                    <i class="bi bi-pencil-square p-1"></i>
                                </a>
                                <a href="students.php?action=delete&id=<?php echo $s['id']; ?>" class="btn btn-sm btn-action-delete" onclick="return confirm('Delete this student permanently?')" title="Remove">
                                    <i class="bi bi-trash3 p-1"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($students)): ?>
                            <tr><td colspan="5" class="text-center py-5 text-muted">No students enrolled yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif ($action == 'add' || $action == 'edit'): 
        $s = ['id'=>'', 'name'=>'', 'father_name'=>'', 'age'=>'', 'institute'=>'', 'qualification'=>'', 'email'=>'', 'mobile'=>'', 'photo'=>'', 'class_id'=>''];
        if ($action == 'edit' && isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $s = $stmt->fetch();
        }
        $classes = $pdo->query("SELECT * FROM classes")->fetchAll();
    ?>
        <div class="stylish-header-bar">
            <h2 class="black-title"><?php echo $action == 'add' ? 'Enroll Student' : 'Edit Profile'; ?></h2>
            <a href="students.php" class="btn btn-outline-dark rounded-pill px-4">Cancel</a>
        </div>

        <div class="main-card p-4">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                <input type="hidden" name="existing_photo" value="<?php echo $s['photo']; ?>">
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($s['name']); ?>" required placeholder="e.g. John Doe">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Father's Name</label>
                        <input type="text" name="father_name" class="form-control" value="<?php echo htmlspecialchars($s['father_name']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Age</label>
                        <input type="number" name="age" class="form-control" value="<?php echo $s['age']; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Academic Track / Class</label>
                        <select name="class_id" class="form-select" required>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo $s['class_id'] == $c['id'] ? 'selected' : ''; ?>><?php echo $c['class_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Profile Image</label>
                        <input type="file" name="photo" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($s['email']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mobile Number</label>
                        <input type="text" name="mobile" class="form-control" value="<?php echo htmlspecialchars($s['mobile']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Current Institute</label>
                        <input type="text" name="institute" class="form-control" value="<?php echo htmlspecialchars($s['institute']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Qualification</label>
                        <input type="text" name="qualification" class="form-control" value="<?php echo htmlspecialchars($s['qualification']); ?>" required>
                    </div>
                </div>

                <div class="mt-5 border-top pt-4 text-end">
                    <button type="submit" class="btn btn-add-new px-5">Save Student Profile</button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
