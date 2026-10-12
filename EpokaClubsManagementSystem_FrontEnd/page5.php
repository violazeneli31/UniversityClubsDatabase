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
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password (empty)
$dbname = "epoka_clubs"; // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Variables to store messages and selected table
$query_message = "";
$executed_query = "";
$table_name = "";
$result = null;

// Function to format query for better display with <br> before specific SQL keywords
function formatQuery($query) {
    // Define the SQL keywords that you want to move to new lines
    $keywords = ['SELECT', 'FROM', 'WHERE', 'GROUP BY', 'ORDER BY', 'HAVING'];

    // Loop through each keyword and add <br> tag before it
    foreach ($keywords as $keyword) {
        // Add <br> tag before each keyword
        $query = preg_replace('/\b(' . preg_quote($keyword, '/') . ')\b/', '<br>$1', $query);
    }

    return $query;  // Return the query formatted with <br> tags
}


// Execute random queries
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['random_query'])) {
    $random_queries = [
        "SELECT s.StFName AS FirstName, s.StLName AS LastName, c.CName AS ClubName FROM Students s, isMemberOf im, Club c WHERE s.StID = im.StID AND im.CID = c.CID AND im.RoleForClub = 'President';",
        "SELECT CName AS ClubName, CNrOfParticipants AS Participants FROM Club WHERE CNrOfParticipants > 500 ORDER BY CName ASC;",
        "SELECT e.EName AS EventName, c.CName AS ClubName, e.EDate AS EventDate FROM Event e, Club c WHERE e.CID = c.CID AND c.CFoundDate < '2020-01-01' ORDER BY e.EDate DESC;",
        "SELECT RoleForClub AS Role, AVG(Attendance) AS AverageAttendance FROM isMemberOf GROUP BY RoleForClub;",
        "SELECT a.AFirstName AS FirstName, a.ALastName AS LastName, c.CName AS ClubName FROM Advisor a,Supervises s,Club c WHERE a.AID = s.AID and s.CID = c.CID ORDER BY a.AFirstName ASC;",
        "SELECT e.EName AS EventName, COUNT(pi.StID) AS TotalParticipants FROM Event e, participatesIn pi WHERE e.EID = pi.EID GROUP BY e.EName HAVING COUNT(pi.StID) > 6;",
        "SELECT c.CName AS ClubName, c.CNrOfParticipants AS TotalParticipants, a.AFirstName AS SupervisorFirstName, a.ALastName AS SupervisorLastName FROM Club c, Supervises s,Advisor a WHERE c.CID = s.CID and s.AID = a.AID ORDER BY c.CNrOfParticipants DESC LIMIT 3;",
        "SELECT c.CID AS ClubID, c.CName AS ClubName, s.StID AS StudentID, s.StFName AS StudentFirstName, s.StLName AS StudentLastName, im.Attendance AS HighestAttendance FROM isMemberOf im JOIN Students s ON im.StID = s.StID JOIN Club c ON im.CID = c.CID WHERE im.Attendance = (SELECT MAX(im2.Attendance) FROM isMemberOf im2 WHERE im2.CID = im.CID) ORDER BY c.CName;",
        "SELECT c.CName AS ClubName, e.EName AS EventName, e.EDate AS EventDate FROM Club c ,Event e WHERE c.CID = e.CID and e.ESponsor LIKE '%Google%' ORDER BY e.EDate DESC;",
        "SELECT s.StFName AS StudentFirstName, s.StLName AS StudentLastName, c.CName AS ClubName FROM Students s JOIN isMemberOf im ON s.StID = im.StID JOIN Club c ON im.CID = c.CID WHERE c.CNrOfParticipants > (SELECT AVG(CNrOfParticipants) FROM Club) ORDER BY s.StFName, s.StLName;",
        "SELECT s.StID AS StudentID, s.StFName AS StudentFirstName, s.StLName AS StudentLastName, e.EName AS EventName FROM Students s JOIN participatesIn pi ON s.StID = pi.StID JOIN Event e ON pi.EID = e.EID WHERE e.CID IN ( SELECT im.CID FROM isMemberOf im WHERE im.StID = s.StID) ORDER BY s.StFName;",
        "SELECT DISTINCT StDepartment AS Department FROM Students;",
        "SELECT s.StID, s.StFName, s.StLName, c.CName AS ClubName FROM Students s JOIN isMemberOf im ON s.StID = im.StID JOIN Club c ON im.CID = c.CID WHERE c.CNrOfParticipants > ( SELECT AVG(CNrOfParticipants) FROM Club);",
        "SELECT c.CID AS ClubID, c.CName AS ClubName, e.EName AS EventName FROM Club c LEFT JOIN Event e ON c.CID = e.CID;",
        "SELECT s.StID AS StudentID, s.StFName AS FirstName, s.StLName AS LastName FROM Students s JOIN participatesIn pi ON s.StID = pi.StID INTERSECT SELECT s.StID, s.StFName, s.StLName FROM Students s JOIN isMemberOf im ON s.StID = im.StID;",
        "SELECT DISTINCT c.CID AS ClubID, c.CName AS ClubName FROM Club c JOIN isMemberOf im ON c.CID = im.CID LEFT JOIN Event e ON c.CID = e.CID WHERE e.EID IS NULL;",
        "SELECT c.CID AS ClubID, c.CName AS ClubName, COUNT(im.StID) AS TotalMembers, AVG(im.Attendance) AS AvgAttendance FROM Club c JOIN isMemberOf im ON c.CID = im.CID GROUP BY c.CID ORDER BY AvgAttendance DESC;",
        "SELECT c.CID as ClubID,c.CName AS ClubName, COUNT(e.EID) AS TotalEvents FROM Club c LEFT JOIN Event e ON c.CID = e.CID GROUP BY c.CID ORDER BY TotalEvents DESC;",
        "SELECT s.StID AS StudentID, s.StFName AS FirstName, s.StLName AS LastName, im.RoleForClub AS Role, c.CName AS ClubName FROM Students s JOIN isMemberOf im ON s.StID = im.StID JOIN Club c ON im.CID = c.CID WHERE s.StYearOfStudy = 1 UNION SELECT s.StID, s.StFName, s.StLName, im.RoleForClub, c.CName FROM Students s JOIN isMemberOf im ON s.StID = im.StID JOIN Club c ON im.CID = c.CID WHERE s.StYearOfStudy = 2 ORDER BY Role;",
        "SELECT * FROM ActiveClubs;",
        "SELECT CONCAT(D.DFirstName, ' ', D.DLastName) AS EmployeeName, R.RAcademicYear AS AcademicYear, R.RDateSubmitted AS DateSubmitted, R.RNrOfEvents AS NumberOfEvents, R.RNrOfClubs AS NumberOfClubs FROM Report R, DOSEmployee D WHERE R.DID = D.DID ORDER BY D.DFirstName;",
        "SELECT CONCAT(A.AFirstName, ' ', A.ALastName) AS AdvisorName, COUNT(S.CID) AS NumberOfClubs FROM Advisor A, Supervises S WHERE A.AID = S.AID GROUP BY A.AID ORDER BY NumberOfClubs DESC;",
        "SELECT E.EName AS EventName, E.EDate AS EventDate, E.ELocation AS Location, E.ESponsor AS Sponsor FROM Event E WHERE E.ESponsor IS NOT NULL ORDER BY E.EDate DESC;",
        "SELECT C.CName AS ClubName FROM Club C LEFT JOIN Supervises S ON C.CID = S.CID WHERE S.AID IS NULL ORDER BY C.CID;",
        "SELECT CONCAT(A.AFirstName, ' ', A.ALastName) AS AdvisorName, COUNT(S.CID) AS TotalClubsSupervised FROM Advisor A JOIN Supervises S ON A.AID = S.AID GROUP BY A.AID HAVING COUNT(S.CID) = (SELECT MAX(ClubCount) FROM (SELECT AID, COUNT(CID) AS ClubCount FROM Supervises GROUP BY AID) AS ClubCounts);"
    ]; 

    // Validate the index
    $query_index = intval($_POST['random_query']);
    if ($query_index >= 0 && $query_index < count($random_queries)) {
        $sql = $random_queries[$query_index];
        $executed_query = formatQuery($sql);  // Format the query with line breaks
        $table_name = "Query Result";

        // Execute query
        $result = $conn ->query($sql);

        // Check for query success
        if ($result) {
            $query_message = "Query executed successfully.";
        } else {
            $query_message = "Error: " . $conn->error;
        }
    } else {
        $query_message = "Invalid query selected.";
        $executed_query = ""; // Set to an empty value to avoid errors
        $result = null; // Reset result
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
        font-size: 1em; /* Increase font size */
        border-radius: 5px; /* Add border radius */
        position: relative; /* Add this line */
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
        padding: 20px;
        background: linear-gradient(135deg, #6a82fb, #fc5c7d);
        min-height: 100vh;
        text-align: center;
        margin-left: 210px; /* Add margin to the left to avoid overlap with sidebar */
    }

    .query-text {
        font-family: Consolas, monospace;
        background-color: #333;
        padding: 15px;
        margin: 30px auto;
        border-radius: 8px;
        font-size: 1.1em;
        width: 80%;
        text-align: left;
        color: #f5f5f5;
    }

    table {
        width: 80%;
        margin: 30px auto;
        border-collapse: collapse;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        background-color: #fff; /* White background for the table */
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
        background-color: #f4f4f4; /* Light gray background for even rows */
    }

    tr:nth-child(odd) {
        background-color: #e9e9e9; /* Slightly darker gray for odd rows */
    }

    tr:hover {
        background-color: #d1d1d1; /* Slightly darker color on row hover */
    }

    /* Style for the buttons in the random queries section */
    .random-queries button {
        background-color: #007bff; /* Button background color */
        color: white; /* Button text color */
        border: none; /* Remove border */
        padding: 10px 10px; /* Padding for the button */
        border-radius: 5px; /* Rounded corners */
        cursor: pointer; /* Pointer cursor on hover */
        margin: 5px 3px; /* Space between buttons */
        transition: 0.5s; /* Transition for hover effect */
        font-weight: bold;
    }

    .random-queries button:hover {
        box-shadow: 0 12px 16px 0 rgba(0,0,0,0.24), 0 17px 50px 0 rgba(0,0,0,0.19);
        background-color:hsl(211, 100.00%, 40.00%); /* Darker blue on hover */
    }

    #addUser, #editUser, #deleteUser{
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
            <button id="executeQueries" style="background-color: hsl(215, 32.60%, 25.5%);">Execute queries</button>
            <button id="newQuery">New query</button>
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
        <h1>Execute Queries</h1>

        <!-- Random query buttons -->
        <form action="" method="post" class="random-queries">
            <?php for ($i = 0; $i < 25; $i++): ?>
                <button type="submit" name="random_query" value="<?php echo $i; ?>">Query <?php echo $i + 1; ?></button>
            <?php endfor; ?>
        </form>

        <!-- Display message after query execution -->
        <?php if ($query_message): ?>
            <p><?php echo $query_message; ?></p>
        <?php endif; ?>

        <!-- Display executed query -->
        <?php if ($executed_query): ?>
            <div class="query-text">Executed Query: <?php echo $executed_query; ?></div>
        <?php endif; ?>

        <!-- Dynamically display table header based on selected table -->
        <h2><?php echo $table_name ? "Data from $table_name" : ""; ?></h2>

        <!-- Display data if query was successful -->
        <?php if ($result && $result->num_rows > 0): ?>
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
        <?php elseif ($table_name): ?>
            <p>No records found.</p>
        <?php endif; ?>

        <?php
        // Close the connection
        $conn->close();
        ?>
    </div>
</body>
</html>