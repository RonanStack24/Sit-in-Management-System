<?php
session_start();
require 'db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$toast_message = '';
$toast_type = '';
$edit_mode = isset($_GET['edit']) && $_GET['edit'] === '1';

// Fetch student data
$stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
$stmt->execute([$user_id]);
$student = $stmt->fetch();

if (!$student) {
    header('Location: logout.php');
    exit;
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $first_name   = trim($_POST['first_name'] ?? '');
    $middle_name  = trim($_POST['middle_name'] ?? '');
    $last_name    = trim($_POST['last_name'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $course       = trim($_POST['course'] ?? '');
    $course_level = trim($_POST['course_level'] ?? '');
    $address      = trim($_POST['address'] ?? '');

    if (!$first_name || !$last_name || !$email || !$course) {
        $toast_message = 'Please fill in all required fields.';
        $toast_type = 'error';
    } else {
        $stmt = $pdo->prepare(
            'UPDATE students SET first_name = ?, middle_name = ?, last_name = ?, email = ?, course = ?, course_level = ?, address = ? WHERE id = ?'
        );
        $stmt->execute([$first_name, $middle_name, $last_name, $email, $course, $course_level, $address, $user_id]);
        
        $toast_message = 'Profile updated successfully!';
        $toast_type = 'success';
        
        // Refresh student data
        $stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
        $stmt->execute([$user_id]);
        $student = $stmt->fetch();
        
        // Redirect to remove edit mode
        header('Location: profile.php?toast=success');
        exit;
    }
}

// Handle photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_photo') {
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['profile_photo']['tmp_name'];
        $file_name = $_FILES['profile_photo']['name'];
        $file_size = $_FILES['profile_photo']['size'];
        $file_type = $_FILES['profile_photo']['type'];
        
        // Validate file
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file_type, $allowed_types)) {
            $toast_message = 'Only JPG, PNG, and GIF files are allowed.';
            $toast_type = 'error';
        } elseif ($file_size > $max_size) {
            $toast_message = 'File size must be less than 5MB.';
            $toast_type = 'error';
        } else {
            // Create upload directory if it doesn't exist
            $upload_dir = 'uploads/profiles/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_filename = 'student_' . $user_id . '_' . time() . '.' . $ext;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Remove old photo
                if (!empty($student['profile_photo']) && file_exists($student['profile_photo'])) {
                    unlink($student['profile_photo']);
                }
                
                // Update database
                $stmt = $pdo->prepare('UPDATE students SET profile_photo = ? WHERE id = ?');
                $stmt->execute([$upload_path, $user_id]);
                
                $toast_message = 'Photo uploaded successfully!';
                $toast_type = 'success';
                
                // Refresh student data
                $stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
                $stmt->execute([$user_id]);
                $student = $stmt->fetch();
            } else {
                $toast_message = 'Failed to upload photo. Please try again.';
                $toast_type = 'error';
            }
        }
    } else {
        $toast_message = 'Please select a photo to upload.';
        $toast_type = 'error';
    }
}

// Check for toast query param
if (isset($_GET['toast'])) {
    if ($_GET['toast'] === 'success') {
        $toast_message = 'Changes saved successfully!';
        $toast_type = 'success';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | CCS Sit-in Monitoring</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.6/tailwind.min.css">
    <script src="js/utils.js"></script>
</head>
<body class="bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 font-[Inter]">

<?php 
    $current_page = 'profile';
    include 'navbar.php'; 
?>

<main class="max-w-6xl mx-auto px-5 py-8">
    <!-- Breadcrumb -->
    <div class="mb-8">
        <a href="dashboard.php" class="text-sm text-slate-500 hover:text-slate-700">Dashboard</a>
        <span class="text-sm text-slate-400 mx-2">›</span>
        <span class="text-sm font-semibold text-slate-700">My Profile</span>
    </div>

    <!-- Page Title -->
    <h1 class="text-3xl font-bold text-slate-900 mb-8">My Profile</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Sidebar: Profile Card (View Only) -->
        <div class="lg:col-span-1">
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm sticky top-24">
                <!-- Profile Picture -->
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-32 h-32 rounded-full bg-gradient-to-br from-[#003366] to-[#004b93] overflow-hidden mb-4">
                        <?php if (!empty($student['profile_photo']) && file_exists($student['profile_photo'])): ?>
                            <img src="<?= htmlspecialchars($student['profile_photo']) ?>" alt="Profile" class="w-full h-full object-cover">
                        <?php else: ?>
                            <span class="text-4xl font-bold text-white">
                                <?= strtoupper(substr($student['first_name'], 0, 1)) . strtoupper(substr($student['last_name'], 0, 1)) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs text-slate-500 mb-3">Profile Picture</p>
                    <form method="POST" enctype="multipart/form-data" id="photoForm">
                        <input type="hidden" name="action" value="upload_photo">
                        <input type="file" id="photoInput" name="profile_photo" accept="image/*" class="hidden" onchange="document.getElementById('photoForm').submit();">
                        <button type="button" onclick="document.getElementById('photoInput').click();" class="inline-block px-4 py-2 bg-[#003366] text-white text-sm font-semibold rounded-lg hover:bg-[#004b93] transition">
                            Change Photo
                        </button>
                    </form>
                </div>

                <!-- Student Info Display -->
                <div class="border-t border-slate-200 pt-6">
                    <div class="mb-5">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Full Name</p>
                        <p class="text-lg font-semibold text-slate-800">
                            <?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name']) ?>
                        </p>
                    </div>
                    <div class="mb-5">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">ID Number</p>
                        <p class="text-lg font-semibold text-slate-800">
                            <?= htmlspecialchars($student['id_number']) ?>
                        </p>
                    </div>
                    <div class="mb-5">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Course</p>
                        <p class="text-lg font-semibold text-slate-800">
                            <?= htmlspecialchars($student['course']) ?>
                        </p>
                    </div>
                    <div class="mb-5">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Year Level</p>
                        <p class="text-lg font-semibold text-slate-800">
                            <?= htmlspecialchars($student['course_level'] ?? '—') ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Email</p>
                        <p class="text-sm font-semibold text-slate-800 break-all">
                            <?= htmlspecialchars($student['email']) ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Remaining Sessions Card -->
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm mt-6">
                <div class="text-center">
                    <div class="flex items-center justify-center gap-2 mb-3">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Sessions Remaining</p>
                        <button onclick="location.reload();" class="text-xs text-indigo-600 hover:text-indigo-700 font-semibold hover:underline" title="Refresh sessions count">
                            🔄
                        </button>
                    </div>
                    <div class="text-5xl font-bold text-[#003366] mb-2">
                        <?= (int)($student['sessions_left'] ?? 30) ?>
                    </div>
                    <p class="text-sm text-slate-600">out of 30 sessions</p>
                    <div class="mt-4 w-full bg-slate-200 rounded-full h-2">
                        <div class="bg-[#003366] h-2 rounded-full" style="width: <?= ((int)($student['sessions_left'] ?? 30) / 30) * 100 ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Content Area: Tabs -->
        <div class="lg:col-span-2">
            <!-- Tab Navigation -->
            <div class="bg-white border border-slate-200 rounded-t-2xl shadow-sm border-b-0">
                <div class="flex gap-6 px-6 pt-6">
                    <h3 class="text-lg font-semibold text-[#003366]">📋 Update Your Information</h3>
                </div>
            </div>

            <!-- Tab 1: My Information -->
            <div id="tab-info" class="tab-content active bg-white border border-slate-200 rounded-b-2xl shadow-sm p-6">
                <form method="POST" id="updateForm">
                    <input type="hidden" name="action" value="update">
                    
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-slate-900 mb-6">Update Your Information</h3>
                    </div>

                    <!-- Form Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                        <!-- ID Number (Read-only) -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                ID Number <span class="text-red-500">*</span>
                            </label>
                            <input type="text" value="<?= htmlspecialchars($student['id_number']) ?>" disabled class="w-full px-4 py-2.5 border border-slate-300 rounded-lg bg-slate-100 text-slate-700 cursor-not-allowed" readonly>
                            <p class="text-xs text-slate-500 mt-1">Cannot be changed</p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" autocomplete="email" required class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#003366] focus:border-transparent transition">
                        </div>

                        <!-- First Name -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                First Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="first_name" value="<?= htmlspecialchars($student['first_name']) ?>" required class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#003366] focus:border-transparent transition">
                        </div>

                        <!-- Middle Name -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Middle Name
                            </label>
                            <input type="text" name="middle_name" value="<?= htmlspecialchars($student['middle_name'] ?? '') ?>" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#003366] focus:border-transparent transition">
                        </div>

                        <!-- Last Name -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Last Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="last_name" value="<?= htmlspecialchars($student['last_name']) ?>" required class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#003366] focus:border-transparent transition">
                        </div>

                        <!-- Course -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Course <span class="text-red-500">*</span>
                            </label>
                            <select name="course" required class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#003366] focus:border-transparent transition">
                                <option value="">Select a course</option>
                                <option value="BSIT" <?= $student['course'] === 'BSIT' ? 'selected' : '' ?>>BSIT</option>
                                <option value="BSCS" <?= $student['course'] === 'BSCS' ? 'selected' : '' ?>>BSCS</option>
                                <option value="BSCS-AI" <?= $student['course'] === 'BSCS-AI' ? 'selected' : '' ?>>BSCS-AI</option>
                                <option value="BSCpE" <?= $student['course'] === 'BSCpE' ? 'selected' : '' ?>>BSCpE</option>
                                <option value="BSMT" <?= $student['course'] === 'BSMT' ? 'selected' : '' ?>>BSMT</option>
                                <option value="BSCE" <?= $student['course'] === 'BSCE' ? 'selected' : '' ?>>BSCE</option>
                                <option value="BSME" <?= $student['course'] === 'BSME' ? 'selected' : '' ?>>BSME</option>
                                <option value="BSEE" <?= $student['course'] === 'BSEE' ? 'selected' : '' ?>>BSEE</option>
                                <option value="BSIE" <?= $student['course'] === 'BSIE' ? 'selected' : '' ?>>BSIE</option>
                                <option value="BSNAME" <?= $student['course'] === 'BSNAME' ? 'selected' : '' ?>>BSNAME</option>
                                <option value="BEEd" <?= $student['course'] === 'BEEd' ? 'selected' : '' ?>>BEEd</option>
                                <option value="BS Crim" <?= $student['course'] === 'BS Crim' ? 'selected' : '' ?>>BS Crim</option>
                                <option value="BSA" <?= $student['course'] === 'BSA' ? 'selected' : '' ?>>BSA</option>
                                <option value="BSBA" <?= $student['course'] === 'BSBA' ? 'selected' : '' ?>>BSBA</option>
                                <option value="BSCA" <?= $student['course'] === 'BSCA' ? 'selected' : '' ?>>BSCA</option>
                                <option value="BSHM" <?= $student['course'] === 'BSHM' ? 'selected' : '' ?>>BSHM</option>
                                <option value="AB PolSci" <?= $student['course'] === 'AB PolSci' ? 'selected' : '' ?>>AB PolSci</option>
                                <option value="AB English" <?= $student['course'] === 'AB English' ? 'selected' : '' ?>>AB English</option>
                                <option value="BSN" <?= $student['course'] === 'BSN' ? 'selected' : '' ?>>BSN</option>
                                <option value="BS Midwifery" <?= $student['course'] === 'BS Midwifery' ? 'selected' : '' ?>>BS Midwifery</option>
                                <option value="BSECE" <?= $student['course'] === 'BSECE' ? 'selected' : '' ?>>BSECE</option>
                                <option value="BSTM" <?= $student['course'] === 'BSTM' ? 'selected' : '' ?>>BSTM</option>
                            </select>
                        </div>

                        <!-- Year Level -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Year Level
                            </label>
                            <select name="course_level" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#003366] focus:border-transparent transition">
                                <option value="">Select a year</option>
                                <option value="1st Year" <?= $student['course_level'] === '1st Year' ? 'selected' : '' ?>>1st Year</option>
                                <option value="2nd Year" <?= $student['course_level'] === '2nd Year' ? 'selected' : '' ?>>2nd Year</option>
                                <option value="3rd Year" <?= $student['course_level'] === '3rd Year' ? 'selected' : '' ?>>3rd Year</option>
                                <option value="4th Year" <?= $student['course_level'] === '4th Year' ? 'selected' : '' ?>>4th Year</option>
                            </select>
                        </div>
                    </div>

                    <!-- Address (Full Width) -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Address
                        </label>
                        <textarea name="address" rows="3" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#003366] focus:border-transparent transition"><?= htmlspecialchars($student['address'] ?? '') ?></textarea>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-3 pt-4 border-t border-slate-200">
                        <button type="submit" class="px-6 py-2.5 bg-[#003366] text-white font-semibold rounded-lg hover:bg-[#004b93] transition">
                            Save Changes
                        </button>
                        <a href="profile.php" class="px-6 py-2.5 bg-slate-200 text-slate-700 font-semibold rounded-lg hover:bg-slate-300 transition">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>

        </div>
    </div>

</main>

<!-- Toast Notification -->
<div id="toast" class="fixed bottom-6 right-6 z-50 hidden items-center gap-3 px-5 py-3 rounded-xl shadow-lg font-semibold text-sm transition-all duration-300">
    <span id="toast-msg"></span>
</div>

<?php if ($toast_message): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    showToastWithType('<?= htmlspecialchars($toast_message, ENT_QUOTES) ?>', '<?= $toast_type ?>');
});
</script>
<?php endif; ?>

</body>
</html>
