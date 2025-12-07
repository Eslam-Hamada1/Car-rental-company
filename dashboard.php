<?php
session_start();
include('db_connection.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Cars</title>
    <link rel="stylesheet" href="Customer_Dahsboard.css?v=1.0">
    <script>
        function showAlert() {
            alert("This car can't be rented because its is not currently Active.");
        }
    </script>
</head>
<body>
    <div class="GREATDIV1">
        <div class="box">
            <h1>Search</h1>
        </div>

        <form method="GET" action="dashboard.php">
            <div class="div1">
                <input type="text" name="model" placeholder="Model">
                <input type="text" name="year" placeholder="Year">
            </div>
            <div class="div2">
                <input type="text" name="plateid" placeholder="Plate ID">
                <input type="text" name="status" placeholder="Status">
            </div>
            <div class="div3">
                <input type="text" name="price" placeholder="Price/Day">
                <button class="button1" type="submit">Search</button>
            </div>
        </form>        

        <a class="link1" href="/reservations.php">Already Have a Reservation? Click Here!</a>

        <form action="logout.php" method="post">
            <button class="logout" type="submit">Logout</button>
        </form>

        <a class="link2" href="/Help site/manual.html" target="_blank">Need Help? Click Here!</a>
    </div>

    <div class="GREATDIV2">
        <div class="text">
            <h1>Available Cars</h1>
        </div>

        <table>
            <tr>
                <th>Model</th>
                <th>Year</th>
                <th>Plate ID</th>
                <th>Status</th>
                <th>Price/Day</th>
                <th>Rent</th>
            </tr>

            <?php
            //base query
            $query = "SELECT * FROM Cars";

            $conditions = [];

            //check each input and add to conditions
            if (!empty($_GET['model'])) {
                $conditions[] = "Model LIKE :model";
            }
            if (!empty($_GET['year'])) {
                $conditions[] = "Year = :year";
            }
            if (!empty($_GET['plateid'])) {
                $conditions[] = "PlateID LIKE :plateid";
            }
            if (!empty($_GET['status'])) {
                $conditions[] = "Status LIKE :status";
            }
            if (!empty($_GET['price'])) {
                $conditions[] = "PricePerDay <= :price";
            }

            //append conditions to the query
            if (!empty($conditions)) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }

            $stmt = $pdo->prepare($query);

            if (!empty($_GET['model'])) {
                $stmt->bindValue(':model', '%' . $_GET['model'] . '%');
            }
            if (!empty($_GET['year'])) {
                $stmt->bindValue(':year', $_GET['year']);
            }
            if (!empty($_GET['plateid'])) {
                $stmt->bindValue(':plateid', '%' . $_GET['plateid'] . '%');
            }
            if (!empty($_GET['status'])) {
                $stmt->bindValue(':status', '%' . $_GET['status'] . '%');
            }
            if (!empty($_GET['price'])) {
                $stmt->bindValue(':price', $_GET['price']);
            }

            //execute query
            $stmt->execute();

            //results in table
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['Model']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Year']) . "</td>";
                echo "<td>" . htmlspecialchars($row['PlateID']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
                echo "<td>" . htmlspecialchars($row['PricePerDay']) . "</td>";
                
                if ($row['Status'] === 'Active') {
                    echo "<td><form method='POST' action='rent_car.php'>
                                <input type='hidden' name='car_id' value='" . $row['CarID'] . "'>
                                <button type='submit' name='rent'>Rent</button>
                            </form></td>";
                } else {
                    echo "<td><button type='button' onclick='showAlert()'>Rent</button></td>";
                }

                echo "</tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>
