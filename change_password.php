<?php
session_start();
include 'db_connect.php';

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error_message = "The new passwords do not match.";
    } else {
       
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            
            $update_sql = "UPDATE users SET password = ? WHERE username = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ss", $hashed_password, $username);
            $update_stmt->execute();

            $error_message = "Password updated successfully.";

        } else {
            $error_message = "No user found with that username.";
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
    <title>Change Password</title>
    <style>
        
        body {
            background: rgb(34,195,36);
background: linear-gradient(0deg, rgba(34,195,36,1) 13%, rgba(77,253,45,0.5634628851540616) 80%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .login-container {
            width: 500px;
            height: 50%px;
        }
        .login-form {
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
            margin-bottom: 20px;
        }
        input {
            width: calc(100% - 20px);
            margin-bottom: 10px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            width: calc(100% - 20px);
            margin-top: 10px;
            padding: 8px;
            border: none;
            border-radius: 5px;
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .signup-link {
            text-align: center;
            margin-top: 10px;
        }
        .signup-link a {
            color: #4CAF50;
            text-decoration: none;
        }
        .error-message {
            color: red;
        }
    </style>
    
</head>
<body>
<div class="login-container">
        <div class="login-form">
            <form method="POST" onsubmit="return validateForm()">
                <h2>Change Password</h2>
                <input type="text" name="username" placeholder="Username" required><br>
                <input type="password" name="new_password" id="newPassword" placeholder="New Password " required><br>
                <input type="password" name="confirm_password" id="confirmPassword" placeholder="Confirm New Password " required><br>
                <div class="error-message" id="passwordError"></div>
                <button type="submit">Change Password</button>
                <div class="signup-link">
                    <p>Back to <a href="login.php">Login</a></p>
                </div>
            </form>
        </div>
    </div>

    <script>
        function validateForm() {
            var newPassword = document.getElementById("newPassword").value;
            var confirmPassword = document.getElementById("confirmPassword").value;
            var passwordError = document.getElementById("passwordError");

            if (newPassword.length < 8 || confirmPassword.length < 8) {
                passwordError.textContent = "Password must be at least 8 characters long.";
                return false; 
            }

  

            return true; 
        }
    </script>
</body>
</html>