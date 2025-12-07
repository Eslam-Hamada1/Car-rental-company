<?php
session_start();
require_once 'db_connection.php';

$carData = [];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['search']) && !empty($_POST['car_id'])) {
        //search for car id (primary key, unique)
        $carID = $_POST['car_id'];
        $stmt = $pdo->prepare("SELECT * FROM Cars WHERE CarID = :carID");
        $stmt->bindParam(':carID', $carID, PDO::PARAM_INT);
        $stmt->execute();
        $carData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$carData) {
            $message = "Car with ID $carID not found.";
        }
    } elseif (isset($_POST['update']) && !empty($_POST['car_id'])) {
        $carID = $_POST['car_id'];
        $model = $_POST['model'];
        $year = $_POST['year'];
        $plateID = $_POST['plate_id'];
        $status = $_POST['status'];
        $officeID = $_POST['office_id'];
        $pricePerDay = $_POST['price_per_day'];

        $pdo->beginTransaction();

        try {
            //delete car form cars table
            $stmt = $pdo->prepare("DELETE FROM Cars WHERE CarID = :carID");
            $stmt->bindParam(':carID', $carID, PDO::PARAM_INT);
            $stmt->execute();

            //insert again but with modified attributes
            $stmt = $pdo->prepare("
                INSERT INTO Cars (CarID, Model, Year, PlateID, Status, OfficeID, PricePerDay) 
                VALUES (:carID, :model, :year, :plateID, :status, :officeID, :pricePerDay)
            ");
            $stmt->bindParam(':carID', $carID, PDO::PARAM_INT);
            $stmt->bindParam(':model', $model, PDO::PARAM_STR);
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
            $stmt->bindParam(':plateID', $plateID, PDO::PARAM_STR);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':officeID', $officeID, PDO::PARAM_INT);
            $stmt->bindParam(':pricePerDay', $pricePerDay, PDO::PARAM_STR);
            $stmt->execute();

            $pdo->commit();

            $message = "Car with ID $carID has been updated successfully.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Failed to update car: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Car</title>
    <link rel="stylesheet" href="/update_car.css?v=1.0">
</head>
<body>
    <div class="GREATDIV1">
        <h2>Update Car Information</h2>
        <div class="box1">
            <form action="" method="POST">
                <div class="box">
                    <input type="number" placeholder="Car ID" name="car_id" id="car_id" required value="<?= htmlspecialchars($_POST['car_id'] ?? '') ?>">
                </div>

                <div class="box3">
                    <button type="submit" name="search">Search</button>
                </div>
            </form>
        </div>
    </div>

<?php if (!empty($carData)): ?>
    <div class="GREATDIV2">
        <form action="" method="POST">
            <input type="hidden" name="car_id" value="<?= htmlspecialchars($carData['CarID']) ?>">

            <div class="box">   
                <p for="model">Model:</p>
                <input type="text" name="model" id="model" value="<?= htmlspecialchars($carData['Model']) ?>" required>

                <p for="year">Year:</p>
                <input type="number" name="year" id="year" value="<?= htmlspecialchars($carData['Year']) ?>" required>
            
                <p for="plate_id">Plate ID:</p>
                <input type="text" name="plate_id" id="plate_id" value="<?= htmlspecialchars($carData['PlateID']) ?>" required>

                <p for="status">Status:</p>
                <select name="status" id="status" required>
                    <option value="Active" <?= $carData['Status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                    <option value="Out of Service" <?= $carData['Status'] === 'Out of Service' ? 'selected' : '' ?>>Out of Service</option>
                    <option value="Rented" <?= $carData['Status'] === 'Rented' ? 'selected' : '' ?>>Rented</option>
                </select>

                <p for="office_id">Office ID:</p>
                <input type="number" name="office_id" id="office_id" value="<?= htmlspecialchars($carData['OfficeID']) ?>" required>

                <p for="price_per_day">Price Per Day:</p>
                <input type="number" step="0.01" name="price_per_day" id="price_per_day" value="<?= htmlspecialchars($carData['PricePerDay']) ?>" required>
                </div>
                
                <div class="box3">
                    <button type="submit" name="update">Update</button>
                </div>
        </form>
    </div>
<?php endif; ?>

<?php if (!empty($message)): ?>
    <p><?= htmlspecialchars($message) ?></p>
<?php endif; ?>
</body>
</html>
