<?php
session_start();
require 'db.php';
require 'admin_notification_helper.php';

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

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = trim($_POST['category'] ?? '');
    $rating = intval($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($category === '' || $rating < 1 || $rating > 5) {
        $message = 'Please fill in all fields correctly.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO feedback (student_id, category, rating, comment) VALUES (?, ?, ?, ?)');
            $stmt->execute([$user_id, $category, $rating, $comment]);
            
            // Get the feedback ID
            $feedback_id = $pdo->lastInsertId();
            
            // Notify all admins about new feedback
            $student_name = htmlspecialchars($student['first_name'] . ' ' . $student['last_name']);
            notifyNewFeedback($pdo, $feedback_id, $student_name, $category, $rating);
            
            $message = 'Thank you! Your feedback has been submitted successfully.';
            $success = true;
        } catch (Exception $e) {
            $message = 'Error submitting feedback: ' . $e->getMessage();
        }
    }
}

$current_page = 'feedback';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Feedback | CCS Sit-in System</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-[Inter]">

<!-- Navigation -->
<?php include 'navbar.php'; ?>

<main class="max-w-2xl mx-auto px-5 py-10">
    <!-- Page Title -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900 mb-2">Submit Feedback</h1>
        <p class="text-slate-600">Help us improve the CCS Sit-in System</p>
    </div>

    <!-- Success/Error Message -->
    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?= $success ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Feedback Form -->
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-8">
        <form method="POST" class="space-y-6">
            <!-- Category Selection -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-3">What is your feedback about?</label>
                <div class="grid grid-cols-1 gap-3">
                    <label class="flex items-center p-4 border border-slate-200 rounded-lg hover:bg-slate-50 cursor-pointer transition">
                        <input type="radio" name="category" value="Lab Quality" required class="mr-3 w-4 h-4">
                        <div>
                            <span class="font-semibold text-slate-900">Lab Quality</span>
                            <p class="text-sm text-slate-500">Equipment, facilities, cleanliness</p>
                        </div>
                    </label>
                    <label class="flex items-center p-4 border border-slate-200 rounded-lg hover:bg-slate-50 cursor-pointer transition">
                        <input type="radio" name="category" value="Admin Service" required class="mr-3 w-4 h-4">
                        <div>
                            <span class="font-semibold text-slate-900">Admin Service</span>
                            <p class="text-sm text-slate-500">Support, assistance, responsiveness</p>
                        </div>
                    </label>
                    <label class="flex items-center p-4 border border-slate-200 rounded-lg hover:bg-slate-50 cursor-pointer transition">
                        <input type="radio" name="category" value="System Usability" required class="mr-3 w-4 h-4">
                        <div>
                            <span class="font-semibold text-slate-900">System Usability</span>
                            <p class="text-sm text-slate-500">Website, mobile app, features</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Rating -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-3">How would you rate your experience?</label>
                <div class="flex gap-2">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <label class="flex items-center cursor-pointer group">
                            <input type="radio" name="rating" value="<?= $i ?>" required class="hidden">
                            <span class="text-4xl transition group-hover:scale-110" data-rating="<?= $i ?>">
                                ⭐
                            </span>
                        </label>
                    <?php endfor; ?>
                </div>
                <p class="text-xs text-slate-500 mt-2">1 = Poor, 5 = Excellent</p>
            </div>

            <!-- Comment -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Additional Comments (Optional)</label>
                <textarea 
                    name="comment"
                    placeholder="Share any specific suggestions or details..."
                    rows="5"
                    class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                ></textarea>
            </div>

            <!-- Submit Button -->
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-blue-600 text-white font-semibold py-3 rounded-lg hover:bg-blue-700 transition">
                    Submit Feedback
                </button>
                <a href="dashboard.php" class="flex-1 bg-slate-300 text-slate-800 font-semibold py-3 rounded-lg hover:bg-slate-400 transition text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>

</main>

<script>
    // Star rating interaction
    document.querySelectorAll('input[name="rating"]').forEach((input, index) => {
        input.addEventListener('change', () => {
            const rating = parseInt(input.value);
            document.querySelectorAll('[data-rating]').forEach((star, i) => {
                star.textContent = i < rating ? '⭐' : '☆';
            });
        });
    });
</script>

</body>
</html>
