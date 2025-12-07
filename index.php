<?php
session_start();
include('db.php');

//if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!preg_match('/^.+@.+\.com$/', $email)) {
        $error_message = "Email must end with '.com'.";
    } else {
        // Prepare and execute the query to get the user by email
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE Email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['Password'])) {
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['first_name'] = $user['FirstName'];
            $_SESSION['user_type'] = $user['UserType'];

            if ($user['UserType'] == 'Admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
            exit;
        } else {
            $error_message = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="mod.css?ver=<?php echo time(); ?>">
    <style>
        .error-message {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Welcome to our Cars Rental Company (CRT)</h1>
        <div class="login">
            <h1>Login</h1>
        </div>

        <?php if ($error_message): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="index.php" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <div class="buttonbox">
                <button type="submit">Login</button>
            </div>
        </form>
        <div class="pbox">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</body>
</html>
