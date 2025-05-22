<?php
/* Template Name: Checkout */
get_header();

if (session_status() == PHP_SESSION_NONE) session_start();
global $wpdb;

$order_success = false;
$order_error = '';

// Lấy cart từ session cho cả GET và POST
$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$subtotal = 0;
$shipping = 40000;
$total = 0;

// Bổ sung thông tin chi tiết cho từng sản phẩm trong cart (dù là GET hay POST)
foreach ($cart_items as &$item) {
    // Lấy thông tin sản phẩm
    $product = $wpdb->get_row($wpdb->prepare("SELECT * FROM products WHERE product_id = %d", $item['product_id']));
    $item['name'] = $product->name;

    // Lấy tên màu
    $color_id = intval($item['color']);
    $color_name = $wpdb->get_var($wpdb->prepare(
        "SELECT color_name FROM product_colors WHERE color_id = %d",
        $color_id
    ));
    $item['color_name'] = $color_name ?: $item['color'];

    // Lấy ảnh đúng theo màu (nếu có)
    $image = $wpdb->get_var($wpdb->prepare(
        "SELECT image_url FROM product_color_images WHERE color_id = %d ORDER BY is_primary DESC, id ASC LIMIT 1",
        $color_id
    ));
    if ($image) {
        $item['image'] = get_template_directory_uri() . '/assets' . $image;
    } else {
        $item['image'] = get_template_directory_uri() . '/assets/images/no-image.jpg';
    }

    $item['price'] = $product->base_price;
    $subtotal += $item['price'] * $item['quantity'] * 2500;
}
unset($item);

$total = $subtotal + $shipping;

// Xử lý đặt hàng khi POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fullname'], $_POST['phone'], $_POST['address'], $_POST['payment_method'])) {
    // Lấy dữ liệu từ form
    $customer_name = trim($_POST['fullname']);
    $customer_phone = trim($_POST['phone']);
    $shipping_address = trim($_POST['address']);
    $customer_note = trim($_POST['note'] ?? '');
    $payment_method = $_POST['payment_method'];
    $customer_email = isset($_POST['email']) ? trim($_POST['email']) : '';

    // Kiểm tra giỏ hàng
    if (empty($cart_items)) {
        $order_error = 'Giỏ hàng trống!';
    } else {
        // Tạo order_id (UUID)
        $order_id = wp_generate_uuid4();

        // Cấu hình VNPAY
        $vnp_TmnCode = 'GXTYW66O';
        $vnp_HashSecret = 'TVI7Z74RUAGTULJYKWBC21OK6ORFJC82';
        $vnp_Url = 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html';
        $vnp_ReturnUrl = site_url('/vnpay-result'); // Trang callback sau thanh toán

        if ($payment_method === 'vnpay') {
            // Lưu thông tin đơn hàng vào session để xử lý sau khi thanh toán thành công
            $_SESSION['pending_order'] = [
                'order_id' => $order_id,
                'customer_name' => $customer_name,
                'customer_phone' => $customer_phone,
                'customer_email' => $customer_email,
                'shipping_address' => $shipping_address,
                'customer_note' => $customer_note,
                'cart_items' => $cart_items,
                'total' => $total
            ];

            // Tạo dữ liệu gửi sang VNPAY
            $vnp_TxnRef = $order_id;
            $vnp_OrderInfo = 'Thanh toan don hang ' . $order_id; // Không dùng ký tự đặc biệt
            $vnp_OrderType = 'billpayment';
            $vnp_Amount = $total * 100; // VNPAY yêu cầu số tiền * 100
            $vnp_Locale = 'vn';
            $vnp_BankCode = '';
            $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
            $vnp_CreateDate = date('YmdHis');

            // Tạo mảng dữ liệu theo đúng thứ tự alphabet
            $inputData = array(
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => $vnp_TmnCode,
                "vnp_Amount" => $vnp_Amount,
                "vnp_Command" => "pay",
                "vnp_CreateDate" => $vnp_CreateDate,
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $vnp_IpAddr,
                "vnp_Locale" => $vnp_Locale,
                "vnp_OrderInfo" => $vnp_OrderInfo,
                "vnp_OrderType" => $vnp_OrderType,
                "vnp_ReturnUrl" => $vnp_ReturnUrl, // <-- Đúng chuẩn VNPAY
                "vnp_TxnRef" => $vnp_TxnRef
            );

            if (!empty($vnp_BankCode)) {
                $inputData['vnp_BankCode'] = $vnp_BankCode;
            }

            // Sắp xếp theo alphabet
            ksort($inputData);
            
            // Tạo hash data và query string
            $hashData = "";
            $query = [];
            $i = 0;
            
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $query[] = urlencode($key) . "=" . urlencode($value);
            }
            
            // Tạo secure hash
            $vnpSecureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
            $vnp_Url_redirect = $vnp_Url . "?" . implode('&', $query) . '&vnp_SecureHash=' . $vnpSecureHash;

            // Debug - có thể xóa khi đã hoạt động
            error_log("VNPAY Hash Data: " . $hashData);
            error_log("VNPAY Secure Hash: " . $vnpSecureHash);
            error_log("VNPAY URL: " . $vnp_Url_redirect);

            // Redirect sang VNPAY
            header('Location: ' . $vnp_Url_redirect);
            exit;
        } else {
            // Xử lý COD - lưu đơn hàng ngay
            $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

            // Lưu vào bảng orders
            $result = $wpdb->insert('orders', [
                'order_id' => $order_id,
                'user_id' => $user_id,
                'customer_email' => $customer_email,
                'customer_phone' => $customer_phone,
                'customer_name' => $customer_name,
                'total_amount' => $total,
                'shipping_address' => $shipping_address,
            ]);
            
            if ($result) {
                // Lưu từng sản phẩm vào order_items
                foreach ($cart_items as $item) {
                    $variant_id = isset($item['variant_id']) ? $item['variant_id'] : null;
                    $unit_price = $item['price'] * 2500;
                    $wpdb->insert('order_items', [
                        'order_id' => $order_id,
                        'variant_id' => $variant_id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $unit_price,
                    ]);
                }
                
                // Lưu thông tin thanh toán
                $wpdb->insert('payments', [
                    'order_id' => $order_id,
                    'payment_method' => $payment_method,
                    'amount' => $total,
                    'status' => 'pending',
                ]);
                
                // Xóa giỏ hàng
                unset($_SESSION['cart']);
                $order_success = true;
            } else {
                $order_error = 'Không thể lưu đơn hàng. Vui lòng thử lại!';
            }
        }
    }
}
?>
<div class="container py-5">
    <h2 class="mb-4 fw-bold text-center">FASCO Demo Checkout</h2>
    <div class="row">
        <div class="col-md-7">
            <form method="post" action="">
                <h4 class="fw-bold mb-3">Thông tin giao hàng</h4>
                <div class="mb-3">
                    <label class="form-label">Họ và tên</label>
                    <input type="text" name="fullname" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Số điện thoại</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email (tùy chọn)</label>
                    <input type="email" name="email" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Địa chỉ</label>
                    <input type="text" name="address" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="note" class="form-control" rows="2"></textarea>
                </div>
                <h4 class="fw-bold mb-3 mt-4">Phương thức thanh toán</h4>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" checked>
                    <label class="form-check-label" for="cod">
                        Thanh toán khi nhận hàng (COD)
                    </label>
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="radio" name="payment_method" id="vnpay" value="vnpay">
                    <label class="form-check-label" for="vnpay">
                        Thanh toán qua VNPAY
                    </label>
                </div>
                <button type="submit" class="btn btn-dark w-100 py-2 fw-bold">Đặt hàng</button>
            </form>
        </div>
        <div class="col-md-5">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Đơn hàng của bạn</h5>
                    <?php foreach ($cart_items as $item): ?>
                        <div class="d-flex mb-3 align-items-center">
                            <img src="<?php echo esc_url($item['image']); ?>" alt="" style="width:60px;height:75px;object-fit:cover;border-radius:6px;margin-right:12px;">
                            <div>
                                <div class="fw-bold"><?php echo esc_html($item['name']); ?></div>
                                <div class="text-muted" style="font-size:14px;">
                                    <?php echo esc_html($item['color_name'] ?? $item['color']); ?> | Size: <?php echo esc_html($item['size']); ?>
                                </div>
                                <div>Số lượng: <?php echo $item['quantity']; ?></div>
                            </div>
                            <div class="ms-auto fw-bold">
                                <?php echo number_format($item['price'] * $item['quantity'] * 2500, 0, ',', '.'); ?>₫
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span>Tạm tính</span>
                        <span><?php echo number_format($subtotal, 0, ',', '.'); ?>₫</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Phí vận chuyển</span>
                        <span><?php echo number_format($shipping, 0, ',', '.'); ?>₫</span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold fs-5 mt-2">
                        <span>Tổng cộng</span>
                        <span><?php echo number_format($total, 0, ',', '.'); ?>₫</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php if ($order_success): ?>
        <div class="alert alert-success">Đặt hàng thành công! Cảm ơn bạn.</div>
    <?php elseif ($order_error): ?>
        <div class="alert alert-danger"><?php echo esc_html($order_error); ?></div>
    <?php endif; ?>
</div>
<?php get_footer(); ?>