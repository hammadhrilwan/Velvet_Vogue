<?php
require_once 'config.php';
require_once 'products.php';

// Handle product filtering requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filters = [
        'category' => $_POST['category'] ?? '',
        'gender' => $_POST['gender'] ?? '',
        'size' => $_POST['size'] ?? '',
        'color' => $_POST['color'] ?? '',
        'min_price' => $_POST['min_price'] ?? '',
        'max_price' => $_POST['max_price'] ?? '',
        'sort' => $_POST['sort'] ?? 'newest'
    ];
    
    $products = filterProducts($filters);
    
    // Generate HTML for filtered products
    $html = '';
    foreach ($products as $product) {
        $sizes = json_decode($product['sizes'], true) ?: [];
        $colors = json_decode($product['colors'], true) ?: [];
        
        $html .= generateProductCard($product, $sizes, $colors);
    }
    
    echo $html;
    exit;
}

function generateProductCard($product, $sizes, $colors) {
    $badgeHtml = '';
    if ($product['is_featured']) {
        $badgeHtml .= '<div class="product-badge">Featured</div>';
    }
    if ($product['is_new_arrival']) {
        $badgeHtml .= '<div class="product-badge new">New</div>';
    }
    if ($product['is_on_sale']) {
        $badgeHtml .= '<div class="product-badge sale">Sale</div>';
    }
    
    $sizeOptions = '';
    if (!empty($sizes)) {
        $sizeOptions = '<select id="size-' . $product['id'] . '" class="form-control mb-1" style="font-size: 0.9rem;">
            <option value="">Select Size</option>';
        foreach ($sizes as $size) {
            $sizeOptions .= '<option value="' . htmlspecialchars($size) . '">' . htmlspecialchars($size) . '</option>';
        }
        $sizeOptions .= '</select>';
    }
    
    $colorOptions = '';
    if (!empty($colors)) {
        $colorOptions = '<select id="color-' . $product['id'] . '" class="form-control mb-1" style="font-size: 0.9rem;">
            <option value="">Select Color</option>';
        foreach ($colors as $color) {
            $colorOptions .= '<option value="' . htmlspecialchars($color) . '">' . htmlspecialchars($color) . '</option>';
        }
        $colorOptions .= '</select>';
    }
    
    $priceHtml = '';
    if ($product['is_on_sale'] && $product['sale_price']) {
        $priceHtml = '$' . number_format($product['sale_price'], 2) . ' <span class="original-price">$' . number_format($product['price'], 2) . '</span>';
    } else {
        $priceHtml = '$' . number_format($product['price'], 2);
    }
    
    return '
        <div class="card product-card fade-in">
            ' . $badgeHtml . '
            
            <img src="' . htmlspecialchars($product['image_url']) . '" alt="' . htmlspecialchars($product['name']) . '" class="card-img" loading="lazy">
            
            <div class="card-body">
                <h5 class="card-title">' . htmlspecialchars($product['name']) . '</h5>
                <p class="card-text">' . htmlspecialchars(substr($product['description'], 0, 100)) . '...</p>
                <p class="category-badge" style="color: var(--accent-color); font-size: 0.9rem; font-weight: 500;">
                    ' . htmlspecialchars($product['category_name'] ?: 'Uncategorized') . ' • ' . ucfirst($product['gender']) . '
                </p>
                
                <div class="product-options mb-2">
                    ' . $sizeOptions . '
                    ' . $colorOptions . '
                </div>
                
                <div class="card-price">
                    ' . $priceHtml . '
                </div>
                
                <button class="btn btn-primary add-to-cart" data-product-id="' . $product['id'] . '">
                    <i class="fas fa-shopping-cart"></i> Add to Cart
                </button>
            </div>
        </div>
    ';
}
?>