<?php
session_start();
require 'db.php';

// Check if user is admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit;
}

$current_page = 'admin_dashboard';

$search_results = [];
$selected_student = null;
$message = '';

// Handle search
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
    $search_term = trim($_GET['search']);
    if (!empty($search_term)) {
        $stmt = $pdo->prepare('SELECT id, id_number, first_name, last_name, course, email FROM students WHERE id_number LIKE ? OR first_name LIKE ? OR last_name LIKE ? LIMIT 10');
        $search_param = '%' . $search_term . '%';
        $stmt->execute([$search_param, $search_param, $search_param]);
        $search_results = $stmt->fetchAll();
    }
}

// Handle student selection
if (isset($_GET['student_id'])) {
    $stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
    $stmt->execute([$_GET['student_id']]);
    $selected_student = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'record_sitin') {
    $student_id = $_POST['student_id'] ?? null;
    $purpose = trim($_POST['purpose'] ?? '');
    $lab = trim($_POST['lab'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $course_level = trim($_POST['course_level'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    if ($student_id && $purpose && $lab) {
        // Update student information if changed
        $stmt = $pdo->prepare('UPDATE students SET first_name = ?, last_name = ?, course = ?, course_level = ?, email = ?, address = ? WHERE id = ?');
        $stmt->execute([$first_name, $last_name, $course, $course_level, $email, $address, $student_id]);
        
        // Insert sit-in record
        $stmt = $pdo->prepare('INSERT INTO sitin_sessions (student_id, purpose, lab, entry_time) VALUES (?, ?, ?, NOW())');
        $stmt->execute([$student_id, $purpose, $lab]);
        
        $message = 'Student info updated and sit-in recorded successfully!';
        
        // Update sessions_left
        $stmt = $pdo->prepare('SELECT sessions_left FROM students WHERE id = ?');
        $stmt->execute([$student_id]);
        $student_data = $stmt->fetch();
        $new_sessions = max(0, ($student_data['sessions_left'] ?? 30) - 1);
        
        $stmt = $pdo->prepare('UPDATE students SET sessions_left = ? WHERE id = ?');
        $stmt->execute([$new_sessions, $student_id]);
        
        // Refresh student data
        $stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
        $stmt->execute([$student_id]);
        $selected_student = $stmt->fetch();
    } else {
        $message = 'Please select both a Purpose and a Lab.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | CCS Sit-in Monitoring</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="js/utils.js"></script>
    <script src="js/admin-dashboard.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-[Inter]">

<?php include 'admin_navbar.php'; ?>

<main class="max-w-7xl mx-auto px-5 py-10">
    <!-- Page Title -->
    <h1 class="text-3xl font-bold text-slate-900 mb-8">Manage Student Sit-ins</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Panel: Search -->
        <div class="lg:col-span-1">
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900 mb-4">🔍 Search Student</h2>
                
                <form method="GET" class="mb-6">
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="ID, Name..." 
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#003366]"
                        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                    >
                    <button type="submit" class="w-full mt-3 px-4 py-2.5 bg-[#003366] text-white font-semibold rounded-lg hover:bg-[#004b93] transition">
                        Search
                    </button>
                </form>

                <!-- Search Results -->
                <?php if (!empty($search_results)): ?>
                    <div class="border-t border-slate-200 pt-4">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Results (<?= count($search_results) ?>)</p>
                        <div class="space-y-2 max-h-96 overflow-y-auto">
                            <?php foreach ($search_results as $student): ?>
                                <a 
                                    href="?search=<?= urlencode($_GET['search'] ?? '') ?>&student_id=<?= $student['id'] ?>"
                                    class="block p-3 bg-slate-50 hover:bg-blue-50 rounded-lg border border-slate-200 transition"
                                >
                                    <p class="font-semibold text-sm text-slate-900">
                                        <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        ID: <?= htmlspecialchars($student['id_number']) ?> | <?= htmlspecialchars($student['course']) ?>
                                    </p>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Panel: Student Details & Sit-in Form -->
        <div class="lg:col-span-2">
            <?php if ($selected_student): ?>
                <!-- Student Information Card -->
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm mb-6">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">👤 Student Information</h2>
                    
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Full Name</p>
                            <p class="font-semibold text-slate-800">
                                <?= htmlspecialchars($selected_student['first_name'] . ' ' . $selected_student['last_name']) ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">ID Number</p>
                            <p class="font-semibold text-slate-800">
                                <?= htmlspecialchars($selected_student['id_number']) ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Course</p>
                            <p class="font-semibold text-slate-800">
                                <?= htmlspecialchars($selected_student['course']) ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Year Level</p>
                            <p class="font-semibold text-slate-800">
                                <?= htmlspecialchars($selected_student['course_level'] ?? '—') ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Email</p>
                            <p class="font-semibold text-slate-800 text-sm break-all">
                                <?= htmlspecialchars($selected_student['email']) ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Address</p>
                            <p class="font-semibold text-slate-800 text-sm">
                                <?= htmlspecialchars($selected_student['address'] ?? '—') ?>
                            </p>
                        </div>
                    </div>

                    <div class="bg-blue-50 border-l-4 border-[#003366] p-4 rounded">
                        <p class="text-sm font-semibold text-slate-800">
                            Sessions Remaining: <span class="text-2xl text-[#003366]"><?= (int)($selected_student['sessions_left'] ?? 30) ?></span>
                        </p>
                    </div>
                </div>

                <!-- Sit-in Form -->
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">📝 Sit-In Form</h2>
                    
                    <?php if ($message): ?>
                        <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 rounded">
                            <p class="text-sm font-semibold text-green-800"><?= htmlspecialchars($message) ?></p>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="sitin_form">
                        <input type="hidden" name="action" value="record_sitin">
                        <input type="hidden" name="student_id" value="<?= $selected_student['id'] ?>">
                        
                        <!-- Student Info (Editable) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                            <!-- ID Number (Read-only) -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2">ID Number</label>
                                <input 
                                    type="text" 
                                    value="<?= htmlspecialchars($selected_student['id_number']) ?>"
                                    disabled
                                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg bg-slate-100 text-slate-700 cursor-not-allowed"
                                >
                            </div>

                            <!-- Last Name -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2">Last Name</label>
                                <input 
                                    type="text" 
                                    name="last_name"
                                    value="<?= htmlspecialchars($selected_student['last_name']) ?>"
                                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#003366] focus:border-transparent transition"
                                >
                            </div>

                            <!-- First Name -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2">First Name</label>
                                <input 
                                    type="text" 
                                    name="first_name"
                                    value="<?= htmlspecialchars($selected_student['first_name']) ?>"
                                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#003366] focus:border-transparent transition"
                                >
                            </div>

                            <!-- Remaining Sessions -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2">Remaining Sessions</label>
                                <div class="flex items-center gap-2 px-4 py-2.5 border border-slate-300 rounded-lg bg-blue-50">
                                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></path></svg>
                                    <span class="text-lg font-bold text-[#003366]"><?= (int)($selected_student['sessions_left'] ?? 30) ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Student Info (Editable) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            <!-- Course -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2">Course</label>
                                <input 
                                    type="text" 
                                    name="course"
                                    value="<?= htmlspecialchars($selected_student['course']) ?>"
                                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#003366] focus:border-transparent transition"
                                >
                            </div>

                            <!-- Course Level -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2">Year Level</label>
                                <input 
                                    type="text" 
                                    name="course_level"
                                    value="<?= htmlspecialchars($selected_student['course_level'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#003366] focus:border-transparent transition"
                                >
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2">Email</label>
                                <input 
                                    type="email" 
                                    name="email"
                                    value="<?= htmlspecialchars($selected_student['email']) ?>"
                                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#003366] focus:border-transparent transition"
                                >
                            </div>

                            <!-- Address -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2">Address</label>
                                <input 
                                    type="text" 
                                    name="address"
                                    value="<?= htmlspecialchars($selected_student['address'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#003366] focus:border-transparent transition"
                                >
                            </div>
                        </div>

                        <!-- Purpose & Lab Selection -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            <!-- Purpose -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2">
                                    Purpose <span class="text-red-500">*</span>
                                </label>
                                <select 
                                    name="purpose" 
                                    required
                                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#003366] focus:border-transparent transition"
                                    onchange="validateForm()"
                                >
                                    <option value="">Select Purpose</option>
                                    <option value="C Programming">C Programming</option>
                                    <option value="Java">Java</option>
                                    <option value="Python">Python</option>
                                    <option value="C++">C++</option>
                                    <option value="Database (SQL)">Database (SQL)</option>
                                    <option value="Web Development">Web Development</option>
                                    <option value="Mobile Development">Mobile Development</option>
                                    <option value="Machine Learning">Machine Learning</option>
                                    <option value="Data Science">Data Science</option>
                                    <option value="Networking">Networking</option>
                                    <option value="System Administration">System Administration</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <!-- Lab -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2">
                                    Lab <span class="text-red-500">*</span>
                                </label>
                                <select 
                                    name="lab" 
                                    required
                                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#003366] focus:border-transparent transition"
                                    onchange="validateForm()"
                                >
                                    <option value="">Select Lab</option>
                                    <option value="524">524</option>
                                    <option value="526">526</option>
                                    <option value="528">528</option>
                                    <option value="530">530</option>
                                    <option value="542">542</option>
                                    <option value="544">544</option>
                                    <option value="546">546</option>
                                    <option value="548">548</option>
                                    <option value="550">550</option>
                                    <option value="552">552</option>
                                </select>
                            </div>
                        </div>

                        <!-- Validation Message -->
                        <div id="validation_message" class="mb-6 p-3 bg-amber-50 border-l-4 border-amber-400 rounded text-sm text-amber-800 font-medium hidden">
                            Please select both a Purpose and a Lab.
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-3">
                            <button 
                                type="submit" 
                                id="submit_btn"
                                class="flex-1 px-4 py-2.5 bg-[#003366] text-white font-semibold rounded-lg hover:bg-[#004b93] transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                                disabled
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Confirm Sit-In
                            </button>
                            <a href="admin_dashboard.php" class="flex-1 px-4 py-2.5 bg-slate-200 text-slate-700 font-semibold rounded-lg hover:bg-slate-300 transition text-center">
                                Clear
                            </a>
                        </div>
                    </form>
                </div>

            <?php else: ?>
                <!-- Empty State -->
                <div class="bg-white border border-slate-200 rounded-2xl p-12 shadow-sm text-center">
                    <p class="text-lg text-slate-500 mb-2">👈 Search and select a student to begin</p>
                    <p class="text-sm text-slate-400">Use the search panel on the left to find a student by ID or name</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</main>

<!-- Toast Notification -->
<div id="toast" class="fixed bottom-6 right-6 z-50 hidden items-center gap-3 bg-green-500 text-white px-5 py-3 rounded-xl shadow-lg font-semibold text-sm">
    <span id="toast-msg"></span>
</div>

<?php if ($message): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    showToast('<?= htmlspecialchars($message, ENT_QUOTES) ?>');
});
</script>
<?php endif; ?>

</body>
</html>
