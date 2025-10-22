<?php
require_once 'config.php';

// Cart operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_to_cart':
            addToCart();
            break;
        case 'remove_from_cart':
            removeFromCart();
            break;
        case 'update_quantity':
            updateQuantity();
            break;
        case 'clear_cart':
            clearCart();
            break;
        case 'auto_save':
            autoSaveCart();
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_count':
            getCartCount();
            break;
        case 'get_total':
            getCartTotal();
            break;
        case 'get_items':
            getCartItems();
            break;
    }
}

function addToCart() {
    $productId = $_POST['product_id'] ?? 0;
    $size = $_POST['size'] ?? '';
    $color = $_POST['color'] ?? '';
    $quantity = (int)($_POST['quantity'] ?? 1);
    
    if ($productId <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
        return;
    }
    
    try {
        $pdo = getConnection();
        
        // Check if product exists and has enough stock
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND stock_quantity >= ?");
        $stmt->execute([$productId, $quantity]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not available or insufficient stock']);
            return;
        }
        
        // Create cart item key
        $cartKey = $productId . '_' . $size . '_' . $color;
        
        // Add to session cart
        if (isset($_SESSION['cart'][$cartKey])) {
            $_SESSION['cart'][$cartKey]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$cartKey] = [
                'product_id' => $productId,
                'name' => $product['name'],
                'price' => $product['price'],
                'size' => $size,
                'color' => $color,
                'quantity' => $quantity,
                'image_url' => $product['image_url']
            ];
        }
        
        echo json_encode(['success' => true, 'message' => 'Product added to cart']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error adding product to cart']);
    }
}

function removeFromCart() {
    $productId = $_POST['product_id'] ?? 0;
    $size = $_POST['size'] ?? '';
    $color = $_POST['color'] ?? '';
    
    $cartKey = $productId . '_' . $size . '_' . $color;
    
    if (isset($_SESSION['cart'][$cartKey])) {
        unset($_SESSION['cart'][$cartKey]);
        echo json_encode(['success' => true, 'message' => 'Product removed from cart']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found in cart']);
    }
}

function updateQuantity() {
    $productId = $_POST['product_id'] ?? 0;
    $size = $_POST['size'] ?? '';
    $color = $_POST['color'] ?? '';
    $quantity = (int)($_POST['quantity'] ?? 1);
    
    $cartKey = $productId . '_' . $size . '_' . $color;
    
    if (isset($_SESSION['cart'][$cartKey])) {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$cartKey]);
        } else {
            $_SESSION['cart'][$cartKey]['quantity'] = $quantity;
        }
        echo json_encode(['success' => true, 'message' => 'Quantity updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found in cart']);
    }
}

function clearCart() {
    $_SESSION['cart'] = [];
    echo json_encode(['success' => true, 'message' => 'Cart cleared']);
}

function getCartCount() {
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
    }
    echo json_encode(['count' => $count]);
}

function getCartTotal() {
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    echo json_encode(['total' => $total]);
}

function getCartItems() {
    echo json_encode(['items' => $_SESSION['cart']]);
}

function autoSaveCart() {
    // Save cart to database for logged-in users
    if (isset($_SESSION['user_id'])) {
        try {
            $pdo = getConnection();
            $userId = $_SESSION['user_id'];
            
            // Clear existing cart items for this user
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            // Save current cart items
            foreach ($_SESSION['cart'] as $item) {
                $stmt = $pdo->prepare("
                    INSERT INTO cart (user_id, product_id, quantity, size, color) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $userId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['size'],
                    $item['color']
                ]);
            }
            
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error saving cart']);
        }
    }
}
?>