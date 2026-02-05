<?php
session_start();

// If user is not logged in, redirect to login page
if (!isset($_SESSION["userid"])) {
    header("Location: login.php");
    exit;
}
/* ---- DATABASE ---- */
require_once 'configuration.php';   // <-- REQUIRED to get $db

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fly_id'])) {
    $flyId = (int)$_POST['fly_id'];
    $userId = (int)$_SESSION['userid'];
    $amount = 1;


    $db->begin_transaction();

    $stmt = $db->prepare(
        "UPDATE fishing_flies
         SET quantity_in_stock = quantity_in_stock - 1 
         WHERE fly_id = ? AND quantity_in_stock > 0"
    );
    $stmt->bind_param('i', $flyId);
    $stmt->execute();
    $stmt->close();
    // 2️⃣ Insert transaction record

    $insert = $db->prepare(
        "INSERT INTO `transactions` (user_id, fly_id, amount)
         VALUES (?, ?, ?)"
    );
     if ($insert === false) {
        $db->rollback();
        die("Insert prepare failed: " . $db->error);
    }
    
    $insert->bind_param("iii", $userId, $flyId, $amount);
    $insert->execute();
    $insert->close();

        // COMMIT if everything worked
    $db->commit();

    header("Location: welcome.php");
    exit;
}
$userId = (int)$_SESSION['userid'];
$stmt = $db->prepare(
    "SELECT 
        f.name,
        f.price,
        SUM(t.amount) AS total_bought
     FROM `transactions` t
     JOIN fishing_flies f ON f.fly_id = t.fly_id
     WHERE t.user_id = ?
     GROUP BY f.fly_id"
    );

    if ($stmt === false) {
        die("Prepare failed: " . $db->error);
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();

    $result = $stmt->get_result();
//     if ($result->num_rows === 0) {
//     echo "<p>You haven’t bought any flies yet.</p>";
// } else {
//     echo "<table border='1'>";
//     echo "<tr>
//             <th>Fly</th>
//             <th>Price</th>
//             <th>Quantity Bought</th>
//           </tr>";

//     while ($row = $result->fetch_assoc()) {
//         echo "<tr>
//                 <td>{$row['name']}</td>
//                 <td>\${$row['price']}</td>
//                 <td>{$row['total_bought']}</td>
//               </tr>";
//     }

//     echo "</table>";
// }

// $stmt->close();


/* ---- FETCH STOCK ---- */
$stocks = $db->query(
    "SELECT fly_id, name, price, quantity_in_stock, description, image FROM fishing_flies"
);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome <?php echo htmlspecialchars($_SESSION['user']['name']); ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">

    <h1>Hello, <strong><?php echo htmlspecialchars($_SESSION['user']['name']); ?></strong></h1>
    <a href="logout.php" class="btn btn-secondary mb-3">Log Out</a>

    <h3>Available Stock</h3>

    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Image</th>
                <th>Product</th>
                <th>Price</th>
                <th>Available</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>

        <?php while ($row = $stocks->fetch_assoc()): ?>
            <tr>
                <td>
                    <img src="assets/<?php echo htmlspecialchars($row['image']); ?>"
                         alt="<?php echo htmlspecialchars($row['name']); ?>"
                         style="width:80px;">
                </td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td>$<?php echo number_format($row['price'], 2); ?></td>
                <td><?php echo (int)$row['quantity_in_stock']; ?></td>
                <td>
                    <?php if ($row['quantity_in_stock'] > 0): ?>
                        <form method="post" class="m-0">
                            <input type="hidden" name="fly_id"
                                   value="<?php echo $row['fly_id']; ?>">
                            <button type="submit" class="btn btn-success btn-sm">
                                Buy
                            </button>
                        </form>
                    <?php else: ?>
                        <span class="text-danger">Out of stock</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>

        </tbody>
    </table>

</div>
    <!-- 1️⃣ Previous purchases -->
    <h3>Your Previous Purchases</h3>
    <?php if ($result->num_rows === 0): ?>
        <p>You haven’t bought any flies yet.</p>
    <?php else: ?>
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Fly</th>
                    <th>Price</th>
                    <th>Quantity Bought</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td>$<?= number_format($row['price'], 2) ?></td>
                    <td><?= (int)$row['total_bought'] ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>

</body>
</html>