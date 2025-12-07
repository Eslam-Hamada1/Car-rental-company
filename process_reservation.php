<?php
session_start();
include('db_connection.php');

//if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['car_id'])) {
    $car_id = $_POST['car_id'];
    $pickup_date = $_POST['pickup_date'];
    $return_date = $_POST['return_date'];
    $user_id = $_POST['user_id'];
    $total_payment = $_POST['total_payment'];

    //dates validation
    if (strtotime($pickup_date) >= strtotime($return_date)) {
        echo "<script>
                alert('Error: The pickup date must be earlier than the return date.');
                window.location.href = 'rent_car.php?car_id={$car_id}';
                </script>";
        exit;
    }

    //other field validation
    if (empty($car_id) || empty($pickup_date) || empty($return_date) || empty($user_id) || empty($total_payment)) {
        echo "<script>
                alert('Please fill all the required fields.');
                window.location.href = 'rent_car.php?car_id={$car_id}';
                </script>";
        exit;
    }

    try {
        $pdo->beginTransaction();

        //insert into reservations table
        $query = "INSERT INTO Reservations (UserID, CarID, PickupDate, ReturnDate, TotalPayment, Status) 
                VALUES (:user_id, :car_id, :pickup_date, :return_date, :total_payment, 'Reserved')";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':car_id', $car_id, PDO::PARAM_INT);
        $stmt->bindValue(':pickup_date', $pickup_date);
        $stmt->bindValue(':return_date', $return_date);
        $stmt->bindValue(':total_payment', $total_payment, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $reservation_id = $pdo->lastInsertId();

            //insert into payments table
            $payment_query = "INSERT INTO Payments (ReservationID, Amount, PaymentMethod) 
                            VALUES (:reservation_id, :amount, 'Credit Card')";
            $payment_stmt = $pdo->prepare($payment_query);
            $payment_stmt->bindValue(':reservation_id', $reservation_id, PDO::PARAM_INT);
            $payment_stmt->bindValue(':amount', $total_payment, PDO::PARAM_STR);
            $payment_stmt->execute();

            //update car status to "Rented"
            $update_query = "UPDATE Cars SET Status = 'Rented' WHERE CarID = :car_id";
            $update_stmt = $pdo->prepare($update_query);
            $update_stmt->bindValue(':car_id', $car_id, PDO::PARAM_INT);
            $update_stmt->execute();

            $pdo->commit();

            echo "<html>
                    <head>
                        <title>Reservation Successful</title>
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                display: flex;
                                justify-content: center;
                                align-items: center;
                                height: 100vh;
                                background-color: #333333; /* Darker background */
                                margin: 0;
                            }
                            .message-box {
                                text-align: center;
                                padding: 20px;
                                background-color: #4CAF50;
                                color: white;
                                border-radius: 10px;
                            }
                            .timer {
                                font-size: 20px;
                                color: yellow;
                            }
                        </style>
                        <script>
                            var timeLeft = 5; // Timer set to 5 seconds
                            var countdown = setInterval(function() {
                                if (timeLeft <= 0) {
                                    clearInterval(countdown);
                                    window.location.href = 'dashboard.php'; // Redirect to dashboard after 5 seconds
                                } else {
                                    document.getElementById('timer').innerHTML = timeLeft + ' seconds remaining...';
                                }
                                timeLeft -= 1;
                            }, 1000);
                        </script>
                    </head>
                    <body>
                        <div class='message-box'>
                            <h2>Car was rented successfully!</h2>
                            <p>Your reservation and payment have been confirmed.</p>
                            <p><span id='timer' class='timer'></span></p>
                        </div>
                    </body>
                </html>";
            exit;
        } else {
            throw new Exception('Failed to insert reservation.');
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<script>
                alert('Error: " . $e->getMessage() . "');
                window.location.href = 'rent_car.php?car_id={$car_id}';
            </script>";
        exit;
    }
} else {
    if (isset($_GET['car_id'])) {
        $car_id = $_GET['car_id'];

        //get car info from car id
        $query = "SELECT * FROM Cars WHERE CarID = :car_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':car_id', $car_id, PDO::PARAM_INT);
        $stmt->execute();
        $car = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$car) {
            echo "<script>
                alert('Error: Car not found.');
                window.location.href = 'dashboard.php';
            </script>";
            exit;
        }
    } else {
        header("Location: dashboard.php");
        exit;
    }
}
?>
