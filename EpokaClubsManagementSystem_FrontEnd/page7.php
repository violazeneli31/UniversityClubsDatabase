<?php
mysqli_report(MYSQLI_REPORT_OFF); // PHP 8.1+: show database errors as messages instead of crashing the page
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect unauthenticated users
    exit();
}

$is_admin = ($_SESSION['role'] === 'admin'); // Assuming the user's role is stored in the session

if (isset($_POST['logout'])) {
    // Unset session variables and destroy the session
    session_unset();
    session_destroy();
    header("Location: login.php"); // Redirect to login page after logout
    exit();
}

// Database credentials
$servername = "localhost"; // Change if needed
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password (empty)
$dbname = "epoka_clubs"; // Replace with your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Variables to store messages and selected table
$query_message = "";
$executed_query = "";
$table = "";
$result = null;
$error_occurred = false; 

// Handle form submission to add a new user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $role = "user";

    if(trim($name) == "" || trim($surname) == ""
    || trim($email) == "" || trim($gender) == "" 
    || trim($username) == ""|| trim($password) == ""
    || trim($confirm_password) == ""){
        $query_message = "Fields cannot be empty";
    }
    else if(!preg_match('/^[a-zA-Z0-9._%+-]+@epoka\.edu\.al$/', $email)){
        $query_message = "Please enter an Epoka University email address.";
    }
    else if(!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)){
        $query_message = "Weak Password. Password should be at least 8 characters must include at least 1 uppercase letter, 1 number and 1 symbol.";
    }
    else if ($password !== $confirm_password) {
        $query_message = "Error: Passwords do not match.";
    } else{
        try{
            $insert_sql = "INSERT INTO users (name, surname, email, gender, username, password, role) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("sssssss", $name, $surname, $email, $gender, $username, $hashedPassword, $role);

            if ($stmt->execute()) {
                $query_message = "Record added successfully.";
                $table = "Users";
            } else {
                $query_message = "Error: " . $stmt->error;
                $error_occurred = true;
            }

            $sql = "SELECT id, name, surname, email, gender, username, role FROM $table";
            $result = $conn->query($sql);
        } catch (Exception $e) {
            $query_message = "An error occurred: " . $e->getMessage();
            $error_occurred = true; // Set error flag
        }
        
        $stmt->close();
    }   
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Epoka Clubs Database</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="script.js"></script>
    <script>
        function toggleTriangle(button) {
            const triangle = button.querySelector(".triangle");
            triangle.classList.toggle("down");
        }
        document.addEventListener("DOMContentLoaded", function () {
            const passwordInput = document.getElementById("password");
            const togglePassword = document.getElementById("togglePassword");
            const confirmPasswordInput = document.getElementById("confirm_password");
            const toggleConfirmPassword = document.getElementById("toggleConfirmPassword");
            
            togglePassword.addEventListener("click", function () {
                const type = passwordInput.type === "password" ? "text" : "password";
                passwordInput.type = type;
                this.classList.toggle("fa-eye-slash");
            });

            toggleConfirmPassword.addEventListener("click", function () {
                const type = confirmPasswordInput.type === "password" ? "text" : "password";
                confirmPasswordInput.type = type;
                this.classList.toggle("fa-eye-slash");
            });

            const form = document.getElementById("form");

            <?php if ($error_occurred): ?>
            form.classList.add("error-animation");

            // Remove the class after the animation completes
            setTimeout(() => form.classList.remove("error-animation"), 400);
            <?php endif; ?>
        });

    </script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
        }

        .sidebar {
            width: 180px;
            height: 100vh;
            background-color: #1E2A3B;
            color: #fff;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.5);
            position: fixed;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            justify-content: space-between; /* Space between menu and logout button */
        }

        .sidebar h2 {
            font-size: 1.5em;
            margin-bottom: 20px;
        }

        .sidebar button {
            background: none;
            border: none;
            color: #fff;
            padding: 10px;
            text-align: left;
            width: 100%;
            cursor: pointer;
            transition: background 0.5s;
            font-size: 1em;
            border-radius: 5px;
            position: relative;
        }

        .sidebar button:hover {
            background-color: hsl(215, 32.60%, 22.5%);
        }

        .sidebar img{
            height: 60px;
            width: 175px;
            margin: 10px 0px 30px 0px;
        }

        .triangle {
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-bottom: 10px solid white; /* Downward triangle */
            position: absolute;
            right: 10px; /* Position to the right */
            top: 50%;
            transform: translateY(-50%) rotate(180deg); /* Center vertically */
        }

        .triangle.down {
            transform: translateY(-50%); /* Rotate for upward triangle */
        }

        .content {
            flex-grow: 1;
            padding: 20px 20px 0px 20px;
            background: linear-gradient(135deg, #6a82fb, #fc5c7d);
            min-height: 100vh;
            text-align: center;
            margin-left: 210px; /* Add margin to the left to avoid overlap with sidebar */
        }

        table {
            width: 80%;
            margin: 30px auto;
            border-collapse: collapse;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }

        th, td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #1E2A3B;
            color: #fff;
        }

        tr:nth-child(even) {
            background-color: #f4f4f4;
        }

        tr:nth-child(odd) {
            background-color: #e9e9e9;
        }

        tr:hover {
            background-color: #d1d1d1;
        }

        #form {
            width: 30%;
            margin: 20px auto;
            padding: 25px 25px 20px 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            align: center;
            flex-direction: column;
            text-align: left;
        }

        #form label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 14px;
        }

        #form input, #form button, #form select {
            width: 97%;
            padding: 7px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        #form select {
            padding: 6px;
        }

        #form button {
            background-color: #1E2A3B;
            width: 80%;
            color: #fff;
            border: none;
            cursor: pointer;
            transition: background 0.5s;
            margin: 10px 0px 0px 37.5px;
            padding: 9px;
        }

        #form button:hover {
            background-color: hsl(215, 32.60%, 22.5%);
        }

        #addUser, #editUser, #deleteUser {
            display: none;
        }

        .dropdown_buttons{
            background-color: hsl(215, 32.60%, 20%);
            border-radius: 5px;
        }

        .logout-button {
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            margin-bottom: 30px;
            color: #fff;
            font-size: 1em;
            padding: 10px;
            cursor: pointer;
            transition: background 0.5s;
            border-radius: 5px;
            background-color: hsl(0, 89.90%, 57.30%) !important;
        }

        .logout-button:hover {
            background-color: hsl(0, 89.90%, 67.30%) !important;
        }

        .logout-button i {
            margin-right: 10px; /* Space between icon and text */
        }

        /* Jiggle animation */
        @keyframes jiggle {
            0%, 100% {
                transform: translateX(0);
            }
            25% {
                transform: translateX(-15px);
            }
            50% {
                transform: translateX(15px);
            }
            75% {
                transform: translateX(-15px);
            }
        }

        .error-animation {
            animation: jiggle 0.4s ease-in-out;
        }

        #query_message {
            background-color: rgb(252, 246, 83);
            border-radius: 5px;
            padding-left: 5px;
            font-style: italic;
        }

        #submenu {
            position: absolute;
            display: flex;
            flex-direction: column;
            left: 91%;
            top: 162px;
            width: 160px;
            background-color: hsl(215, 32.60%, 20%) !important;
            border-radius: 5px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div>
            <img src="Epoka.png" alt="epoka"></img>
            <button id="showTables">Show Tables</button>
            <button id="addRecord">Add record</button>
            <div id="submenu">
                <button id="addAdvisor">Advisor</button>
                <button id="addClub">Club</button>
                <button id="addDoSEmployee">DoS Employee</button>
                <button id="addEvent">Event</button>
                <button id="addIsMemberOf">Is Member of</button>
                <button id="addParticipatesIn">Participates in</button>
                <button id="addReport">Report</button>
                <button id="addStudents">Students</button>
                <button id="addSupervises">Supervises</button>
            </div>
            <button id="executeQueries">Execute queries</button>
            <button id="newQuery">New query</button>
            <?php if ($is_admin): ?>
                <button id="userOptions" onclick="toggleTriangle(this)" style="background-color: hsl(215, 32.60%, 25.5%);">User Options<div class="triangle"></div></button>
                <div class="dropdown_buttons">
                    <button id="addUser" style="background-color: hsl(215, 32.60%, 25.5%); margin-top: 1px;">Add User</button>
                    <button id="editUser">Edit User</button>
                    <button id="deleteUser">Delete User</button>
                </div>
            <?php endif; ?>
        </div>
        <div>
            <form method="POST">
                <button type="submit" name="logout" class="logout-button">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <div class="content">
        <h1>Add User</h1>

        <form id="form" action="" method="post">
        <div style="display: flex; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
            <div style="flex: 1;">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required style="width: 93.5%;">
            </div>
            <div style="flex: 1; padding-left: 5px;">
                <label for="surname">Surname:</label>
                <input type="text" id="surname" name="surname" required style="width: 93.5%;">
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; gap: 26px; flex-wrap: wrap;">
            <!-- Email Field -->
            <div style="flex: 3;">
                <label for="email">Email:</label>
                <input type="text" id="email" name="email" required style="width: 100%;">
            </div>
            <!-- Gender Field -->
            <div style="flex: 1;">
                <label for="gender">Gender:</label>
                <select name="gender" id="gender" required style="width: 107.5%;">
                    <option value="" disabled selected hidden></option>
                    <option>Male</option>
                    <option>Female</option>
                    <option>Other</option>
                </select>
            </div>
        </div>

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
        <div style="position: relative; width: 100%; display: flex; align-items: center;">
            <input type="password" id="password" name="password" required style="flex-grow: 1; margin-right: -2%;">
            <i class="fas fa-eye" id="togglePassword" style="position: absolute; right: 5px; bottom: 17px; cursor: pointer; color: #888;"></i>
        </div>

        <label for="confirm_password">Confirm Password:</label>
        <div style="position: relative; width: 100%; display: flex; align-items: center;">
            <input type="password" id="confirm_password" name="confirm_password" required style="flex-grow: 1; margin-right: -2%;">
            <i class="fas fa-eye" id="toggleConfirmPassword" style="position: absolute; right: 5px; bottom: 17px; cursor: pointer; color: #888;"></i>
        </div>

            <p id="query_message"><?php echo htmlspecialchars($query_message); ?></p>
            <button type="submit" name="add_user">Submit</button>
        </form>

        <h2><?php echo $table ? "$table" : ""; ?></h2>

        <!-- Display data if query was successful -->
        <?php if ($table && $result && $result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <?php
                    // Dynamically display column headers
                    $fields = $result->fetch_fields();
                    foreach ($fields as $field) {
                        echo "<th>" . htmlspecialchars($field->name) . "</th>";
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                // Output data for each row
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    foreach ($row as $column) {
                        echo "<td>" . htmlspecialchars($column) . "</td>";
                    }
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        <?php elseif ($table): ?>
            <p>No records found.</p>
        <?php endif; ?>

        <?php
        // Close the connection
        $conn->close();
        ?>
    </div>
</body>
</html>