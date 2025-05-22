<?php
/* Template Name: VNPAY Result */
get_header();

if (session_status() == PHP_SESSION_NONE) session_start();
global $wpdb;

$vnp_HashSecret = 'TVI7Z74RUAGTULJYKWBC21OK6ORFJC82'; // Cùng secret key

$message = '';
$order_success = false;

if (!empty($_GET)) {
    $vnpData = $_GET;
    $vnp_SecureHash = $vnpData['vnp_SecureHash'];
    unset($vnpData['vnp_SecureHash']);
    
    // Sắp xếp dữ liệu theo alphabet
    ksort($vnpData);
    
    // Tạo hash để xác minh
    $hashData = "";
    $i = 0;
    foreach ($vnpData as $key => $value) {
        if ($i == 1) {
            $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
        } else {
            $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }
    }
    
    $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
    
    // Debug log
    error_log("VNPAY Return Hash Data: " . $hashData);
    error_log("VNPAY Return Calculated Hash: " . $secureHash);
    error_log("VNPAY Return Received Hash: " . $vnp_SecureHash);
    
    if ($secureHash == $vnp_SecureHash) {
        if ($_GET['vnp_ResponseCode'] == '00') {
            // Thanh toán thành công
            $order_id = $_GET['vnp_TxnRef'];
            
            // Lấy thông tin đơn hàng từ session
            if (isset($_SESSION['pending_order']) && $_SESSION['pending_order']['order_id'] == $order_id) {
                $pending_order = $_SESSION['pending_order'];
                
                // Lưu đơn hàng vào database
                $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
                
                $result = $wpdb->insert('orders', [
                    'order_id' => $order_id,
                    'user_id' => $user_id,
                    'customer_email' => $pending_order['customer_email'],
                    'customer_phone' => $pending_order['customer_phone'],
                    'customer_name' => $pending_order['customer_name'],
                    'total_amount' => $pending_order['total'],
                    'shipping_address' => $pending_order['shipping_address'],
                ]);
                
                if ($result) {
                    // Lưu order items
                    foreach ($pending_order['cart_items'] as $item) {
                        $variant_id = isset($item['variant_id']) ? $item['variant_id'] : null;
                        $unit_price = $item['price'] * 2500;
                        $wpdb->insert('order_items', [
                            'order_id' => $order_id,
                            'variant_id' => $variant_id,
                            'quantity' => $item['quantity'],
                            'unit_price' => $unit_price,
                        ]);
                    }
                    
                    // Lưu payment info
                    $wpdb->insert('payments', [
                        'order_id' => $order_id,
                        'payment_method' => 'vnpay',
                        'amount' => $pending_order['total'],
                        'status' => 'completed',
                        'transaction_id' => $_GET['vnp_TransactionNo'] ?? null,
                    ]);
                    
                    // Xóa giỏ hàng và pending order
                    unset($_SESSION['cart']);
                    unset($_SESSION['pending_order']);
                    
                    $order_success = true;
                    $message = 'Thanh toán thành công! Đơn hàng của bạn đã được xác nhận.';
                } else {
                    $message = 'Có lỗi xảy ra khi lưu đơn hàng. Vui lòng liên hệ hỗ trợ.';
                }
            } else {
                $message = 'Không tìm thấy thông tin đơn hàng.';
            }
        } else {
            // Thanh toán thất bại
            $message = 'Thanh toán không thành công. Mã lỗi: ' . $_GET['vnp_ResponseCode'];
        }
    } else {
        $message = 'Chữ ký không hợp lệ.';
    }
} else {
    $message = 'Không có dữ liệu trả về từ VNPAY.';
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <?php if ($order_success): ?>
                        <div class="text-success mb-4">
                            <i class="fas fa-check-circle" style="font-size: 4rem;"></i>
                        </div>
                        <h2 class="text-success mb-3">Thanh toán thành công!</h2>
                        <p class="mb-4"><?php echo esc_html($message); ?></p>
                        <p class="text-muted">Mã đơn hàng: <strong><?php echo esc_html($_GET['vnp_TxnRef'] ?? ''); ?></strong></p>
                        <p class="text-muted">Mã giao dịch: <strong><?php echo esc_html($_GET['vnp_TransactionNo'] ?? ''); ?></strong></p>
                    <?php else: ?>
                        <div class="text-danger mb-4">
                            <i class="fas fa-times-circle" style="font-size: 4rem;"></i>
                        </div>
                        <h2 class="text-danger mb-3">Thanh toán thất bại!</h2>
                        <p class="mb-4"><?php echo esc_html($message); ?></p>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <a href="<?php echo home_url('/'); ?>" class="btn btn-primary me-3">Về trang chủ</a>
                        <?php if (!$order_success): ?>
                            <a href="<?php echo site_url('/checkout'); ?>" class="btn btn-outline-primary">Thử lại</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>