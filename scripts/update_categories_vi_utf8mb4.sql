SET NAMES utf8mb4;
START TRANSACTION;

ALTER TABLE tbl_top_category CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE tbl_mid_category CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE tbl_end_category CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

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

COMMIT;
