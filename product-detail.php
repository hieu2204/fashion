<?php
/* Template Name: Product Detail */
get_header();
?>
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/product-detail.css">
<style>
.product-detail-page {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 4px 32px rgba(0,0,0,0.07);
    padding: 40px 32px 32px 32px;
    margin-top: 36px;
    margin-bottom: 36px;
    font-family: 'Inter', Arial, sans-serif;
}
.product-gallery {
    background: #f8f8f8;
    border-radius: 14px;
    padding: 24px 18px 18px 18px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}
.product-thumbnails {
    display: flex;
    gap: 10px;
    margin-bottom: 18px;
    align-items:center;
    height:400px;
    overflow:auto;
    flex-direction:column;
    width: 80px;
}
.product-thumbnails::-webkit-scrollbar {
  display: none;      
}
.thumb-img {
    width: 60px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid #eee;
    cursor: pointer;
    opacity: 0.7;
    transition: border 0.2s, opacity 0.2s;
}
.thumb-img.active,
.thumb-img:hover {
    border: 2.5px solid #222;
    opacity: 1;
}
.product-main-image {
    text-align: center;
    margin-bottom: 0;
}
.product-main-image img {
    border-radius: 12px;
    box-shadow: 0 2px 16px rgba(0,0,0,0.07);
    background: #fff;
    max-height: 380px;
    max-width: 100%;
}
.product-info {
    padding: 18px 12px 12px 32px;
}
.product-info h2 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 18px;
    letter-spacing: 1px;
}
.product-price {
    font-size: 1.4rem;
    font-weight: 700;
    color: #222;
    margin-bottom: 18px;
}
.product-price .old-price {
    color: #aaa;
    text-decoration: line-through;
    font-size: 1.1rem;
    margin-left: 10px;
}
.size-label, .color-label {
    font-weight: 600;
    font-size: 1rem;
    margin-right: 8px;
}
.size-options {
    display: flex;
    gap: 8px;
    margin-top: 6px;
    margin-bottom: 12px;
}
.size-option {
    display: inline-block;
    min-width: 36px;
    height: 36px;
    line-height: 34px;
    text-align: center;
    border: 1.5px solid #bbb;
    border-radius: 8px;
    background: #fafbfc;
    color: #333;
    font-size: 15px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s;
    user-select: none;
}
.size-option.active,
.size-option:hover {
    background: #222;
    color: #fff;
    border-color: #222;
}
.color-options {
    display: flex;
    gap: 8px;
    margin-top: 6px;
    margin-bottom: 12px;
}
.color-dot {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    border: 2.5px solid #eee;
    display: inline-block;
    cursor: pointer;
    transition: border 0.15s, box-shadow 0.15s;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    position: relative;
}
.color-dot.selected,
.color-dot:hover {
    border: 2.5px solid #222;
    box-shadow: 0 0 0 2px #fff, 0 0 0 5px #222;
}
.stock-text {
    font-size: 1rem;
    margin-bottom: 16px;
    color: #1a8917;
    font-weight: 500;
    min-height: 24px;
}
.stock-text:empty {
    display: none;
}
.quantity-group {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 18px;
}
.quantity-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: #f2f2f2;
    color: #222;
    font-size: 1.3rem;
    border-radius: 8px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.15s;
}
.quantity-btn:disabled {
    background: #eee;
    color: #bbb;
    cursor: not-allowed;
}
.quantity-input {
    width: 48px;
    height: 36px;
    text-align: center;
    border: 1.5px solid #eee;
    border-radius: 8px;
    font-size: 1.1rem;
    background: #fff;
    font-weight: 600;
}
#add-to-cart-form .btn-dark {
    border-radius: 8px;
    padding: 12px 32px;
    font-size: 1.1rem;
    font-weight: 600;
    margin-top: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
}
@media (max-width: 991px) {
    .product-detail-page {
        padding: 18px 4px;
    }
    .product-info {
        padding: 18px 0 0 0;
    }
    .product-main-image img {
        max-height: 220px;
    }
}
</style>
<?php
global $wpdb;

// Lấy ID sản phẩm từ URL (?product_id=...)
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$product = $wpdb->get_row($wpdb->prepare("SELECT * FROM products WHERE product_id = %d", $product_id));

if (!$product) {
    echo '<div class="container py-5"><h3>Không tìm thấy sản phẩm.</h3></div>';
    get_footer();
    exit;
}

// Lấy màu, ảnh, size...
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
$colors_data = get_product_colors_with_images($product->product_id);

$sizes = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT size FROM product_variants WHERE product_id = %d AND stock_quantity > 0", $product->product_id));

// Giá sản phẩm
$has_discount = ($product->discount_price && $product->discount_price < $product->base_price);
$display_price = $has_discount ? $product->discount_price : $product->base_price;
?>

<div class="container py-5 product-detail-page">
  <div class="row">
    <div class="col-md-6">
      <div class="product-gallery">
        <div class="product-thumbnails">
          <?php
          $thumbs = [];
          foreach ($colors_data as $color_id => $color) {
              foreach ($color['images'] as $img) {
                  $thumbs[] = [
                      'url' => $img['url'],
                      'color_id' => $color_id,
                      'color_name' => $color['color_name']
                  ];
              }
          }
          foreach ($thumbs as $i => $thumb): ?>
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets' . $thumb['url']); ?>"
                 class="thumb-img<?php echo $i === 0 ? ' active' : ''; ?>"
                 data-img="<?php echo esc_attr($thumb['url']); ?>"
                 data-color="<?php echo esc_attr($thumb['color_id']); ?>"
                 alt=""
            >
          <?php endforeach; ?>
        </div>
        <div class="product-main-image">
          <img id="mainProductImg"
               src="<?php echo esc_url(get_template_directory_uri() . '/assets' . ($thumbs[0]['url'] ?? '')); ?>"
               alt="<?php echo esc_attr($product->name); ?>"
               class="img-fluid mb-3"
               style="max-height:400px;">
        </div>
      </div>
    </div>
    <div class="col-md-6 product-info">
      <h2><?php echo esc_html($product->name); ?></h2>
      <div class="product-price mb-2">
        <span>$<?php echo number_format($display_price, 2); ?></span>
        <?php if ($has_discount): ?>
          <span class="old-price">$<?php echo number_format($product->base_price, 2); ?></span>
        <?php endif; ?>
      </div>
      <div class="mb-3">
        <span class="size-label">Size:</span>
        <div class="size-options">
          <?php foreach ($sizes as $size): ?>
            <span class="size-option" data-size="<?php echo esc_attr($size); ?>"><?php echo esc_html($size); ?></span>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="mb-3">
        <span class="color-label">Color:</span>
        <div class="color-options">
          <?php
          // Hiển thị chấm màu với tên màu phổ biến
          $color_map = [
              'Red' => '#ff6b6b', 'Blue' => '#2196f3', 'Green' => '#4caf50',
              'Yellow' => '#ffeb3b', 'Orange' => '#ffa500', 'Purple' => '#9c27b0',
              'Pink' => '#e91e63', 'Black' => '#333', 'White' => '#fff',
              'Gray' => '#607d8b', 'Brown' => '#795548'
          ];
          foreach ($colors_data as $color_id => $color):
              $color_code = isset($color_map[$color['color_name']]) ? $color_map[$color['color_name']] : '#ccc';
          ?>
            <span class="color-dot"
                  data-color="<?php echo esc_attr($color_id); ?>"
                  style="background:<?php echo esc_attr($color_code); ?>"
                  title="<?php echo esc_attr($color['color_name']); ?>">
            </span>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="mb-3 stock-text" id="variant-stock"></div>
      <div class="quantity-group">
        <button class="quantity-btn" id="qty-minus">-</button>
        <input type="text" class="quantity-input" id="qty-input" value="1" readonly>
        <button class="quantity-btn" id="qty-plus">+</button>
      </div>
      <form method="post" id="add-to-cart-form" autocomplete="off">
          <input type="hidden" name="product_id" value="<?php echo $product->product_id; ?>">
          <input type="hidden" name="color" id="cart-color">
          <input type="hidden" name="size" id="cart-size">
          <input type="hidden" name="quantity" id="cart-qty">
          <button type="submit" class="btn btn-dark w-100">Thêm vào giỏ</button>
      </form>
      <div id="toast-message" style="display:none;position:fixed;top:30px;right:30px;z-index:9999;padding:14px 24px;background:#222;color:#fff;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.15);font-size:16px;"></div>
    </div>
  </div>
</div>

<?php
// Lấy tồn kho từng variant
$variants = $wpdb->get_results($wpdb->prepare(
    "SELECT color_id, size, stock_quantity FROM product_variants WHERE product_id = %d",
    $product->product_id
));
$variant_data = [];
foreach ($variants as $v) {
    $variant_data[$v->color_id][$v->size] = $v->stock_quantity;
}
?>
<script>
window.variantStock = <?php echo json_encode($variant_data); ?>;
document.addEventListener('DOMContentLoaded', function() {
    // Thumbnail click
    document.querySelectorAll('.thumb-img').forEach(function(img) {
        img.addEventListener('click', function() {
            document.getElementById('mainProductImg').src = img.src;
            document.querySelectorAll('.thumb-img').forEach(i => i.classList.remove('active'));
            img.classList.add('active');
            // Tự động chọn color theo thumbnail
            let color = img.getAttribute('data-color');
            document.querySelectorAll('.color-dot').forEach(dot => {
                dot.classList.toggle('selected', dot.getAttribute('data-color') === color);
            });
            updateStock();
        });
    });

    // Color click
    document.querySelectorAll('.color-dot').forEach(function(dot) {
        dot.addEventListener('click', function() {
            document.querySelectorAll('.color-dot').forEach(d => d.classList.remove('selected'));
            dot.classList.add('selected');
            // Đổi ảnh chính sang ảnh đầu tiên của màu đó
            let color = dot.getAttribute('data-color');
            let thumb = document.querySelector('.thumb-img[data-color="' + color + '"]');
            if (thumb) {
                document.getElementById('mainProductImg').src = thumb.src;
                document.querySelectorAll('.thumb-img').forEach(i => i.classList.remove('active'));
                thumb.classList.add('active');
            }
            updateStock();
        });
    });

    // Size click
    document.querySelectorAll('.size-option').forEach(function(opt) {
        opt.addEventListener('click', function() {
            document.querySelectorAll('.size-option').forEach(o => o.classList.remove('active'));
            opt.classList.add('active');
            updateStock();
        });
    });

    // Hiển thị tồn kho variant và giới hạn số lượng
    function updateStock() {
        let color = document.querySelector('.color-dot.selected')?.getAttribute('data-color');
        let size = document.querySelector('.size-option.active')?.getAttribute('data-size');
        let stock = color && size && window.variantStock[color] && window.variantStock[color][size]
            ? window.variantStock[color][size] : 0;
        let stockText = document.getElementById('variant-stock');
        let qtyInput = document.getElementById('qty-input');
        if (color && size) {
            if (stock > 0) {
                stockText.textContent = `Còn ${stock} sản phẩm trong kho`;
                qtyInput.value = 1;
                qtyInput.max = stock;
                document.getElementById('qty-plus').disabled = false;
                document.getElementById('qty-minus').disabled = false;
            } else {
                stockText.textContent = 'Hết hàng';
                qtyInput.value = 0;
                document.getElementById('qty-plus').disabled = true;
                document.getElementById('qty-minus').disabled = true;
            }
        } else {
            stockText.textContent = 'Vui lòng chọn màu và size';
            qtyInput.value = 1;
            document.getElementById('qty-plus').disabled = true;
            document.getElementById('qty-minus').disabled = true;
        }
    }

    // Mặc định chọn đầu tiên
    document.querySelector('.color-dot')?.classList.add('selected');
    document.querySelector('.size-option')?.classList.add('active');
    updateStock();

    // Quantity tăng/giảm theo tồn kho
    let qtyInput = document.getElementById('qty-input');
    document.getElementById('qty-minus').onclick = function() {
        let v = parseInt(qtyInput.value, 10);
        if (v > 1) qtyInput.value = v - 1;
    };
    document.getElementById('qty-plus').onclick = function() {
        let color = document.querySelector('.color-dot.selected')?.getAttribute('data-color');
        let size = document.querySelector('.size-option.active')?.getAttribute('data-size');
        let stock = color && size && window.variantStock[color] && window.variantStock[color][size]
            ? window.variantStock[color][size] : 0;
        let v = parseInt(qtyInput.value, 10);
        if (v < stock) qtyInput.value = v + 1;
    };

    // Thêm vào giỏ hàng
    document.getElementById('add-to-cart-form').onsubmit = function(e) {
        e.preventDefault();
        document.getElementById('cart-color').value = document.querySelector('.color-dot.selected')?.getAttribute('data-color') || '';
        document.getElementById('cart-size').value = document.querySelector('.size-option.active')?.getAttribute('data-size') || '';
        document.getElementById('cart-qty').value = document.getElementById('qty-input').value;

        var formData = new FormData(this);
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            credentials: 'same-origin',
            body: new URLSearchParams([
                ['action', 'fashion_add_to_cart'],
                ['product_id', formData.get('product_id')],
                ['color', formData.get('color')],
                ['size', formData.get('size')],
                ['quantity', formData.get('quantity')]
            ])
        })
        .then(res => res.json())
        .then(data => {
            showToast(data.success ? "Đã thêm vào giỏ hàng!" : (data.message || "Có lỗi xảy ra!"), data.success ? "success" : "error");
        });
        return false;
    };

    function showToast(message, type) {
        var toast = document.getElementById('toast-message');
        toast.textContent = message;
        toast.style.background = type === "success" ? "#222" : "#e53935";
        toast.style.display = 'block';
        setTimeout(function() {
            toast.style.display = 'none';
        }, 2500);
    }
});
</script>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'], $_POST['color'], $_POST['size'], $_POST['quantity'])) {
    require_once get_template_directory() . '/functions.php'; // Đảm bảo đã có hàm add_to_cart
    add_to_cart(
        intval($_POST['product_id']),
        $_POST['color'],
        $_POST['size'],
        intval($_POST['quantity'])
    );
    // Chuyển hướng sang trang cart
    wp_redirect(home_url('/cart'));
    exit;
}
?>

<!-- Sản phẩm tương tự -->
<div class="container mt-5 mb-4">
    <h3 style="font-weight:700; font-size:1.5rem; margin-bottom:24px;">Sản phẩm tương tự</h3>
    <div class="row">
        <?php
        // Lấy các sản phẩm cùng loại (ví dụ cùng category_id), loại trừ sản phẩm hiện tại
        $similar_products = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM products WHERE product_id != %d ORDER BY RAND() LIMIT 4",
            $product->product_id
        ));
        foreach ($similar_products as $sp):
            $sp_colors = get_product_colors_with_images($sp->product_id);
            // Lấy ảnh đầu tiên của bất kỳ màu nào
            $sp_img_url = null;
            foreach ($sp_colors as $color) {
                if (!empty($color['images'])) {
                    $sp_img_url = $color['images'][0]['url'];
                    break;
                }
            }
            $sp_has_discount = ($sp->discount_price && $sp->discount_price < $sp->base_price);
            $sp_display_price = $sp_has_discount ? $sp->discount_price : $sp->base_price;
        ?>
        <div class="col-md-3 mb-4">
            <a href="<?php echo esc_url( get_permalink( get_page_by_path('product-detail') ) . '?product_id=' . $sp->product_id ); ?>" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm" style="border-radius:14px;">
                    <div class="text-center" style="background:#f8f8f8; border-radius:12px 12px 0 0; padding:18px 0; min-height:210px;">
                        <?php if ($sp_img_url): ?>
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets' . $sp_img_url); ?>"
                                 alt="<?php echo esc_attr($sp->name); ?>"
                                 style="max-height:170px; max-width:100%; border-radius:10px;">
                        <?php else: ?>
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/no-image.jpg'); ?>"
                                 alt="No image"
                                 style="max-height:170px; max-width:100%; border-radius:10px;">
                        <?php endif; ?>
                    </div>
                    <div class="card-body text-center px-2 py-3" style="background:#fff;">
                        <div class="mb-2" style="min-height:24px;">
                            <?php if (!empty($sp_colors)): ?>
                                <?php
                                $color_map = [
                                    'Red' => '#ff6b6b', 'Blue' => '#2196f3', 'Green' => '#4caf50',
                                    'Yellow' => '#ffeb3b', 'Orange' => '#ffa500', 'Purple' => '#9c27b0',
                                    'Pink' => '#e91e63', 'Black' => '#333', 'White' => '#fff',
                                    'Gray' => '#607d8b', 'Brown' => '#795548'
                                ];
                                $rendered = [];
                                $first = true;
                                foreach ($sp_colors as $color_id => $color_info):
                                    if (in_array($color_info['color_name'], $rendered)) continue;
                                    $rendered[] = $color_info['color_name'];
                                    $color_code = isset($color_map[$color_info['color_name']]) ? $color_map[$color_info['color_name']] : '#ccc';
                                ?>
                                    <span style="display:inline-block;width:15px;height:15px;border-radius:50%;background:<?php echo esc_attr($color_code); ?>;border:1.5px solid #eee;margin-right:3px;vertical-align:middle;"></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div style="font-size:15px;font-weight:600;min-height:36px;color:#222;"><?php echo esc_html($sp->name); ?></div>
                        <div style="font-size:16px;font-weight:700;color:#222;">
                            $<?php echo number_format($sp_display_price, 2); ?>
                            <?php if ($sp_has_discount): ?>
                                <span style="text-decoration:line-through;color:#aaa;font-size:13px;margin-left:7px;">
                                    $<?php echo number_format($sp->base_price, 2); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php get_footer(); ?>