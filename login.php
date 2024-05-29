<?php
session_start();
include 'db_connect.php';

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'customer') {
                header('Location: customer_dashboard.php');
            } else if ($user['role'] == 'organizer') {
                header('Location: organizer_dashboard.php');
            }
            exit();
        } else {
            $error_message = "Invalid password.";
        }
    } else {
        $error_message = "No user found.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SeatAble</title>
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
        .error-message{
            color: red;
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
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <form method="POST">
                <h2>Login</h2>
                <input type="text" name="username" placeholder="Username" required><br>
                <input type="password" name="password" placeholder="Password" required><br>
                <div class="error-message">
                    <?php echo $error_message; ?>
                </div>
                <button type="submit">Login</button>
                <div class="signup-link">
            <p>Forgot Password? <a href="change_password.php">Change Password</a></p>
            <p>Don't have an account? <a href="register.php">Sign Up</a></p>
        </div>
            </form>
        </div>
        
    </div>
</body>
</html>
