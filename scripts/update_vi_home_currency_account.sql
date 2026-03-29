SET NAMES utf8mb4;
START TRANSACTION;

UPDATE tbl_settings
SET featured_product_title = 'Sản phẩm nổi bật',
    featured_product_subtitle = 'Danh sách sản phẩm nổi bật được chọn lọc',
    latest_product_title = 'Sản phẩm mới nhất',
    latest_product_subtitle = 'Những sản phẩm mới cập nhật gần đây',
    popular_product_title = 'Sản phẩm phổ biến',
    popular_product_subtitle = 'Sản phẩm được khách hàng quan tâm nhiều'
WHERE id = 1;

UPDATE tbl_page
SET about_title = 'Giới thiệu',
    about_content = '<p>Chào mừng bạn đến với cửa hàng thương mại điện tử của chúng tôi!</p><p>Chúng tôi luôn nỗ lực mang đến đa dạng sản phẩm chất lượng, mức giá hợp lý và trải nghiệm mua sắm thuận tiện cho mọi khách hàng.</p><p>Đội ngũ của chúng tôi theo dõi liên tục xu hướng thị trường để cập nhật những sản phẩm phù hợp nhất, đáp ứng đúng nhu cầu thực tế của bạn.</p><ul><li>Giá cạnh tranh và minh bạch</li><li>Hỗ trợ khách hàng tận tâm 24/7</li><li>Giao hàng nhanh trên toàn quốc</li><li>Chính sách đổi trả rõ ràng, dễ thực hiện</li></ul><p>Mục tiêu của chúng tôi là giúp bạn mua sắm dễ dàng, tiết kiệm thời gian và luôn an tâm về chất lượng sản phẩm.</p><p>Cảm ơn bạn đã đồng hành cùng cửa hàng. Chúng tôi luôn sẵn sàng hỗ trợ bạn trong suốt quá trình mua sắm.</p>'
WHERE id = 1;

UPDATE tbl_language SET lang_value = '₫' WHERE lang_id = 1;
UPDATE tbl_language SET lang_value = 'Đăng nhập' WHERE lang_id = 9;
UPDATE tbl_language SET lang_value = 'Đăng nhập khách hàng' WHERE lang_id = 10;
UPDATE tbl_language SET lang_value = 'Bấm vào đây để đăng nhập' WHERE lang_id = 11;
UPDATE tbl_language SET lang_value = 'Quay lại trang đăng nhập' WHERE lang_id = 12;
UPDATE tbl_language SET lang_value = 'Thêm vào giỏ' WHERE lang_id = 154;
UPDATE tbl_language SET lang_value = 'Sản phẩm liên quan' WHERE lang_id = 155;
UPDATE tbl_language SET lang_value = 'Xem thêm các sản phẩm liên quan bên dưới' WHERE lang_id = 156;
UPDATE tbl_language SET lang_value = 'Kích thước' WHERE lang_id = 157;
UPDATE tbl_language SET lang_value = 'Màu sắc' WHERE lang_id = 158;
UPDATE tbl_language SET lang_value = 'Giá' WHERE lang_id = 159;
UPDATE tbl_language SET lang_value = 'Vui lòng đăng nhập tài khoản khách hàng để thanh toán' WHERE lang_id = 160;
UPDATE tbl_language SET lang_value = 'Địa chỉ thanh toán' WHERE lang_id = 161;
UPDATE tbl_language SET lang_value = 'Địa chỉ giao hàng' WHERE lang_id = 162;
UPDATE tbl_language SET lang_value = 'Gửi đánh giá thành công!' WHERE lang_id = 163;

COMMIT;
