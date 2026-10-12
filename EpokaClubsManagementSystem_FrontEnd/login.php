<?php
mysqli_report(MYSQLI_REPORT_OFF); // PHP 8.1+: show database errors as messages instead of crashing the page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

session_start();

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
  header("Location: page1.php"); // Redirect to dashboard if already logged in
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

// Establish connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$errorMsg = ""; // Variable to store error messages
$successMsg = $_SESSION['signup_success'] ?? ""; // Shown once after creating an account
unset($_SESSION['signup_success']);
$fieldError = false; // To track if there's an error in the fields


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
      die("Invalid CSRF token");
  }
  
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validate inputs
    if (empty($username) || empty($password)) {
        $errorMsg = "Please fill in all fields.";
        $fieldError = true;
    } else {
        // Query to fetch the user by username
        $sql = "SELECT id, username, password, role FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql); 
        $stmt->bind_param('s', $username); 
        $stmt->execute(); 
        $result = $stmt->get_result(); 

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Store user information in the session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect to dashboard
                header("Location: page1.php");
                exit();
            } else {
                $errorMsg = "Invalid username or password!";
                $fieldError = true;
            }
        } else {
            $errorMsg = "Invalid username or password!";
            $fieldError = true;
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
  <title>Epoka Clubs Database</title>
  <link rel="stylesheet" href="login-css.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <style>
    /* Add wiggle animation */
    @keyframes wiggle {
      0%, 100% {
        transform: translateX(0);
      }
      25% {
        transform: translateX(-10px);
      }
      50% {
        transform: translateX(10px);
      }
      75% {
        transform: translateX(-10px);
      }
    }

    .input-area.wiggle {
      animation: wiggle 0.3s ease-in-out;
    }
  </style>
</head>
<body>
<div class="wrapper">
  <img src="Epoka.png" alt="logo" style="height:73px; width: 200px; margin-bottom: 5px;">
  <header style="color: #0c5ca7;">Club Database</header>
  <form action="" method="POST" id="loginForm">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

    <!-- Username field -->
    <div class="field email">
      <div class="input-area" id="usernameField">
        <input type="text" name="username" autocomplete="off" placeholder="Username" required>
        <i class="icon fas fa-user"></i>
      </div>
      <div class="error error-txt">Username can't be blank</div>
    </div>

    <!-- Password field -->
    <div class="field password">
      <div class="input-area" id="passwordField">
        <input type="password" id="password" name="password" autocomplete="off" placeholder="Password" required>
        <i class="icon fas fa-lock"></i>
        <i class="fas fa-eye" id="togglePassword" style="position: absolute; right: 10px; bottom: 7px; cursor: pointer; color: #bfbfbf; z-index: 10; pointer-events: auto;"></i>
      </div>
      <div class="error error-txt">Password can't be blank</div>
    </div>

    <!-- Success message after sign up -->
    <?php if (!empty($successMsg)): ?>
      <div class="success-msg" style="color: green; margin-bottom: 5px;">
        <?php echo htmlspecialchars($successMsg); ?>
      </div>
    <?php endif; ?>

    <!-- Error message display -->
    <?php if (!empty($errorMsg)): ?>
      <div class="error-msg" style="color: red; margin-bottom: 5px;">
        <?php echo $errorMsg; ?>
      </div>
    <?php endif; ?>

    <button class="submitlogin" type="submit" name="login" style="width: 100%; border-radius: 5px; font-size: 18px;">Login</button>
  </form>
  <div style="margin-top: 5px; font-size: 15px;">Don't have an account? <a href="signup.php">Sign up</a></div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const passwordInput = document.getElementById("password");
    const togglePassword = document.getElementById("togglePassword");
    
    togglePassword.addEventListener("click", function () {
      console.log("Eye icon clicked");
      const type = passwordInput.type === "password" ? "text" : "password";
      passwordInput.type = type;

      // Toggle the icon class for open/closed eye
      this.classList.toggle("fa-eye-slash");
    });

  const fieldError = <?php echo json_encode($fieldError); ?>;

  if (fieldError) {
    const usernameField = document.getElementById('usernameField');
    const passwordField = document.getElementById('passwordField');

    // Add error class and wiggle animation
    usernameField.classList.add('wiggle');
    passwordField.classList.add('wiggle');

    const usernameInput = usernameField.querySelector('input');
    const passwordInput = passwordField.querySelector('input');

    const usernameIcon = usernameField.querySelector('.icon');
    const passwordIcon = passwordField.querySelector('.icon');

    // Set error styles
    usernameInput.style.borderColor = "#dc3545";
    passwordInput.style.borderColor = "#dc3545";
    usernameIcon.style.color = "#dc3545";
    passwordIcon.style.color = "#dc3545";

    // Remove wiggle class after animation ends
    setTimeout(() => {
      usernameField.classList.remove('wiggle');
      passwordField.classList.remove('wiggle');
    }, 300);
  }
});
</script>
</body>
</html>