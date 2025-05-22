<?php
// Khởi động session
if (session_status() == PHP_SESSION_NONE) {
    add_action('init', function() {
        session_start();
    });
}

function add_to_cart($product_id, $color, $size, $quantity) {
    if (session_status() == PHP_SESSION_NONE) session_start();
    $key = $product_id . '_' . $color . '_' . $size;
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    if (isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$key] = [
            'product_id' => $product_id,
            'color' => $color,
            'size' => $size,
            'quantity' => $quantity
        ];
    }
}

// Kết nối PDO
function get_pdo_connection() {
    static $pdo = null;
    if ($pdo === null) {
        $host = DB_HOST;
        $db   = DB_NAME;
        $user = DB_USER;
        $pass = DB_PASSWORD;
        $charset = DB_CHARSET;

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            error_log('Kết nối cơ sở dữ liệu thất bại: ' . $e->getMessage());
            wp_die('Lỗi kết nối cơ sở dữ liệu. Vui lòng thử lại sau.');
        }
    }
    return $pdo;
}

// Hàm kiểm tra đăng nhập
function is_custom_user_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_custom_current_user() {
    global $pdo;
    if (is_custom_user_logged_in()) {
        $pdo = get_pdo_connection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }
    return null;
}

// Đăng ký menu
function register_my_menus() {
    register_nav_menus(array(
        'main_menu' => __('Main Menu', 'your-text-domain'),
    ));
}
add_action('init', 'register_my_menus');

// Nạp CSS/JS
function my_theme_enqueue() {
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css', [], '5.3.0');
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', ['jquery'], '5.3.0', true);
    wp_enqueue_style('header-style', get_template_directory_uri() . '/assets/css/header.css', [], filemtime(get_template_directory() . '/assets/css/header.css'));
}
add_action('wp_enqueue_scripts', 'my_theme_enqueue');

// Nạp Navwalker
if (file_exists(get_template_directory() . '/class-wp-bootstrap-navwalker.php')) {
    require_once get_template_directory() . '/class-wp-bootstrap-navwalker.php';
} else {
    error_log('Tệp Navwalker không tồn tại: ' . get_template_directory() . '/class-wp-bootstrap-navwalker.php');
}

add_action('wp_ajax_fashion_search_products', 'fashion_search_products');
add_action('wp_ajax_nopriv_fashion_search_products', 'fashion_search_products');
function fashion_search_products() {
    global $wpdb;
    $keyword = isset($_GET['keyword']) ? sanitize_text_field($_GET['keyword']) : '';
    $results = [];
    $total = 0;
    if ($keyword) {
        $total = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM products WHERE name LIKE %s",
            '%' . $wpdb->esc_like($keyword) . '%'
        ));
        $products = $wpdb->get_results($wpdb->prepare(
            "SELECT p.product_id, p.name, p.discount_price, pci.image_url
             FROM products p
             LEFT JOIN product_colors pc ON p.product_id = pc.product_id
             LEFT JOIN product_color_images pci ON pc.color_id = pci.color_id AND pci.is_primary = 1
             WHERE p.name LIKE %s
             GROUP BY p.product_id
             LIMIT 20",
            '%' . $wpdb->esc_like($keyword) . '%'
        ));
        foreach ($products as $p) {
            $results[] = [
                'id' => $p->product_id,
                'name' => $p->name,
                'image' => $p->image_url,
                'price' => $p->discount_price
            ];
        }
    }
    wp_send_json(['total' => intval($total), 'products' => $results]);
}

add_action('wp_ajax_fashion_add_to_cart', 'fashion_add_to_cart');
add_action('wp_ajax_nopriv_fashion_add_to_cart', 'fashion_add_to_cart');
function fashion_add_to_cart() {
    if (session_status() == PHP_SESSION_NONE) session_start();
    $product_id = intval($_POST['product_id']);
    $color = sanitize_text_field($_POST['color']);
    $size = sanitize_text_field($_POST['size']);
    $quantity = intval($_POST['quantity']);
    if ($product_id && $color && $size && $quantity > 0) {
        $key = $product_id . '_' . $color . '_' . $size;
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        if (isset($_SESSION['cart'][$key])) {
            $_SESSION['cart'][$key]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$key] = [
                'product_id' => $product_id,
                'color' => $color,
                'size' => $size,
                'quantity' => $quantity
            ];
        }
        wp_send_json(['success' => true]);
    } else {
        wp_send_json(['success' => false, 'message' => 'Thiếu thông tin sản phẩm']);
    }
    wp_die();
}

add_action('wp_ajax_fashion_cart_update', 'fashion_cart_update');
add_action('wp_ajax_nopriv_fashion_cart_update', 'fashion_cart_update');
function fashion_cart_update() {
    if (session_status() == PHP_SESSION_NONE) session_start();
    $key = $_POST['key'];
    $type = $_POST['type'];
    if (isset($_SESSION['cart'][$key])) {
        if ($type === 'plus') {
            $_SESSION['cart'][$key]['quantity']++;
        } elseif ($type === 'minus') {
            $_SESSION['cart'][$key]['quantity']--;
            if ($_SESSION['cart'][$key]['quantity'] < 1) unset($_SESSION['cart'][$key]);
        } elseif ($type === 'remove') {
            unset($_SESSION['cart'][$key]);
        }
    }
    wp_die();
}





