<?php
session_start();
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!$username || !$password) {
        $error = 'Please enter username and password.';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT id, username, password FROM admin_users WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if ($admin) {
                if (password_verify($password, $admin['password'])) {
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['is_admin'] = true;
                    header('Location: admin_dashboard.php');
                    exit;
                } else {
                    $error = 'Invalid username or password.';
                }
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (Exception $e) {
            $error = 'Login error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | CCS Sit-in Monitoring</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-[#003366] to-[#004b93] min-h-screen flex items-center justify-center">

<div class="w-full max-w-md">
    <div class="bg-white rounded-2xl shadow-2xl p-8">
        <h1 class="text-3xl font-bold text-[#003366] mb-2 text-center">Admin Login</h1>
        <p class="text-slate-500 text-center mb-8">CCS Sit-in Monitoring System</p>

        <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded">
                <p class="text-sm font-semibold text-red-800"><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <!-- Username -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Username</label>
                <input 
                    type="text" 
                    name="username" 
                    required
                    placeholder="Enter admin username"
                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#003366] focus:border-transparent transition"
                >
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
                <input 
                    type="password" 
                    name="password" 
                    required
                    placeholder="Enter admin password"
                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#003366] focus:border-transparent transition"
                >
            </div>

            <!-- Login Button -->
            <button 
                type="submit"
                class="w-full px-4 py-2.5 bg-[#003366] text-white font-semibold rounded-lg hover:bg-[#004b93] transition mt-6"
            >
                Login
            </button>
        </form>

        <hr class="my-6">

        <div class="bg-blue-50 border-l-4 border-[#003366] p-4 rounded text-sm text-slate-700">
            <p class="font-semibold mb-2">Default Credentials (First Time):</p>
            <p>👤 Username: <code class="bg-white px-2 py-1 rounded">admin</code></p>
            <p>🔑 Password: <code class="bg-white px-2 py-1 rounded">admin123</code></p>
        </div>

        <p class="text-center text-sm text-slate-500 mt-6">
            <a href="index.php" class="text-[#003366] hover:underline">Back to Home</a>
        </p>
    </div>
</div>

</body>
</html>
