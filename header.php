<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php wp_title('|', true, 'right'); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<nav class="navbar navbar-expand-md bg-white fixed-top">
    <div class="container">
        <!-- Logo -->
        <a href="<?php echo esc_url(home_url('/')); ?>" class="navbar-brand">FASCO</a>

        <!-- Mobile Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainMenu">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'main_menu',
                'container' => false,
                'menu_class' => 'navbar-nav me-auto',
                'fallback_cb' => false,
                'depth' => 2,
                'walker' => class_exists('WP_Bootstrap_Navwalker') ? new WP_Bootstrap_Navwalker() : null
            ));
            ?>

            <!-- Icon người dùng với dropdown -->
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a href="#" id="userIcon" class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/user.png" alt="Đăng nhập" width="24" height="24">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userIcon" style="min-width: 180px;">
                        <?php if (isset($_SESSION['user_id'])): 
                            $user = get_custom_current_user();
                        ?>
                            <li class="dropdown-item-text text-center fw-bold"><?php echo esc_html($user['full_name'] ?? 'Người dùng'); ?></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a href="<?php echo esc_url(home_url('/logout/')); ?>" class="dropdown-item text-danger text-center">Đăng xuất</a>
                            </li>
                        <?php else: ?>
                            <li>
                                <a href="<?php echo esc_url(home_url('/page-login')); ?>" class="dropdown-item text-center">Đăng nhập</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var userIcon = document.getElementById('userIcon');
    if (!userIcon) return;
    userIcon.addEventListener('click', function(e) {
        e.preventDefault();
        <?php if (isset($_SESSION['user_id'])): ?>
            var modal = new bootstrap.Modal(document.getElementById('userModal'));
            modal.show();
        <?php else: ?>
            window.location.href = "<?php echo esc_url(home_url('/page-login')); ?>";
        <?php endif; ?>
    });
});
</script>


<?php wp_footer(); ?>
</body>
</html>