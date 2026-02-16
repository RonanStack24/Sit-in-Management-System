<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | CCS Sit-in Monitoring</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
    <div class="brand">
        <a class="logo" href="index.php">
            <span class="logo-mark">
                <img src="ccs-logo.png" alt="CCS logo">
            </span>
            <span>CCS Sit-in System</span>
        </a>
        <div class="nav-badge">
            <img src="uc-logo.png" alt="University of Cebu logo">
        </div>
    </div>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <div class="dropdown">
            <button class="dropdown-toggle" type="button" aria-haspopup="true" aria-expanded="false">Community</button>
            <div class="dropdown-menu" role="menu">
                <a href="events.php" role="menuitem">Events</a>
                <a href="announcements.php" role="menuitem">Announcements</a>
                <a href="support.php" role="menuitem">Support</a>
            </div>
        </div>
        <a href="about.php">About</a>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
    </div>
</nav>

<section class="register-page">
    <div class="register-card">
        <div class="register-form">
            <a class="back-link" href="index.php">Back</a>
            <h1>Sign up</h1>

            <form action="#" method="post">
                <label for="id_number">ID Number</label>
                <input id="id_number" name="id_number" type="text" placeholder="Enter your ID" required>

                <label for="last_name">Last Name</label>
                <input id="last_name" name="last_name" type="text" placeholder="Enter your last name" required>

                <label for="first_name">First Name</label>
                <input id="first_name" name="first_name" type="text" placeholder="Enter your first name" required>

                <label for="middle_name">Middle Name</label>
                <input id="middle_name" name="middle_name" type="text" placeholder="Enter your middle name">

                <label for="course_level">Course Level</label>
                <input id="course_level" name="course_level" type="text" placeholder="e.g. 1">

                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="Create a password" required>

                <label for="confirm_password">Repeat your password</label>
                <input id="confirm_password" name="confirm_password" type="password" placeholder="Repeat your password" required>

                <label for="email">Email</label>
                <input id="email" name="email" type="email" placeholder="name@example.com" required>

                <label for="course">Course</label>
                <input id="course" name="course" type="text" placeholder="BSIT" required>

                <label for="address">Address</label>
                <input id="address" name="address" type="text" placeholder="Street, City, Province" required>

                <button class="btn btn-primary" type="submit">Register</button>
            </form>
        </div>

        <div class="register-visual">
            <div class="visual-card">
                <img class="register-image" src="register-illustration.jpg" alt="Register illustration">
            </div>
        </div>
    </div>
</section>

</body>
</html>
