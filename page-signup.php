<?php
/* Template Name: Signup */

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $wpdb;
    $first_name = sanitize_text_field($_POST['first_name'] ?? '');
    $last_name = sanitize_text_field($_POST['last_name'] ?? '');
    $full_name = trim($first_name . ' ' . $last_name);
    $email = sanitize_email($_POST['email'] ?? '');
    $phone = sanitize_text_field($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate
    if (!$first_name || !$last_name || !$email || !$password || !$confirm_password) {
        $error = 'Vui lòng nhập đầy đủ thông tin.';
    } elseif (!is_email($email)) {
        $error = 'Email không hợp lệ.';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu nhập lại không khớp.';
    } else {
        // Check email/phone tồn tại
        $table = 'users';
        $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE email = %s OR phone_number = %s", $email, $phone));
        if ($exists) {
            $error = 'Email hoặc số điện thoại đã tồn tại.';
        } else {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            // Insert user
            $result = $wpdb->insert($table, [
                'email' => $email,
                'phone_number' => $phone,
                'password_hash' => $password_hash,
                'full_name' => $full_name,
                'role' => 'customer',
                'is_active' => 1,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ]);
            if ($result) {
                $success = 'Đăng ký thành công! Bạn có thể đăng nhập ngay.';
            } else {
                $error = 'Có lỗi xảy ra, vui lòng thử lại.<br>' . $wpdb->last_error;
            }
        }
    }
}

get_header();
?>
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/signup.css">

<style>
.signup-page {
    min-height: 100vh;
    background: #f8f9fa;
}
.signup-content {
    max-width: 450px;
    margin: auto;
}
.signup-content input.form-control {
    border-radius: 10px;
    padding: 14px 18px;
    font-size: 16px;
}
.signup-content .btn-dark {
    border-radius: 10px;
    padding: 12px;
    font-weight: 600;
}
</style>

<div class="container signup-page py-5">
    <div class="row justify-content-center align-items-center">
        <div class="col-lg-6 d-none d-lg-block">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/sign_up.png" alt="Fashion Model" class="img-fluid rounded-4 shadow">
        </div>
        <div class="col-lg-6">
            <div class="signup-content p-4 p-md-5 bg-white rounded-4 shadow">
                <h1 class="text-center mb-3" style="font-weight:700;letter-spacing:2px;">FASCO</h1>
                <h4 class="text-center mb-4">Tạo tài khoản mới</h4>
                <form id="signUpForm" method="post" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <input type="text" class="form-control" name="first_name" placeholder="Họ" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <input type="text" class="form-control" name="last_name" placeholder="Tên" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <input type="email" class="form-control" name="email" placeholder="Email" required>
                    </div>
                    <div class="mb-3">
                        <input type="tel" class="form-control" name="phone" placeholder="Số điện thoại">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <input type="password" class="form-control" name="password" placeholder="Mật khẩu" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <input type="password" class="form-control" name="confirm_password" placeholder="Nhập lại mật khẩu" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark w-100 mb-2">Tạo tài khoản</button>
                </form>
                <div class="text-center mt-3">
                    Đã có tài khoản? <a href="<?php echo esc_url( home_url('/page-login') ); ?>">Đăng nhập</a>
                </div>
                <div class="text-center mt-4 text-muted">
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
    <?php if (!empty($error)): ?>
        showToast("<?php echo esc_js($error); ?>", "error");
    <?php elseif (!empty($success)): ?>
        showToast("<?php echo esc_js($success); ?>", "success");
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