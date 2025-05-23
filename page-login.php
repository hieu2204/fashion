<?php
/* Template Name: Login */

// Khởi động session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$pdo = get_pdo_connection();


// Xử lý form đăng nhập
$login_success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['log'], $_POST['pwd'])) {
    try {
        if ($pdo !== null) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :id OR phone_number = :id LIMIT 1");
            $stmt->execute(['id' => trim($_POST['log'])]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($_POST['pwd'], $user['password_hash'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $login_success = true;
            } else {
                $error = 'Email/số điện thoại hoặc mật khẩu không đúng.';
            }
        } else {
            $error = 'Lỗi kết nối cơ sở dữ liệu. Vui lòng thử lại sau.';
        }
    } catch (PDOException $e) {
        $error = 'Lỗi hệ thống: ' . $e->getMessage();
        error_log($error);
    }
}

get_header();
?>
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/login.css">

<div class="container login-page">
    <div class="row">
        <div class="col-md-6 login-image">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/login1.png" alt="Đăng nhập" class="img-fluid">
        </div>
        <div class="col-md-6 login-form">
            <div class="login-content">
                <h1>FASCO</h1>
                <h4 class="text-center">Đăng nhập vào FASCO</h4>

                <?php if ($login_success) : ?>
                    <?php $current_user = get_custom_current_user(); ?>
                    <div class="alert alert-success text-center">
                        Đăng nhập thành công! Xin chào, <?php echo esc_html($current_user->full_name ?? 'Người dùng'); ?>
                    </div>
                    <p class="text-center"><a href="<?php echo esc_url(wp_logout_url()); ?>">Đăng xuất</a></p>
                <?php elseif ($error) : ?>
                    <div class="alert alert-danger text-center"><?php echo esc_html($error); ?></div>
                <?php endif; ?>

                <?php if (!$login_success) : ?>
                    <form method="post" action="<?php echo esc_url(get_permalink()); ?>">
                        <div class="mb-3">
                            <label for="username">Tên đăng nhập hoặc Email</label>
                            <input type="text" name="log" id="username" class="form-control" required value="<?php echo esc_attr($_POST['log'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="password">Mật khẩu</label>
                            <input type="password" name="pwd" id="password" class="form-control" required>
                        </div>
                        <input type="submit" value="Đăng nhập" class="btn btn-dark w-100">
                    </form>

                    <div class="text-center mt-3">
                        <a class="btn btn-outline-primary mb-2" href="<?php echo esc_url( home_url('/signup') ); ?>">Đăng ký ngay</a><br>
                        <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="text-muted">Quên mật khẩu?</a>
                    </div>
                <?php endif; ?>

                <div class="text-center mt-5 text-muted">
                    <small>Điều khoản & Điều kiện của FASCO</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast-message" class="toast-message"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($login_success): ?>
        showToast("Đăng nhập thành công!", "success");
        setTimeout(function() {
            window.location.href = "<?php echo esc_url(home_url('/')); ?>";
        }, 1000);
    <?php elseif (!empty($error)): ?>
        showToast("<?php echo esc_js($error); ?>", "error");
    <?php endif; ?>

    function showToast(message, type) {
        var toast = document.getElementById('toast-message');
        toast.textContent = message;
        toast.className = 'toast-message toast-' + type;
        toast.style.display = 'block';
        setTimeout(function() {
            toast.style.display = 'none';
        }, 3500);
    }
});
</script>


<?php get_footer(); ?>