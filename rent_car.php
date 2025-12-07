<?php
include('db_connection.php');

if (isset($_POST['car_id'])) {
    $car_id = $_POST['car_id'];

    //get car info from car id
    $query = "SELECT * FROM Cars WHERE CarID = :car_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':car_id', $car_id, PDO::PARAM_INT);
    $stmt->execute();
    $car = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$car) {
        header("Location: dashboard.php?error=CarNotFound");
        exit;
    }
    
    session_start();
    $user_id = $_SESSION['user_id'];
} else {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Car</title>
    <link rel="stylesheet" href="rent_car.css">
    <script>
        function calculatePayment() {
            const pickupDate = document.getElementById('pickup_date').value;
            const returnDate = document.getElementById('return_date').value;
            const pricePerDay = <?php echo $car['PricePerDay']; ?>; // Get the car's price per day from PHP

            if (pickupDate && returnDate) {
                const pickup = new Date(pickupDate);
                const returnD = new Date(returnDate);
                
                const diffTime = returnD - pickup;
                const diffDays = diffTime / (1000 * 3600 * 24);

                if (diffDays >= 1) {
                    const totalPayment = diffDays * pricePerDay;
                    document.getElementById('total_payment').value = totalPayment.toFixed(2);
                } else {
                    document.getElementById('total_payment').value = "Invalid dates!";
                }
            }
        }
    </script>
</head>
<body>
    <div class="box">
        <h1>Rent Car</h1>
    </div>
    <div class="bigdiv">
        <div class="div1">
            <h2>Car Information</h2>
            <p>Model: <?php echo htmlspecialchars($car['Model']); ?></p>
            <p>Year: <?php echo htmlspecialchars($car['Year']); ?></p>
            <p>Plate ID: <?php echo htmlspecialchars($car['PlateID']); ?></p>
            <p>Price/Day: <?php echo htmlspecialchars($car['PricePerDay']); ?></p>
        </div>

        <div class="div2">
            <form method="POST" action="process_reservation.php">
                <input type="hidden" name="car_id" value="<?php echo $car_id; ?>">
                <p class="labels" for="pickup_date">Pickup Date:</p>
                <input type="date" id="pickup_date" name="pickup_date" onchange="calculatePayment()" required><br>
                <p class="labels" for="return_date">Return Date:</p>
                <input type="date" id="return_date" name="return_date" onchange="calculatePayment()" required><br>

                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

                <p class="labels" for="total_payment">Total Payment:</p>
                <input type="text" id="total_payment" name="total_payment" readonly><br>
                
                <button type="submit" name="reserve">Confirm Reservation</button>
            </form>
        </div>
    </div>
</body>
</html>
