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

// Variables to store messages and results
$query_message = "";
$error_occurred = false; // Track error occurrence
$result = null;
$table_name = "";

// Handle form submission to execute a query
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['execute_sql_query'])) {
    $query = $_POST['query'];
    $executed_query = trim($query); // Sanitize input query

    if (!empty($executed_query)) {
        // Check for restricted actions
        if (stripos($executed_query, "delete") !== false && !$is_admin) {
            $query_message = "Permission to use the DELETE command DENIED.";
            $error_occurred = true; // Set error flag
        } elseif (stripos($executed_query, "users") !== false && !$is_admin) {
            $query_message = "Permission to access table Users DENIED.";
            $error_occurred = true; // Set error flag
        } else {
            try {
                if(stripos($executed_query, "users" ) !== false && $is_admin) {
                    $conn2 = new mysqli($servername, $username, $password, "epoka_clubs");
                    if ($result = $conn2->query($executed_query)) {
                        if (stripos($executed_query, "select") === 0) {
                            $query_message = "Query executed successfully.";
                            $table_name = "Query Result";
                        } else {
                            $query_message = "Query executed successfully. Affected rows: " . $conn->affected_rows;
                        }
                    } else {
                        throw new Exception("Error executing query: " . $conn->error);
                    }
                }
                // Execute the query
                else if ($result = $conn->query($executed_query)) {
                    if (stripos($executed_query, "select") === 0) {
                        $query_message = "Query executed successfully.";
                        $table_name = "Query Result";
                    } else {
                        $query_message = "Query executed successfully. Affected rows: " . $conn->affected_rows;
                    }
                } else {
                    throw new Exception("Error executing query: " . $conn->error);
                }
            } catch (Exception $e) {
                $query_message = "An error occurred: " . $e->getMessage();
                $error_occurred = true; // Set error flag
            }
        }
    } else {
        $query_message = "Please enter a query.";
        $error_occurred = true; // Set error flag
    }
}

// Close the connection at the end of the script
$conn->close();
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
        // Toggle triangle icon
        function toggleTriangle(button) {
            const triangle = button.querySelector(".triangle");
            triangle.classList.toggle("down");
        }
        // Add the jiggle effect if an error occurs
        document.addEventListener("DOMContentLoaded", () => {
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
            position: relative; /* Position relative for triangle positioning */
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
            width: 40%;
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

        #form input, #form button {
            width: 97%;
            padding: 7px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        #form textarea {
            font-size: 16px;
            resize: none;
        }

        #form button {
            background-color: #1E2A3B;
            width: 80%;
            color: #fff;
            border: none;
            cursor: pointer;
            transition: background 0.5s;
            margin: 10px 0px 0px 45px;
            padding: 9px;
        }

        #form button:hover {
            background-color: hsl(215, 32.60%, 22.5%);
        }

        #addUser, #editUser, #deleteUser {
            display: none;
        }

        .dropdown_buttons {
            background-color: hsl(215, 32.60%, 20%);
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
            <button id="newQuery" style="background-color: hsl(215, 32.60%, 25.5%);">New query</button>
            <?php if ($is_admin): ?>
                <button id="userOptions" onclick="toggleTriangle(this)">User Options<div class="triangle"></div></button>
                <div class="dropdown_buttons">
                    <button id="addUser">Add User</button>
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
        <h1>New Query</h1>

        <!-- Form for Query Input -->
        <form id="form" action="" method="post">
            <label for="query">Write Your SQL Query:</label>
            <textarea id="query" name="query" rows="10" placeholder="Enter your query..."></textarea>
            <p id="query_message"><?php echo $query_message; ?></p>
            <button type="submit" name="execute_sql_query">Execute Query</button>
        </form>

        <!-- Display Result -->
        <h2><?php echo $table_name ? "Data from Query Result" : ""; ?></h2>

        <?php if ($table_name && $result && $result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <?php
                    $fields = $result->fetch_fields();
                    foreach ($fields as $field) {
                        echo "<th>" . htmlspecialchars($field->name) . "</th>";
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
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
        <?php elseif ($table_name): ?>
            <p>No records found.</p>
        <?php endif; ?>
    </div>
</body>
</html>