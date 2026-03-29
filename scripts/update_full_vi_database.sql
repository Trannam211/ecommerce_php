SET NAMES utf8mb4;
START TRANSACTION;

ALTER TABLE tbl_top_category CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE tbl_mid_category CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE tbl_end_category CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE tbl_faq CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE tbl_service CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE tbl_page CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE tbl_product CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

UPDATE tbl_top_category SET tcat_name='Nam' WHERE tcat_id=1;
UPDATE tbl_top_category SET tcat_name='Nữ' WHERE tcat_id=2;
UPDATE tbl_top_category SET tcat_name='Trẻ em' WHERE tcat_id=3;
UPDATE tbl_top_category SET tcat_name='Điện tử' WHERE tcat_id=4;
UPDATE tbl_top_category SET tcat_name='Sức khỏe và gia dụng' WHERE tcat_id=5;

UPDATE tbl_mid_category SET mcat_name='Phụ kiện nam' WHERE mcat_id=1;
UPDATE tbl_mid_category SET mcat_name='Giày nam' WHERE mcat_id=2;
UPDATE tbl_mid_category SET mcat_name='Phụ kiện nữ' WHERE mcat_id=4;
UPDATE tbl_mid_category SET mcat_name='Quần áo nữ' WHERE mcat_id=7;
UPDATE tbl_mid_category SET mcat_name='Quần nam' WHERE mcat_id=8;
UPDATE tbl_mid_category SET mcat_name='Áo thun và áo sơ mi nam' WHERE mcat_id=9;
UPDATE tbl_mid_category SET mcat_name='Quần áo trẻ em' WHERE mcat_id=10;
UPDATE tbl_mid_category SET mcat_name='Thiết bị điện tử' WHERE mcat_id=14;
UPDATE tbl_mid_category SET mcat_name='Máy tính' WHERE mcat_id=15;
UPDATE tbl_mid_category SET mcat_name='Chăm sóc sức khỏe' WHERE mcat_id=16;

UPDATE tbl_end_category SET ecat_name='Đồng hồ nam' WHERE ecat_id=3;
UPDATE tbl_end_category SET ecat_name='Áo hoodie nữ' WHERE ecat_id=14;
UPDATE tbl_end_category SET ecat_name='Áo khoác nữ' WHERE ecat_id=15;
UPDATE tbl_end_category SET ecat_name='Quần jogger nam' WHERE ecat_id=18;
UPDATE tbl_end_category SET ecat_name='Áo thun nam' WHERE ecat_id=20;
UPDATE tbl_end_category SET ecat_name='Áo sơ mi nam thường ngày' WHERE ecat_id=21;
UPDATE tbl_end_category SET ecat_name='Giày nam thường ngày' WHERE ecat_id=25;
UPDATE tbl_end_category SET ecat_name='Bé trai' WHERE ecat_id=26;
UPDATE tbl_end_category SET ecat_name='Đầm nữ' WHERE ecat_id=32;
UPDATE tbl_end_category SET ecat_name='Trang sức nữ' WHERE ecat_id=42;
UPDATE tbl_end_category SET ecat_name='Túi xách nữ' WHERE ecat_id=60;
UPDATE tbl_end_category SET ecat_name='Điện thoại và phụ kiện' WHERE ecat_id=61;
UPDATE tbl_end_category SET ecat_name='Tai nghe' WHERE ecat_id=62;
UPDATE tbl_end_category SET ecat_name='Linh kiện gắn ngoài' WHERE ecat_id=71;
UPDATE tbl_end_category SET ecat_name='Thiết bị và vật tư y tế' WHERE ecat_id=73;

UPDATE tbl_page
SET about_title='Giới thiệu',
    faq_title='Hỏi đáp',
    blog_title='Tin tức',
    contact_title='Liên hệ',
    pgallery_title='Thư viện ảnh',
    vgallery_title='Thư viện video',
    about_meta_title='Giới thiệu',
    faq_meta_title='Hỏi đáp',
    blog_meta_title='Tin tức',
    contact_meta_title='Liên hệ',
    pgallery_meta_title='Thư viện ảnh',
    vgallery_meta_title='Thư viện video'
WHERE id=1;

UPDATE tbl_faq SET
faq_title='Làm sao để tìm sản phẩm phù hợp?',
faq_content='Bạn có thể nhập từ khóa vào ô tìm kiếm, lọc theo danh mục và mức giá để tìm sản phẩm nhanh hơn.'
WHERE faq_id=1;

UPDATE tbl_faq SET
faq_title='Chính sách đổi trả như thế nào?',
faq_content='Chúng tôi hỗ trợ đổi trả trong vòng 15-30 ngày tùy sản phẩm, miễn là sản phẩm còn nguyên trạng và đủ phụ kiện.'
WHERE faq_id=2;

UPDATE tbl_faq SET
faq_title='Nếu nhận hàng lỗi hoặc hư hỏng tôi có được hoàn tiền không?',
faq_content='Có. Nếu sản phẩm lỗi do nhà sản xuất hoặc hư hỏng trong quá trình vận chuyển, bạn sẽ được đổi mới hoặc hoàn tiền theo chính sách.'
WHERE faq_id=3;

UPDATE tbl_faq SET
faq_title='Trường hợp nào không áp dụng đổi trả?',
faq_content='Sản phẩm đã qua sử dụng, thiếu phụ kiện, hư hỏng do người dùng hoặc quá thời hạn đổi trả sẽ không được áp dụng.'
WHERE faq_id=4;

UPDATE tbl_faq SET
faq_title='Những sản phẩm nào không thể đổi trả?',
faq_content='Một số sản phẩm vệ sinh cá nhân, đồ dùng tiêu hao hoặc hàng khuyến mãi đặc biệt có thể không hỗ trợ đổi trả.'
WHERE faq_id=5;

UPDATE tbl_service SET title='Đổi trả dễ dàng', content='Hỗ trợ đổi trả nhanh chóng, thủ tục đơn giản và minh bạch.' WHERE id=5;
UPDATE tbl_service SET title='Miễn phí vận chuyển', content='Áp dụng miễn phí vận chuyển cho đơn hàng đủ điều kiện theo chính sách cửa hàng.' WHERE id=6;
UPDATE tbl_service SET title='Giao hàng nhanh', content='Đơn hàng được xử lý nhanh, giao đúng hẹn và theo dõi thuận tiện.' WHERE id=7;
UPDATE tbl_service SET title='Cam kết hài lòng', content='Chúng tôi luôn ưu tiên trải nghiệm mua sắm và sự hài lòng của khách hàng.' WHERE id=8;
UPDATE tbl_service SET title='Thanh toán an toàn', content='Nhiều hình thức thanh toán bảo mật giúp bạn yên tâm khi mua sắm.' WHERE id=9;
UPDATE tbl_service SET title='Cam kết hoàn tiền', content='Hỗ trợ hoàn tiền theo chính sách khi đơn hàng đủ điều kiện.' WHERE id=10;

UPDATE tbl_product SET p_name='Áo thun cotton nam cao cấp (bộ nhiều chiếc)' WHERE p_id=83;
UPDATE tbl_product SET p_name='Đầm maxi len gân lệch vai dáng rộng' WHERE p_id=84;
UPDATE tbl_product SET p_name='Giày sneaker nam cổ điển êm chân' WHERE p_id=85;
UPDATE tbl_product SET p_name='Đồng hồ thông minh Amazfit GTS 3 cho Android và iPhone' WHERE p_id=86;
UPDATE tbl_product SET p_name='Bộ đồ ngủ bé trai họa tiết xe tải' WHERE p_id=87;
UPDATE tbl_product SET p_name='Áo thun thể thao nam Under Armour ngắn tay' WHERE p_id=88;
UPDATE tbl_product SET p_name='Quần jogger nỉ nam' WHERE p_id=89;
UPDATE tbl_product SET p_name='Áo hoodie nữ mỏng khóa kéo' WHERE p_id=90;
UPDATE tbl_product SET p_name='Áo hoodie nỉ nữ form rộng' WHERE p_id=91;
UPDATE tbl_product SET p_name='Túi tote du lịch xách tay đựng laptop Travelpro' WHERE p_id=92;
UPDATE tbl_product SET p_name='Khuyên tai tròn bản lớn mạ vàng họa tiết da báo' WHERE p_id=93;
UPDATE tbl_product SET p_name='Ổ cứng di động WD Elements 5TB' WHERE p_id=94;
UPDATE tbl_product SET p_name='Tai nghe không dây Bose QuietComfort 45' WHERE p_id=95;
UPDATE tbl_product SET p_name='Áo thun tay dài nam dáng rộng có túi ngực' WHERE p_id=96;
UPDATE tbl_product SET p_name='Đầm nữ dài qua gối đính hoa' WHERE p_id=97;
UPDATE tbl_product SET p_name='Áo khoác cardigan nỉ lông nữ cổ ve' WHERE p_id=98;
UPDATE tbl_product SET p_name='Kính thực tế ảo Oculus Quest 2' WHERE p_id=99;
UPDATE tbl_product SET p_name='Quần jogger yoga nam bo gấu dài' WHERE p_id=100;
UPDATE tbl_product SET p_name='Nhiệt kế hồng ngoại điện tử cho người lớn và trẻ em' WHERE p_id=101;
UPDATE tbl_product SET p_name='Đầm sơ mi nữ big size phụ kiện ánh kim' WHERE p_id=102;

UPDATE tbl_product
SET p_short_description = CONCAT('Sản phẩm ', p_name, ' chính hãng, thiết kế hiện đại, phù hợp nhu cầu sử dụng hằng ngày.'),
    p_description = CONCAT('<p>', p_name, ' là lựa chọn phù hợp cho nhu cầu sử dụng hằng ngày, chú trọng chất lượng và độ bền khi sử dụng.</p><p>Vui lòng xem thông số kỹ thuật, kích thước và tùy chọn trước khi đặt hàng để chọn đúng phiên bản phù hợp.</p>'),
    p_feature = '<ul><li>Hàng chính hãng, nguồn gốc rõ ràng.</li><li>Thiết kế tối ưu cho trải nghiệm sử dụng.</li><li>Dễ phối hợp và phù hợp nhiều nhu cầu.</li></ul>',
    p_condition = '<p>Sản phẩm mới 100%, nguyên hộp, đầy đủ phụ kiện theo tiêu chuẩn nhà sản xuất.</p>',
    p_return_policy = '<p>Hỗ trợ đổi trả trong vòng 15-30 ngày tùy từng sản phẩm. Điều kiện áp dụng: sản phẩm còn nguyên trạng, đầy đủ phụ kiện và hóa đơn.</p>';

COMMIT;
