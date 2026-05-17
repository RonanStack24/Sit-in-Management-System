<?php
session_start();
require 'db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch fresh student data from DB
$stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch();

$user_id = $_SESSION['user_id'];
$toast_message = '';

// Check for login toast
if (isset($_GET['toast']) && $_GET['toast'] === 'login') {
    $toast_message = 'Welcome back, ' . htmlspecialchars($student['first_name']) . '!';
}

// Fetch latest announcements from database
$stmt = $pdo->query('SELECT * FROM announcements ORDER BY created_at DESC LIMIT 3');
$announcements = $stmt->fetchAll();

// Fetch lab availability
$stmt = $pdo->query('SELECT * FROM labs ORDER BY lab_name ASC');
$labs = $stmt->fetchAll();

// Fetch lab software
$stmt = $pdo->query('SELECT ls.*, l.lab_name FROM lab_software ls JOIN labs l ON ls.lab_id = l.id ORDER BY l.lab_name, ls.software_name ASC');
$lab_software = $stmt->fetchAll();

// Group software by lab
$software_by_lab = [];
foreach ($lab_software as $soft) {
    if (!isset($software_by_lab[$soft['lab_id']])) {
        $software_by_lab[$soft['lab_id']] = ['lab_name' => $soft['lab_name'], 'software' => []];
    }
    $software_by_lab[$soft['lab_id']]['software'][] = $soft;
}

// Fetch student's lab reservations
$stmt = $pdo->prepare('SELECT * FROM lab_reservations WHERE student_id = ? ORDER BY reservation_date DESC, start_time DESC LIMIT 5');
$stmt->execute([$user_id]);
$reservations = $stmt->fetchAll();

// Handle photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_photo') {
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['profile_photo']['tmp_name'];
        $file_name = $_FILES['profile_photo']['name'];
        $file_size = $_FILES['profile_photo']['size'];
        $file_type = $_FILES['profile_photo']['type'];
        
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024;
        
        if (!in_array($file_type, $allowed_types)) {
            $toast_message = 'Only JPG, PNG, and GIF files are allowed.';
        } elseif ($file_size > $max_size) {
            $toast_message = 'File size must be less than 5MB.';
        } else {
            $upload_dir = 'uploads/profiles/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_filename = 'student_' . $user_id . '_' . time() . '.' . $ext;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                if (!empty($student['profile_photo']) && file_exists($student['profile_photo'])) {
                    unlink($student['profile_photo']);
                }
                
                $stmt = $pdo->prepare('UPDATE students SET profile_photo = ? WHERE id = ?');
                $stmt->execute([$upload_path, $user_id]);
                
                $toast_message = 'Photo uploaded successfully!';
                
                $stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
                $stmt->execute([$user_id]);
                $student = $stmt->fetch();
            } else {
                $toast_message = 'Failed to upload photo.';
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | CCS Sit-in Monitoring</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="js/utils.js"></script>
    <style>
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-slideUp {
            animation: slideUp 0.6s ease-out forwards;
            opacity: 0;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-[Inter]">

<?php
    $current_page = 'dashboard';
    include 'navbar.php';
?>

<main class="max-w-6xl mx-auto px-5 py-10">

    <!-- Greeting and Profile Header -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 mb-10">
        <!-- Profile Card with Photo -->
        <div class="lg:col-span-1">
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm text-center">
                <!-- Profile Picture -->
                <div class="inline-flex items-center justify-center w-32 h-32 rounded-full bg-gradient-to-br from-[#003366] to-[#004b93] overflow-hidden mb-4 mx-auto">
                    <?php if (!empty($student['profile_photo']) && file_exists($student['profile_photo'])): ?>
                        <img src="<?= htmlspecialchars($student['profile_photo']) ?>" alt="Profile" class="w-full h-full object-cover">
                    <?php else: ?>
                        <span class="text-4xl font-bold text-white">
                            <?= strtoupper(substr($student['first_name'], 0, 1)) . strtoupper(substr($student['last_name'], 0, 1)) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <h2 class="text-lg font-bold text-slate-900 mb-1">
                    <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                </h2>
                <p class="text-sm text-slate-500 mb-4"><?= htmlspecialchars($student['course']) ?></p>
                
                <form method="POST" enctype="multipart/form-data" id="photoForm" class="mb-4">
                    <input type="hidden" name="action" value="upload_photo">
                    <input type="file" id="photoInput" name="profile_photo" accept="image/*" class="hidden" onchange="document.getElementById('photoForm').submit();">
                    <button type="button" onclick="document.getElementById('photoInput').click();" class="w-full px-4 py-2 bg-[#003366] text-white text-sm font-semibold rounded-lg hover:bg-[#004b93] transition">
                        Change Photo
                    </button>
                </form>

                <!-- Student Info -->
                <div class="border-t border-slate-200 pt-4 text-left">
                    <div class="mb-3">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">ID Number</p>
                        <p class="font-semibold text-slate-800"><?= htmlspecialchars($student['id_number']) ?></p>
                    </div>
                    <div class="mb-3">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Year Level</p>
                        <p class="font-semibold text-slate-800"><?= htmlspecialchars($student['course_level'] ?? '—') ?></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Email</p>
                        <p class="font-semibold text-slate-800 text-sm break-all"><?= htmlspecialchars($student['email']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Stats and Quick Info -->
        <div class="lg:col-span-3">
            <!-- Welcome Message -->
            <h1 class="text-3xl font-bold text-slate-900 mb-1">
                Welcome back, <?= htmlspecialchars($student['first_name']) ?>!
            </h1>
            <p class="text-sm text-slate-500 mb-6">Here's your sit-in overview.</p>

            <!-- Session Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Sessions Left -->
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Sessions Left</p>
                        <button onclick="location.reload();" class="text-xs text-indigo-600 hover:text-indigo-700 font-semibold hover:underline" title="Refresh sessions count">
                            🔄 Refresh
                        </button>
                    </div>
                    <p class="text-5xl font-bold text-[#003366] mb-3"><?= (int)($student['sessions_left'] ?? 30) ?></p>
                    <p class="text-sm text-slate-600">out of 30 sessions</p>
                    <div class="mt-3 w-full bg-slate-200 rounded-full h-2">
                        <div class="bg-[#003366] h-2 rounded-full" style="width: <?= ((int)($student['sessions_left'] ?? 30) / 30) * 100 ?>%"></div>
                    </div>
                </div>

                <!-- Personal Info Card -->
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Personal Information</p>
                    <div class="space-y-2 text-sm">
                        <div><span class="text-slate-500">Full Name:</span> <span class="font-semibold"><?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name']) ?></span></div>
                        <div><span class="text-slate-500">Address:</span> <span class="font-semibold"><?= htmlspecialchars($student['address'] ?? '—') ?></span></div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex gap-3">
                <a href="profile.php" class="inline-flex items-center rounded-lg bg-[#003366] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#004b93] transition">
                    ✏️ Edit Profile
                </a>
                <a href="logout.php" class="inline-flex items-center rounded-lg bg-red-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-600 transition">
                    Sign Out
                </a>
            </div>
        </div>
    </div>

    <!-- Announcements Section -->
    <div class="mb-8">
        <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
            📢 Latest Announcements
        </h2>
        <?php if (count($announcements) > 0): ?>
        <div class="space-y-3">
            <?php foreach ($announcements as $announcement):
                $date = new DateTime($announcement['created_at']);
                $formatted_date = $date->format('M d, Y');
            ?>
                <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-4 hover:shadow-md transition border-l-4 border-l-[#003366]">
                    <h3 class="font-bold text-slate-900 mb-1"><?= htmlspecialchars($announcement['title']) ?></h3>
                    <p class="text-sm text-slate-600 mb-2"><?= nl2br(htmlspecialchars($announcement['content'])) ?></p>
                    <p class="text-xs text-slate-400">Posted: <?= $formatted_date ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-8 text-center">
            <div class="text-4xl mb-3">📭</div>
            <p class="text-slate-600 font-semibold">No announcements yet</p>
            <p class="text-slate-500 text-sm">Check back later for updates from the CCS Department</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Rules & Regulations Section -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <h2 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
            ⚙️ Laboratory Rules & Regulations
        </h2>
        <div class="bg-blue-50 border-l-4 border-[#003366] p-4 mb-6 rounded">
            <h4 class="font-bold text-slate-900 mb-2">University of Cebu</h4>
            <p class="font-semibold text-slate-700">COLLEGE OF INFORMATION & COMPUTER STUDIES<br>LABORATORY RULES AND REGULATIONS</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="font-semibold text-slate-900 mb-2">1. Maintain Discipline</p>
                <p class="text-sm text-slate-600">Maintain silence, proper decorum, and discipline inside the laboratory. Mobile phones, walkman and other personal pieces of equipment must be switched off.</p>
            </div>
            <div>
                <p class="font-semibold text-slate-900 mb-2">2. No Games Allowed</p>
                <p class="text-sm text-slate-600">Games are not allowed inside the lab. This includes computer-related games, card games and other games that may affect the operation of the lab.</p>
            </div>
            <div>
                <p class="font-semibold text-slate-900 mb-2">3. Internet Usage</p>
                <p class="text-sm text-slate-600">Surfing the internet is allowed only with the permission of the instructor. Downloading and installing of software are strictly prohibited.</p>
            </div>
            <div>
                <p class="font-semibold text-slate-900 mb-2">4. File Management</p>
                <p class="text-sm text-slate-600">Deleting computer files and changing computer settings are not allowed.</p>
            </div>
        </div>
    </div>

    <!-- Lab Availability Section -->
    <div class="mt-8">
        <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
            🏢 Lab Availability & Status
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($labs as $lab): ?>
                <?php 
                    $occupancy_percent = ($lab['current_occupancy'] / $lab['capacity']) * 100;
                    $available_seats = $lab['capacity'] - $lab['current_occupancy'];
                    $status_color = $lab['status'] === 'Available' ? 'bg-green-50 border-green-200' : ($lab['status'] === 'Maintenance' ? 'bg-yellow-50 border-yellow-200' : 'bg-red-50 border-red-200');
                    $status_badge_color = $lab['status'] === 'Available' ? 'bg-green-100 text-green-700' : ($lab['status'] === 'Maintenance' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700');
                    $status_icon = $lab['status'] === 'Available' ? '✅' : ($lab['status'] === 'Maintenance' ? '⚠️' : '❌');
                ?>
                <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-5 hover:shadow-md transition animate-slideUp" style="animation-delay: <?= 0.1 * array_search($lab, $labs) ?>s;">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-slate-900"><?= htmlspecialchars($lab['lab_name']) ?></h3>
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $status_badge_color ?>">
                            <?= $status_icon . ' ' . $lab['status'] ?>
                        </span>
                    </div>
                    <div class="mb-3">
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-sm text-slate-600"><span class="font-semibold"><?= $available_seats ?></span> seats available</p>
                            <p class="text-xs text-slate-500"><?= $lab['current_occupancy'] ?>/<?= $lab['capacity'] ?></p>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2">
                            <div class="bg-gradient-to-r from-green-400 to-green-500 h-2 rounded-full transition-all" style="width: <?= $occupancy_percent ?>%"></div>
                        </div>
                    </div>
                    <a href="lab_reservation.php" class="text-sm text-[#003366] font-semibold hover:underline">Reserve Lab →</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Software Availability Section -->
    <div class="mt-8">
        <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
            💻 Software Available by Lab
        </h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <?php foreach ($software_by_lab as $lab_id => $data): ?>
                <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-5 animate-slideUp" style="animation-delay: <?= 0.15 * (array_keys($software_by_lab) == array_keys(array_flip(array_keys($software_by_lab))) ? array_search($lab_id, array_keys($software_by_lab)) : 0) ?>s;">
                    <h3 class="font-bold text-slate-900 mb-3 flex items-center gap-2">
                        🖥️ <?= htmlspecialchars($data['lab_name']) ?>
                    </h3>
                    <div class="space-y-2">
                        <?php foreach ($data['software'] as $software): ?>
                            <div class="flex items-start gap-2 p-2 bg-slate-50 rounded hover:bg-slate-100 transition">
                                <span class="text-sm font-semibold text-[#003366]">•</span>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($software['software_name']) ?></p>
                                    <p class="text-xs text-slate-500">v<?= htmlspecialchars($software['version'] ?? 'N/A') ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Lab Reservations Status Section -->
    <div class="mt-8">
        <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
            📅 Your Lab Reservations
        </h2>
        <?php if (count($reservations) > 0): ?>
            <div class="space-y-3">
                <?php foreach ($reservations as $index => $res):
                    $res_date = new DateTime($res['reservation_date']);
                    $formatted_date = $res_date->format('M d, Y');
                    $start_time = date('H:i A', strtotime($res['start_time']));
                    $end_time = date('H:i A', strtotime($res['end_time']));
                    $status_colors = [
                        'Pending' => 'bg-yellow-50 border-yellow-200 text-yellow-700',
                        'Approved' => 'bg-green-50 border-green-200 text-green-700',
                        'Rejected' => 'bg-red-50 border-red-200 text-red-700',
                        'Cancelled' => 'bg-slate-50 border-slate-200 text-slate-700'
                    ];
                    $status_badge = $status_colors[$res['status']] ?? $status_colors['Pending'];
                ?>
                    <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-4 hover:shadow-md transition animate-slideUp" style="animation-delay: <?= 0.4 + ($index * 0.05) ?>s;">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-bold text-slate-900"><?= htmlspecialchars($res['lab_name']) ?></h3>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold border <?= $status_badge ?>">
                                <?= $res['status'] ?>
                            </span>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm text-slate-600">
                            <div><span class="text-slate-500">Date:</span> <?= $formatted_date ?></div>
                            <div><span class="text-slate-500">Time:</span> <?= $start_time ?> - <?= $end_time ?></div>
                            <div><span class="text-slate-500">Purpose:</span> <?= htmlspecialchars($res['purpose'] ?? '—') ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-8 text-center">
                <div class="text-4xl mb-3">📭</div>
                <p class="text-slate-600 font-semibold">No reservations yet</p>
                <p class="text-slate-500 text-sm">
                    <a href="lab_reservation.php" class="text-[#003366] font-semibold hover:underline">Make a reservation now →</a>
                </p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Rules & Regulations Section -->
    <div class="mt-8 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        <h2 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
            ⚙️ Laboratory Rules & Regulations
        </h2>
        <div class="bg-blue-50 border-l-4 border-[#003366] p-4 mb-6 rounded">
            <h4 class="font-bold text-slate-900 mb-2">University of Cebu</h4>
            <p class="font-semibold text-slate-700">COLLEGE OF INFORMATION & COMPUTER STUDIES<br>LABORATORY RULES AND REGULATIONS</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="font-semibold text-slate-900 mb-2">1. Maintain Discipline</p>
                <p class="text-sm text-slate-600">Maintain silence, proper decorum, and discipline inside the laboratory. Mobile phones, walkman and other personal pieces of equipment must be switched off.</p>
            </div>
            <div>
                <p class="font-semibold text-slate-900 mb-2">2. No Games Allowed</p>
                <p class="text-sm text-slate-600">Games are not allowed inside the lab. This includes computer-related games, card games and other games that may affect the operation of the lab.</p>
            </div>
            <div>
                <p class="font-semibold text-slate-900 mb-2">3. Internet Usage</p>
                <p class="text-sm text-slate-600">Surfing the internet is allowed only with the permission of the instructor. Downloading and installing of software are strictly prohibited.</p>
            </div>
            <div>
                <p class="font-semibold text-slate-900 mb-2">4. File Management</p>
                <p class="text-sm text-slate-600">Deleting computer files and changing computer settings are not allowed.</p>
            </div>
        </div>
    </div>

</main>

<!-- Toast notification -->
<div id="toast" class="fixed bottom-6 right-6 z-50 hidden items-center gap-3 bg-[#003366] text-white text-sm font-semibold px-5 py-3 rounded-xl shadow-lg">
    <svg class="w-5 h-5 shrink-0 text-green-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    <span id="toast-msg"></span>
</div>

<?php if ($toast_message): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    showToast('<?= htmlspecialchars($toast_message, ENT_QUOTES) ?>');
});
</script>
<?php endif; ?>

</body>
</html>
