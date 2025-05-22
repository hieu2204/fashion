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

// Filter màu
if (!empty($_GET['filter_colors'])) {
    $color_names = array_map(function($c) use ($wpdb) { return "'" . esc_sql($c) . "'"; }, $_GET['filter_colors']);
    $where .= " AND p.product_id IN (
        SELECT product_id FROM product_colors WHERE color_name IN (" . implode(',', $color_names) . ")
    )";
}

// Filter size
if (!empty($_GET['filter_sizes'])) {
    $sizes = array_map(function($s) use ($wpdb) { return "'" . esc_sql($s) . "'"; }, $_GET['filter_sizes']);
    $where .= " AND p.product_id IN (
        SELECT product_id FROM product_variants WHERE size IN (" . implode(',', $sizes) . ") AND stock_quantity > 0
    )";
}

// Filter giá
if (!empty($_GET['filter_prices'])) {
    $price_conditions = [];
    foreach ($_GET['filter_prices'] as $range) {
        if (strpos($range, '-') !== false) {
            list($min, $max) = explode('-', $range);
            if ($max === '') $max = 1000000; // Giá trên $200
            $price_conditions[] = "(COALESCE(p.discount_price, p.base_price) BETWEEN $min AND $max)";
        }
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
        font-family: 'Arial', sans-serif;
        background-color: #f8f9fa;
    }
    /* Fashion Title Styles */
.fashion-title {
    margin-top: 32px;
    margin-bottom: 32px;
    font-size: 2.2rem;
    letter-spacing: 2px;
}

.container-fluid.py-4 {
    margin-top: 0; /* Đã có margin ở .fashion-title rồi */
}

@media (max-width: 991px) {
    .fashion-title {
        margin-top: 16px;
        margin-bottom: 18px;
        font-size: 1.4rem;
    }
}
    /* Sidebar filter styles */
.sidebar {
    background: #fff;
    border-radius: 12px;
    padding: 28px 20px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.07);
    margin-bottom: 30px;
    margin-left: 24px;
    margin-right: 24px;
    margin-top: 40px;
}

.filter-section {
    margin-bottom: 32px;
    padding-bottom: 18px;
    border-bottom: 1px solid #eee;
}
.filter-section:last-child {
    border-bottom: none;
}
.filter-section h6 {
    font-weight: 700;
    margin-bottom: 18px;
    color: #222;
    font-size: 17px;
    letter-spacing: 0.5px;
}
    
    .color-option {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: inline-block;
        margin: 0 8px 8px 0;
        cursor: pointer;
        border: 2px solid #eee;
        position: relative;
        transition: box-shadow 0.15s, border 0.15s;
    }
    
    .color-option.selected {
        border: 2.5px solid #e53935 !important;
        box-shadow: 0 0 0 2px #fff, 0 0 0 5px #e53935;
    }
    
    .color-option.selected::after {
        content: '✓';
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        color: #fff;
        font-size: 13px;
        font-weight: bold;
        text-shadow: 0 1px 2px rgba(0,0,0,0.25);
    }
    
    /* Size filter */
.size-option {
    display: inline-block;
    width: 38px;
    height: 38px;
    line-height: 36px;
    text-align: center;
    border: 1.5px solid #bbb;
    border-radius: 8px;
    margin: 0 8px 10px 0;
    cursor: pointer;
    font-size: 15px;
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
    
    .product-card {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        transition: transform 0.3s, box-shadow 0.3s;
        margin-bottom: 32px;
        cursor: pointer;
    }
    
    .product-card:hover {
        transform: translateY(-6px) scale(1.01);
        box-shadow: 0 6px 24px rgba(0,0,0,0.13);
    }
    
    .product-image {
        position: relative;
        overflow: hidden;
        height: 320px;
        background: #f6f6f6;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .product-image img {
        max-height: 300px;
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
        transform: scale(1.05);
    }
    
    .product-info {
        padding: 15px;
    }
    
    .product-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 7px;
        color: #222;
        min-height: 38px;
    }
    
    .product-price {
        font-size: 17px;
        font-weight: 700;
        color: #222;
        margin-bottom: 10px;
    }
    
    .product-price .old-price {
        text-decoration: line-through;
        color: #999;
        font-size: 15px;
        margin-left: 10px;
    }
    
    .product-colors {
        margin-bottom: 10px;
    }
    
    .product-color-dot {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
        border: 1px solid #eee;
        cursor: pointer;
        transition: transform 0.2s ease;
    }
    
    .product-color-dot:hover {
        transform: scale(1.2);
    }
    
    .product-color-dot.active {
        border: 3px solid rgb(0, 0, 0) !important;   /* Viền đỏ nổi bật */
        /* box-shadow: 0 0 0 3px #fff, 0 0 0 6px #e53935; */
        outline: none;
    }
    
    .product-sizes {
        font-size: 13px;
        color: #666;
    }
    
    .sale-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        background: #dc3545;
        color: #fff;
        padding: 5px 12px;
        border-radius: 16px;
        font-size: 13px;
        font-weight: 700;
        z-index: 2;
    }
    
    .filter-section {
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }
    
    .filter-section:last-child {
        border-bottom: none;
    }
    
    .filter-section h6 {
        font-weight: 600;
        margin-bottom: 15px;
        color: #333;
    }
    
    .view-options {
        display: flex;
        gap: 5px;
        margin-bottom: 20px;
    }
    
    .view-btn {
        width: 30px;
        height: 30px;
        border: 1px solid #ddd;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    
    .view-btn.active {
        background: #333;
        color: white;
    }
    
    .pagination-custom {
        justify-content: center;
        margin-top: 40px;
    }
    
    .pagination-custom .page-link {
        color: #333;
        border: 1px solid #ddd;
        margin: 0 2px;
        text-decoration: none;
    }
    
    .pagination-custom .page-item.active .page-link {
        background-color: #333;
        border-color: #333;
        color: white;
    }
    
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255,255,255,0.8);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
    
    @media (max-width: 768px) {
        .sidebar {
            margin-bottom: 20px;
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
        <div class="col-lg-9">
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
                            <div class="card product-card h-100" data-product-id="<?php echo $product->product_id; ?>">
                                <div class="product-image position-relative text-center" style="height:320px;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                                    <?php if ($has_discount): ?>
                                        <span class="sale-badge position-absolute top-0 start-0 bg-danger text-white px-2 py-1" style="font-size:12px;"><?php _e('SALE', 'textdomain'); ?></span>
                                    <?php endif; ?>
                                    <?php if ($first_image_url): ?>
                                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets' . $first_image_url); ?>"
                                             alt="<?php echo esc_attr($product->name); ?>"
                                             class="product-main-image img-fluid"
                                             data-color-id="<?php echo $first_color_id; ?>"
                                             style="max-height:300px;object-fit:cover;">
                                    <?php else: ?>
                                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/no-image.jpg'); ?>"
                                             alt="No image"
                                             class="product-main-image img-fluid"
                                             style="max-height:300px;object-fit:cover;">
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
                                    <h6 class="product-title mb-2" style="min-height:38px;"><?php echo esc_html($product->name); ?></h6>
                                    <div class="product-price mb-2">
                                        <span class="fw-bold text-dark">$<?php echo number_format($display_price, 2); ?></span>
                                        <?php if ($has_discount): ?>
                                            <span class="old-price text-muted ms-2" style="text-decoration:line-through;">$<?php echo number_format($product->base_price, 2); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($colors_data)): ?>
                                        <div class="product-colors mb-2">
                                            <?php 
                                            $color_map = [
                                                'Red' => '#ff6b6b', 'Blue' => '#2196f3', 'Green' => '#4caf50',
                                                'Yellow' => '#ffeb3b', 'Orange' => '#ffa500', 'Purple' => '#9c27b0',
                                                'Pink' => '#e91e63', 'Black' => '#333', 'White' => '#fff',
                                                'Gray' => '#607d8b', 'Brown' => '#795548'
                                            ];
                                            $color_names_rendered = [];
                                            $first = true;
                                            foreach ($colors_data as $color_id => $color_info):
                                                // Nếu tên màu đã render rồi thì bỏ qua
                                                if (in_array($color_info['color_name'], $color_names_rendered)) continue;
                                                $color_names_rendered[] = $color_info['color_name'];
                                                $color_code = isset($color_map[$color_info['color_name']]) ? $color_map[$color_info['color_name']] : '#ccc';
                                            ?>
                                                <span class="product-color-dot<?php echo $first ? ' active' : ''; ?>"
                                                      data-color-id="<?php echo $color_id; ?>"
                                                      style="background-color: <?php echo $color_code; ?>; border:1px solid #ddd; width:18px; height:18px; display:inline-block; border-radius:50%; margin:0 2px; cursor:pointer;"
                                                      title="<?php echo esc_attr($color_info['color_name']); ?>"></span>
                                            <?php
                                                $first = false;
                                            endforeach;
                                            ?>
                                        </div>
                                    <?php endif; ?>
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