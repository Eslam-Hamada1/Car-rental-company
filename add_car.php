<?php
session_start();
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $model = $_POST['model'];
    $year = $_POST['year'];
    $plate_id = $_POST['plate_id'];
    $price_per_day = $_POST['price_per_day'];
    $office = $_POST['office'];
    $status = $_POST['status'];

    if (!empty($model) && !empty($year) && !empty($plate_id) && !empty($price_per_day) && !empty($office) && !empty($status)) {
        try {
            //SQL insert
            $stmt = $pdo->prepare("INSERT INTO Cars (Model, Year, PlateID, PricePerDay, OfficeID, Status) 
                                    VALUES (:model, :year, :plate_id, :price_per_day, :office, :status)");

            $stmt->bindParam(':model', $model);
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
            $stmt->bindParam(':plate_id', $plate_id);
            $stmt->bindParam(':price_per_day', $price_per_day);
            $stmt->bindParam(':office', $office, PDO::PARAM_INT);
            $stmt->bindParam(':status', $status);

            //excute query
            $stmt->execute();

            echo "<p>Car added successfully!</p>";
        } catch (PDOException $e) {
            echo "<p>Error: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>Please fill in all fields.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Management</title>
    <link rel="stylesheet" href="/add_car.css?v=1.0">
</head>
<body>
    <div class="box1">
        <h2>Car Management</h2>
    </div>

    <div class="formdiv">
        <form action="add_car.php" method="POST">
            <div class="box2">
                <input type="text" name="model" placeholder="Car Model" required>
            </div>

            <div class="box2">
                <input type="number" name="year" placeholder="Year" required>
            </div>
            
            <div class="box2">
                <input type="text" name="plate_id" placeholder="Plate ID" required>
            </div>
            
            <div class="box2">
                <input type="number" name="price_per_day" placeholder="Price per Day" required>
            </div>
            
            <div class="box2">
                <input type="number" name="office" placeholder="Office Number" required>
            </div>
            
            <div class="box3">
                <select name="status" class="box2">
                    <option value="Active">Active</option>
                    <option value="Out of Service">Out of Service</option>
                    <option value="Rented">Rented</option>
                </select>
            </div>

            <div class="buttonbox">
                <button type="submit">Add Car</button>
            </div>
        </form>
    </div>
</body>
</html>
