<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';

$user_id = $_SESSION['user_id'];

// Handle update quantity
if (isset($_POST['update_quantity'])) {
    $cart_id = $_POST['cart_id'];
    $quantity = (int)$_POST['quantity'];
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$quantity, $cart_id, $user_id]);
}

// Handle remove item
if (isset($_POST['remove_from_cart'])) {
    $cart_id = $_POST['cart_id'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
}

// Fetch cart items
$stmt = $conn->prepare("SELECT cart.id AS cart_id, products.name, products.price, cart.quantity 
                        FROM cart 
                        JOIN products ON cart.product_id = products.id 
                        WHERE cart.user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_cost = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart</title>
    <style>
        .cart-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ccc;
        }
        button {
            padding: 6px 12px;
            background-color: red;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="cart-container">
    <h2>Your Cart</h2>
    <table>
        <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Total</th>
            <th>Actions</th>
        </tr>

        <?php foreach ($cart_items as $item): 
            $subtotal = $item['price'] * $item['quantity'];
            $total_cost += $subtotal;
        ?>
        <tr>
            <td><?= htmlspecialchars($item['name']); ?></td>
            <td>$<?= number_format($item['price'], 2); ?></td>
            <td>
                <form method="POST" style="display:inline;">
                    <input type="number" name="quantity" value="<?= $item['quantity']; ?>" min="1" required>
                    <input type="hidden" name="cart_id" value="<?= $item['cart_id']; ?>">
                    <button type="submit" name="update_quantity">Update</button>
                </form>
            </td>
            <td>$<?= number_format($subtotal, 2); ?></td>
            <td>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="cart_id" value="<?= $item['cart_id']; ?>">
                    <button type="submit" name="remove_from_cart">Remove</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>

        <tr>
            <td colspan="3"><strong>Total:</strong></td>
            <td colspan="2"><strong>$<?= number_format($total_cost, 2); ?></strong></td>
        </tr>
    </table>
</div>

</body>
</html>
