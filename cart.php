<?php
/* Template Name: Cart */
get_header();

if (session_status() == PHP_SESSION_NONE) session_start();
$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

$subtotal = 0;
foreach ($cart_items as &$item) {
    // Lấy thông tin sản phẩm từ DB
    $product = $wpdb->get_row($wpdb->prepare("SELECT * FROM products WHERE product_id = %d", $item['product_id']));
    $item['name'] = $product->name;

    // Lấy tên màu từ bảng product_colors
    $color_id = intval($item['color']);
    $color_name = $wpdb->get_var($wpdb->prepare(
        "SELECT color_name FROM product_colors WHERE color_id = %d",
        $color_id
    ));
    $item['color_name'] = $color_name ?: $item['color'];

    // Lấy ảnh đúng theo màu (nếu có)
    $image = $wpdb->get_var($wpdb->prepare(
        "SELECT image_url FROM product_color_images WHERE color_id = %d ORDER BY is_primary DESC, id ASC LIMIT 1",
        $color_id
    ));
    if ($image) {
        $item['image'] = get_template_directory_uri() . '/assets' . $image;
    } else {
        $item['image'] = get_template_directory_uri() . '/assets/images/no-image.jpg';
    }

    $item['price'] = $product->base_price;
    $item['total'] = $item['price'] * $item['quantity'];
    $subtotal += $item['total'];
}
unset($item);
?>

<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/cart.css">
<div class="shopping-cart-page">
<div class="container py-5">
    <?php if (!empty($cart_items)): ?>
        <h3 class="mb-4 fw-bold">Shopping Cart</h3>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th style="width:40%;">Product</th>
                        <th style="width:15%;">Price</th>
                        <th style="width:20%;">Quantity</th>
                        <th style="width:15%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?php echo esc_url($item['image']); ?>" alt="<?php echo esc_attr($item['name']); ?>" style="width:80px;height:100px;object-fit:cover;border-radius:8px;margin-right:16px;">
                                <div>
                                    <div class="fw-bold"><?php echo esc_html($item['name']); ?></div>
                                    <div class="text-muted" style="font-size:15px;">
                                        Color: <?php echo esc_html($item['color_name']); ?> |
                                        Size: <?php echo esc_html($item['size']); ?>
                                    </div>
                                    <a href="#" class="text-decoration-underline text-muted btn-remove" data-key="<?php echo esc_attr($item['product_id'].'_'.$item['color'].'_'.$item['size']); ?>" style="font-size:15px;">Remove</a>
                                </div>
                            </div>
                        </td>
                        <td class="fw-bold">$<?php echo number_format($item['price'], 2); ?></td>
                        <td>
                            <div class="input-group input-group-sm" style="max-width:110px;">
                                <button class="btn btn-outline-secondary btn-minus" type="button" data-key="<?php echo esc_attr($item['product_id'].'_'.$item['color'].'_'.$item['size']); ?>">-</button>
                                <input type="text" class="form-control text-center" value="<?php echo $item['quantity']; ?>" style="min-width:36px;" readonly>
                                <button class="btn btn-outline-secondary btn-plus" type="button" data-key="<?php echo esc_attr($item['product_id'].'_'.$item['color'].'_'.$item['size']); ?>">+</button>
                            </div>
                        </td>
                        <td class="fw-bold">$<?php echo number_format($item['total'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="wrap-product">
                    <label class="form-check-label" for="wrap-product">
                        For <b>$10.00</b> Please Wrap The Product
                    </label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex flex-column align-items-end">
                    <div class="mb-2 fs-5">
                        <span class="fw-bold">Subtotal</span>
                        <span class="ms-3">$<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <!-- Thay nút Checkout trong cart.php -->
                    <form action="<?php echo site_url('/checkout'); ?>" method="get" class="w-100" style="max-width:320px;">
                        <button type="submit" class="btn btn-dark w-100">Checkout</button>
                    </form>
                </div>
            </div>
        </div>
    <?php else: ?>
        <h3 class="mb-4 fw-bold text-center">Vui lòng nhập sản phẩm vào giỏ hàng</h3>
    <?php endif; ?>
</div>
</div>


<style>
.shopping-cart-page {
    margin-top: 80px; /* Tăng khoảng cách từ header tùy ý */
}
@media (max-width: 767.98px) {
    .shopping-cart-page {
        margin-top: 50px; /* Giảm khoảng cách cho màn hình nhỏ */
    }
}

/* Table style */

.table th, .table td {
    vertical-align: middle;
    background: #fff;
}
.table thead th {
    background: #f8f9fa;
    font-weight: 600;
    font-size: 1.05rem;
    border-bottom: 2px solid #dee2e6;
}
.table tbody tr {
    border-bottom: 1px solid #f1f1f1;
    transition: background 0.2s;
}
.table tbody tr:hover {
    background: #f6f8fa;
}

/* Product image and info */
.d-flex.align-items-center img {
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    border: 1px solid #eee;
}
.d-flex.align-items-center .fw-bold {
    font-size: 1.08rem;
}
.text-muted {
    color: #6c757d !important;
}

/* Remove link */
.btn-remove {
    color: #dc3545 !important;
    font-weight: 500;
    transition: color 0.2s;
}
.btn-remove:hover {
    color: #a71d2a !important;
    text-decoration: underline !important;
}

/* Quantity input */
.input-group-sm > .btn {
    min-width: 32px;
    font-size: 1.1rem;
}
.input-group-sm .form-control {
    font-size: 1.05rem;
    border-radius: 0 0.25rem 0.25rem 0;
}

/* Wrap product */
.form-check-label b {
    color: #0d6efd;
}
.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

/* Subtotal & Checkout */
.fs-5 .fw-bold {
    font-size: 1.1rem;
}
.btn-dark {
    background: linear-gradient(90deg, #232526 0%, #414345 100%);
    border: none;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: background 0.2s;
}
.btn-dark:hover {
    background: linear-gradient(90deg, #414345 0%, #232526 100%);
}

/* Responsive */
@media (max-width: 767.98px) {
    .table th, .table td {
        font-size: 0.97rem;
    }
    .d-flex.align-items-center img {
        width: 60px !important;
        height: 75px !important;
        margin-right: 10px !important;
    }
    .mb-2.fs-5 {
        font-size: 1rem !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tăng số lượng
    document.querySelectorAll('.btn-plus').forEach(function(btn) {
        btn.addEventListener('click', function() {
            let key = this.dataset.key;
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=fashion_cart_update&key=' + encodeURIComponent(key) + '&type=plus'
            }).then(() => location.reload());
        });
    });

    // Giảm số lượng
    document.querySelectorAll('.btn-minus').forEach(function(btn) {
        btn.addEventListener('click', function() {
            let key = this.dataset.key;
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=fashion_cart_update&key=' + encodeURIComponent(key) + '&type=minus'
            }).then(() => location.reload());
        });
    });

    // Xóa sản phẩm
    document.querySelectorAll('.btn-remove').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            let key = this.dataset.key;
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=fashion_cart_update&key=' + encodeURIComponent(key) + '&type=remove'
            }).then(() => location.reload());
        });
    });
});
</script>

<?php get_footer(); ?>