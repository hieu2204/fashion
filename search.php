<?php
/* Template Name: Search Results */
get_header();

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$shop_page = isset($_GET['shop_page']) ? max(1, (int)$_GET['shop_page']) : 1;
$per_page = 9;
$offset = ($shop_page - 1) * $per_page;

global $wpdb;
$where = "";
$params = [];
if ($keyword !== '') {
    $where .= " WHERE p.name LIKE %s";
    $params[] = '%' . $keyword . '%';
}

// SQL query for products with pagination
$sql = "SELECT p.product_id, p.name, p.description, p.base_price, p.discount_price, p.sku, p.created_at
        FROM products p
        $where
        ORDER BY p.name ASC
        LIMIT %d OFFSET %d";
$params[] = $per_page;
$params[] = $offset;
$sql = $wpdb->prepare($sql, ...$params);
$products = $wpdb->get_results($sql);

// Count total products
$count_sql = "SELECT COUNT(*) FROM products p $where";
if (!empty($params)) {
    // Remove LIMIT and OFFSET params for count query
    $count_params = array_slice($params, 0, -2);
    $count_sql = $wpdb->prepare($count_sql, ...$count_params);
}
$total_products = $wpdb->get_var($count_sql);
$total_pages = ceil($total_products / $per_page);

// Function to get colors and images
function get_product_colors_with_images($product_id) {
    global $wpdb;
    $colors = $wpdb->get_results($wpdb->prepare("
        SELECT pc.color_id, pc.color_name, pci.image_url, pci.is_primary
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

// Map color names to hex codes
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
    ];
    $key = strtoupper(trim($color_name));
    return $map[$key] ?? '#eee';
}
?>

<div class="container py-4">
    <h3 class="mb-4" style="font-weight:700;">
        Kết quả tìm kiếm cho: <span style="color:#e53935;"><?php echo esc_html($keyword); ?></span>
    </h3>
    <div class="row" id="products-container">
        <?php if (!empty($products)): ?>
            <?php foreach($products as $product):
                $colors_data = get_product_colors_with_images($product->product_id);
                $has_discount = !empty($product->discount_price);
                $display_price = $has_discount ? $product->discount_price : $product->base_price;
                // Get first color with image
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
                <div class="card product-card h-100 border-0 shadow-sm" style="border-radius:18px;">
                    <div class="product-image text-center" style="background:#fff; border-radius:14px 14px 0 0; padding:18px 0;">
                        <a href="<?php echo esc_url( get_permalink( get_page_by_path('product-detail') ) . '?product_id=' . $product->product_id ); ?>">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets' . ($first_image_url ?: '/images/no-image.jpg')); ?>"
                                 alt="<?php echo esc_attr($product->name); ?>"
                                 style="width:110px; height:150px; object-fit:cover; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.07); border:1.5px solid #eee;">
                        </a>
                    </div>
                    <div class="card-body product-info text-center" style="padding:12px 8px 8px 8px;">
                        <div class="product-title" style="font-size:15px;font-weight:600;margin-bottom:4px;color:#222;">
                            <?php echo esc_html($product->name); ?>
                        </div>
                        <div class="product-price" style="font-size:15px;font-weight:700;color:#222;margin-bottom:6px;">
                            $<?php echo number_format($display_price, 2); ?>
                        </div>
                        <div class="product-colors mb-2">
                            <?php foreach ($colors_data as $color_id => $color_info): ?>
                                <span class="product-color-dot"
                                      title="<?php echo esc_attr($color_info['color_name']); ?>"
                                      style="background:<?php echo get_color_hex_by_name($color_info['color_name']); ?>; border:2px solid #eee; width:18px; height:18px; border-radius:50%; display:inline-block; margin-right:4px; vertical-align:middle;">
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <h5>Không tìm thấy sản phẩm phù hợp</h5>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination pagination-custom justify-content-center">
                <?php if ($shop_page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo add_query_arg(['keyword' => $keyword, 'shop_page' => $shop_page - 1]); ?>" aria-label="Previous">
                            <span aria-hidden="true">«</span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php
                $start_page = max(1, $shop_page - 2);
                $end_page = min($total_pages, $shop_page + 2);
                if ($start_page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo add_query_arg(['keyword' => $keyword, 'shop_page' => 1]); ?>">1</a>
                    </li>
                    <?php if ($start_page > 2): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <li class="page-item <?php echo ($i === $shop_page) ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo add_query_arg(['keyword' => $keyword, 'shop_page' => $i]); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo add_query_arg(['keyword' => $keyword, 'shop_page' => $total_pages]); ?>"><?php echo $total_pages; ?></a>
                    </li>
                <?php endif; ?>
                <?php if ($shop_page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo add_query_arg(['keyword' => $keyword, 'shop_page' => $shop_page + 1]); ?>" aria-label="Next">
                            <span aria-hidden="true">»</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>
<?php
echo "<!-- SQL: $sql -->";
echo "<!-- Total: $total_products -->";
echo "<!-- TEMPLATE: search-results.php loaded -->";
get_footer();
?>