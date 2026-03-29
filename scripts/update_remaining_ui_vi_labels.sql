SET NAMES utf8mb4;
START TRANSACTION;

UPDATE tbl_language SET lang_value = '₫' WHERE lang_name = 'Currency';
UPDATE tbl_language SET lang_value = 'Đăng nhập' WHERE lang_name = 'Login';
UPDATE tbl_language SET lang_value = 'Đăng xuất' WHERE lang_name = 'Logout';
UPDATE tbl_language SET lang_value = 'Đăng ký' WHERE lang_name = 'Register';
UPDATE tbl_language SET lang_value = 'Giỏ hàng' WHERE lang_name = 'Cart';
UPDATE tbl_language SET lang_value = 'Xem giỏ hàng' WHERE lang_name = 'View Cart';
UPDATE tbl_language SET lang_value = 'Cập nhật giỏ hàng' WHERE lang_name = 'Update Cart';
UPDATE tbl_language SET lang_value = 'Quay lại giỏ hàng' WHERE lang_name = 'Back to Cart';
UPDATE tbl_language SET lang_value = 'Thanh toán' WHERE lang_name = 'Checkout';
UPDATE tbl_language SET lang_value = 'Tiến hành thanh toán' WHERE lang_name = 'Proceed to Checkout';
UPDATE tbl_language SET lang_value = 'Thêm vào giỏ' WHERE lang_name = 'Add to Cart';
UPDATE tbl_language SET lang_value = 'Bạn phải đăng nhập để đánh giá sản phẩm' WHERE lang_name = 'You must have to login to give a review';
UPDATE tbl_language SET lang_value = 'Bảng điều khiển' WHERE lang_name = 'Dashboard';
UPDATE tbl_language SET lang_value = 'Chào mừng đến với Bảng điều khiển' WHERE lang_name = 'Welcome to the Dashboard';
UPDATE tbl_language SET lang_value = 'Quay lại Bảng điều khiển' WHERE lang_name = 'Back to Dashboard';
UPDATE tbl_language SET lang_value = 'Email của bạn đã được xác minh thành công. Bây giờ bạn có thể đăng nhập vào website.' WHERE lang_name = 'Your email is verified successfully. You can now login to our website.';
UPDATE tbl_language SET lang_value = 'Đặt lại mật khẩu thành công. Bây giờ bạn có thể đăng nhập.' WHERE lang_name = 'Password is reset successfully. You can now login.';
UPDATE tbl_language SET lang_value = 'Xin lỗi! Tài khoản của bạn đang bị khóa. Vui lòng liên hệ quản trị viên.' WHERE lang_name = 'Sorry! Your account is inactive. Please contact to the administrator.';
UPDATE tbl_language SET lang_value = 'Cảm ơn bạn đã đăng ký! Tài khoản đã được tạo. Để kích hoạt tài khoản, vui lòng bấm vào liên kết bên dưới:' WHERE lang_name = 'Thank you for your registration! Your account has been created. To active your account click on the link below:';
UPDATE tbl_language SET lang_value = 'Vui lòng đăng nhập tài khoản khách hàng để thanh toán' WHERE lang_name = 'Please login as customer to checkout';

COMMIT;
