<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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

            <!-- Icon người dùng với dropdown + search + cart -->
            <ul class="navbar-nav ms-auto align-items-center">
                <!-- Search Icon -->
                <li class="nav-item me-2">
                    <a href="#" class="nav-link" id="openSearchSidebar" title="Tìm kiếm">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/search.png" alt="Tìm kiếm" width="22" height="22">
                    </a>
                </li>
                <!-- Cart Icon -->
                <li class="nav-item me-2">
                    <a href="<?php echo esc_url(home_url('/cart')); ?>" class="nav-link position-relative" title="Giỏ hàng">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/cart.png" alt="Giỏ hàng" width="24" height="24">
                        <!-- Badge số lượng sản phẩm trong giỏ (nếu có) -->
                        <?php if (!empty($_SESSION['cart_count'])): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.8rem;">
                                <?php echo intval($_SESSION['cart_count']); ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
                <!-- User Dropdown -->
                <li class="nav-item dropdown">
                    <a href="#" id="userIcon" class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/user.png" alt="Đăng nhập" width="24" height="24">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userIcon" style="min-width: 180px;">
                        <?php if (isset($_SESSION['user_id'])): 
                            $user = get_custom_current_user();
                        ?>
                            <li class="dropdown-item-text text-center fw-bold"><?php echo esc_html(isset($user->full_name) ? $user->full_name : 'Người dùng'); ?></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a href="<?php echo esc_url(home_url('/logout/')); ?>" class="dropdown-item text-center">Đăng xuất</a>
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

<!-- Search Sidebar Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="searchSidebar" aria-labelledby="searchSidebarLabel">
  <div class="offcanvas-header">
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Đóng"></button>
  </div>
  <div class="offcanvas-body">
    <div class="mb-3">
      <form action="<?php echo esc_url(home_url('/shop')); ?>" method="get" class="d-flex align-items-center" id="sidebarSearchForm">
        <input type="text" class="form-control rounded-pill px-4" name="s" placeholder="Search" style="height:44px;">
      </form>
    </div>
    <div class="mb-3">
      <div class="fw-bold mb-2">Từ khóa hot</div>
      <span class="badge bg-light text-dark rounded-pill px-3 py-2" style="font-size:1rem;">
        H <span style="color:#e53935;">🔥</span>
      </span>
    </div>
    <div>
      <div class="fw-bold mb-2">Gợi ý sản phẩm</div>
      <div class="row row-cols-2">
        <div class="col mb-2">Quần ống suông lưng cao dây kéo sau</div>
        <div class="col mb-2">Áo blazer nhún xắn tay cách điệu</div>
        <div class="col mb-2">Đầm midi sát nách rút nhún ngực thun eo</div>
        <div class="col mb-2">Quần dài ống rộng lưng thun</div>
        <div class="col mb-2">Váy mini tennis cơ bản</div>
      </div>
    </div>
    <!-- Đặt search-results ở đây -->
    <div id="search-results" class="mt-3"></div>
  </div>
</div>

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

    var searchBtn = document.getElementById('openSearchSidebar');
    if (searchBtn) {
        searchBtn.addEventListener('click', function(e) {
            e.preventDefault();
            var sidebar = new bootstrap.Offcanvas(document.getElementById('searchSidebar'));
            sidebar.show();
        });
    }

    // Realtime search with "Xem thêm"
    var searchInput = document.querySelector('#searchSidebar input[name="s"]');
    var resultsBox = document.getElementById('search-results');
    var timer = null;
    var lastData = null;
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(timer);
            let keyword = this.value.trim();
            if (keyword.length === 0) {
                resultsBox.innerHTML = '';
                return;
            }
            timer = setTimeout(function() {
                fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=fashion_search_products&keyword=' + encodeURIComponent(keyword))
                    .then(res => res.json())
                    .then(data => {
                        lastData = data;
                        renderResults(5);
                    });
            }, 250);
        });
        // Khi bấm Enter sẽ submit form (chuyển trang)
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                window.location.href = "<?php echo esc_url( get_permalink( get_page_by_path('search-results') ) ); ?>?keyword=" + encodeURIComponent(this.value.trim());
            }
        });
    }

    // Hàm render kết quả, n là số sản phẩm muốn hiển thị
    function renderResults(n) {
        if (!lastData || !lastData.products) return;
        let products = lastData.products;
        let total = lastData.total;
        if (products.length === 0) {
            resultsBox.innerHTML = '<div class="text-muted px-2 py-2">Không tìm thấy sản phẩm phù hợp.</div>';
            return;
        }
        let html = products.slice(0, n).map(item => `
            <a href="<?php echo esc_url( get_permalink( get_page_by_path('product-detail') ) ); ?>?product_id=${item.id}" 
               class="d-flex align-items-center py-2 border-bottom text-decoration-none text-dark search-result-item"
               style="cursor:pointer;">
                <img src="<?php echo get_template_directory_uri(); ?>/assets${item.image}" alt="" style="width:54px;height:68px;object-fit:cover;border-radius:8px;margin-right:12px;">
                <div>
                    <div style="font-size:1rem;font-weight:500;">${item.name}</div>
                    <div style="color:#e53935;font-weight:600;">${Number(item.price).toLocaleString('vi-VN')}đ</div>
                </div>
            </a>
        `).join('');
        if (total > n) {
            html += `<div class="text-center py-2">
                <a href="<?php echo esc_url( get_permalink( get_page_by_path('search-results') ) ); ?>?keyword=${encodeURIComponent(searchInput.value.trim())}" 
                   id="showMoreSearch" 
                   style="color:#e53935;font-weight:500;">Xem thêm ${total-n} sản phẩm</a>
            </div>`;
        }
        resultsBox.innerHTML = html;

        // Xử lý nút "Xem thêm"
        var showMoreBtn = document.getElementById('showMoreSearch');
        if (showMoreBtn) {
            showMoreBtn.onclick = function(e) {
                e.preventDefault();
                renderResults(total);
            };
        }
    }
});
</script>


<?php wp_footer(); ?>
</body>
</html>