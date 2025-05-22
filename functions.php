<?php
// Khởi động session
if (session_status() == PHP_SESSION_NONE) {
    add_action('init', function() {
        session_start();
    });
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

