<?php
session_start();

/* ---- AUTH CHECK ---- */
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit;
}

/* ---- DATABASE ---- */
require_once 'configuration.php';   // <-- REQUIRED to get $db

/* ---- BUY ACTION ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['stock_id'])) {
    $stockId = (int)$_POST['stock_id'];

    $stmt = $db->prepare(
        "UPDATE stock 
         SET quantity = quantity - 1 
         WHERE StockID = ? AND quantity > 0"
    );
    $stmt->bind_param('i', $stockId);
    $stmt->execute();
    $stmt->close();

    header("Location: welcome.php");
    exit;
}

/* ---- FETCH STOCK ---- */
$stocks = $db->query(
    "SELECT StockID, Sock_Name, price, quantity, image FROM stock"
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
                    <img src="../Images/<?php echo htmlspecialchars($row['image']); ?>"
                         alt="<?php echo htmlspecialchars($row['Sock_Name']); ?>"
                         style="width:80px;">
                </td>
                <td><?php echo htmlspecialchars($row['Sock_Name']); ?></td>
                <td>$<?php echo number_format($row['price'], 2); ?></td>
                <td><?php echo (int)$row['quantity']; ?></td>
                <td>
                    <?php if ($row['quantity'] > 0): ?>
                        <form method="post" class="m-0">
                            <input type="hidden" name="stock_id"
                                   value="<?php echo $row['StockID']; ?>">
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
</body>
</html>