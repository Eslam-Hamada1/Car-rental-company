<?php
session_start();
require_once 'db_connection.php';

$results = [];
$search_type = isset($_POST['search_type']) ? $_POST['search_type'] : '';
$search_term = isset($_POST['search_term']) ? $_POST['search_term'] : '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($search_term)) {
        $search_term = "%$search_term%";

        if ($search_type === 'car') { //car query
            $stmt = $pdo->prepare("
                SELECT c.*, o.OfficeName 
                FROM Cars c
                LEFT JOIN Offices o ON c.OfficeID = o.OfficeID
                WHERE c.Model LIKE :search_term1 
                OR c.Year LIKE :search_term2 
                OR c.PlateID LIKE :search_term3 
                OR c.Status LIKE :search_term4 
                OR o.OfficeName LIKE :search_term5 
                OR c.PricePerDay LIKE :search_term6
            ");
            $stmt->bindParam(':search_term1', $search_term, PDO::PARAM_STR);
            $stmt->bindParam(':search_term2', $search_term, PDO::PARAM_STR);
            $stmt->bindParam(':search_term3', $search_term, PDO::PARAM_STR);
            $stmt->bindParam(':search_term4', $search_term, PDO::PARAM_STR);
            $stmt->bindParam(':search_term5', $search_term, PDO::PARAM_STR);
            $stmt->bindParam(':search_term6', $search_term, PDO::PARAM_STR);
        } elseif ($search_type === 'customer') { //customer query
            $stmt = $pdo->prepare("
                SELECT u.*, r.ReservationDate, r.PickupDate, r.ReturnDate, r.TotalPayment, c.Model AS CarModel
                FROM Users u
                LEFT JOIN Reservations r ON u.UserID = r.UserID
                LEFT JOIN Cars c ON r.CarID = c.CarID
                WHERE u.FirstName LIKE :search_term1 
                OR u.LastName LIKE :search_term2 
                OR u.Email LIKE :search_term3 
                OR u.PhoneNumber LIKE :search_term4 
                OR u.Address LIKE :search_term5 
                OR u.DriverLicenseID LIKE :search_term6 
                OR u.UserType LIKE :search_term7
            ");
            $stmt->bindParam(':search_term1', $search_term, PDO::PARAM_STR);
            $stmt->bindParam(':search_term2', $search_term, PDO::PARAM_STR);
            $stmt->bindParam(':search_term3', $search_term, PDO::PARAM_STR);
            $stmt->bindParam(':search_term4', $search_term, PDO::PARAM_STR);
            $stmt->bindParam(':search_term5', $search_term, PDO::PARAM_STR);
            $stmt->bindParam(':search_term6', $search_term, PDO::PARAM_STR);
            $stmt->bindParam(':search_term7', $search_term, PDO::PARAM_STR);
        } elseif ($search_type === 'reservation') { //reservations query
            $stmt = $pdo->prepare("
                SELECT r.*, u.FirstName, u.LastName, u.Email, c.Model AS CarModel, o.OfficeName
                FROM Reservations r
                LEFT JOIN Cars c ON r.CarID = c.CarID
                LEFT JOIN Users u ON r.UserID = u.UserID
                LEFT JOIN Offices o ON c.OfficeID = o.OfficeID
                WHERE r.ReservationDate LIKE :search_term1 
                OR r.PickupDate LIKE :search_term2 
                OR r.ReturnDate LIKE :search_term3 
                OR r.TotalPayment LIKE :search_term4 
                OR r.Status LIKE :search_term5 
                OR c.Model LIKE :search_term6 
                OR o.OfficeName LIKE :search_term7 
                OR u.FirstName LIKE :search_term8 
                OR u.LastName LIKE :search_term9 
                OR u.Email LIKE :search_term10
            ");
            $stmt->bindParam(':search_term1', $search_term, PDO::PARAM_STR);
            $stmt->bindParam(':search_term2', $search_term, PDO::PARAM_STR);
            $stmt->bindParam(':search_term3', $search_term, PDO::PARAM_STR);
            $stmt->bindParam(':search_term4', $search_term, PDO::PARAM_STR);
            $stmt->bindParam(':search_term5', $search_term, PDO::PARAM_STR);
            $stmt->bindParam(':search_term6', $search_term, PDO::PARAM_STR);
            $stmt->bindParam(':search_term7', $search_term, PDO::PARAM_STR);
            $stmt->bindParam(':search_term8', $search_term, PDO::PARAM_STR);
            $stmt->bindParam(':search_term9', $search_term, PDO::PARAM_STR);
            $stmt->bindParam(':search_term10', $search_term, PDO::PARAM_STR);
        }

        //execute query
        $stmt->execute();

        //results
        $results = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Search</title>
    <link rel="stylesheet" href="/advanced_search.css?v=1.0">
</head>
<body>
    <div class="box1">
        <h2>Advanced Search</h2>
    </div>

    <div class="formdiv">
        <form action="" method="POST">
            <div class="box">
                <input type="text" name="search_term" placeholder="Enter search term" required>
            </div>
            
            <div class="box">
                <select name="search_type" required>
                    <option value="car">Search by Car</option>
                    <option value="customer">Search by Customer</option>
                    <option value="reservation">Search by Reservation</option>
                </select>
            </div>

            <div class="box">
                <button type="submit">Search</button>
            </div>
        </form>
    </div>

    <?php if (!empty($results)): ?>
        <div class="results">
            <h3>Search Results:</h3>
            <table>
                <?php if ($search_type === 'car'): ?>
                    <thead>
                        <tr>
                            <th>Model</th>
                            <th>Year</th>
                            <th>Plate ID</th>
                            <th>Status</th>
                            <th>Office</th>
                            <th>Price Per Day</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['Model']) ?></td>
                                <td><?= htmlspecialchars($row['Year']) ?></td>
                                <td><?= htmlspecialchars($row['PlateID']) ?></td>
                                <td><?= htmlspecialchars($row['Status']) ?></td>
                                <td><?= htmlspecialchars($row['OfficeName']) ?></td>
                                <td><?= htmlspecialchars($row['PricePerDay']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                <?php elseif ($search_type === 'customer'): ?>
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Car Model</th>
                            <th>Pickup Date</th>
                            <th>Return Date</th>
                            <th>Total Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['FirstName']) ?></td>
                                <td><?= htmlspecialchars($row['LastName']) ?></td>
                                <td><?= htmlspecialchars($row['Email']) ?></td>
                                <td><?= htmlspecialchars($row['CarModel']) ?></td>
                                <td><?= htmlspecialchars($row['PickupDate']) ?></td>
                                <td><?= htmlspecialchars($row['ReturnDate']) ?></td>
                                <td><?= htmlspecialchars($row['TotalPayment']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                <?php elseif ($search_type === 'reservation'): ?>
                    <thead>
                        <tr>
                            <th>Reservation Date</th>
                            <th>Pickup Date</th>
                            <th>Return Date</th>
                            <th>Customer Name</th>
                            <th>Car Model</th>
                            <th>Office</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['ReservationDate']) ?></td>
                                <td><?= htmlspecialchars($row['PickupDate']) ?></td>
                                <td><?= htmlspecialchars($row['ReturnDate']) ?></td>
                                <td><?= htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']) ?></td>
                                <td><?= htmlspecialchars($row['CarModel']) ?></td>
                                <td><?= htmlspecialchars($row['OfficeName']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                <?php endif; ?>
            </table>
        </div>
    <?php endif; ?>
</body>
</html>
