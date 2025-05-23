<?php
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






