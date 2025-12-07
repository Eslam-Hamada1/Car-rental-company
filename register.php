<?php
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $passwordHash = password_hash($password, PASSWORD_DEFAULT); //password hash
    $license = $_POST['license'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    //validation for input
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($license) || empty($phone) || empty($address)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!is_numeric($phone) || strlen($phone) < 10) {
        $error = "Phone number must be a valid 10-digit number.";
    } elseif (!is_numeric($license) || strlen($license) < 6) {
        $error = "License number must be a valid number with at least 6 digits.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        //unique email validation
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE Email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = "Email is already taken.";
        } else {
            //insert into users table
            $stmt = $pdo->prepare("INSERT INTO Users (FirstName, LastName, Email, Password, UserType, DriverLicenseID, PhoneNumber, Address) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$firstName, $lastName, $email, $passwordHash, 'Customer', $license, $phone, $address]);

            header("Location: index.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="/register.css?v=1.0">
</head>
<body>
    <div class="register-container">
        <div class="registerBox">
            <h1>Register</h1>
        </div>
        <?php if (isset($error)): ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <form action="register.php" method="POST">
            <div>
                <input type="text" name="first_name" placeholder="First Name" required>
                <input type="text" name="last_name" placeholder="Last Name" required>
            </div>
            <div>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div>
                <input type="text" name="license" placeholder="Driver's License" required>
                <input type="text" name="phone" placeholder="Phone Number" required>
            </div>
            <div class="alone">
                <input type="text" name="address" placeholder="Address" required>
            </div>
            <div class="buttonbox">
                <button type="submit">Register</button>
            </div>
        </form>
        <div class="pbox">
            <p>Already have an account? <a href="index.php">Login here</a></p>
        </div>
    </div>
</body>
</html>
