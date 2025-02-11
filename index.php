<?php
session_start();
include 'db.php'; //  the database configuration file

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and execute SQL query
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows > 0) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username; // Store the username
        header('Location: dashboard.php');
        exit();
    } else {
        $error = "Invalid username or password. Try Again ! ";
    }

    // Close statement
    $stmt->close();
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>R&W Computer Systems | Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            
            margin: 0;
        }
        .login-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .login-container:hover {
            transform: scale(1.02);
            box-shadow: 0 6px 12px rgba(0,0,0,0.2);
        }
        .login-container h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
            text-align: center;
        }
        .login-container label {
            display: block;
            margin: 10px 0 5px;
        }
        .login-container input[type="text"], 
        .login-container input[type="password"] {
            width: calc(100% - 22px);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 16px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .login-container input[type="text"]:focus, 
        .login-container input[type="password"]:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0,123,255,0.2);
            outline: none;
        }
        .login-container input[type="submit"] {
            background: #007bff;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: background 0.3s ease, transform 0.3s ease;
        }
        .login-container input[type="submit"]:hover {
            background: #0056b3;
            transform: scale(1.05);
        }
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            z-index: 1000;
            width: 90%;
            max-width: 400px;
            text-align: center;
        }
        .popup.active {
            display: block;
        }
        .popup .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ddd;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            font-size: 16px;
            line-height: 24px;
            text-align: center;
        }
        .popup .close-btn:hover {
            background: #ccc;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form action="index.php" method="post" id="loginForm">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            
            <input type="submit" value="Login">
        </form>
    </div>

    <!-- Popup Modal -->
    <div id="popup" class="popup">
        <button class="close-btn" onclick="closePopup()">×</button>
        <p id="popupMessage"></p>
    </div>

    <script>
        function showPopup(message) {
            document.getElementById('popupMessage').innerText = message;
            document.getElementById('popup').classList.add('active');
        }

        function closePopup() {
            document.getElementById('popup').classList.remove('active');
        }

        document.getElementById('loginForm').addEventListener('submit', function(event) {
            var username = document.getElementById('username').value;
            var password = document.getElementById('password').value;

            if (username.trim() === '' || password.trim() === '') {
                event.preventDefault();
                showPopup('Please fill in all fields.');
            }
        });
    </script>
</body>
</html>
