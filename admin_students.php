<?php
session_start();
require 'db.php';

// Check if user is admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit;
}

$search = $_GET['search'] ?? '';
$course_filter = $_GET['course'] ?? '';

// Fetch all students with filters
$query = 'SELECT * FROM students WHERE 1=1';
$params = [];

if ($search) {
    $query .= ' AND (first_name LIKE ? OR last_name LIKE ? OR id_number LIKE ? OR email LIKE ?)';
    $search_term = '%' . $search . '%';
    $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
}

if ($course_filter) {
    $query .= ' AND course = ?';
    $params[] = $course_filter;
}

$query .= ' ORDER BY last_name ASC, first_name ASC';

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();
$student_count = count($students);

// Get unique courses for filter
$courses_stmt = $pdo->query('SELECT DISTINCT course FROM students ORDER BY course');
$courses = $courses_stmt->fetchAll(PDO::FETCH_COLUMN);

// Calculate stats
$total_students = $pdo->query('SELECT COUNT(*) as count FROM students')->fetch()['count'];
$active_sitins = $pdo->query('SELECT COUNT(DISTINCT student_id) as count FROM sitin_sessions')->fetch()['count'];
$avg_sessions = $pdo->query('SELECT AVG(sessions_left) as avg FROM students')->fetch()['avg'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List | CCS Admin</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-[Inter]">

<!-- Navigation Bar -->
<nav class="bg-[#003366] text-white shadow-lg sticky top-0 z-50">
    <div class="px-[5%] py-4">
        <div class="flex items-center justify-between mb-3">
            <h1 class="font-bold text-xl">CCS Admin</h1>
            <a href="admin_logout.php" class="text-sm bg-red-600 hover:bg-red-700 px-3 py-1.5 rounded transition">Logout</a>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="admin_home.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Home</a>
            <a href="admin_dashboard.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Record Sit-in</a>
            <a href="admin_current_sitin.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Current Sit-ins</a>
            <a href="admin_history.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Sit-in History</a>
            <a href="admin_announcements.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Announcements</a>
            <a href="admin_feedback.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Feedback</a>
            <a href="admin_reservations.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Reservations</a>
            <a href="admin_reports.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Reports</a>
            <a href="admin_students.php" class="px-3 py-2 text-sm font-semibold bg-white/20 rounded hover:bg-white/30 transition border-b-2 border-white">Students</a>
        </div>
    </div>
</nav>

<main class="max-w-full mx-auto px-5 py-10">
    <!-- Page Title -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900 mb-2">Student Management</h1>
        <p class="text-slate-600">View and manage all registered students</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Total Students</p>
                    <p class="text-3xl font-bold text-[#003366]"><?= $total_students ?></p>
                </div>
                <div class="text-4xl">👥</div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Active Students</p>
                    <p class="text-3xl font-bold text-blue-600"><?= $active_sitins ?></p>
                </div>
                <div class="text-4xl">✅</div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Avg Sessions Left</p>
                    <p class="text-3xl font-bold text-green-600"><?= round($avg_sessions ?? 0) ?></p>
                </div>
                <div class="text-4xl">📊</div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Courses</p>
                    <p class="text-3xl font-bold text-purple-600"><?= count($courses) ?></p>
                </div>
                <div class="text-4xl">📚</div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Search by Name, ID, or Email</label>
                <input 
                    type="text" 
                    name="search" 
                    value="<?= htmlspecialchars($search) ?>"
                    placeholder="Search students..."
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#003366]"
                >
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Filter by Course</label>
                <select name="course" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#003366]">
                    <option value="">All Courses</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= htmlspecialchars($course) ?>" <?= $course_filter === $course ? 'selected' : '' ?>>
                            <?= htmlspecialchars($course) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-[#003366] text-white font-semibold rounded-lg hover:bg-[#004b93] transition">
                    Search
                </button>
                <a href="admin_students.php" class="px-4 py-2 bg-slate-200 text-slate-700 font-semibold rounded-lg hover:bg-slate-300 transition">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Students Table -->
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-x-auto">
        <?php if (count($students) > 0): ?>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-200">
                        <th class="px-6 py-3 text-left font-semibold text-slate-700">#</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-700">ID Number</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-700">Full Name</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-700">Course</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-700">Year Level</th>
                        <th class="px-6 py-3 text-center font-semibold text-slate-700">Sessions Left</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-700">Email</th>
                        <th class="px-6 py-3 text-center font-semibold text-slate-700">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $index => $student): 
                        $is_active = $pdo->prepare('SELECT COUNT(*) as count FROM sitin_sessions WHERE student_id = ?');
                        $is_active->execute([$student['id']]);
                        $has_active = $is_active->fetch()['count'] > 0;
                    ?>
                        <tr class="border-b border-slate-200 hover:bg-slate-50 transition">
                            <td class="px-6 py-3 font-semibold text-slate-900"><?= $index + 1 ?></td>
                            <td class="px-6 py-3 font-semibold text-slate-900"><?= htmlspecialchars($student['id_number']) ?></td>
                            <td class="px-6 py-3 text-slate-700">
                                <p class="font-semibold"><?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name']) ?></p>
                                <p class="text-xs text-slate-500"><?= htmlspecialchars($student['middle_name'] ?? '') ?></p>
                            </td>
                            <td class="px-6 py-3 text-slate-700"><?= htmlspecialchars($student['course']) ?></td>
                            <td class="px-6 py-3 text-slate-700"><?= htmlspecialchars($student['course_level'] ?? '—') ?></td>
                            <td class="px-6 py-3 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold <?= ($student['sessions_left'] ?? 30) > 15 ? 'bg-green-100 text-green-800' : (($student['sessions_left'] ?? 30) > 5 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                    <?= (int)($student['sessions_left'] ?? 30) ?>
                                </span>
                            </td>
                            <td class="px-6 py-3 text-slate-700 text-sm break-all"><?= htmlspecialchars($student['email']) ?></td>
                            <td class="px-6 py-3 text-center">
                                <?php if ($has_active): ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                        Active
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                        Inactive
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="p-12 text-center">
                <div class="text-5xl mb-3">🔍</div>
                <p class="text-lg text-slate-500 mb-2">No students found</p>
                <p class="text-sm text-slate-400">Try adjusting your search or filter criteria</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination Info -->
    <?php if (count($students) > 0): ?>
        <div class="mt-4 text-sm text-slate-600">
            <p>Showing <?= count($students) ?> of <?= $total_students ?> students</p>
        </div>
    <?php endif; ?>

</main>

</body>
</html>
