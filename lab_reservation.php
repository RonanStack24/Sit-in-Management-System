<?php
session_start();
require 'db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$success = false;

// Fetch student info
$stmt = $pdo->prepare('SELECT first_name, last_name FROM students WHERE id = ?');
$stmt->execute([$user_id]);
$student = $stmt->fetch();

// Handle new reservation request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request') {
    $lab_name = trim($_POST['lab_name'] ?? '');
    $reservation_date = trim($_POST['reservation_date'] ?? '');
    $start_time = trim($_POST['start_time'] ?? '');
    $end_time = trim($_POST['end_time'] ?? '');
    $purpose = trim($_POST['purpose'] ?? '');

    if ($lab_name === '' || $reservation_date === '' || $start_time === '' || $end_time === '') {
        $message = 'Please fill in all required fields.';
    } elseif (strtotime($end_time) <= strtotime($start_time)) {
        $message = 'End time must be after start time.';
    } else {
        try {
            $stmt = $pdo->prepare('
                INSERT INTO lab_reservations (student_id, lab_name, reservation_date, start_time, end_time, purpose, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([$user_id, $lab_name, $reservation_date, $start_time, $end_time, $purpose, 'Pending']);
            $message = 'Reservation request submitted successfully!';
            $success = true;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'unique_slot') !== false) {
                $message = 'This time slot is already reserved. Please choose a different time.';
            } else {
                $message = 'Error submitting reservation: ' . $e->getMessage();
            }
        }
    }
}

// Cancel reservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $id = intval($_POST['id'] ?? 0);
    try {
        $stmt = $pdo->prepare('UPDATE lab_reservations SET status = ? WHERE id = ? AND student_id = ?');
        $stmt->execute(['Cancelled', $id, $user_id]);
        $message = 'Reservation cancelled.';
        $success = true;
    } catch (Exception $e) {
        $message = 'Error cancelling reservation.';
    }
}

// Get student's reservations
$stmt = $pdo->prepare('
    SELECT * FROM lab_reservations
    WHERE student_id = ?
    ORDER BY reservation_date DESC, start_time DESC
');
$stmt->execute([$user_id]);
$reservations = $stmt->fetchAll();

// Separate by status
$pending = [];
$approved = [];
$rejected = [];
foreach ($reservations as $res) {
    if ($res['status'] === 'Pending') $pending[] = $res;
    elseif ($res['status'] === 'Approved') $approved[] = $res;
    elseif ($res['status'] === 'Rejected') $rejected[] = $res;
}

// Available labs
$labs = ['CCS Lab 1', 'CCS Lab 2', 'CCS Lab 3', 'Network Lab', 'Server Lab'];

$current_page = 'reservation';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Reservations | CCS Sit-in System</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 font-[Inter]">

<!-- Navigation -->
<?php include 'navbar.php'; ?>

<main class="max-w-6xl mx-auto px-5 py-10">
    <!-- Page Title -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900 mb-2">Lab Reservations</h1>
        <p class="text-slate-600">Request and manage your lab time slots</p>
    </div>

    <!-- Success/Error Message -->
    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?= $success ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Reservation Form -->
        <div class="lg:col-span-1">
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6 sticky top-20">
                <h2 class="text-lg font-bold text-slate-900 mb-4">New Reservation</h2>
                
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="request">

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Laboratory</label>
                        <select name="lab_name" required class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select a lab...</option>
                            <?php foreach ($labs as $lab): ?>
                                <option value="<?= htmlspecialchars($lab) ?>"><?= htmlspecialchars($lab) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Date</label>
                        <input 
                            type="date" 
                            name="reservation_date" 
                            min="<?= date('Y-m-d') ?>"
                            required
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Start Time</label>
                        <input 
                            type="time" 
                            name="start_time" 
                            required
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">End Time</label>
                        <input 
                            type="time" 
                            name="end_time" 
                            required
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Purpose (Optional)</label>
                        <input 
                            type="text" 
                            name="purpose" 
                            placeholder="e.g., Project work, Study, Lab exam"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                    </div>

                    <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-2 rounded-lg hover:bg-blue-700 transition">
                        Request Reservation
                    </button>
                </form>
            </div>
        </div>

        <!-- Reservations List -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Approved Reservations -->
            <?php if (count($approved) > 0): ?>
                <div class="bg-white border border-green-200 rounded-lg shadow-sm overflow-hidden">
                    <div class="bg-green-50 p-4 border-b border-green-200">
                        <h3 class="text-lg font-bold text-green-900">✓ Approved (<?= count($approved) ?>)</h3>
                    </div>
                    <div class="divide-y divide-slate-200">
                        <?php foreach ($approved as $res):
                            $date = new DateTime($res['reservation_date']);
                            $formatted_date = $date->format('M d, Y');
                        ?>
                            <div class="p-4 hover:bg-slate-50 transition">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <p class="font-semibold text-slate-900"><?= htmlspecialchars($res['lab_name']) ?></p>
                                        <p class="text-sm text-slate-600"><?= $formatted_date ?> • <?= $res['start_time'] ?> - <?= $res['end_time'] ?></p>
                                    </div>
                                    <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">Approved</span>
                                </div>
                                <?php if ($res['purpose']): ?>
                                    <p class="text-sm text-slate-500">Purpose: <?= htmlspecialchars($res['purpose']) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Pending Reservations -->
            <?php if (count($pending) > 0): ?>
                <div class="bg-white border border-yellow-200 rounded-lg shadow-sm overflow-hidden">
                    <div class="bg-yellow-50 p-4 border-b border-yellow-200">
                        <h3 class="text-lg font-bold text-yellow-900">⏳ Pending (<?= count($pending) ?>)</h3>
                    </div>
                    <div class="divide-y divide-slate-200">
                        <?php foreach ($pending as $res):
                            $date = new DateTime($res['reservation_date']);
                            $formatted_date = $date->format('M d, Y');
                        ?>
                            <div class="p-4 hover:bg-slate-50 transition">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <p class="font-semibold text-slate-900"><?= htmlspecialchars($res['lab_name']) ?></p>
                                        <p class="text-sm text-slate-600"><?= $formatted_date ?> • <?= $res['start_time'] ?> - <?= $res['end_time'] ?></p>
                                    </div>
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-semibold rounded-full">Pending</span>
                                </div>
                                <?php if ($res['purpose']): ?>
                                    <p class="text-sm text-slate-500 mb-3">Purpose: <?= htmlspecialchars($res['purpose']) ?></p>
                                <?php endif; ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="cancel">
                                    <input type="hidden" name="id" value="<?= $res['id'] ?>">
                                    <button type="submit" onclick="return confirm('Cancel this reservation?')" class="text-sm px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 transition font-semibold">
                                        Cancel Request
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Rejected Reservations -->
            <?php if (count($rejected) > 0): ?>
                <div class="bg-white border border-red-200 rounded-lg shadow-sm overflow-hidden">
                    <div class="bg-red-50 p-4 border-b border-red-200">
                        <h3 class="text-lg font-bold text-red-900">✗ Rejected (<?= count($rejected) ?>)</h3>
                    </div>
                    <div class="divide-y divide-slate-200">
                        <?php foreach ($rejected as $res):
                            $date = new DateTime($res['reservation_date']);
                            $formatted_date = $date->format('M d, Y');
                        ?>
                            <div class="p-4 hover:bg-slate-50 transition">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <p class="font-semibold text-slate-900"><?= htmlspecialchars($res['lab_name']) ?></p>
                                        <p class="text-sm text-slate-600"><?= $formatted_date ?> • <?= $res['start_time'] ?> - <?= $res['end_time'] ?></p>
                                    </div>
                                    <span class="px-3 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full">Rejected</span>
                                </div>
                                <?php if ($res['purpose']): ?>
                                    <p class="text-sm text-slate-500">Purpose: <?= htmlspecialchars($res['purpose']) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Empty State -->
            <?php if (count($reservations) === 0): ?>
                <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-12 text-center">
                    <div class="text-5xl mb-3">📅</div>
                    <p class="text-slate-600 font-semibold">No reservations yet</p>
                    <p class="text-slate-500 text-sm">Create your first lab reservation using the form on the left</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</main>

</body>
</html>
