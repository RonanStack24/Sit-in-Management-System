<?php
session_start();
require 'db.php';

// Check if user is admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$action = '';

// Handle delete announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    if ($id > 0) {
        try {
            $stmt = $pdo->prepare('DELETE FROM announcements WHERE id = ?');
            $stmt->execute([$id]);
            $message = 'Announcement deleted successfully!';
        } catch (Exception $e) {
            $message = 'Error deleting announcement: ' . $e->getMessage();
        }
    }
}

// Handle create/edit announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $id = intval($_POST['id'] ?? 0);

    if ($title === '' || $content === '') {
        $message = 'Please fill in all fields.';
    } else {
        try {
            if ($id > 0) {
                // Update existing announcement
                $stmt = $pdo->prepare('UPDATE announcements SET title = ?, content = ? WHERE id = ?');
                $stmt->execute([$title, $content, $id]);
                $message = 'Announcement updated successfully!';
            } else {
                // Create new announcement
                $stmt = $pdo->prepare('INSERT INTO announcements (title, content) VALUES (?, ?)');
                $stmt->execute([$title, $content]);
                $message = 'Announcement created successfully!';
            }
            $action = '';
        } catch (Exception $e) {
            $message = 'Error saving announcement: ' . $e->getMessage();
        }
    }
}

// Get all announcements
$stmt = $pdo->query('SELECT * FROM announcements ORDER BY created_at DESC');
$announcements = $stmt->fetchAll();

// Get announcement being edited
$edit_announcement = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM announcements WHERE id = ?');
    $stmt->execute([$_GET['edit']]);
    $edit_announcement = $stmt->fetch();
    if ($edit_announcement) {
        $action = 'edit';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements | CCS Admin</title>
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
            <a href="admin_announcements.php" class="px-3 py-2 text-sm font-semibold bg-white/20 rounded hover:bg-white/30 transition border-b-2 border-white">Announcements</a>
            <a href="admin_feedback.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Feedback</a>
            <a href="admin_reservations.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Reservations</a>
            <a href="admin_reports.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Reports</a>
            <a href="admin_students.php" class="px-3 py-2 text-sm text-white/90 hover:bg-white/10 rounded transition">Students</a>
        </div>
    </div>
</nav>

<main class="max-w-6xl mx-auto px-5 py-10">
    <!-- Page Title -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900 mb-4">Announcements</h1>
    </div>

    <!-- Success/Error Message -->
    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?= strpos($message, 'successfully') !== false ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Section -->
        <div class="lg:col-span-1">
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-4">
                    <?= $edit_announcement ? 'Edit Announcement' : 'New Announcement' ?>
                </h2>
                
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="save">
                    <?php if ($edit_announcement): ?>
                        <input type="hidden" name="id" value="<?= $edit_announcement['id'] ?>">
                    <?php endif; ?>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Title</label>
                        <input 
                            type="text" 
                            name="title" 
                            value="<?= htmlspecialchars($edit_announcement['title'] ?? '') ?>"
                            placeholder="Announcement title"
                            class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Content</label>
                        <textarea 
                            name="content" 
                            placeholder="Announcement content"
                            rows="6"
                            class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                            required
                        ><?= htmlspecialchars($edit_announcement['content'] ?? '') ?></textarea>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-blue-600 text-white font-semibold py-2 rounded-lg hover:bg-blue-700 transition">
                            <?= $edit_announcement ? 'Update' : 'Create' ?>
                        </button>
                        <?php if ($edit_announcement): ?>
                            <a href="admin_announcements.php" class="flex-1 bg-slate-300 text-slate-800 font-semibold py-2 rounded-lg hover:bg-slate-400 transition text-center">
                                Cancel
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Announcements List Section -->
        <div class="lg:col-span-2">
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-200">
                    <h2 class="text-lg font-bold text-slate-900">All Announcements (<?= count($announcements) ?>)</h2>
                </div>

                <?php if (count($announcements) > 0): ?>
                    <div class="divide-y divide-slate-200">
                        <?php foreach ($announcements as $announcement): 
                            $date = new DateTime($announcement['created_at']);
                            $formatted_date = $date->format('M d, Y H:i');
                        ?>
                            <div class="p-6 hover:bg-slate-50 transition">
                                <div class="flex items-start justify-between mb-2">
                                    <h3 class="text-lg font-bold text-slate-900"><?= htmlspecialchars($announcement['title']) ?></h3>
                                    <span class="text-xs text-slate-500"><?= $formatted_date ?></span>
                                </div>
                                <p class="text-slate-600 mb-4 line-clamp-3"><?= htmlspecialchars($announcement['content']) ?></p>
                                <div class="flex gap-2">
                                    <a href="admin_announcements.php?edit=<?= $announcement['id'] ?>" class="text-sm px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition font-semibold">
                                        Edit
                                    </a>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $announcement['id'] ?>">
                                        <button type="submit" onclick="return confirm('Delete this announcement?')" class="text-sm px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 transition font-semibold">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="p-12 text-center">
                        <div class="text-5xl mb-3">📢</div>
                        <p class="text-slate-600 font-semibold">No announcements yet</p>
                        <p class="text-slate-500 text-sm">Create your first announcement to get started!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</main>

</body>
</html>
