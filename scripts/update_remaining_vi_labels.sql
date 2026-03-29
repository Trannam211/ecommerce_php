SET NAMES utf8mb4;
START TRANSACTION;

ALTER TABLE tbl_color CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE tbl_size CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE tbl_page CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

UPDATE tbl_color SET color_name='Đỏ' WHERE color_id=1;
UPDATE tbl_color SET color_name='Đen' WHERE color_id=2;
UPDATE tbl_color SET color_name='Xanh dương' WHERE color_id=3;
UPDATE tbl_color SET color_name='Vàng' WHERE color_id=4;
UPDATE tbl_color SET color_name='Xanh lá' WHERE color_id=5;
UPDATE tbl_color SET color_name='Trắng' WHERE color_id=6;
UPDATE tbl_color SET color_name='Cam' WHERE color_id=7;
UPDATE tbl_color SET color_name='Nâu' WHERE color_id=8;
UPDATE tbl_color SET color_name='Nâu nhạt' WHERE color_id=9;
UPDATE tbl_color SET color_name='Hồng' WHERE color_id=10;
UPDATE tbl_color SET color_name='Phối màu' WHERE color_id=11;
UPDATE tbl_color SET color_name='Xanh da trời nhạt' WHERE color_id=12;
UPDATE tbl_color SET color_name='Tím' WHERE color_id=13;
UPDATE tbl_color SET color_name='Tím nhạt' WHERE color_id=14;
UPDATE tbl_color SET color_name='Hồng cam' WHERE color_id=15;
UPDATE tbl_color SET color_name='Vàng kim' WHERE color_id=16;
UPDATE tbl_color SET color_name='Xám' WHERE color_id=17;
UPDATE tbl_color SET color_name='Xám tro' WHERE color_id=18;
UPDATE tbl_color SET color_name='Đỏ đô' WHERE color_id=19;
UPDATE tbl_color SET color_name='Bạc' WHERE color_id=20;
UPDATE tbl_color SET color_name='Nâu đất đậm' WHERE color_id=21;
UPDATE tbl_color SET color_name='Nâu vàng' WHERE color_id=22;
UPDATE tbl_color SET color_name='Nâu cà phê' WHERE color_id=23;
UPDATE tbl_color SET color_name='Xám than' WHERE color_id=24;
UPDATE tbl_color SET color_name='Xanh navy' WHERE color_id=25;
UPDATE tbl_color SET color_name='Hồng tím' WHERE color_id=26;
UPDATE tbl_color SET color_name='Xanh olive' WHERE color_id=27;
UPDATE tbl_color SET color_name='Đỏ burgundy' WHERE color_id=28;
UPDATE tbl_color SET color_name='Xanh xanh đen' WHERE color_id=29;

UPDATE tbl_size SET size_name='Kích cỡ tự do' WHERE size_id=26;
UPDATE tbl_size SET size_name='Một cỡ dùng cho tất cả' WHERE size_id=27;
UPDATE tbl_size SET size_name='12 tháng' WHERE size_id=29;
UPDATE tbl_size SET size_name='6 tuổi' WHERE size_id=34;
UPDATE tbl_size SET size_name='7 tuổi' WHERE size_id=35;
UPDATE tbl_size SET size_name='8 tuổi' WHERE size_id=36;
UPDATE tbl_size SET size_name='10 tuổi' WHERE size_id=37;
UPDATE tbl_size SET size_name='12 tuổi' WHERE size_id=38;
UPDATE tbl_size SET size_name='14 tuổi' WHERE size_id=39;

UPDATE tbl_page
SET about_meta_keyword='giới thiệu, cửa hàng, thương mại điện tử',
    about_meta_description='Trang giới thiệu về cửa hàng, định hướng dịch vụ và cam kết chất lượng.',
    faq_meta_keyword='hỏi đáp, câu hỏi thường gặp, hỗ trợ khách hàng',
    faq_meta_description='Giải đáp những câu hỏi thường gặp về mua hàng, giao hàng và đổi trả.',
    blog_meta_keyword='tin tức, xu hướng, kinh nghiệm mua sắm',
    blog_meta_description='Tin tức và bài viết mới nhất về sản phẩm và kinh nghiệm mua sắm.',
    contact_meta_keyword='liên hệ, hỗ trợ, chăm sóc khách hàng',
    contact_meta_description='Thông tin liên hệ và kênh hỗ trợ khách hàng nhanh chóng.',
    pgallery_meta_keyword='thư viện ảnh, hình ảnh sản phẩm',
    pgallery_meta_description='Bộ sưu tập hình ảnh sản phẩm và hoạt động nổi bật của cửa hàng.',
    vgallery_meta_keyword='thư viện video, video sản phẩm',
    vgallery_meta_description='Video giới thiệu sản phẩm và nội dung nổi bật từ cửa hàng.'
WHERE id=1;

COMMIT;
