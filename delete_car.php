<?php
session_start();
require_once 'db_connection.php';

//delete car algorithm
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_id = $_POST['car_id'];

    if (!empty($car_id)) {
        try {
            $stmt = $pdo->prepare("DELETE FROM Cars WHERE CarID = :car_id");
            
            $stmt->bindParam(':car_id', $car_id, PDO::PARAM_INT);
            
            //excute query
            if ($stmt->execute()) {
                echo "<p>Car deleted successfully!</p>";
            } else {
                echo "<p>Failed to delete the car. Please try again.</p>";
            }
        } catch (PDOException $e) {
            echo "<p>Error: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>Please provide a valid Car ID.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Car</title>
    <link rel="stylesheet" href="/delete_car.css?v=1.0">
</head>
<body>
    <div class="box1">
        <h2>Delete Car</h2>
    </div>

    <div class="formdiv">
        <form action="delete_car.php" method="POST">
            <div class="box2">
                <input type="number" name="car_id" placeholder="Car ID" required>
            </div>

            <div class="buttonbox">
                <button type="submit">Delete Car</button>
            </div>
        </form>
    </div>
</body>
</html>
