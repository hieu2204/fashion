<?php
/* Template Name: Product Detail */
get_header();
?>
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/product-detail.css">
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
                      'color_id' => $color_id, // Lấy key ngoài làm color_id
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
      <!-- Giá, mô tả ... -->
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
          <?php foreach ($colors_data as $color_id => $color): ?>
            <span class="color-dot"
                  data-color="<?php echo esc_attr($color_id); ?>"
                  style="background:<?php echo esc_attr($color['color_name']); ?>"
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
      <!-- Thêm vào giỏ hàng -->
      <form method="post" id="add-to-cart-form" autocomplete="off">
          <input type="hidden" name="product_id" value="<?php echo $product->product_id; ?>">
          <input type="hidden" name="color" id="cart-color">
          <input type="hidden" name="size" id="cart-size">
          <input type="hidden" name="quantity" id="cart-qty">
          <button type="submit" class="btn btn-dark">Thêm vào giỏ</button>
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

<?php get_footer(); ?>