<?php
include 'db_connect.php';

$error_username = "";
$error_password = "";
$error_role = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($username)) {
        $error_username = "Username is required.";
    } elseif (strlen($username) < 6) {
        $error_username = "Username must be at least 6 characters long.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error_username = "Username can only contain letters, numbers, and underscores.";
    }

    if (empty($password)) {
        $error_password = "Password is required.";
    } elseif (strlen($password) < 8) {
        $error_password = "Password must be at least 8 characters long.";
    }

    if (empty($error_username) && empty($error_password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $hashed_password, $role);

        if ($stmt->execute()) {
            header('Location: login.php');
        } else {
            $error_message = "Error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
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
        form {
            width: 500px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        input, select, button {
            width: 100%;
            margin-bottom: 10px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
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
        .login-link {
            text-align: center;
            margin-top: 10px;
        }
        .login-link a {
            color: #4CAF50; 
            text-decoration: none;
        }
        .error{
            color: red;
        }
    </style>
</head>
<body>
<form method="POST">
        <h2>Signup</h2>
        <input type="text" name="username" placeholder="Username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
        <span class="error"><?php echo $error_username; ?></span><br>
        
        <input type="password" name="password" placeholder="Password" required>
        <span class="error"><?php echo $error_password; ?></span><br>

        <select name="role" required>
            <option value="" disabled selected hidden>Select Role</option>
            <option value="organizer">Organizer</option>
            <option value="customer">Customer</option>
        </select>
        <span class="error"><?php echo $error_role; ?></span><br>

        <button type="submit">Signup</button>
        <div class="login-link">
            <p>Already have an account? <a href="login.php">Go to Login</a></p>
        </div>
    </form>
</body>
</html>
