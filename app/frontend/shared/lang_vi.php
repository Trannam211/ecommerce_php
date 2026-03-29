<?php

if (!function_exists('vn_load_frontend_language_constants')) {
    function vn_load_frontend_language_constants()
    {
        $lang = array(
            4 => 'Gửi',
            9 => 'Đăng nhập',
            11 => 'Bấm vào đây để đăng nhập',
            12 => 'Quay lại trang đăng nhập',
            18 => 'Giỏ hàng',
            23 => 'Tiến hành thanh toán',
            49 => 'Danh mục',
            63 => 'Đánh giá',
            64 => 'Đánh giá',
            65 => 'Viết đánh giá',
            67 => 'Gửi đánh giá',
            68 => 'Bạn đã đánh giá sản phẩm này rồi!',
            69 => 'Bạn cần đăng nhập để đánh giá sản phẩm',
            74 => 'Chưa có đánh giá',
            75 => 'Tên khách hàng',
            76 => 'Bình luận',
            78 => 'Điểm đánh giá',
            94 => 'Địa chỉ email',
            97 => 'Quên mật khẩu',
            100 => 'Mật khẩu mới',
            101 => 'Nhập lại mật khẩu mới',
            131 => 'Email không được để trống',
            134 => 'Địa chỉ email không hợp lệ',
            135 => 'Email không tồn tại trong hệ thống',
            139 => 'Mật khẩu nhập lại không khớp',
            140 => 'Vui lòng nhập mật khẩu mới và xác nhận mật khẩu',
            142 => 'Để đặt lại mật khẩu, vui lòng bấm vào liên kết bên dưới.',
            143 => 'YÊU CẦU ĐẶT LẠI MẬT KHẨU - WIZARD ECOMMERCE',
            144 => 'Liên kết đặt lại mật khẩu đã hết hạn (24 giờ). Vui lòng thử lại.',
            146 => 'Đặt lại mật khẩu thành công. Bây giờ bạn có thể đăng nhập.',
            149 => 'Đổi mật khẩu',
            153 => 'Không tìm thấy sản phẩm',
            160 => 'Vui lòng đăng nhập tài khoản khách hàng để thanh toán',
            163 => 'Gửi đánh giá thành công!'
        );

        foreach ($lang as $id => $value) {
            $key = 'LANG_VALUE_' . $id;
            if (!defined($key)) {
                define($key, $value);
            }
        }
    }
}

if (!function_exists('vn_frontend_translate')) {
    function vn_frontend_translate($html)
    {
        $map = array(
            '>Search Product<' => '>Tìm sản phẩm<',
            '>Search<' => '>Tìm kiếm<',
            '>Read More<' => '>Xem thêm<',
            '>Categories<' => '>Danh mục<',
            '>Category:<' => '>Danh mục:<',
            '>All Products Under<' => '>Tất cả sản phẩm thuộc<',
            '>Select Size<' => '>Chọn kích cỡ<',
            '>Select Color<' => '>Chọn màu sắc<',
            '>Product Price<' => '>Giá sản phẩm<',
            '>Quantity<' => '>Số lượng<',
            '>Out of Stock<' => '>Hết hàng<',
            '>Share This<' => '>Chia sẻ<',
            '>Share This Product<' => '>Chia sẻ sản phẩm này<',
            '>Product Description<' => '>Mô tả sản phẩm<',
            '>Features<' => '>Tính năng<',
            '>Conditions<' => '>Điều kiện<',
            '>Return Policy<' => '>Chính sách đổi trả<',
            '>Reviews<' => '>Đánh giá<',
            '>Review<' => '>Đánh giá<',
            '>Give a Review<' => '>Viết đánh giá<',
            '>Submit Review<' => '>Gửi đánh giá<',
            '>Comment<' => '>Bình luận<',
            '>Rating<' => '>Điểm đánh giá<',
            '>Customer Name<' => '>Tên khách hàng<',
            '>Previous<' => '>Trước<',
            '>Next<' => '>Tiếp theo<',
            '>Sub Total<' => '>Tạm tính<',
            '>Total<' => '>Tổng tiền<',
            '>Action<' => '>Thao tác<',
            '>Shipping Cost<' => '>Phí vận chuyển<',
            '>Continue Shopping<' => '>Tiếp tục mua sắm<',
            '>Update Billing Address<' => '>Cập nhật địa chỉ thanh toán<',
            '>Update Shipping Address<' => '>Cập nhật địa chỉ giao hàng<',
            '>Update Billing and Shipping Info<' => '>Cập nhật thông tin thanh toán và giao hàng<',
            '>Dashboard<' => '>Bảng điều khiển<',
            '>Welcome to the Dashboard<' => '>Chào mừng đến với bảng điều khiển<',
            '>Back to Dashboard<' => '>Quay lại bảng điều khiển<',
            '>Subscribe<' => '>Đăng ký nhận tin<',
            '>Subscribe To Our Newsletter<' => '>Đăng ký nhận bản tin của chúng tôi<',
            '>Email Address<' => '>Địa chỉ email<',
            '>Enter Your Email Address<' => '>Nhập địa chỉ email của bạn<',
            '>Password<' => '>Mật khẩu<',
            '>Forget Password<' => '>Quên mật khẩu<',
            '>Retype Password<' => '>Nhập lại mật khẩu<',
            '>Update Password<' => '>Cập nhật mật khẩu<',
            '>New Password<' => '>Mật khẩu mới<',
            '>Retype New Password<' => '>Nhập lại mật khẩu mới<',
            '>Full Name<' => '>Họ và tên<',
            '>Company Name<' => '>Tên công ty<',
            '>Phone Number<' => '>Số điện thoại<',
            '>Address<' => '>Địa chỉ<',
            '>Country<' => '>Quốc gia<',
            '>City<' => '>Thành phố<',
            '>State<' => '>Tỉnh/Thành<',
            '>Zip Code<' => '>Mã bưu chính<',
            '>About Us<' => '>Về chúng tôi<',
            '>Featured Posts<' => '>Bài viết nổi bật<',
            '>Popular Posts<' => '>Bài viết phổ biến<',
            '>Recent Posts<' => '>Bài viết mới<',
            '>Contact Information<' => '>Thông tin liên hệ<',
            '>Contact Form<' => '>Biểu mẫu liên hệ<',
            '>Our Office<' => '>Văn phòng của chúng tôi<',
            '>Update Profile<' => '>Cập nhật hồ sơ<',
            '>Send Message<' => '>Gửi tin nhắn<',
            '>Message<' => '>Tin nhắn<',
            '>Find Us On Map<' => '>Tìm chúng tôi trên bản đồ<',
            '>Products<' => '>Sản phẩm<',
            '>Product<' => '>Sản phẩm<',
            '>Add to Cart<' => '>Thêm vào giỏ hàng<',
            '>Related Products<' => '>Sản phẩm liên quan<',
            '>Size<' => '>Kích cỡ<',
            '>Color<' => '>Màu sắc<',
            '>Price<' => '>Giá<',
            '>No Product Found<' => '>Không tìm thấy sản phẩm<',
            '>Checkout<' => '>Thanh toán<',
            '>Cart<' => '>Giỏ hàng<',
            '>Proceed to Checkout<' => '>Tiến hành thanh toán<',
            '>Login<' => '>Đăng nhập<',
            '>Logout<' => '>Đăng xuất<',
            '>Register<' => '>Đăng ký<',
            '>Submit<' => '>Gửi<',
            '>Update<' => '>Cập nhật<',
            '>Delete<' => '>Xóa<',
            '>Edit<' => '>Sửa<'
        );

        return str_replace(array_keys($map), array_values($map), $html);
    }
}
