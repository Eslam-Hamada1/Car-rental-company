<?php
include('db_connection.php');
session_start();    
$userID = $_SESSION['user_id'];

//if pressed Return button
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_reservation'])) {
    $reservationID = $_POST['reservation_id'];
    $carID = $_POST['car_id'];

    $query = "SELECT PickupDate FROM Reservations WHERE ReservationID = :reservationID AND UserID = :userID";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':reservationID', $reservationID, PDO::PARAM_INT);
    $stmt->bindValue(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($reservation) {
        $pickupDate = $reservation['PickupDate'];
        $currentDate = date('Y-m-d');

        // If return date is before pickup date, then ok
        if ($currentDate < $pickupDate) {
            $pdo->beginTransaction();
            try {
                //update reservation status to "Cancelled"
                $updateReservationQuery = "UPDATE Reservations SET Status = 'Cancelled' WHERE ReservationID = :reservationID";
                $stmt = $pdo->prepare($updateReservationQuery);
                $stmt->bindValue(':reservationID', $reservationID, PDO::PARAM_INT);
                $stmt->execute();

                //delete payment record from table (we assume returning money xD)
                $deletePaymentQuery = "DELETE FROM Payments WHERE ReservationID = :reservationID";
                $stmt = $pdo->prepare($deletePaymentQuery);
                $stmt->bindValue(':reservationID', $reservationID, PDO::PARAM_INT);
                $stmt->execute();

                //update car status to "Active"
                $updateCarStatusQuery = "UPDATE Cars SET Status = 'Active' WHERE CarID = :carID";
                $stmt = $pdo->prepare($updateCarStatusQuery);
                $stmt->bindValue(':carID', $carID, PDO::PARAM_INT);
                $stmt->execute();

                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                echo "Error: " . $e->getMessage();
            }
        }
    }
}


//query to get reservations and office locations
$query = "
    SELECT r.ReservationID, r.CarID, r.PickupDate, r.ReturnDate, r.TotalPayment, r.Status, c.OfficeID, o.Location AS OfficeLocation 
    FROM Reservations r
    JOIN Cars c ON r.CarID = c.CarID
    JOIN Offices o ON c.OfficeID = o.OfficeID
    WHERE r.UserID = :userID
";
$stmt = $pdo->prepare($query);
$stmt->bindValue(':userID', $userID, PDO::PARAM_INT);
$stmt->execute();
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reservations</title>
    <link rel="stylesheet" href="reservations.css">
</head>
<body>
    <h1>My Reservations</h1>
    <table>
        <tr>
            <th>Reservation ID</th>
            <th>Car ID</th>
            <th>Pickup Date</th>
            <th>Return Date</th>
            <th>Total Payment</th>
            <th>Status</th>
            <th>Office Location</th>
            <th>Return</th>
        </tr>
        <?php foreach ($reservations as $reservation): ?>
            <tr>
                <td><?= htmlspecialchars($reservation['ReservationID']) ?></td>
                <td><?= htmlspecialchars($reservation['CarID']) ?></td>
                <td><?= htmlspecialchars($reservation['PickupDate']) ?></td>
                <td><?= htmlspecialchars($reservation['ReturnDate']) ?></td>
                <td><?= htmlspecialchars($reservation['TotalPayment']) ?></td>
                <td><?= htmlspecialchars($reservation['Status']) ?></td>
                <td><?= htmlspecialchars($reservation['OfficeLocation']) ?></td>
                <td>
                    <?php if (date('Y-m-d') < $reservation['PickupDate']): ?>
                        <form method="POST" action="reservations.php">
                            <input type="hidden" name="reservation_id" value="<?= $reservation['ReservationID'] ?>">
                            <input type="hidden" name="car_id" value="<?= $reservation['CarID'] ?>">
                            <button type="submit" name="return_reservation">Return</button>
                        </form>
                    <?php else: ?>
                        <span>Cannot return past pickup date</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <form method="GET" action="dashboard.php" style="display: inline;">
        <button class="rollback" type="submit">Roll Back</button>
    </form>
</body>
</html>
