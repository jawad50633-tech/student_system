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
        // Update
        $stmt = $pdo->prepare("UPDATE students SET name=?, father_name=?, age=?, institute=?, qualification=?, email=?, mobile=?, photo=?, class_id=? WHERE id=?");
        $stmt->execute([$name, $father_name, $age, $institute, $qualification, $email, $mobile, $photo, $class_id, $_POST['id']]);
        $message = "Student updated successfully!";
    } else {
        // Add
        $stmt = $pdo->prepare("INSERT INTO students (name, father_name, age, institute, qualification, email, mobile, photo, class_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $father_name, $age, $institute, $qualification, $email, $mobile, $photo, $class_id]);
        $message = "Student added successfully!";
    }
    $action = 'list';
}

include '../includes/header.php';
?>

<div class="container-fluid">
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if ($action == 'list'): ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Student List</h3>
            <a href="students.php?action=add" class="btn btn-primary">Add New Student</a>
        </div>
        <div class="card shadow">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Mobile</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $students = $pdo->query("SELECT s.*, c.class_name FROM students s LEFT JOIN classes c ON s.class_id = c.id ORDER BY s.id DESC")->fetchAll();
                        foreach ($students as $s):
                        ?>
                        <tr>
                            <td>
                                <?php if ($s['photo']): ?>
                                    <img src="../uploads/<?php echo $s['photo']; ?>" width="40" height="40" class="rounded-circle">
                                <?php else: ?>
                                    <div class="bg-secondary rounded-circle text-white d-flex align-items-center justify-content-center" style="width:40px; height:40px;">?</div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($s['name']); ?></td>
                            <td><?php echo htmlspecialchars($s['class_name']); ?></td>
                            <td><?php echo htmlspecialchars($s['mobile']); ?></td>
                            <td>
                                <a href="students.php?action=edit&id=<?php echo $s['id']; ?>" class="btn btn-sm btn-info text-white"><i class="bi bi-pencil"></i></a>
                                <a href="students.php?action=delete&id=<?php echo $s['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
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
        <h3><?php echo $action == 'add' ? 'Add New Student' : 'Edit Student'; ?></h3>
        <div class="card shadow">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $s['id']; ?>">
                    <input type="hidden" name="existing_photo" value="<?php echo $s['photo']; ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Student Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($s['name']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Father's Name</label>
                            <input type="text" name="father_name" class="form-control" value="<?php echo htmlspecialchars($s['father_name']); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Age</label>
                            <input type="number" name="age" class="form-control" value="<?php echo $s['age']; ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Institute</label>
                            <input type="text" name="institute" class="form-control" value="<?php echo htmlspecialchars($s['institute']); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Qualification</label>
                            <input type="text" name="qualification" class="form-control" value="<?php echo htmlspecialchars($s['qualification']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($s['email']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mobile</label>
                            <input type="text" name="mobile" class="form-control" value="<?php echo htmlspecialchars($s['mobile']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Class</label>
                            <select name="class_id" class="form-select" required>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?php echo $c['id']; ?>" <?php echo $s['class_id'] == $c['id'] ? 'selected' : ''; ?>><?php echo $c['class_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Photo</label>
                            <input type="file" name="photo" class="form-control">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Student</button>
                    <a href="students.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
