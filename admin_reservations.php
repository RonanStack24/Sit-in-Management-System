<?php
session_start();
require 'db.php';

// Check if user is admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit;
}

$message = '';

// Handle approve/reject reservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = intval($_POST['id'] ?? 0);
    $action = trim($_POST['action'] ?? '');
    
    if ($action === 'approve' || $action === 'reject') {
        $status = $action === 'approve' ? 'Approved' : 'Rejected';
        try {
            $stmt = $pdo->prepare('UPDATE lab_reservations SET status = ? WHERE id = ?');
            $stmt->execute([$status, $id]);
            $message = 'Reservation ' . strtolower($status) . ' successfully!';
        } catch (Exception $e) {
            $message = 'Error updating reservation: ' . $e->getMessage();
        }
    }
}

// Get stats
$stmt = $pdo->query('SELECT status, COUNT(*) as count FROM lab_reservations GROUP BY status');
$stats = [];
foreach ($stmt->fetchAll() as $row) {
    $stats[$row['status']] = $row['count'];
}

// Get all reservations grouped by status
$pending_stmt = $pdo->query('
    SELECT lr.*, s.first_name, s.last_name, s.id_number
    FROM lab_reservations lr
    JOIN students s ON lr.student_id = s.id
    WHERE lr.status = "Pending"
    ORDER BY lr.created_at ASC
');
$pending = $pending_stmt->fetchAll();

$all_stmt = $pdo->query('
    SELECT lr.*, s.first_name, s.last_name, s.id_number
    FROM lab_reservations lr
    JOIN students s ON lr.student_id = s.id
    ORDER BY lr.reservation_date DESC, lr.start_time DESC
');
$all_reservations = $all_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Reservations | CCS Admin</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 font-[Inter]">

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
            <a href="admin_announcements.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Announcements</a>
            <a href="admin_feedback.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Feedback</a>
            <a href="admin_reservations.php" class="px-3 py-2 text-sm font-semibold bg-white/20 rounded hover:bg-white/30 transition border-b-2 border-white">Reservations</a>
        </div>
    </div>
</nav>

<main class="max-w-7xl mx-auto px-5 py-10">
    <!-- Page Title -->
    <h1 class="text-3xl font-bold text-slate-900 mb-8">Lab Reservation Management</h1>

    <!-- Success/Error Message -->
    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg bg-green-100 text-green-800 border border-green-300">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-8">
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Total Reservations</p>
                    <p class="text-3xl font-bold text-[#003366]"><?= count($all_reservations) ?></p>
                </div>
                <div class="text-4xl">📊</div>
            </div>
        </div>

        <div class="bg-white border border-yellow-200 rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Pending</p>
                    <p class="text-3xl font-bold text-yellow-600"><?= $stats['Pending'] ?? 0 ?></p>
                </div>
                <div class="text-4xl">⏳</div>
            </div>
        </div>

        <div class="bg-white border border-green-200 rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Approved</p>
                    <p class="text-3xl font-bold text-green-600"><?= $stats['Approved'] ?? 0 ?></p>
                </div>
                <div class="text-4xl">✓</div>
            </div>
        </div>

        <div class="bg-white border border-red-200 rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Rejected</p>
                    <p class="text-3xl font-bold text-red-600"><?= $stats['Rejected'] ?? 0 ?></p>
                </div>
                <div class="text-4xl">✗</div>
            </div>
        </div>
    </div>

    <!-- Pending Requests Section -->
    <?php if (count($pending) > 0): ?>
        <div class="bg-white border border-yellow-200 rounded-lg shadow-sm mb-8 overflow-hidden">
            <div class="bg-yellow-50 p-6 border-b border-yellow-200">
                <h2 class="text-xl font-bold text-yellow-900">Pending Requests (<?= count($pending) ?>)</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-100 border-b border-slate-200">
                            <th class="px-6 py-3 text-left font-semibold text-slate-700">Student</th>
                            <th class="px-6 py-3 text-left font-semibold text-slate-700">Lab</th>
                            <th class="px-6 py-3 text-left font-semibold text-slate-700">Date & Time</th>
                            <th class="px-6 py-3 text-left font-semibold text-slate-700">Purpose</th>
                            <th class="px-6 py-3 text-left font-semibold text-slate-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending as $res):
                            $date = new DateTime($res['reservation_date']);
                            $formatted_date = $date->format('M d, Y');
                        ?>
                            <tr class="border-b border-slate-200 hover:bg-slate-50 transition">
                                <td class="px-6 py-4 font-semibold text-slate-900">
                                    <?= htmlspecialchars($res['first_name'] . ' ' . $res['last_name']) ?>
                                    <br><span class="text-xs text-slate-500"><?= htmlspecialchars($res['id_number']) ?></span>
                                </td>
                                <td class="px-6 py-4 text-slate-700"><?= htmlspecialchars($res['lab_name']) ?></td>
                                <td class="px-6 py-4 text-slate-700">
                                    <div><?= $formatted_date ?></div>
                                    <div class="text-xs text-slate-500"><?= $res['start_time'] ?> - <?= $res['end_time'] ?></div>
                                </td>
                                <td class="px-6 py-4 text-slate-700"><?= htmlspecialchars($res['purpose'] ?? '-') ?></td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-2">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="approve">
                                            <input type="hidden" name="id" value="<?= $res['id'] ?>">
                                            <button type="submit" class="px-3 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200 transition font-semibold text-xs">
                                                Approve
                                            </button>
                                        </form>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="reject">
                                            <input type="hidden" name="id" value="<?= $res['id'] ?>">
                                            <button type="submit" class="px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 transition font-semibold text-xs">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- All Reservations Table -->
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200">
            <h2 class="text-lg font-bold text-slate-900">All Reservations</h2>
        </div>
        
        <?php if (count($all_reservations) > 0): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-100 border-b border-slate-200">
                            <th class="px-6 py-3 text-left font-semibold text-slate-700">Student</th>
                            <th class="px-6 py-3 text-left font-semibold text-slate-700">Lab</th>
                            <th class="px-6 py-3 text-left font-semibold text-slate-700">Reservation</th>
                            <th class="px-6 py-3 text-left font-semibold text-slate-700">Purpose</th>
                            <th class="px-6 py-3 text-center font-semibold text-slate-700">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_reservations as $res):
                            $date = new DateTime($res['reservation_date']);
                            $formatted_date = $date->format('M d, Y');
                            $status_color = '';
                            if ($res['status'] === 'Pending') $status_color = 'bg-yellow-100 text-yellow-800';
                            elseif ($res['status'] === 'Approved') $status_color = 'bg-green-100 text-green-800';
                            elseif ($res['status'] === 'Rejected') $status_color = 'bg-red-100 text-red-800';
                            else $status_color = 'bg-slate-100 text-slate-800';
                        ?>
                            <tr class="border-b border-slate-200 hover:bg-slate-50 transition">
                                <td class="px-6 py-4 font-semibold text-slate-900">
                                    <?= htmlspecialchars($res['first_name'] . ' ' . $res['last_name']) ?>
                                    <br><span class="text-xs text-slate-500"><?= htmlspecialchars($res['id_number']) ?></span>
                                </td>
                                <td class="px-6 py-4 text-slate-700"><?= htmlspecialchars($res['lab_name']) ?></td>
                                <td class="px-6 py-4 text-slate-700">
                                    <div><?= $formatted_date ?></div>
                                    <div class="text-xs text-slate-500"><?= $res['start_time'] ?> - <?= $res['end_time'] ?></div>
                                </td>
                                <td class="px-6 py-4 text-slate-700"><?= htmlspecialchars($res['purpose'] ?? '-') ?></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $status_color ?>">
                                        <?= $res['status'] ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-12 text-center">
                <div class="text-5xl mb-3">📭</div>
                <p class="text-slate-600 font-semibold">No reservations yet</p>
            </div>
        <?php endif; ?>
    </div>

</main>

</body>
</html>
