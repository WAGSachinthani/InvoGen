<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = $_POST['db_host'];
    $db_user = $_POST['db_user'];
    $db_pass = $_POST['db_pass'];
    $db_name = $_POST['db_name'];

    // Connect to the database
    $conn = new mysqli($db_host, $db_user, $db_pass);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Create database
    $sql = "CREATE DATABASE $db_name";
    if ($conn->query($sql) === TRUE) {
        echo "Database created successfully<br>";
        $conn->select_db($db_name);

        // Run SQL file to create tables
        $sql = file_get_contents('path_to_your_sql_dump.sql');
        if ($conn->multi_query($sql)) {
            echo "Tables created successfully<br>";
        } else {
            echo "Error creating tables: " . $conn->error;
        }
    } else {
        echo "Error creating database: " . $conn->error;
    }

    $conn->close();
}
?>

<form method="POST">
    <label for="db_host">Database Host:</label><br>
    <input type="text" id="db_host" name="db_host" required><br>
    <label for="db_user">Database User:</label><br>
    <input type="text" id="db_user" name="db_user" required><br>
    <label for="db_pass">Database Password:</label><br>
    <input type="password" id="db_pass" name="db_pass" ><br>
    <label for="db_name">Database Name:</label><br>
    <input type="text" id="db_name" name="db_name" required><br><br>
    <input type="submit" value="Install">
</form>
