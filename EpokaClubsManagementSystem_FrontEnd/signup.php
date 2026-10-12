<?php
mysqli_report(MYSQLI_REPORT_OFF); // PHP 8.1+: show database errors as messages instead of crashing the page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

session_start();

// Already logged in? Go to the dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: page1.php");
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$database = "epoka_clubs";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$errorMsg = "";
// Keep what the user typed if the form has an error
$old = ['name' => '', 'surname' => '', 'email' => '', 'gender' => '', 'username' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    foreach ($old as $key => $value) {
        $old[$key] = trim($_POST[$key] ?? '');
    }
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Same rules as the admin's "Add User" page
    if (in_array('', $old, true) || $newPassword === '' || $confirmPassword === '') {
        $errorMsg = "Please fill in all fields.";
    } elseif (!in_array($old['gender'], ['Male', 'Female'], true)) {
        $errorMsg = "Please select a gender.";
    } elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@epoka\.edu\.al$/', $old['email'])) {
        $errorMsg = "Please enter an Epoka University email address.";
    } elseif (!preg_match('/^[A-Za-z0-9._]{3,20}$/', $old['username'])) {
        $errorMsg = "Username must be 3-20 characters (letters, numbers, . or _).";
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $newPassword)) {
        $errorMsg = "Weak password. Use at least 8 characters with 1 uppercase letter, 1 number and 1 symbol (@$!%*?&).";
    } elseif ($newPassword !== $confirmPassword) {
        $errorMsg = "Passwords do not match.";
    } else {
        // Is the username already taken?
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param('s', $old['username']);
        $check->execute();
        $taken = $check->get_result()->num_rows > 0;
        $check->close();

        if ($taken) {
            $errorMsg = "This username is already taken.";
        } else {
            // New accounts always get the "user" role; an admin is set with SQL (see README)
            $role = "user";
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, surname, email, gender, username, password, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssssss', $old['name'], $old['surname'], $old['email'], $old['gender'], $old['username'], $hashedPassword, $role);

            if ($stmt->execute()) {
                $_SESSION['signup_success'] = "Account created! You can now log in.";
                header("Location: login.php");
                exit();
            } else {
                $errorMsg = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up - Epoka Clubs Database</title>
  <link rel="stylesheet" href="login-css.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <style>
    /* The sign-up form is taller than the login form, so let the page scroll */
    body { height: auto; min-height: 100vh; padding: 30px 0; }
    form .field { margin-bottom: 14px; }
    form .field .input-area { height: 44px; }
    form input, form select { font-size: 16px; }
    .row { display: flex; gap: 10px; }
    .row .field { flex: 1; }
    form select {
      width: 100%; height: 100%; outline: none; padding: 0 45px; font-size: 16px;
      background: none; border-radius: 5px; border: 1px solid #bfbfbf;
      border-bottom-width: 2px; color: #333; appearance: none; cursor: pointer;
    }
    form select:focus { border-color: #5372F0; }
    form select:invalid { color: #bfbfbf; }   /* grey like the other placeholders */
    form select option { color: #333; }
    .row input { padding: 0 10px 0 40px; }
    .switch-link { margin-top: 5px; font-size: 15px; }
  </style>
</head>
<body>
<div class="wrapper">
  <img src="Epoka.png" alt="logo" style="height:73px; width: 200px; margin-bottom: 5px;">
  <header style="color: #0c5ca7;">Sign Up</header>
  <form action="" method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

    <div class="row">
      <div class="field">
        <div class="input-area">
          <input type="text" name="name" placeholder="Name" value="<?php echo htmlspecialchars($old['name']); ?>" required>
          <i class="icon fas fa-user"></i>
        </div>
      </div>
      <div class="field">
        <div class="input-area">
          <input type="text" name="surname" placeholder="Surname" value="<?php echo htmlspecialchars($old['surname']); ?>" required>
          <i class="icon fas fa-user"></i>
        </div>
      </div>
    </div>

    <div class="field">
      <div class="input-area">
        <input type="email" name="email" placeholder="Email (@epoka.edu.al)" value="<?php echo htmlspecialchars($old['email']); ?>" required>
        <i class="icon fas fa-envelope"></i>
      </div>
    </div>

    <div class="field">
      <div class="input-area">
        <select name="gender" required>
          <option value="" disabled <?php echo $old['gender'] === '' ? 'selected' : ''; ?>>Gender</option>
          <option value="Male" <?php echo $old['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
          <option value="Female" <?php echo $old['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
        </select>
        <i class="icon fas fa-venus-mars"></i>
      </div>
    </div>

    <div class="field">
      <div class="input-area">
        <input type="text" name="username" autocomplete="off" placeholder="Username" value="<?php echo htmlspecialchars($old['username']); ?>" required>
        <i class="icon fas fa-at"></i>
      </div>
    </div>

    <div class="field">
      <div class="input-area">
        <input type="password" name="password" autocomplete="new-password" placeholder="Password" required>
        <i class="icon fas fa-lock"></i>
      </div>
    </div>

    <div class="field">
      <div class="input-area">
        <input type="password" name="confirm_password" autocomplete="new-password" placeholder="Confirm password" required>
        <i class="icon fas fa-lock"></i>
      </div>
    </div>

    <?php if (!empty($errorMsg)): ?>
      <div class="error-msg" style="color: red; margin-bottom: 5px;">
        <?php echo htmlspecialchars($errorMsg); ?>
      </div>
    <?php endif; ?>

    <button class="submitlogin" type="submit" name="signup" style="width: 100%; border-radius: 5px; font-size: 18px;">Create account</button>
  </form>
  <div class="switch-link">Already have an account? <a href="login.php">Log in</a></div>
</div>
</body>
</html>
