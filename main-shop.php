<?php
/* Template Name: Main Shop */
get_header();
?>
<div class="fashion-title text-center py-3">
    <h2 style="font-weight:700; letter-spacing:2px; margin-bottom:0;">Fashion</h2>
</div>
<?php
// Enqueue main-shop CSS & JS
function fashion_enqueue_main_shop_assets() {
    if (is_page_template('main-shop.php')) {
        wp_enqueue_style('main-shop-css', get_template_directory_uri() . '/assets/css/main-shop.css', [], null);
        wp_enqueue_script('main-shop-js', get_template_directory_uri() . '/assets/js/main-shop.js', ['jquery'], null, true);
    }
}
add_action('wp_enqueue_scripts', 'fashion_enqueue_main_shop_assets');

// Database connection
global $wpdb;

// Pagination setup
$paged = isset($_GET['paged']) ? max(1, (int)$_GET['paged']) : 1;
$shop_page = isset($_GET['shop_page']) ? max(1, (int)$_GET['shop_page']) : 1;
$per_page = 9;
$offset = ($shop_page - 1) * $per_page;
echo "<!-- shop_page=$shop_page, offset=$offset -->";

// Không filter, chỉ lấy tất cả sản phẩm
$order_by = "p.name ASC";

// Main query to get products with pagination
$base_query = "
    SELECT DISTINCT
        p.product_id,
        p.name,
        p.description, 
        p.base_price,
        p.discount_price,
        p.sku,
        p.created_at
    FROM products p
    WHERE 1=1
";

// Get total count for pagination
$count_query = "SELECT COUNT(*) FROM products";
$total_products = $wpdb->get_var($count_query);
$total_pages = ceil($total_products / $per_page);
// Khởi tạo biến where
$where = "WHERE 1=1";

// Tìm kiếm theo từ khóa
if (!empty($_GET['s'])) {
    $keyword = trim($_GET['s']);
    $where .= " AND p.name LIKE '%" . esc_sql($keyword) . "%'";
}

// Filter theo màu
if (!empty($_GET['filter_colors'])) {
    $colors = array_map('esc_sql', $_GET['filter_colors']);
    $color_placeholders = "'" . implode("','", $colors) . "'";
    $where .= " AND EXISTS (
        SELECT 1 FROM product_colors pc
        WHERE pc.product_id = p.product_id
        AND pc.color_name IN ($color_placeholders)
    )";
}

// Filter theo size
if (!empty($_GET['filter_sizes'])) {
    $sizes = array_map('esc_sql', $_GET['filter_sizes']);
    $size_placeholders = "'" . implode("','", $sizes) . "'";
    $where .= " AND EXISTS (
        SELECT 1 FROM product_variants pv
        WHERE pv.product_id = p.product_id
        AND pv.size IN ($size_placeholders)
        AND pv.stock_quantity > 0
    )";
}

// Filter theo price
if (!empty($_GET['filter_prices'])) {
    $price_conditions = [];
    foreach ($_GET['filter_prices'] as $range) {
        if ($range === '0-50') $price_conditions[] = "(p.base_price < 50)";
        elseif ($range === '50-100') $price_conditions[] = "(p.base_price >= 50 AND p.base_price < 100)";
        elseif ($range === '100-200') $price_conditions[] = "(p.base_price >= 100 AND p.base_price < 200)";
        elseif ($range === '200-') $price_conditions[] = "(p.base_price >= 200)";
    }
    if ($price_conditions) {
        $where .= " AND (" . implode(' OR ', $price_conditions) . ")";
    }
}

// Get products for current page
$products_query = "
    SELECT
        p.product_id,
        p.name,
        p.description, 
        p.base_price,
        p.discount_price,
        p.sku,
        p.created_at
    FROM products p
    $where
    ORDER BY $order_by
    LIMIT $per_page OFFSET $offset
";
$products = $wpdb->get_results($products_query);

$count_query = "SELECT COUNT(*) FROM products p $where";
$total_products = $wpdb->get_var($count_query);
$total_pages = ceil($total_products / $per_page);

// Function to get product colors and images
function get_product_colors_with_images($product_id) {
    global $wpdb;
    $colors = $wpdb->get_results($wpdb->prepare("
        SELECT 
            pc.color_id,
            pc.color_name,
            pci.image_url,
            pci.is_primary
        FROM product_colors pc
        LEFT JOIN product_color_images pci ON pc.color_id = pci.color_id
        WHERE pc.product_id = %d
        ORDER BY pci.is_primary DESC, pci.id ASC
    ", $product_id));
    $color_data = [];
    foreach ($colors as $color) {
        if (!isset($color_data[$color->color_id])) {
            $color_data[$color->color_id] = [
                'color_name' => $color->color_name,
                'images' => []
            ];
        }
        if ($color->image_url) {
            $color_data[$color->color_id]['images'][] = [
                'url' => $color->image_url,
                'is_primary' => $color->is_primary
            ];
        }
    }
    return $color_data;
}

// Function to get available sizes for product
function get_product_sizes($product_id) {
    global $wpdb;
    return $wpdb->get_col($wpdb->prepare("
        SELECT DISTINCT size 
        FROM product_variants 
        WHERE product_id = %d AND stock_quantity > 0
        ORDER BY 
            CASE size 
                WHEN 'XS' THEN 1
                WHEN 'S' THEN 2
                WHEN 'M' THEN 3
                WHEN 'L' THEN 4
                WHEN 'XL' THEN 5
                WHEN 'XXL' THEN 6
                ELSE 7
            END
    ", $product_id));
}

// Lấy tất cả màu duy nhất theo tên màu
$all_colors = $wpdb->get_results("SELECT color_name FROM product_colors GROUP BY color_name ORDER BY color_name ASC");

// Lấy tất cả kích thước có trong bảng product_variants
$all_sizes = $wpdb->get_col("SELECT DISTINCT size FROM product_variants WHERE stock_quantity > 0 ORDER BY 
    CASE size 
        WHEN 'XS' THEN 1
        WHEN 'S' THEN 2
        WHEN 'M' THEN 3
        WHEN 'L' THEN 4
        WHEN 'XL' THEN 5
        WHEN 'XXL' THEN 6
        ELSE 7
    END
");

// Thiết lập khoảng giá cho bộ lọc
$min_price = $wpdb->get_var("SELECT MIN(base_price) FROM products");
$max_price = $wpdb->get_var("SELECT MAX(base_price) FROM products");
$price_ranges = [
    '0-50' => 'Under $50',
    '50-100' => '$50 - $100',
    '100-200' => '$100 - $200',
    '200-' => 'Above $200'
];
?>
<style>
    body {
        font-family: 'Inter', Arial, sans-serif;
        background: #fff;
        color: #222;
    }
    /* Fashion Title Styles */
.fashion-title {
    margin-top: 40px;
    margin-bottom: 18px;
    font-size: 2.3rem;
    font-weight: 700;
    letter-spacing: 2px;
}

.breadcrumb {
    background: none;
    padding: 0;
    margin-bottom: 28px;
    font-size: 15px;
    color: #888;
}
.breadcrumb a {
    color: #888;
    text-decoration: none;
}
.breadcrumb .active {
    color: #222;
    font-weight: 500;
}

.container-fluid.py-4 {
    margin-top: 0;
}

.row {
    margin-left: 0;
    margin-right: 0;
}

/* Sidebar filter styles */
.sidebar {
    background: none;
    border-radius: 0;
    box-shadow: none;
    padding: 0 0 0 10px;
    margin: 0 0 0 0;
    min-width: 210px;
}

.filter-section {
    margin-bottom: 28px;
    padding-bottom: 0;
    border-bottom: none;
}
.filter-section h6 {
    font-weight: 700;
    margin-bottom: 12px;
    color: #222;
    font-size: 15px;
    letter-spacing: 0.5px;
    text-transform: none;
}
    
    .color-option,
.product-color-dot {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    display: inline-block;
    margin: 0 6px 8px 0;
    cursor: pointer;
    border: 2px solid #eee;
    position: relative;
    transition: box-shadow 0.15s, border 0.15s;
}
.color-option.selected,
.product-color-dot.active {
    border: 2.5px solid #222 !important;
    box-shadow: 0 0 0 2px #fff, 0 0 0 5px #222;
}
.size-option {
    display: inline-block;
    width: 32px;
    height: 32px;
    line-height: 30px;
    text-align: center;
    border: 1.5px solid #bbb;
    border-radius: 6px;
    margin: 0 6px 8px 0;
    cursor: pointer;
    font-size: 14px;
    background: #fafbfc;
    color: #333;
    transition: all 0.15s;
    font-weight: 500;
}
.size-option.active,
.size-option:hover {
    background: #222;
    color: #fff;
    border-color: #222;
}
.filter-section label {
    font-size: 14px;
    color: #444;
    font-weight: 400;
    margin-bottom: 0;
    cursor: pointer;
}
.filter-section input[type="checkbox"] {
    margin-right: 7px;
    accent-color: #222;
}
.btn.btn-dark.btn-sm {
    background: #222;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    padding: 6px 18px;
    margin-top: 10px;
}
    
    .product-card {
        background: #fff;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        transition: transform 0.2s, box-shadow 0.2s;
        margin-bottom: 32px;
        cursor: pointer;
        border: 1px solid #f2f2f2;
    }
    
    .product-card:hover {
        transform: translateY(-4px) scale(1.01);
        box-shadow: 0 6px 24px rgba(0,0,0,0.10);
    }
    
    .product-image {
        position: relative;
        overflow: hidden;
        height: 260px;
        background: #f6f6f6;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .product-image img {
        max-height: 240px;
        object-fit: cover;
        width: auto;
        margin: 0 auto;
        transition: transform 0.3s, opacity 0.3s;
    }
    
    .product-image img.hidden {
        opacity: 0;
        position: absolute;
        top: 0;
        left: 0;
    }
    
    .product-card:hover .product-image img:not(.hidden) {
        transform: scale(1.04);
    }
    
    .product-info {
        padding: 14px 10px 10px 10px;
    }
    
    .product-title {
        font-size: 15px;
        font-weight: 600;
        margin-bottom: 6px;
        color: #222;
        min-height: 36px;
    }
    
    .product-price {
        font-size: 16px;
        font-weight: 700;
        color: #222;
        margin-bottom: 8px;
    }
    
    .product-price .old-price {
        text-decoration: line-through;
        color: #999;
        font-size: 14px;
        margin-left: 8px;
    }
    
    .product-colors {
        margin-bottom: 8px;
    }
    
    .product-color-dot {
        border: 1.5px solid #eee;
        margin-right: 3px;
        width: 16px;
        height: 16px;
    }
    
    .product-color-dot.active {
        border: 2.5px solid #222 !important;
        box-shadow: 0 0 0 2px #fff, 0 0 0 5px #222;
    }
    
    .product-sizes {
        font-size: 12px;
        color: #888;
    }
    
    .sale-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        background: #222;
        color: #fff;
        padding: 4px 10px;
        border-radius: 14px;
        font-size: 12px;
        font-weight: 700;
        z-index: 2;
        letter-spacing: 1px;
    }
    
    .pagination-custom {
        justify-content: center;
        margin-top: 30px;
        margin-bottom: 10px;
    }
    
    .pagination-custom .page-link {
        color: #222;
        border: 1px solid #eee;
        background: #fff;
        padding: 0.45rem 0.85rem;
        margin: 0 2px;
        border-radius: 6px;
        font-size: 15px;
        text-decoration: none;
        transition: all 0.2s;
    }
    
    .pagination-custom .page-link:hover {
        color: #fff;
        background: #222;
        border-color: #222;
    }
    
    .pagination-custom .page-item.active .page-link {
        color: #fff;
        background: #222;
        border-color: #222;
    }
    
    .pagination-custom .page-item.disabled .page-link {
        color: #bbb;
        background: #fff;
        border-color: #eee;
        cursor: not-allowed;
    }
    
    .pagination-debug {
        background: none;
        padding: 0;
        border-radius: 0;
        margin-bottom: 0.5rem;
        text-align: right;
        font-size: 13px;
        color: #888;
    }
    
    @media (max-width: 991px) {
        .fashion-title {
            margin-top: 18px;
            margin-bottom: 12px;
            font-size: 1.3rem;
        }
        .sidebar {
            min-width: 100%;
            padding: 0;
            margin-bottom: 18px;
        }
        .col-lg-9 {
            padding-left: 0;
        }
        .product-image {
            height: 180px;
        }
        .product-image img {
            max-height: 160px;
        }
    }

    /* Pagination Styles */
.pagination-custom {
    margin: 2rem 0;
}

.pagination-custom .page-link {
    color: #333;
    background-color: #fff;
    border: 1px solid #dee2e6;
    padding: 0.5rem 0.75rem;
    margin: 0 2px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.pagination-custom .page-link:hover {
    color: #fff;
    background-color: #333;
    border-color: #333;
}

.pagination-custom .page-item.active .page-link {
    color: #fff;
    background-color: #333;
    border-color: #333;
}

.pagination-custom .page-item.disabled .page-link {
    color: #6c757d;
    background-color: #fff;
    border-color: #dee2e6;
    cursor: not-allowed;
}

.pagination-debug {
    background-color: #f8f9fa;
    padding: 0.5rem;
    border-radius: 0.25rem;
    margin-bottom: 1rem;
}

/* Product Color Switching Fix */
.product-color-dot {
    transition: all 0.2s ease;
    cursor: pointer !important;
    position: relative;
}

.product-color-dot:hover {
    transform: scale(1.1);
}

.product-color-dot.active {
    border: 1.5px solid rgba(0, 0, 0, 1) !important;   /* Viền đỏ nổi bật */
    box-shadow: 0 0 0 1px #fff, 0 0 0 3px rgba(0, 0, 0, 1);
    outline: none;
}

/* Loading overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loading-overlay.show {
    display: flex;
}

/* Thêm khoảng cách phía trên cho toàn bộ nội dung */
.container-fluid.py-4 {
    margin-top: 32px; /* hoặc 40px nếu muốn rộng hơn */
}

/* Tạo khoảng cách đều hai bên cho filter và sản phẩm */
.row {
    margin-left: 0;
    margin-right: 0;
}

/* Sidebar filter cách trái và phải đều */
.sidebar {
    margin-left: 24px;
    margin-right: 24px;
}

/* Đảm bảo sản phẩm không dính sát filter */
.col-lg-9 {
    padding-left: 24px;
}

/* Đảm bảo filter không dính sát lề trái */
.col-lg-3 {
    padding-right: 0;
    padding-left: 0;
}

/* Responsive: giảm khoảng cách trên mobile */
@media (max-width: 991px) {
/* Thêm khoảng cách phía trên cho toàn bộ nội dung */
.container-fluid.py-4 {
    margin-top: 32px; /* hoặc 40px nếu muốn rộng hơn */
}

/* Tạo khoảng cách đều hai bên cho filter và sản phẩm */
.row {
    margin-left: 0;
    margin-right: 0;
}

/* Sidebar filter cách trái và phải đều */
.sidebar {
    margin-left: 24px;
    margin-right: 24px;
}

/* Đảm bảo sản phẩm không dính sát filter */
.col-lg-9 {
    padding-left: 24px;
}

/* Đảm bảo filter không dính sát lề trái */
.col-lg-3 {
    padding-right: 0;
    padding-left: 0;
}

/* Responsive: giảm khoảng cách trên mobile */
@media (max-width: 991px) {
    .container-fluid.py-4 {
        margin-top: 16px;
    }
    .sidebar,
    .col-lg-9 {
        margin-left: 0;
        margin-right: 0;
        padding-left: 0;
        padding-right: 0;
    }
}
</style>
<div class="container-fluid py-4">
    <div class="row">
        <?php if (empty($_GET['s'])): ?>
        <div class="col-lg-3">
            <!-- Filter Sidebar -->
            <div class="sidebar">
                <form id="shop-filter-form" method="get">
                    <div class="filter-section">
                        <h6>Colors</h6>
                        <?php foreach ($all_colors as $color): ?>
                            <div>
                                <label>
                                    <input type="checkbox" name="filter_colors[]" value="<?php echo esc_attr($color->color_name); ?>"
                                        <?php if (!empty($_GET['filter_colors']) && in_array($color->color_name, $_GET['filter_colors'])) echo 'checked'; ?>>
                                    <?php echo esc_html($color->color_name); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="filter-section">
                        <h6>Sizes</h6>
                        <?php foreach ($all_sizes as $size): ?>
                            <div>
                                <label>
                                    <input type="checkbox" name="filter_sizes[]" value="<?php echo esc_attr($size); ?>"
                                        <?php if (!empty($_GET['filter_sizes']) && in_array($size, $_GET['filter_sizes'])) echo 'checked'; ?>>
                                    <?php echo esc_html($size); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="filter-section">
                        <h6>Prices</h6>
                        <?php foreach ($price_ranges as $range => $label): ?>
                            <div>
                                <label>
                                    <input type="checkbox" name="filter_prices[]" value="<?php echo esc_attr($range); ?>"
                                        <?php if (!empty($_GET['filter_prices']) && in_array($range, $_GET['filter_prices'])) echo 'checked'; ?>>
                                    <?php echo esc_html($label); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="btn btn-dark btn-sm mt-2">Apply Filter</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
        <div class="<?php echo empty($_GET['s']) ? 'col-lg-9' : 'col-12'; ?>">
            <!-- Products Grid -->
            <div class="row" id="products-container">
                <?php if (!empty($products)): ?>
                    <?php foreach($products as $product): 
                        $colors_data = get_product_colors_with_images($product->product_id);
                        $sizes = get_product_sizes($product->product_id);
                        $has_discount = !empty($product->discount_price);
                        $display_price = $has_discount ? $product->discount_price : $product->base_price;

                        // Lấy màu đầu tiên có ảnh
                        $first_color_id = null;
                        $first_image_url = null;
                        foreach ($colors_data as $color_id => $color_info) {
                            if (!empty($color_info['images'])) {
                                $first_color_id = $color_id;
                                $first_image_url = $color_info['images'][0]['url'];
                                break;
                            }
                        }
                        ?>
                        <div class="col-lg-4 col-md-6 mb-4 product-item">
                            <div class="card product-card h-100 border-0 shadow-sm" data-product-id="<?php echo $product->product_id; ?>" style="border-radius:18px;">
                                <div class="product-image position-relative text-center" style="height:260px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#f8f8f8;">
                                    <?php if ($has_discount): ?>
                                        <span class="sale-badge" style="top:14px;left:14px;"><?php _e('SALE', 'textdomain'); ?></span>
                                    <?php endif; ?>
                                    <?php if ($first_image_url): ?>
                                        <a href="<?php echo esc_url( get_permalink( get_page_by_path('product-detail') ) . '?product_id=' . $product->product_id ); ?>">
                                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets' . $first_image_url); ?>"
                                                 alt="<?php echo esc_attr($product->name); ?>"
                                                 class="product-main-image img-fluid"
                                                 data-color-id="<?php echo $first_color_id; ?>"
                                                 style="max-height:300px;object-fit:cover;">
                                        </a>
                                    <?php else: ?>
                                        <a href="<?php echo esc_url( get_permalink( get_page_by_path('product-detail') ) . '?product_id=' . $product->product_id ); ?>">
                                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/no-image.jpg'); ?>"
                                                 alt="No image"
                                                 class="product-main-image img-fluid"
                                                 style="max-height:300px;object-fit:cover;">
                                        </a>
                                    <?php endif; ?>

                                    <?php
                                    // Ảnh ẩn cho từng màu (để JS chuyển đổi)
                                    foreach ($colors_data as $color_id => $color_info):
                                        if (!empty($color_info['images'])):
                                    ?>
                                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets' . $color_info['images'][0]['url']); ?>"
                                             alt="<?php echo esc_attr($product->name . ' - ' . $color_info['color_name']); ?>"
                                             class="product-image-hidden d-none"
                                             data-color-id="<?php echo $color_id; ?>">
                                    <?php
                                        endif;
                                    endforeach;
                                    ?>
                                </div>
                                <div class="card-body product-info text-center">
                                    <h6 class="product-title mb-2" style="min-height:38px;">
                                        <a href="<?php echo esc_url( get_permalink( get_page_by_path('product-detail') ) . '?product_id=' . $product->product_id ); ?>" class="text-dark text-decoration-none">
                                            <?php echo esc_html($product->name); ?>
                                        </a>
                                    </h6>
                                    <?php if (!empty($colors_data)): ?>
                                        <div class="product-colors mb-2">
                                            <?php foreach ($colors_data as $color_id => $color_info): ?>
                                                <span class="product-color-dot<?php echo ($color_id == $first_color_id) ? ' active' : ''; ?>"
                                                      title="<?php echo esc_attr($color_info['color_name']); ?>"
                                                      data-color-id="<?php echo $color_id; ?>"
                                                      style="background:<?php echo get_color_hex_by_name($color_info['color_name']); ?>; border-color:#ccc;">
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="product-price mb-2">
                                        <span class="fw-bold text-dark">$<?php echo number_format($display_price, 2); ?></span>
                                        <?php if ($has_discount): ?>
                                            <span class="old-price text-muted ms-2" style="text-decoration:line-through;">$<?php echo number_format($product->base_price, 2); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($sizes)): ?>
                                        <div class="product-sizes text-muted" style="font-size:13px;">
                                            <?php _e('Sizes:', 'textdomain'); ?> <?php echo implode(', ', $sizes); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <h5><?php _e('No products found', 'textdomain'); ?></h5>
                        <p><?php _e('Try adjusting your filters', 'textdomain'); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <div class="pagination-debug mb-3">
                <small class="text-muted">
                    Showing <?php echo count($products); ?> of <?php echo $total_products; ?> products 
                    (Page <?php echo $paged; ?> of <?php echo $total_pages; ?>)
                </small>
            </div>

            <?php if ($total_pages > 1): ?>
                <nav aria-label="<?php _e('Page navigation', 'textdomain'); ?>" class="mt-4">
                    <ul class="pagination pagination-custom justify-content-center">
                        <?php if ($shop_page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo add_query_arg('shop_page', $shop_page - 1); ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php
                        $start_page = max(1, $shop_page - 2);
                        $end_page = min($total_pages, $shop_page + 2);
                        if ($start_page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo add_query_arg('shop_page', 1); ?>">1</a>
                            </li>
                            <?php if ($start_page > 2): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <li class="page-item <?php echo ($i === $shop_page) ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo add_query_arg('shop_page', $i); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <?php if ($end_page < $total_pages): ?>
                            <?php if ($end_page < $total_pages - 1): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo add_query_arg('shop_page', $total_pages); ?>"><?php echo $total_pages; ?></a>
                            </li>
                        <?php endif; ?>
                        <?php if ($shop_page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo add_query_arg('shop_page', $shop_page + 1); ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php

/* Nhúng trực tiếp JS để test */ ?>
<script>
jQuery(document).ready(function($) {
    console.log('main-shop.js inline loaded');

    // Đổi ảnh chính khi click màu
    $(document).on('click', '.product-color-dot', function(e) {
        console.log('Color dot clicked', this);
        e.preventDefault();
        e.stopPropagation();

        var $this = $(this);
        var colorId = $this.data('color-id');
        var $productCard = $this.closest('.product-card');

        // Đổi class active cho màu
        $productCard.find('.product-color-dot').removeClass('active');
        $this.addClass('active');

        // Đổi ảnh chính
        var $mainImage = $productCard.find('.product-main-image');
        var $targetImage = $productCard.find('.product-image-hidden[data-color-id="' + colorId + '"]');
        if ($targetImage.length > 0) {
            var newSrc = $targetImage.attr('src');
            var newAlt = $targetImage.attr('alt');
            $mainImage.fadeOut(150, function() {
                $mainImage.attr('src', newSrc);
                $mainImage.attr('alt', newAlt);
                $mainImage.attr('data-color-id', colorId);
                $mainImage.fadeIn(150);
            });
        }
    });
});
</script>
<?php get_footer(); ?>

<!-- Instagram Section -->
<div class="shop-instagram-section" style="background:#fff; padding:48px 0 32px 0; margin-top:32px;">
    <div class="container text-center">
        <h3 style="font-family:'Inter',serif; font-weight:600; font-size:2rem; margin-bottom:10px;">Follow Us On Instagram</h3>
        <p style="color:#888; font-size:15px; margin-bottom:32px;">
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
        </p>
        <div class="shop-instagram-gallery d-flex justify-content-center align-items-center" style="gap:12px; flex-wrap:wrap;">
            <?php
            // Lấy 7 sản phẩm ngẫu nhiên
            $random_products = $wpdb->get_results("SELECT product_id, name FROM products ORDER BY RAND() LIMIT 7");
            foreach ($random_products as $insta_product):
                $insta_colors = get_product_colors_with_images($insta_product->product_id);
                $insta_img_url = null;
                // Lấy ảnh đầu tiên của bất kỳ màu nào
                foreach ($insta_colors as $color) {
                    if (!empty($color['images'])) {
                        $insta_img_url = $color['images'][0]['url'];
                        break;
                    }
                }
                if ($insta_img_url):
            ?>
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets' . $insta_img_url); ?>"
                     alt="<?php echo esc_attr($insta_product->name); ?>"
                     style="width:110px; height:150px; object-fit:cover; border-radius:10px;">
            <?php else: ?>
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/no-image.jpg'); ?>"
                     alt="No image"
                     style="width:110px; height:150px; object-fit:cover; border-radius:10px;">
            <?php endif; endforeach; ?>
        </div>
    </div>
</div>

<!-- Newsletter Section -->
<div class="shop-newsletter-section" style="background:#fff; padding:56px 0 32px 0; position:relative;">
    <div class="container position-relative">
        <div class="row align-items-center justify-content-center">
            <div class="col-lg-8 mx-auto text-center">
                <h3 style="font-family:'Inter',serif; font-weight:600; font-size:2rem; margin-bottom:10px;">Subscribe To Our Newsletter</h3>
                <p style="color:#888; font-size:15px; margin-bottom:28px;">
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                </p>
                <form class="d-flex justify-content-center align-items-center flex-wrap" style="gap:12px;">
                    <input type="email" class="form-control" placeholder="micholajiy@mail.com" style="max-width:320px; border-radius:6px; border:1.5px solid #eee; padding:12px 16px; font-size:15px;">
                    <button type="submit" class="btn btn-dark" style="border-radius:6px; padding:12px 28px; font-size:15px;">Subscribe Now</button>
                </form>
            </div>
        </div>
        <!-- Fashion models left/right -->
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/model-left.png" alt="" style="position:absolute; left:0; bottom:0; width:120px; max-width:30vw; z-index:1;">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/model-right.png" alt="" style="position:absolute; right:0; bottom:0; width:120px; max-width:30vw; z-index:1;">
    </div>
</div>

<?php
// Map tên màu sang mã màu hex
function get_color_hex_by_name($color_name) {
    $map = [
        'RED' => '#ff0000',
        'GREEN' => '#00ff00',
        'BLUE' => '#0074d9',
        'YELLOW' => '#ffe600',
        'WHITE' => '#ffffff',
        'BLACK' => '#222222',
        'BEIGE' => '#f5f5dc',
        'PINK' => '#ffb6c1',
        'BROWN' => '#8b4513',
        // Thêm các màu khác nếu cần
    ];
    $key = strtoupper(trim($color_name));
    return $map[$key] ?? '#eee';
}
?>