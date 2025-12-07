<?php
session_start();
require_once 'db_connection.php';

$reportResults = [];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['report_type'])) {
            $reportType = $_POST['report_type'];

            switch ($reportType) {
                case 'all_reservations':
                    $startDate = $_POST['start_date'];
                    $endDate = $_POST['end_date'];

                    $stmt = $pdo->prepare("SELECT Reservations.*, Users.FirstName, Users.LastName, Users.Email, Cars.Model, Cars.PlateID FROM Reservations
                                            JOIN Users ON Reservations.UserID = Users.UserID
                                            JOIN Cars ON Reservations.CarID = Cars.CarID
                                            WHERE Reservations.PickupDate >= :startDate AND Reservations.ReturnDate <= :endDate");
                    $stmt->bindParam(':startDate', $startDate);
                    $stmt->bindParam(':endDate', $endDate);
                    $stmt->execute();
                    $reportResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;

                case 'car_reservations':
                    $carID = $_POST['car_id'];
                    $startDate = $_POST['start_date'];
                    $endDate = $_POST['end_date'];

                    $stmt = $pdo->prepare("SELECT Reservations.*, Cars.Model, Cars.PlateID FROM Reservations
                                            JOIN Cars ON Reservations.CarID = Cars.CarID
                                            WHERE Reservations.CarID = :carID AND Reservations.PickupDate >= :startDate AND Reservations.ReturnDate <= :endDate");
                    $stmt->bindParam(':carID', $carID, PDO::PARAM_INT);
                    $stmt->bindParam(':startDate', $startDate);
                    $stmt->bindParam(':endDate', $endDate);
                    $stmt->execute();
                    $reportResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;

                case 'car_status':
                    $specificDate = $_POST['specific_date'];

                    $stmt = $pdo->prepare("SELECT Cars.*, Reservations.Status AS ReservationStatus FROM Cars
                                            LEFT JOIN Reservations ON Cars.CarID = Reservations.CarID AND :specificDate BETWEEN Reservations.PickupDate AND Reservations.ReturnDate");
                    $stmt->bindParam(':specificDate', $specificDate);
                    $stmt->execute();
                    $reportResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;

                case 'customer_reservations':
                    $userID = $_POST['user_id'];

                    $stmt = $pdo->prepare("SELECT Reservations.*, Users.FirstName, Users.LastName, Cars.Model, Cars.PlateID FROM Reservations
                                            JOIN Users ON Reservations.UserID = Users.UserID
                                            JOIN Cars ON Reservations.CarID = Cars.CarID
                                            WHERE Reservations.UserID = :userID");
                    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
                    $stmt->execute();
                    $reportResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;

                case 'daily_payments':
                    $startDate = $_POST['start_date'];
                    $endDate = $_POST['end_date'];

                    $stmt = $pdo->prepare("SELECT Payments.*, Reservations.PickupDate, Reservations.ReturnDate, Users.FirstName, Users.LastName FROM Payments
                                            JOIN Reservations ON Payments.ReservationID = Reservations.ReservationID
                                            JOIN Users ON Reservations.UserID = Users.UserID
                                            WHERE Payments.PaymentDate BETWEEN :startDate AND :endDate");
                    $stmt->bindParam(':startDate', $startDate);
                    $stmt->bindParam(':endDate', $endDate);
                    $stmt->execute();
                    $reportResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;

                default:
                    $message = "Invalid report type selected.";
            }
        }
    } catch (Exception $e) {
        $message = "Error generating report: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link rel="stylesheet" href="/reports.css?v=1.0">
</head>
<body>
        <h2>Generate Reports</h2>

        <form action="" method="POST">
            <p for="report_type">Select Report Type:</p>
            <select name="report_type" id="report_type" required>
                <option value="all_reservations">All Reservations (By Period)</option>
                <option value="car_reservations">Reservations of a Specific Car (By Period)</option>
                <option value="car_status">Status of All Cars (By Day)</option>
                <option value="customer_reservations">Reservations of a Specific Customer</option>
                <option value="daily_payments">Daily Payments (By Period)</option>
            </select>

            <div id="input-fields">
                <!-- according to the report type selected, inputs will be shown here -->
            </div>
            
            <div class="box">
                <button type="submit">Generate Report</button>
            </div>
        </form>

        <?php if (!empty($message)): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <?php if (!empty($reportResults)): ?>
            <h3>Report Results</h3>
            <table>
                <thead>
                    <tr>
                        <?php foreach (array_keys($reportResults[0]) as $column): ?>
                            <th><?= htmlspecialchars($column) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportResults as $row): ?>
                        <tr>
                            <?php foreach ($row as $value): ?>
                                <td><?= htmlspecialchars($value) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    <script>
        document.getElementById('report_type').addEventListener('change', function () {
            const inputFields = document.getElementById('input-fields');
            inputFields.innerHTML = '';

            switch (this.value) {
                case 'all_reservations':
                case 'car_reservations':
                case 'daily_payments':
                    inputFields.innerHTML = `
                        <p for="start_date">Start Date:</p>
                        <input type="date" name="start_date" id="start_date" required>
                        <p for="end_date">End Date:</p>
                        <input type="date" name="end_date" id="end_date" required>
                    `;
                    if (this.value === 'car_reservations') {
                        inputFields.innerHTML += `
                            <p for="car_id">Car ID:</p>
                            <input type="number" name="car_id" id="car_id" required>
                        `;
                    }
                    break;
                case 'car_status':
                    inputFields.innerHTML = `
                        <p for="specific_date">Specific Date:</p>
                        <input type="date" name="specific_date" id="specific_date" required>
                    `;
                    break;
                case 'customer_reservations':
                    inputFields.innerHTML = `
                        <p for="user_id">Customer ID:</p>
                        <input type="number" name="user_id" id="user_id" required>
                    `;
                    break;
            }
        });
    </script>
</body>
</html>
