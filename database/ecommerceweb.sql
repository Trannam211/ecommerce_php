-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th4 09, 2026 lúc 09:40 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `ecommerceweb`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_color`
--

CREATE TABLE `tbl_color` (
  `color_id` int(11) NOT NULL,
  `color_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_color`
--

INSERT INTO `tbl_color` (`color_id`, `color_name`) VALUES
(1, 'Đỏ'),
(2, 'Đen'),
(3, 'Xanh dương'),
(4, 'Vàng'),
(5, 'Xanh lá'),
(6, 'Trắng'),
(7, 'Cam'),
(8, 'Nâu'),
(9, 'Nâu nhạt'),
(10, 'Hồng'),
(11, 'Phối màu'),
(12, 'Xanh da trời nhạt'),
(13, 'Tím'),
(14, 'Tím nhạt'),
(15, 'Hồng cam'),
(16, 'Vàng kim'),
(17, 'Xám'),
(18, 'Xám tro'),
(19, 'Đỏ đô'),
(20, 'Bạc'),
(21, 'Nâu đất đậm'),
(22, 'Nâu vàng'),
(23, 'Nâu cà phê'),
(24, 'Xám than'),
(25, 'Xanh navy'),
(26, 'Hồng tím'),
(27, 'Xanh olive'),
(28, 'Đỏ burgundy'),
(29, 'Xanh đen'),
(30, 'Kem');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_customer`
--

CREATE TABLE `tbl_customer` (
  `cust_id` int(11) NOT NULL,
  `cust_name` varchar(100) NOT NULL,
  `cust_email` varchar(100) NOT NULL,
  `cust_phone` varchar(50) NOT NULL,
  `cust_gender` varchar(20) NOT NULL DEFAULT '',
  `cust_dob` date DEFAULT NULL,
  `cust_photo` varchar(255) NOT NULL DEFAULT '',
  `cust_password` varchar(255) NOT NULL,
  `cust_token` varchar(255) NOT NULL,
  `cust_datetime` varchar(100) NOT NULL,
  `cust_timestamp` varchar(100) NOT NULL,
  `cust_status` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_customer`
--

INSERT INTO `tbl_customer` (`cust_id`, `cust_name`, `cust_email`, `cust_phone`, `cust_gender`, `cust_dob`, `cust_photo`, `cust_password`, `cust_token`, `cust_datetime`, `cust_timestamp`, `cust_status`) VALUES
(12, 'Trần Văn A', 'tranvana@gmail.com', '0123456789', '', NULL, 'customer-12-1774777597.jpg', '$2y$10$bzBF9vMkm5SxlOkDGVUhG.KZXSGzbC9m713qP50vZSFUxpy.CSxp6', '', '2026-03-29 12:06:47', '1774768007', 1),
(13, 'Trần Văn B', 'tranvanb@gmail.com', '0123456798', '', NULL, '', 'd433670ad4c7bf77e7f6692a9fda3f9a', '', '2026-03-29 02:48:02', '1774777682', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_customer_address`
--

CREATE TABLE `tbl_customer_address` (
  `address_id` int(10) UNSIGNED NOT NULL,
  `cust_id` int(11) NOT NULL,
  `receiver_name` varchar(255) NOT NULL,
  `receiver_phone` varchar(50) NOT NULL,
  `address_line` varchar(500) NOT NULL,
  `city` varchar(191) NOT NULL,
  `district` varchar(191) NOT NULL,
  `ward` varchar(191) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_customer_address`
--

INSERT INTO `tbl_customer_address` (`address_id`, `cust_id`, `receiver_name`, `receiver_phone`, `address_line`, `city`, `district`, `ward`, `is_default`, `created_at`, `updated_at`) VALUES
(2, 12, 'Trần Văn A', '0123456789', '18 Đường 31', 'Thành phố Hồ Chí Minh', 'Quận 8', 'Phường 5', 1, '2026-03-29 16:26:40', '2026-03-29 16:26:40'),
(4, 13, 'Trần Văn B', '0123456798', '18 đường 30', 'Thành phố Hồ Chí Minh', 'Quận 8', 'Phường 5', 1, '2026-03-29 16:48:02', '2026-03-29 16:48:02');

--
-- Bẫy `tbl_customer_address`
--
DELIMITER $$
CREATE TRIGGER `trg_customer_address_single_default_insert` BEFORE INSERT ON `tbl_customer_address` FOR EACH ROW BEGIN
    DECLARE default_count INT DEFAULT 0;

    IF NEW.is_default = 1 THEN
        SELECT COUNT(*) INTO default_count
        FROM tbl_customer_address
        WHERE cust_id = NEW.cust_id
          AND is_default = 1;

        IF default_count > 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Mỗi khách hàng chỉ được có một địa chỉ mặc định.';
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_customer_address_single_default_update` BEFORE UPDATE ON `tbl_customer_address` FOR EACH ROW BEGIN
    DECLARE default_count INT DEFAULT 0;

    IF NEW.is_default = 1 THEN
        SELECT COUNT(*) INTO default_count
        FROM tbl_customer_address
        WHERE cust_id = NEW.cust_id
          AND is_default = 1
          AND address_id <> NEW.address_id;

        IF default_count > 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Mỗi khách hàng chỉ được có một địa chỉ mặc định.';
        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_end_category`
--

CREATE TABLE `tbl_end_category` (
  `ecat_id` int(11) NOT NULL,
  `ecat_name` varchar(255) NOT NULL,
  `mcat_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_end_category`
--

INSERT INTO `tbl_end_category` (`ecat_id`, `ecat_name`, `mcat_id`) VALUES
(3, 'Đồng hồ nam', 1),
(14, 'Áo hoodie nữ', 7),
(15, 'Áo khoác nữ', 7),
(18, 'Quần jogger nam', 8),
(20, 'Áo thun nam', 9),
(21, 'Áo sơ mi nam thường ngày', 9),
(25, 'Giày nam thường ngày', 2),
(26, 'Bé trai', 10),
(32, 'Đầm nữ', 7),
(42, 'Trang sức nữ', 4),
(60, 'Túi xách nữ', 4),
(80, 'Áo gió nam', 18),
(81, 'Giày nữ', 4);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_import_receipt`
--

CREATE TABLE `tbl_import_receipt` (
  `receipt_id` int(11) NOT NULL,
  `receipt_code` varchar(50) NOT NULL,
  `import_date` datetime NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Draft',
  `note` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_import_receipt`
--

INSERT INTO `tbl_import_receipt` (`receipt_id`, `receipt_code`, `import_date`, `status`, `note`, `created_by`, `created_at`, `updated_at`, `completed_at`) VALUES
(1, 'PN-20260406-021913-58', '2026-04-06 10:15:00', 'Completed', 'FLOW-TEST-RECEIPT', 3, '2026-04-06 02:19:13', '2026-04-06 02:19:38', '2026-04-06 02:19:38'),
(5, 'PN-20260407-001249-92', '2026-04-06 21:34:00', 'Completed', '', 2, '2026-04-07 00:12:49', '2026-04-07 00:15:27', '2026-04-07 00:15:27'),
(6, 'PN-20260407-001610-77', '2026-04-07 00:16:00', 'Completed', '', 2, '2026-04-07 00:16:10', '2026-04-07 00:16:52', '2026-04-07 00:16:52');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_import_receipt_item`
--

CREATE TABLE `tbl_import_receipt_item` (
  `item_id` int(11) NOT NULL,
  `receipt_id` int(11) NOT NULL,
  `p_id` int(11) NOT NULL,
  `size_id` int(11) NOT NULL DEFAULT 0,
  `color_id` int(11) NOT NULL DEFAULT 0,
  `import_qty` int(11) NOT NULL DEFAULT 0,
  `import_price` decimal(15,4) NOT NULL DEFAULT 0.0000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_import_receipt_item`
--

INSERT INTO `tbl_import_receipt_item` (`item_id`, `receipt_id`, `p_id`, `size_id`, `color_id`, `import_qty`, `import_price`) VALUES
(1, 1, 114, 0, 0, 2, 150000.0000),
(2, 5, 114, 0, 0, 10, 100000.0000),
(3, 6, 114, 0, 0, 8, 100000.0000);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_mid_category`
--

CREATE TABLE `tbl_mid_category` (
  `mcat_id` int(11) NOT NULL,
  `mcat_name` varchar(255) NOT NULL,
  `tcat_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_mid_category`
--

INSERT INTO `tbl_mid_category` (`mcat_id`, `mcat_name`, `tcat_id`) VALUES
(1, 'Phụ kiện nam', 1),
(2, 'Giày nam', 1),
(4, 'Phụ kiện nữ', 2),
(7, 'Quần áo nữ', 2),
(8, 'Quần nam', 1),
(9, 'Áo thun và áo sơ mi nam', 1),
(10, 'Quần áo trẻ em', 3),
(18, 'Áo khoác nam', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_order`
--

CREATE TABLE `tbl_order` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `size` varchar(100) NOT NULL,
  `color` varchar(100) NOT NULL,
  `quantity` varchar(50) NOT NULL,
  `unit_price` varchar(50) NOT NULL,
  `payment_id` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_order`
--

INSERT INTO `tbl_order` (`id`, `product_id`, `product_name`, `size`, `color`, `quantity`, `unit_price`, `payment_id`) VALUES
(11, 114, 'Áo Thun Nam LADOS ', 'XS', 'Đỏ', '1', '120000', '1775022936'),
(13, 114, 'Áo Thun Nam LADOS ', 'M', 'Đỏ', '1', '120000', '1775467072');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_page`
--

CREATE TABLE `tbl_page` (
  `id` int(11) NOT NULL,
  `about_title` varchar(255) NOT NULL,
  `about_content` mediumtext NOT NULL,
  `about_banner` varchar(255) NOT NULL,
  `about_meta_title` varchar(255) NOT NULL,
  `about_meta_keyword` mediumtext NOT NULL,
  `about_meta_description` mediumtext NOT NULL,
  `faq_title` varchar(255) NOT NULL,
  `faq_banner` varchar(255) NOT NULL,
  `faq_meta_title` varchar(255) NOT NULL,
  `faq_meta_keyword` mediumtext NOT NULL,
  `faq_meta_description` mediumtext NOT NULL,
  `blog_title` varchar(255) NOT NULL,
  `blog_banner` varchar(255) NOT NULL,
  `blog_meta_title` varchar(255) NOT NULL,
  `blog_meta_keyword` mediumtext NOT NULL,
  `blog_meta_description` mediumtext NOT NULL,
  `contact_title` varchar(255) NOT NULL,
  `contact_banner` varchar(255) NOT NULL,
  `contact_meta_title` varchar(255) NOT NULL,
  `contact_meta_keyword` mediumtext NOT NULL,
  `contact_meta_description` mediumtext NOT NULL,
  `pgallery_title` varchar(255) NOT NULL,
  `pgallery_banner` varchar(255) NOT NULL,
  `pgallery_meta_title` varchar(255) NOT NULL,
  `pgallery_meta_keyword` mediumtext NOT NULL,
  `pgallery_meta_description` mediumtext NOT NULL,
  `vgallery_title` varchar(255) NOT NULL,
  `vgallery_banner` varchar(255) NOT NULL,
  `vgallery_meta_title` varchar(255) NOT NULL,
  `vgallery_meta_keyword` mediumtext NOT NULL,
  `vgallery_meta_description` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_page`
--

INSERT INTO `tbl_page` (`id`, `about_title`, `about_content`, `about_banner`, `about_meta_title`, `about_meta_keyword`, `about_meta_description`, `faq_title`, `faq_banner`, `faq_meta_title`, `faq_meta_keyword`, `faq_meta_description`, `blog_title`, `blog_banner`, `blog_meta_title`, `blog_meta_keyword`, `blog_meta_description`, `contact_title`, `contact_banner`, `contact_meta_title`, `contact_meta_keyword`, `contact_meta_description`, `pgallery_title`, `pgallery_banner`, `pgallery_meta_title`, `pgallery_meta_keyword`, `pgallery_meta_description`, `vgallery_title`, `vgallery_banner`, `vgallery_meta_title`, `vgallery_meta_keyword`, `vgallery_meta_description`) VALUES
(1, 'Giới thiệu', 'Chào mừng bạn đến với Wizard Ecommerce – nền tảng mua sắm trực tuyến hiện đại, tiện lợi và đáng tin cậy.<br><br>\nChúng tôi cam kết mang đến cho khách hàng đa dạng sản phẩm chất lượng, giá cả cạnh tranh cùng trải nghiệm mua sắm nhanh chóng và dễ dàng. Mỗi sản phẩm đều được lựa chọn kỹ lưỡng nhằm đáp ứng nhu cầu thực tế và xu hướng tiêu dùng hiện nay.<br><br>\nVới định hướng lấy khách hàng làm trung tâm, Wizard Ecommerce không ngừng cải thiện dịch vụ để mang lại sự hài lòng tối đa trong suốt quá trình mua sắm.<br><br>\nChúng tôi cung cấp:<br>\n<ul>\n<li>Sản phẩm đa dạng, cập nhật liên tục</li>\n<li>Giá cả minh bạch, hợp lý</li>\n<li>Dịch vụ hỗ trợ khách hàng tận tâm</li>\n<li>Giao hàng nhanh chóng trên toàn quốc</li>\n<li>Chính sách đổi trả rõ ràng, thuận tiện</li>\n</ul>\nMục tiêu của chúng tôi là xây dựng một nền tảng mua sắm trực tuyến đáng tin cậy, giúp khách hàng tiết kiệm thời gian và yên tâm khi lựa chọn sản phẩm.<br><br>\nCảm ơn bạn đã tin tưởng và đồng hành cùng Wizard Ecommerce.', 'about-banner.jpg', 'Giới thiệu', 'giới thiệu, cửa hàng, thương mại điện tử', 'Trang giới thiệu về cửa hàng, định hướng dịch vụ và cam kết chất lượng.', 'Hỏi đáp', 'faq-banner.jpg', 'Hỏi đáp', 'hỏi đáp, câu hỏi thường gặp, hỗ trợ khách hàng', 'Giải đáp những câu hỏi thường gặp về mua hàng, giao hàng và đổi trả.', 'Tin tức', 'blog-banner.jpg', 'Tin tức', 'tin tức, xu hướng, kinh nghiệm mua sắm', 'Tin tức và bài viết mới nhất về sản phẩm và kinh nghiệm mua sắm.', 'Liên hệ', 'contact-banner.jpg', 'Liên hệ', 'liên hệ, hỗ trợ, chăm sóc khách hàng', 'Thông tin liên hệ và kênh hỗ trợ khách hàng nhanh chóng.', 'Thư viện ảnh', 'pgallery-banner.jpg', 'Thư viện ảnh', 'thư viện ảnh, hình ảnh sản phẩm', 'Bộ sưu tập hình ảnh sản phẩm và hoạt động nổi bật của cửa hàng.', 'Thư viện video', 'vgallery-banner.jpg', 'Thư viện video', 'thư viện video, video sản phẩm', 'Video giới thiệu sản phẩm và nội dung nổi bật từ cửa hàng.');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_payment`
--

CREATE TABLE `tbl_payment` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `payment_date` varchar(50) NOT NULL,
  `txnid` varchar(255) NOT NULL,
  `paid_amount` int(11) NOT NULL,
  `card_number` varchar(50) NOT NULL,
  `card_cvv` varchar(10) NOT NULL,
  `card_month` varchar(10) NOT NULL,
  `card_year` varchar(10) NOT NULL,
  `bank_transaction_info` mediumtext NOT NULL,
  `payment_method` varchar(20) NOT NULL,
  `payment_status` varchar(25) NOT NULL,
  `shipping_status` varchar(20) NOT NULL,
  `payment_id` varchar(255) NOT NULL,
  `order_total_amount` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_payment`
--

INSERT INTO `tbl_payment` (`id`, `customer_id`, `customer_name`, `customer_email`, `payment_date`, `txnid`, `paid_amount`, `card_number`, `card_cvv`, `card_month`, `card_year`, `bank_transaction_info`, `payment_method`, `payment_status`, `shipping_status`, `payment_id`, `order_total_amount`) VALUES
(61, 12, 'Trần Văn A', 'tranvana@gmail.com', '2026-03-31 22:55:36', '', 120100, '', '', '', '', '', 'Cash On Delivery', 'Completed', 'Completed', '1775022936', 120100),
(63, 14, 'Khach Test Flow', 'flowtest.customer@example.com', '2026-04-06 02:17:52', '', 120000, '', '', '', '', 'TEST-REF-001', 'Bank Deposit', 'Completed', 'Completed', '1775467072', 120000);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_photo`
--

CREATE TABLE `tbl_photo` (
  `id` int(11) NOT NULL,
  `caption` varchar(255) NOT NULL,
  `photo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_photo`
--

INSERT INTO `tbl_photo` (`id`, `caption`, `photo`) VALUES
(1, 'Photo 1', 'photo-1.jpg'),
(2, 'Photo 2', 'photo-2.jpg'),
(3, 'Photo 3', 'photo-3.jpg'),
(4, 'Photo 4', 'photo-4.jpg'),
(5, 'Photo 5', 'photo-5.jpg'),
(6, 'Photo 6', 'photo-6.jpg');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_product`
--

CREATE TABLE `tbl_product` (
  `p_id` int(11) NOT NULL,
  `p_name` varchar(255) NOT NULL,
  `p_code` varchar(80) NOT NULL DEFAULT '',
  `p_unit` varchar(30) NOT NULL DEFAULT 'sp',
  `p_old_price` varchar(10) NOT NULL,
  `p_cost_price` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `p_profit_percent` decimal(8,2) NOT NULL DEFAULT 30.00,
  `p_low_stock_threshold` int(11) NOT NULL DEFAULT 5,
  `p_current_price` varchar(10) NOT NULL,
  `p_qty` int(10) NOT NULL,
  `p_featured_photo` varchar(255) NOT NULL,
  `p_description` mediumtext NOT NULL,
  `p_short_description` mediumtext NOT NULL,
  `p_feature` mediumtext NOT NULL,
  `p_condition` mediumtext NOT NULL,
  `p_return_policy` mediumtext NOT NULL,
  `p_total_view` int(11) NOT NULL,
  `p_is_featured` int(1) NOT NULL,
  `p_is_active` int(1) NOT NULL,
  `ecat_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_product`
--

INSERT INTO `tbl_product` (`p_id`, `p_name`, `p_code`, `p_unit`, `p_old_price`, `p_cost_price`, `p_profit_percent`, `p_low_stock_threshold`, `p_current_price`, `p_qty`, `p_featured_photo`, `p_description`, `p_short_description`, `p_feature`, `p_condition`, `p_return_policy`, `p_total_view`, `p_is_featured`, `p_is_active`, `ecat_id`) VALUES
(114, 'Áo Thun Nam LADOS ', 'SP00114', 'sp', '150000', 115024.3902, 30.00, 5, '149532', 40, 'product-featured-114.png', '<p>Áo thun nam mang phong cách basic, trẻ trung và năng động, là item không thể thiếu trong tủ đồ của phái mạnh. Sản phẩm được may từ chất liệu vải thun cotton 100% cao cấp, mang đến cảm giác mềm mại, thoáng mát và khả năng thấm hút mồ hôi vượt trội, giúp bạn luôn thoải mái trong mọi hoạt động suốt ngày dài. Áo có phom dáng chuẩn, ôm vừa vặn cơ thể, đường may sắc nét và tỉ mỉ đến từng chi tiết. Thiết kế tối giản giúp bạn dễ dàng mix &amp; match cùng quần jeans, quần kaki, hay quần short để đi dạo phố, cà phê hoặc mặc ở nhà.</p>', '<p>Áo thun nam cổ tròn basic, chất liệu 100% cotton cao cấp mềm mịn, thoáng mát. Phom dáng trẻ trung, thấm hút mồ hôi tốt, cực kỳ dễ phối đồ và phù hợp cho mọi hoạt động hàng ngày.</p>', '<p>Chất liệu: 100% Cotton tự nhiên, mềm mại, an toàn cho da.</p><p>Thiết kế: Cổ tròn, tay ngắn năng động, phom dáng basic dễ mặc cho mọi dáng người.</p><p>Độ bền: Vải giữ form tốt, không bai nhão, không xù lông và bền màu sau nhiều lần giặt.</p><p>Đường may: Chắc chắn, tỉ mỉ, hoàn thiện cao không có chỉ thừa.</p><p>Tính ứng dụng: Dễ dàng kết hợp với nhiều trang phục và phụ kiện khác nhau.</p>', '<p>Size XS: Phù hợp với cân nặng dưới 50kg và chiều cao dưới 1m60.<br />\nSize S: Phù hợp với cân nặng từ 50kg đến 58kg và chiều cao từ 1m60 đến 1m65.<br />\nSize M: Phù hợp với cân nặng từ 59kg đến 68kg và chiều cao từ 1m66 đến 1m72.<br />\nSize L: Phù hợp với cân nặng từ 69kg đến 78kg và chiều cao từ 1m73 đến 1m78.</p><p>Lưu ý khi chọn size:<br />\n-Ưu tiên chiều cao: Nếu số đo của bạn nằm ở 2 size khác nhau, hãy ưu tiên chọn size theo chiều cao để tránh áo bị cộc (Ví dụ: Bạn nặng 55kg nhưng cao 1m70 thì nên chọn size M thay vì size S).<br />\n-Tùy chỉnh theo vóc dáng và sở thích: Nếu bạn có vòng bụng to, vai rộng hoặc đơn giản là thích mặc theo phong cách rộng rãi thoải mái (oversize), hãy cân nhắc chọn lớn hơn 1 size so với hướng dẫn trên.<br />\n-Độ co rút của vải: Vì chất liệu áo là cotton nên có thể co rút nhẹ sau lần giặt đầu tiên. Nếu số đo của bạn nằm sát ranh giới giữa 2 size, chọn size lớn hơn sẽ là giải pháp an toàn</p>', '<p>Hỗ trợ đổi trả trong vòng 7 ngày kể từ ngày nhận hàng với điều kiện sản phẩm còn nguyên tem mác, chưa qua giặt ủi, sử dụng hoặc phát hiện lỗi do nhà sản xuất.</p>', 89, 1, 1, 21),
(115, 'Quần jean nam ống suông', 'SP00115', 'sp', '190', 100000.0000, 30.00, 5, '130000', 30, 'product-photo-115-20260408093243-6345-0.png', '<p>Quần jean nam ống suông (straight fit) mang phom dáng cổ điển kết hợp cùng hơi thở hiện đại, là mảnh ghép hoàn hảo cho phong cách thời trang phái mạnh. Sở hữu tông màu xanh đen được wash xước nhẹ (faded wash) cá tính, chiếc quần mang lại vẻ ngoài bụi bặm và mạnh mẽ. Sản phẩm được hoàn thiện từ chất liệu denim cao cấp, vải dày dặn giúp giữ phom dáng đứng chuẩn, hạn chế nhăn nhàu nhưng vẫn có độ co giãn nhẹ để bạn thoải mái vận động. Thiết kế 5 túi truyền thống vô cùng tiện lợi. Item này cực kỳ đa năng, dễ dàng kết hợp cùng áo thun năng động, sơ mi thanh lịch hay jacket cá tính để đi học, đi làm hoặc dạo phố.</p>', '<p>Quần jean nam ống suông (straight) màu xanh đen wash cá tính. Chất liệu denim cao cấp dày dặn, giữ form cực tốt và co giãn nhẹ. Thiết kế basic, bền bỉ, dễ dàng phối hợp với nhiều phong cách trang phục khác nhau.</p>', '<p>- Phom dáng: Ống suông (Straight Fit) vừa vặn, tạo cảm giác thoải mái và che khuyết điểm chân hiệu quả.</p><p>- Chất liệu: Denim pha spandex co giãn nhẹ, độ bền cao, thấm hút tốt.</p><p>- Màu sắc: Xanh đen wash vintage, nam tính và cực kỳ dễ phối đồ.</p><p>- Thiết kế: Chuẩn 5 túi tiện dụng (2 túi trước, 1 túi đồng hồ, 2 túi sau).</p><p>- Phụ kiện: Khóa kéo trơn tru, nút cài kim loại chống gỉ sét, đường chỉ may mí đôi chắc chắn.</p>', '<p>Hướng dẫn chọn size quần jean nam:</p><p>- Size 28: Phù hợp với cân nặng từ 45kg đến 52kg, chiều cao từ 1m55 đến 1m62.</p><p>- Size 29: Phù hợp với cân nặng từ 53kg đến 57kg, chiều cao từ 1m60 đến 1m65.</p><p>- Size 30: Phù hợp với cân nặng từ 58kg đến 62kg, chiều cao từ 1m65 đến 1m69.</p><p>- Size 31: Phù hợp với cân nặng từ 63kg đến 68kg, chiều cao từ 1m68 đến 1m72.</p><p>- Size 32: Phù hợp với cân nặng từ 69kg đến 74kg, chiều cao từ 1m72 đến 1m76.</p><p>- Size 34: Phù hợp với cân nặng từ 75kg đến 82kg, chiều cao từ 1m75 đến 1m80</p>', '<p>Hỗ trợ đổi hoặc trả hàng trong vòng 07 ngày kể từ ngày khách hàng nhận được sản phẩm (thời gian được tính dựa trên hệ thống theo dõi đơn hàng của đơn vị vận chuyển).</p>', 2, 0, 1, 18),
(116, 'Áo Polo Nam Polomanor Symbol - Regular Fit Cotton Cao Cấp', 'SP00116', 'sp', '350000', 200000.0000, 30.00, 5, '260000', 30, 'product-photo-116-20260408194503-9443-0.png', '<p>Áo Polo Polomanor Symbol là sự kết hợp hoàn hảo giữa phong cách thanh lịch cổ điển và tinh thần hiện đại. Với chất liệu Cotton tự nhiên mềm mịn, thoáng khí cùng form dáng Regular Fit tôn dáng, đây là lựa chọn tối ưu cho phái mạnh dù là đi làm, đi chơi hay gặp gỡ đối tác. Điểm nhấn tinh tế nằm ở logo biểu tượng được thêu tỉ mỉ trên ngực trái, khẳng định phong cách riêng biệt.</p>', '<p>- Chất liệu: Vải thun Cotton cá sấu (Pique) cao cấp, có khả năng co giãn tốt, thấm hút mồ hôi cực nhanh, mang lại cảm giác mát mẻ suốt ngày dài.</p><p>- Thiết kế: Cổ bẻ phối nút gài chỉn chu. Tay áo ngắn bo nhẹ giúp bắp tay trông gọn gàng, nam tính.</p><p>- Màu sắc: Đen basic – gam màu kinh điển, cực kỳ dễ phối đồ và giúp che khuyết điểm cơ thể hiệu quả.</p><p>- Đường may: Công nghệ may tinh xảo, đường chỉ chắc chắn, không chỉ thừa, đảm bảo độ bền đẹp sau nhiều lần giặt.</p>', '<p>- Kháng khuẩn &amp; Chống mùi: Sợi vải được xử lý giúp hạn chế mùi cơ thể, giữ cho áo luôn sạch sẽ.</p><p>- Bền màu, không biến dạng: Áo không bị xù lông, không phai màu và giữ được form dáng chuẩn sau khi giặt máy.</p><p>- Logo Symbol độc quyền: Biểu tượng chiến binh được thêu sắc nét, tạo điểm nhấn sang trọng và khác biệt so với các dòng polo trơn thông thường.</p><p>- Đa năng: Dễ dàng kết hợp với quần Tây cho vẻ ngoài lịch lãm hoặc quần Short/Jean cho phong cách năng động.</p>', '<p>- Size M: Phù hợp với người có cân nặng từ 50kg đến 62kg và chiều cao từ 1m60 đến 1m67.</p><p>- Size L: Phù hợp với người có cân nặng từ 63kg đến 72kg và chiều cao từ 1m68 đến 1m74.</p><p>- Size XL: Phù hợp với người có cân nặng từ 73kg đến 82kg và chiều cao từ 1m75 đến 1m80.</p><p>- Size XXL: Phù hợp với người có cân nặng từ 83kg đến 92kg và chiều cao từ 1m81 đến 1m88.</p>', '<p>Thời hạn đổi trả: Hỗ trợ đổi trả trong vòng 7 ngày kể từ ngày nhận hàng.</p>', 0, 0, 1, 20),
(117, 'Áo Sơ Mi Nam Tay Dài Oxford Xanh Đen - Phom Slim Fit', 'SP00117', 'sp', '360000', 210000.0000, 30.00, 5, '273000', 30, 'product-photo-117-20260408194925-2887-0.png', '<p>- Chất liệu: Vải Oxford (cotton phối polyester), có bề mặt dệt đặc trưng, dày dặn, bền màu và ít nhăn.</p><p>- Phom dáng: Slim Fit (ôm nhẹ cơ thể), tạo cảm giác gọn gàng và trẻ trung.</p><p>- Cổ áo: Cổ button-down (cổ bẻ có cúc cài), giúp cổ áo luôn đứng form.</p><p>- Chi tiết:</p><p>- Nẹp áo có hàng cúc màu trắng nổi bật trên nền vải xanh đen.</p><p>- Ngực trái có thêu logo biểu tượng nhỏ màu trắng.</p><p>- Cổ tay áo bo nhẹ với cúc cài.</p><p>- Sản xuất: Tại Việt Nam.</p>', '<p>Áo sơ mi oxford màu xanh đen mang lại vẻ ngoài thanh lịch và nam tính cho phái mạnh. Với chất liệu vải oxford cao cấp, dày dặn và thấm hút mồ hôi tốt, chiếc áo này là sự lựa chọn hoàn hảo cho môi trường công sở, các buổi gặp gỡ đối tác hay những dịp trang trọng khác. Phom dáng Slim Fit hiện đại giúp tôn lên đường nét cơ thể.</p>', '<p>- Màu sắc dễ phối đồ: Gam màu xanh đen cổ điển, dễ dàng kết hợp với nhiều loại trang phục và phụ kiện.</p><p>- Chất liệu bền đẹp: Vải Oxford có độ bền cao, không dễ bị sờn rách hay mất dáng sau nhiều lần giặt.</p><p>- Sự đa năng: Phù hợp với nhiều hoàn cảnh khác nhau, từ đi làm đến đi chơi.</p><p>- Thoải mái khi mặc: Chất liệu vải thoáng khí, giúp người mặc luôn cảm thấy thoải mái và dễ chịu.</p>', '<p>- Size S: Dành cho người có cân nặng từ 50kg đến 57kg và chiều cao từ 1m60 đến 1m65.</p><p>- Size M: Dành cho người có cân nặng từ 58kg đến 65kg và chiều cao từ 1m66 đến 1m70.</p><p>- Size L: Dành cho người có cân nặng từ 66kg đến 73kg và chiều cao từ 1m71 đến 1m75.</p><p>- Size XL: Dành cho người có cân nặng từ 74kg đến 82kg và chiều cao từ 1m76 đến 1m82.</p><p>- Size XXL: Dành cho người có cân nặng từ 83kg đến 90kg và chiều cao từ 1m83 trở lên.</p>', '<p>Thời hạn: Hỗ trợ đổi trả trong vòng 15 ngày kể từ ngày nhận hàng.    </p>', 0, 1, 1, 21),
(118, 'Áo Polo Nam Polomanor Nivix - Phối Màu Thể Thao', 'SP00118', 'sp', '400000', 240000.0000, 30.00, 5, '312000', 20, 'product-photo-118-20260408195529-9102-0.png', '<p>- Chất liệu: Vải thun Cotton cá sấu (Pique) co giãn 4 chiều, bề mặt vải có các lỗ thoáng khí giúp thấm hút mồ hôi cực tốt và không gây bí bách.</p><p>- Thiết kế: * Phần thân trước phối sọc dọc màu xanh đen và xám trên nền trắng kem, tạo hiệu ứng thị giác giúp cơ thể trông cao và cân đối hơn.</p><p>- Cổ áo và một phần tay áo phối tông màu xám tinh tế.</p><p>- Điểm nhấn: Logo biểu tượng của Polomanor được thêu sắc nét ở ngực trái, tạo nên sự sang trọng cho sản phẩm.</p><p>- Màu sắc: Trắng kem phối Xám &amp; Xanh đen – sự kết hợp màu sắc nhã nhặn, dễ mặc và không lỗi mốt.</p>', '<p>Áo Polo Nivix là sự phá cách trong thiết kế với các đường phối sọc dọc thân áo, mang lại vẻ ngoài năng động, trẻ trung nhưng vẫn giữ được nét lịch sự. Chất liệu vải cao cấp mềm mại cùng form dáng Regular Fit giúp nam giới tự tin vận động trong mọi hoạt động hằng ngày.</p>', '<p>- Thiết kế độc đáo: Khác biệt hoàn toàn với các dòng polo trơn, Nivix mang đến làn gió mới cho tủ đồ của bạn với các mảng phối màu hiện đại.</p><p>- Form dáng chuẩn: Regular Fit giúp che khuyết điểm bụng hiệu quả mà vẫn đảm bảo sự tôn dáng ở vùng vai và ngực.</p><p>- Độ bền cao: Cổ áo được dệt bo chắc chắn, không bị nhão hay mất form sau nhiều lần giặt.</p><p>- Ứng dụng cao: Thích hợp mặc đi đánh golf, dạo phố, đi làm hoặc tham gia các buổi tiệc ngoài trời.</p>', '<p>- Size M: Dành cho nam giới nặng từ 50kg - 62kg, chiều cao từ 1m60 - 1m67.</p><p>- Size L: Dành cho nam giới nặng từ 63kg - 72kg, chiều cao từ 1m68 - 1m74.</p><p>- Size XL: Dành cho nam giới nặng từ 73kg - 82kg, chiều cao từ 1m75 - 1m80.</p><p>- Size XXL: Dành cho nam giới nặng từ 83kg - 92kg, chiều cao từ 1m81 - 1m88.</p>', '<p>Thời gian: Đổi trả trong vòng 7 ngày kể từ khi nhận sản phẩm.</p>', 0, 0, 1, 20),
(119, 'Áo Polo Nam Polomanor Icon Diamond', 'SP00119', 'sp', '240000', 160000.0000, 30.00, 5, '208000', 20, 'product-photo-119-20260408200101-2680-0.png', '<p>Thiết kế: Cổ bẻ và bo tay được dệt viền kép màu vàng be nổi<br />\nbật trên nền đen.</p><p>Họa tiết kim cương (Diamond) dệt chìm nhẹ nhàng trên thân áo<br />\ntạo chiều sâu và sự cao cấp cho sản phẩm.</p><p>Điểm nhấn: Logo biểu tượng của Polomanor thêu chỉ vàng đồng<br />\nsang trọng trên ngực trái, đồng bộ với màu viền áo.</p><p>Form dáng: Regular Fit (Vừa vặn), phù hợp với vóc dáng đàn<br />\nông Việt, giúp che khuyết điểm và tôn lên sự nam tính.</p>', '<p>Áo Polo Icon Diamond mang đến vẻ đẹp cổ điển đầy lịch lãm với điểm nhấn là các đường viền kép tinh tế ở cổ và bo tay. Chất liệu vải cao cấp cùng họa tiết dệt chìm sang trọng giúp quý ông khẳng định phong cách chuyên nghiệp nhưng không kém phần trẻ trung. Đây là item không thể thiếu cho những buổi gặp gỡ quan trọng hay môi trường công sở hiện đại.</p>', '<p>- Sang trọng &amp; Tinh tế: Các chi tiết phối màu vàng đồng trên nền đen tạo nên sự tương phản đẳng cấp, dễ dàng thu hút ánh nhìn.</p><p>- Thoáng khí tối ưu: Cấu trúc dệt tổ ong giúp thoát nhiệt nhanh chóng, giữ cho cơ thể luôn khô thoáng ngay cả trong thời tiết oi bức.</p><p>- Giữ form bền bỉ: Cổ áo và tay áo được dệt bo chắc chắn bằng công nghệ hiện đại, không bị nhão hay quăn mép sau nhiều lần giặt.</p><p>- Dễ phối đồ: Hoàn hảo khi kết hợp cùng quần tây, quần kaki hoặc quần jean tối màu.</p>', '<p>- Size M: Dành cho người có cân nặng từ 50kg đến 62kg, chiều cao từ 1m60 đến 1m67.</p><p>- Size L: Dành cho người có cân nặng từ 63kg đến 72kg, chiều cao từ 1m68 đến 1m74.</p><p>- Size XL: Dành cho người có cân nặng từ 73kg đến 82kg, chiều cao từ 1m75 đến 1m80.</p><p>- Size XXL: Dành cho người có cân nặng từ 83kg đến 92kg, chiều cao từ 1m81 đến 1m88.</p>', '<p>Thời gian: Hỗ trợ đổi trả trong vòng 7 ngày kể từ ngày nhận hàng.</p>', 0, 0, 1, 20),
(120, 'Áo Thun Nam Polomanor Basic USA', 'SP00120', 'sp', '320000', 230000.0000, 30.00, 5, '299000', 20, 'product-photo-120-20260408200439-9634-0.png', '<p>- Chất liệu: 100% Cotton USA tự nhiên, sợi vải bền chắc, bề mặt mịn màng và thân thiện với làn da.</p><p>- Thiết kế: Kiểu dáng Oversize nhẹ (thả lỏng) mang lại sự thoải mái tối đa và vẻ ngoài năng động.</p><p>- Điểm nhấn: Logo Polomanor nhỏ được thêu tinh tế cùng tông màu ở ngực trái, tạo nên sự tối giản sang trọng (Minimalism).</p><p>- Màu sắc: Be đậm (Beige) – tông màu trung tính hiện đại, cực kỳ tôn da và dễ dàng phối cùng nhiều loại quần khác nhau.</p>', '<p>Áo thun T-shirt Basic USA mang phong cách tối giản nhưng đầy tinh tế, là món đồ không thể thiếu trong tủ đồ của phái mạnh. Với chất liệu Cotton USA cao cấp, áo mang lại sự mềm mại tuyệt đối và khả năng thấm hút vượt trội, phù hợp cho mọi hoạt động từ dạo phố, tập thể thao đến mặc ở nhà.</p>', '<p>- Công nghệ dệt không đường may sườn: Giúp áo đứng form, không bị vặn sườn khi mặc và tạo cảm giác êm ái cho làn da.</p><p>- Siêu thoáng khí: Khả năng kiểm soát nhiệt độ cơ thể tốt, luôn khô thoáng dù vận động mạnh.</p><p>- Không xù lông, ít nhăn: Sợi Cotton USA giúp hạn chế tối đa tình trạng đổ lông và dễ dàng phẳng phiu chỉ sau một lần giũ.</p><p>- Đa năng: Dễ dàng kết hợp với quần Jean, quần Short hoặc quần Kaki để tạo nên bộ trang phục trẻ trung.</p>', '<p>- Size M: Phù hợp với người nặng từ 50kg - 65kg, chiều cao từ 1m60 - 1m68.</p><p>- Size L: Phù hợp với người nặng từ 66kg - 75kg, chiều cao từ 1m69 - 1m75.</p><p>- Size XL: Phù hợp với người nặng từ 76kg - 85kg, chiều cao từ 1m76 - 1m82.</p><p>- Size XXL: Phù hợp với người nặng từ 86kg - 95kg, chiều cao từ 1m83 trở lên.</p>', '<p>Thời gian hỗ trợ: Đổi trả trong vòng 7 ngày kể từ khi nhận hàng thành công.</p>', 0, 1, 1, 20),
(121, 'Áo Polo Nam Dài Tay Polomanor Lucian', 'SP00121', 'sp', '400000', 340000.0000, 30.00, 5, '442000', 20, 'product-photo-121-20260408200748-5910-0.png', '<p>- Chất liệu: Sợi len dệt (Knitted) cao cấp, bề mặt vải mềm mịn, không gây châm chích và có độ bền cao.</p><p>- Thiết kế: * Kiểu dáng dài tay với phần bo thun ở cổ tay và gấu áo, giúp giữ ấm và tạo form dáng gọn gàng.</p><p>- Màu sắc: Đen tuyền – dễ dàng phối cùng quần Tây, quần Kaki hoặc mặc bên trong áo Blazer/Vest.</p><p>- Form dáng: Regular Fit (Vừa vặn), ôm nhẹ ở vai và thoải mái ở phần thân, phù hợp với mọi vóc dáng.</p>', '<p>Áo Polo dài tay Lucian là biểu tượng của sự lịch lãm và sang trọng dành cho phái mạnh trong những ngày thời tiết giao mùa. Với chất liệu len dệt mịn màng, co giãn tốt cùng thiết kế cổ điển, sản phẩm mang lại vẻ ngoài chỉn chu, ấm áp nhưng vẫn cực kỳ thoáng mát và dễ chịu.</p>', '<p>- Chất len dệt tinh xảo: Công nghệ dệt hiện đại giúp áo có độ đàn hồi tốt, ít nhăn và giữ ấm nhẹ nhàng cho cơ thể.</p><p>- Phong cách &quot;Old Money&quot;: Thiết kế tối giản, không logo lộ liễu, tập trung vào chất liệu và đường cắt, mang lại vẻ ngoài đẳng cấp cho người mặc.</p><p>- Thoáng khí: Dù là chất liệu dệt nhưng áo vẫn có các khe hở siêu nhỏ giúp thoát nhiệt, không gây cảm giác bí bách khi mặc cả ngày.</p><p>- Bo thun chắc chắn: Phần bo tay và gấu áo được dệt mật độ cao, hạn chế tình trạng bị giãn sau thời gian dài sử dụng.</p>', '<p>- Size M: Phù hợp với người nặng từ 52kg - 63kg, chiều cao từ 1m60 - 1m68.</p><p>- Size L: Phù hợp với người nặng từ 64kg - 73kg, chiều cao từ 1m69 - 1m75.</p><p>- Size XL: Phù hợp với người nặng từ 74kg - 83kg, chiều cao từ 1m76 - 1m82.</p><p>- Size XXL: Phù hợp với người nặng từ 84kg - 95kg, chiều cao từ 1m83 trở lên.</p>', '<p>Thời hạn: Hỗ trợ đổi trả trong vòng 7 ngày kể từ ngày nhận hàng.</p>', 0, 0, 1, 20),
(122, 'Áo Polo Nam Polomanor Irish', 'SP00122', 'sp', '410000', 240000.0000, 30.00, 5, '312000', 15, 'product-photo-122-20260408201144-7605-0.png', '<p>- Chất liệu: Vải thun Cotton Pique cao cấp, co giãn 4 chiều tốt, bề mặt vải có độ bền cao và khả năng thấm hút mồ hôi ưu việt.</p><p>- Thiết kế: Phần thân trên và cổ áo sử dụng tông màu xanh đen (Navy) sang trọng.</p><p>- Điểm nhấn: Logo biểu tượng của Polomanor được thêu tinh tế bằng chỉ trắng ở ngực trái, khẳng định chất lượng thương hiệu.</p><p>- Màu sắc: Xanh đen phối Trắng kem – một sự kết hợp màu sắc kinh điển, thanh lịch và không bao giờ lỗi mốt.</p>', '<p>Áo Polo Irish là sự kết hợp tinh tế giữa phong cách thể thao năng động và vẻ ngoài lịch sự, hiện đại. Với thiết kế phối màu độc đáo ở phần ngực và các đường kẻ ngang sắc nét, chiếc áo này mang lại sự trẻ trung, giúp phái mạnh nổi bật hơn trong các buổi dạo phố, đi làm hay tham gia các hoạt động ngoại khóa.</p>', '<p>- Thiết kế tôn dáng: Các đường kẻ ngang ở ngực giúp tạo cảm giác bờ vai rộng và vóc dáng cân đối hơn.</p><p>- Thoáng khí tối đa: Cấu trúc sợi vải giúp thoát nhiệt nhanh, giữ cho người mặc luôn cảm thấy mát mẻ dù vận động liên tục.</p><p>- Độ bền màu: Công nghệ nhuộm tiên tiến giúp áo luôn giữ được màu sắc tươi mới, không bị phai hay lem màu khi giặt.</p><p>- Đa năng: Phù hợp để mặc cùng quần Kaki sáng màu, quần Short hoặc quần Jean năng động.</p>', '<p>- Size M: Phù hợp với người nặng từ 50kg - 62kg, chiều cao từ 1m60 - 1m67.</p><p>- Size L: Phù hợp với người nặng từ 63kg - 72kg, chiều cao từ 1m68 - 1m74.</p><p>- Size XL: Phù hợp với người nặng từ 73kg - 82kg, chiều cao từ 1m75 - 1m80.</p><p>- Size XXL: Phù hợp với người nặng từ 83kg - 92kg, chiều cao từ 1m81 - 1m88.</p>', '<p>- Thời hạn: Hỗ trợ đổi trả sản phẩm trong vòng 7 ngày kể từ ngày nhận hàng.</p><p>-</p>', 0, 0, 1, 20),
(123, 'Áo Sơ Mi Nam Polomanor Oxford Premium', 'SP00123', 'sp', '720000', 500000.0000, 30.00, 5, '650000', 20, 'product-photo-123-20260408201543-2235-0.png', '<p>- Chất liệu: Vải Oxford cao cấp (Cotton pha), bề mặt vải có cấu trúc dệt đặc trưng bền bỉ, giúp áo ít nhăn và giữ được độ phẳng phiu suốt cả ngày.</p><p>- Thiết kế: *Kiểu dáng tay dài, cổ bẻ đứng form giúp tôn lên nét thanh lịch.</p><p>Điểm nhấn: Logo biểu tượng đặc trưng của Polomanor được thêu sắc nét bằng chỉ màu xanh đen ở ngực trái, tạo điểm nhấn thương hiệu tinh tế.</p><p>- Màu sắc: Trắng tinh khôi – cực kỳ dễ phối đồ, phù hợp với mọi tông da.</p>', '<p>Áo sơ mi Oxford trắng là món đồ &quot;kinh điển&quot; mà mọi quý ông đều cần có. Với chất liệu vải Oxford dày dặn, đứng form và khả năng thấm hút tốt, sản phẩm mang lại vẻ ngoài lịch lãm, sạch sẽ và vô cùng chuyên nghiệp. Thiết kế tối giản giúp bạn dễ dàng biến hóa phong cách từ công sở chỉn chu đến dạo phố năng động.</p>', '<p>- Form dáng Slim Fit: Đường cắt may ôm nhẹ vừa vặn, giúp tôn lên vóc dáng nam tính mà vẫn đảm bảo sự thoải mái trong mọi cử động.</p><p>- Chất vải &quot;biết thở&quot;: Vải Oxford nổi tiếng với độ thông thoáng, không gây bí bách khi mặc trong thời gian dài.</p><p>- Độ bền vượt trội: Cổ áo và măng sét tay áo được gia công kỹ lưỡng, không bị mất form hay biến dạng sau khi giặt.</p><p>- Dễ dàng kết hợp: Hoàn hảo khi mặc cùng quần Tây, quần Kaki tối màu hoặc phối bên trong áo len dệt kim.</p>', '<p>- Size S: Phù hợp với người nặng từ 50kg - 57kg, chiều cao từ 1m60 - 1m65.</p><p>- Size M: Phù hợp với người nặng từ 58kg - 65kg, chiều cao từ 1m66 - 1m70.</p><p>- Size L: Phù hợp với người nặng từ 66kg - 73kg, chiều cao từ 1m71 - 1m75.</p><p>- Size XL: Phù hợp với người nặng từ 74kg - 82kg, chiều cao từ 1m76 - 1m82.</p><p>- Size XXL: Phù hợp với người nặng từ 83kg - 90kg, chiều cao từ 1m83 trở lên.</p>', '<p>- Thời gian: Hỗ trợ đổi trả trong vòng 7 ngày kể từ ngày nhận hàng.</p><p>-</p>', 0, 0, 1, 21),
(124, 'Áo Sơ Mi Nam Polomanor Santo', 'SP00124', 'sp', '505555', 330000.0000, 30.00, 5, '429000', 15, 'product-photo-124-20260408201920-8634-0.png', '<p>- Chất liệu: Vải đũi (Linen) cao cấp, có khả năng thấm hút cực tốt, bề mặt vải có độ nhám nhẹ tự nhiên tạo vẻ ngoài mộc mạc nhưng sang trọng.</p><p>- Thiết kế: * Kiểu dáng cổ vest mở rộng trẻ trung, tạo sự thông thoáng cho phần cổ.</p><p>- Điểm nhấn: Logo biểu tượng của Polomanor thêu chỉ cùng tông màu tinh tế phía ngực trái, giữ đúng tinh thần tối giản (Minimalism).</p><p>- Màu sắc: Màu Be (Beige) – tông màu nhã nhặn, mang lại cảm giác dễ chịu và cực kỳ dễ phối với các loại quần sáng màu hoặc tối màu.</p>', '<p>Áo sơ mi Santo mang phong cách phóng khoáng và hiện đại với thiết kế cổ vest (cổ Cuban) đặc trưng. Chất liệu vải đũi/linen cao cấp nhẹ tênh và thoáng mát, kết hợp cùng tông màu be trung tính, đây là lựa chọn hoàn hảo cho những chuyến du lịch hè, dạo phố hoặc những buổi gặp mặt thân mật.</p>', '<p>- Phong cách lãng tử: Thiết kế cổ Cuban giúp người mặc trông phong cách và lãng tử hơn so với sơ mi cổ đức truyền thống.</p><p>- Giải nhiệt mùa hè: Chất vải Linen có cấu trúc sợi thưa tự nhiên, giúp lưu thông không khí tối đa, mang lại cảm giác mát lạnh khi mặc.</p><p>- Đường may tỉ mỉ: Các chi tiết vai và nách áo được xử lý kỹ lưỡng, đảm bảo độ bền và giữ phom áo luôn đẹp.</p><p>- Dễ phối đồ: Phối cùng quần short cho phong cách đi biển hoặc quần tây/kaki dài cho phong cách dạo phố thanh lịch.</p>', '<p>- Size M: Phù hợp với người nặng từ 55kg - 65kg, chiều cao từ 1m60 - 1m68.</p><p>- Size L: Phù hợp với người nặng từ 66kg - 75kg, chiều cao từ 1m69 - 1m75.</p><p>- Size XL: Phù hợp với người nặng từ 76kg - 85kg, chiều cao từ 1m76 - 1m82.</p><p>- Size XXL: Phù hợp với người nặng từ 86kg - 95kg, chiều cao từ 1m83 trở lên.</p>', '<p>Thời hạn: Hỗ trợ đổi trả trong vòng 7 ngày kể từ khi nhận hàng.</p>', 0, 0, 1, 21),
(125, 'Áo Thun Nam Polomanor Codin', 'SP00125', 'sp', '310000', 230000.0000, 30.00, 5, '299000', 15, 'product-photo-125-20260408202249-1808-0.png', '<p>- Chất liệu: Vải thun Cotton 2 chiều cao cấp, bề mặt vải đanh mịn, không xù lông và có khả năng thấm hút mồ hôi vượt trội, giữ cơ thể luôn khô thoáng.</p><p>- Thiết kế: * Kiểu dáng T-shirt cổ tròn truyền thống nhưng được nâng cấp với phần bo cổ dệt dày dặn.</p><p>- Điểm nhấn: * Cổ áo phối viền màu be và các đường kẻ sọc tinh tế, tạo điểm nhấn thu hút cho gương mặt.</p><p>- Form dáng: Regular Fit – Vừa vặn thoải mái, phù hợp để mặc hằng ngày hoặc tham gia các hoạt động ngoài trời.</p>', '<p>Áo thun Codin là bản phối hoàn hảo giữa sự đơn giản và nét phá cách hiện đại. Điểm nhấn nổi bật nằm ở phần cổ tròn được dệt bo viền tinh tế, kết hợp cùng chất liệu vải cao cấp mang lại diện mạo năng động, trẻ trung. Đây là item lý tưởng cho những chàng trai yêu thích phong cách tối giản nhưng vẫn muốn tạo sự khác biệt</p>', '<p>- Cổ áo chống giãn: Công nghệ dệt bo đặc biệt giúp phần cổ áo luôn ôm sát, không bị nhão hay biến dạng sau thời gian dài sử dụng.</p><p>- Màu sắc bền bỉ: Gam màu xanh đen Navy được xử lý cầm màu tốt, giữ cho áo luôn như mới sau nhiều lần giặt.</p><p>- Phong cách linh hoạt: Dễ dàng &quot;biến hóa&quot; khi phối cùng quần Short cho vẻ ngoài năng động hoặc quần Jean/Kaki cho vẻ ngoài thanh lịch, chỉn chu.</p><p>- Đường may tinh xảo: Từng đường kim mũi chỉ được gia công tỉ mỉ, đảm bảo độ bền và tính thẩm mỹ cao nhất.</p>', '<p>- Size M: Phù hợp cho người từ 50kg - 62kg, chiều cao từ 1m60 - 1m67.</p><p>- Size L: Phù hợp cho người từ 63kg - 72kg, chiều cao từ 1m68 - 1m74.</p><p>- Size XL: Phù hợp cho người từ 73kg - 82kg, chiều cao từ 1m75 - 1m80.</p><p>- Size XXL: Phù hợp cho người từ 83kg - 92kg, chiều cao từ 1m81 trở lên.</p>', '<p>Thời hạn: Hỗ trợ đổi trả trong vòng 7 ngày kể từ khi nhận hàng.</p>', 0, 0, 1, 20),
(126, 'Áo Polo Nam Polomanor Kanis', 'SP00126', 'sp', '750000', 510000.0000, 30.00, 5, '663000', 20, 'product-photo-126-20260408202634-4338-0.png', '<p>- Chất liệu: Sợi len dệt (Knitted) siêu mịn, trọng lượng nhẹ, bề mặt vải êm ái, không gây ngứa và có độ đàn hồi cực tốt.</p><p>- Thiết kế: * Kiểu dáng ngắn tay, bo nhẹ ở phần tay và gấu áo giúp tạo phom dáng gọn gàng, tôn vòng ngực và vai.</p><p>- Họa tiết: Sọc ngang đen bản vừa trải đều trên nền màu be (beige), tạo hiệu ứng thị giác hiện đại và đầy nam tính.</p><p>- Màu sắc: Be phối Đen – sự kết hợp màu sắc nhã nhặn, sang trọng và rất dễ để phối cùng nhiều trang phục khác nhau.</p>', '<p>Áo Polo Kanis là sự giao thoa hoàn hảo giữa nét cổ điển và hơi thở thời đại. Với chất liệu len dệt cao cấp mềm mại cùng họa tiết sọc ngang bản lớn thời thượng, sản phẩm mang đến cho phái mạnh diện mạo lãng tử, sang trọng nhưng vẫn vô cùng trẻ trung. Đây là món đồ lý tưởng cho những quý ông theo đuổi phong cách &quot;Old Money&quot; thanh lịch.</p>', '<p>- Chất liệu len dệt đặc biệt: Khác với vải thun thông thường, vải len dệt của dòng Kanis giúp áo có độ rủ tự nhiên, ít nhăn và giữ ấm nhẹ vào mùa lạnh nhưng vẫn đủ thoáng mát cho mùa hè.</p><p>- Họa tiết sọc ngang thời thượng: Được dệt trực tiếp vào sợi vải, đảm bảo không bị phai màu hay bong tróc, giúp người mặc trông cân đối và đầy đặn hơn.</p><p>- Phong cách đa năng: Phù hợp để đi làm công sở, đi chơi golf, dạo phố hoặc tham gia các buổi tiệc tối nhẹ nhàng.</p><p>- Độ hoàn thiện cao: Các đường nối vai và nách áo được xử lý công nghệ dệt không đường gờ, mang lại sự thoải mái tuyệt đối khi vận động.</p>', '<p>- Size M: Phù hợp với người nặng từ 52kg - 63kg, chiều cao từ 1m60 - 1m68.</p><p>- Size L: Phù hợp với người nặng từ 64kg - 73kg, chiều cao từ 1m69 - 1m75.</p><p>- Size XL: Phù hợp với người nặng từ 74kg - 83kg, chiều cao từ 1m76 - 1m82.</p><p>- Size XXL: Phù hợp với người nặng từ 84kg - 95kg, chiều cao từ 1m83 trở lên.</p>', '<p>Thời hạn: Đổi trả trong vòng 7 ngày kể từ ngày nhận hàng.</p>', 0, 1, 1, 20),
(127, 'Áo Khoác Nam Polomanor Orin', 'SP00127', 'sp', '800000', 540000.0000, 30.00, 5, '702000', 20, 'product-photo-127-20260408203121-9008-0.png', '<p>- Chất liệu: Vải Poly-Cotton cao cấp, có khả năng chống gió tốt, bề mặt vải lì mịn, bền màu và hạn chế bám bụi. Lớp lót bên trong thoáng khí, tạo cảm giác dễ chịu khi mặc.</p><p>- Thiết kế: * Kiểu dáng áo khoác Bomber cổ cao giúp bảo vệ phần cổ.</p><p>- Điểm nhấn: Logo Polomanor thêu nhỏ cùng tông màu ở ngực trái, giữ đúng tinh thần tinh tế, không phô trương.</p><p>- Màu sắc: Màu Be (Beige) – sang trọng, thanh lịch và cực kỳ bắt sáng khi lên ảnh.</p>', '<p>Áo khoác Orin mang phong cách hiện đại với thiết kế tối giản, tập trung vào phom dáng mạnh mẽ và tính ứng dụng cao. Chất liệu vải bền bỉ cùng tông màu be trung tính giúp bạn dễ dàng hoàn thiện bộ trang phục (outfit) dạo phố hay đi làm trong những ngày thời tiết se lạnh hoặc cần che chắn nắng gió.</p>', '<p>- Phom dáng hiện đại: Thiết kế vai raglan và bo gấu giúp người mặc trông cao ráo và năng động hơn.</p><p>- Khóa kéo cao cấp: Hệ thống khóa kéo trơn tru, đồng màu với vải áo, đảm bảo độ bền và tính thẩm mỹ cao.</p><p>- Đa năng: Là chiếc áo khoác &quot;quốc dân&quot; có thể phối cùng áo thun, áo sơ mi bên trong và quần Jean hoặc quần Kaki.</p><p>- Dễ bảo quản: Chất vải ít nhăn, nhanh khô và giữ được phom dáng ổn định sau nhiều lần giặt.</p>', '<p>- Size M: Phù hợp với người nặng từ 50kg - 63kg, chiều cao từ 1m60 - 1m68.</p><p>- Size L: Phù hợp với người nặng từ 64kg - 73kg, chiều cao từ 1m69 - 1m75.</p><p>- Size XL: Phù hợp với người nặng từ 74kg - 83kg, chiều cao từ 1m76 - 1m82.</p><p>- Size XXL: Phù hợp với người nặng từ 84kg - 95kg, chiều cao từ 1m83 trở lên.</p>', '<p>Thời hạn: Hỗ trợ đổi trả trong vòng 7 ngày kể từ ngày nhận hàng.</p>', 0, 0, 1, 80),
(128, 'Áo Khoác Nam Polomanor Aston', 'SP00128', 'sp', '800000', 560000.0000, 30.00, 5, '728000', 5, 'product-photo-128-20260408203552-1960-0.png', '<li><p data-path-to-node=\"9,0,0\"><b data-path-to-node=\"9,0,0\" data-index-in-node=\"0\">Chất liệu:</b> Vải gió (Poly-synthetic) cao cấp tích hợp công nghệ chống thấm nước nhẹ và cản gió tối ưu. Mặt trong lót lưới giúp thoát nhiệt tốt, không gây bí bách khi mặc lâu.</p></li><li><p data-path-to-node=\"9,1,0\"><b data-path-to-node=\"9,1,0\" data-index-in-node=\"0\">Thiết kế:</b></p><p data-path-to-node=\"9,1,0\"><b data-path-to-node=\"9,1,0\" data-index-in-node=\"0\"><br></b>Kiểu dáng Bomber hiện đại với mũ trùm đầu (Hoodie) có thể điều chỉnh.</p><p data-path-to-node=\"9,1,0\">Phần gấu áo và cổ tay được bo thun dày dặn, giúp giữ ấm và tạo độ phồng thời trang cho dáng áo.</p><p data-path-to-node=\"9,1,0\">Hệ thống túi khóa kéo hai bên hông an toàn để cất giữ điện thoại, ví tiền.</p></li><li><p data-path-to-node=\"9,2,0\"><b data-path-to-node=\"9,2,0\" data-index-in-node=\"0\">Điểm nhấn:</b> Logo Polomanor thêu chỉ trắng nổi bật trên nền vải đen ở ngực trái, tạo điểm nhấn thương hiệu đặc trưng.</p></li><li><p data-path-to-node=\"9,3,0\"><b data-path-to-node=\"9,3,0\" data-index-in-node=\"0\">Màu sắc:</b> Đen tuyền (Black) – mạnh mẽ, sạch sẽ và cực kỳ dễ phối đồ.</p></li>', '<p>Áo khoác Aston là sự kết hợp hoàn hảo giữa phong cách thể thao đường phố và tính năng bảo vệ vượt trội. Với thiết kế có mũ trùm đầu tiện lợi và gam màu đen tối giản, sản phẩm mang lại vẻ ngoài cực kỳ nam tính, bí ẩn và phù hợp cho mọi điều kiện thời tiết.</p>', '<li><p data-path-to-node=\"12,0,0\"><b data-path-to-node=\"12,0,0\" data-index-in-node=\"0\">Tính ứng dụng cao:</b> Vừa là áo khoác giữ ấm, vừa là áo chống nắng và kháng nước nhẹ cho những cơn mưa rào bất chợt.</p></li><li><p data-path-to-node=\"12,1,0\"><b data-path-to-node=\"12,1,0\" data-index-in-node=\"0\">Phom dáng mạnh mẽ:</b> Đường cắt vai Raglan giúp người mặc cử động cánh tay linh hoạt, không bị gò bó.</p></li><li><p data-path-to-node=\"12,2,0\"><b data-path-to-node=\"12,2,0\" data-index-in-node=\"0\">Khóa kéo chắc chắn:</b> Khóa kéo cao cấp trơn tru, kéo cao lên tận cổ giúp bảo vệ cơ thể tối đa khỏi gió lạnh.</p></li><li><p data-path-to-node=\"12,3,0\"><b data-path-to-node=\"12,3,0\" data-index-in-node=\"0\">Phong cách trẻ trung:</b> Dễ dàng kết hợp với áo thun bên trong, quần Jogger hoặc quần Jean để tạo nên bộ trang phục năng động.</p></li>', '<li><p data-path-to-node=\"16,0,0\"><b data-path-to-node=\"16,0,0\" data-index-in-node=\"0\">Size M:</b> Phù hợp với người nặng từ <b data-path-to-node=\"16,0,0\" data-index-in-node=\"34\">50kg - 63kg</b>, chiều cao từ <b data-path-to-node=\"16,0,0\" data-index-in-node=\"60\">1m60 - 1m68</b>.</p></li><li><p data-path-to-node=\"16,1,0\"><b data-path-to-node=\"16,1,0\" data-index-in-node=\"0\">Size L:</b> Phù hợp với người nặng từ <b data-path-to-node=\"16,1,0\" data-index-in-node=\"34\">64kg - 73kg</b>, chiều cao từ <b data-path-to-node=\"16,1,0\" data-index-in-node=\"60\">1m69 - 1m75</b>.</p></li><li><p data-path-to-node=\"16,2,0\"><b data-path-to-node=\"16,2,0\" data-index-in-node=\"0\">Size XL:</b> Phù hợp với người nặng từ <b data-path-to-node=\"16,2,0\" data-index-in-node=\"35\">74kg - 83kg</b>, chiều cao từ <b data-path-to-node=\"16,2,0\" data-index-in-node=\"61\">1m76 - 1m82</b>.</p></li><li><p data-path-to-node=\"16,3,0\"><b data-path-to-node=\"16,3,0\" data-index-in-node=\"0\">Size XXL:</b> Phù hợp với người nặng từ <b data-path-to-node=\"16,3,0\" data-index-in-node=\"36\">84kg - 95kg</b>, chiều cao từ <b data-path-to-node=\"16,3,0\" data-index-in-node=\"62\">1m83 trở lên</b>.</p></li>', '<p><b data-path-to-node=\"20,0,0\" data-index-in-node=\"0\">Thời hạn:</b> Hỗ trợ đổi trả trong vòng <b data-path-to-node=\"20,0,0\" data-index-in-node=\"36\">7 ngày</b> kể từ khi nhận hàng.</p>', 0, 0, 1, 80),
(129, 'Túi Xách Nữ Valmira - Top Handle Bag Luxury', 'SP00129', 'sp', '2000000', 1500000.0000, 30.00, 5, '1950000', 10, 'product-photo-129-20260408211149-4480-0.png', '<li><p data-path-to-node=\"9,0,0\"><b data-path-to-node=\"9,0,0\" data-index-in-node=\"0\">Chất liệu:</b> Da tổng hợp cao cấp với bề mặt mịn màng, có độ bóng mờ sang trọng, khả năng giữ phom tốt và dễ dàng vệ sinh.</p></li><li><p data-path-to-node=\"9,1,0\"><b data-path-to-node=\"9,1,0\" data-index-in-node=\"0\">Thiết kế:</b></p><p data-path-to-node=\"9,1,0\"><b data-path-to-node=\"9,1,0\" data-index-in-node=\"0\"><br></b>Kiểu dáng túi hình thang (Trapeze) hiện đại, đáy rộng giúp tối ưu không gian lưu trữ.</p><p data-path-to-node=\"9,1,0\">Tay cầm dạng tròn được bện da tỉ mỉ, mang lại cảm giác chắc chắn và êm ái khi xách tay.</p></li><li><p data-path-to-node=\"9,2,0\"><b data-path-to-node=\"9,2,0\" data-index-in-node=\"0\">Điểm nhấn:</b>&nbsp; Mặt trước trang trí bằng dây đai da luồn qua các khoen kim loại, kết nối bằng khóa biểu tượng nút thắt màu vàng đồng vô cùng nổi bật.</p><p data-path-to-node=\"9,2,0\">Các chi tiết kim loại được mạ vàng sáng bóng, chống rỉ sét, tạo nên tổng thể cao cấp.</p></li><li><p data-path-to-node=\"9,3,0\"><b data-path-to-node=\"9,3,0\" data-index-in-node=\"0\">Màu sắc:</b> Màu Nâu Đậm (Brown) – tông màu trầm ấm, quyền lực và rất dễ phối hợp với nhiều phong cách thời trang khác nhau.</p></li>', '<p>Túi xách Valmira mang vẻ đẹp sang trọng và cổ điển, là phụ kiện hoàn hảo cho những quý cô yêu thích phong cách quý phái. Với thiết kế hình thang thanh lịch kết hợp cùng các chi tiết kim loại vàng đồng và tay cầm bện tinh xảo, sản phẩm này không chỉ là vật dụng đựng đồ mà còn là điểm nhấn đẳng cấp cho mọi bộ trang phục từ công sở đến dự tiệc.</p>', '<li><p data-path-to-node=\"12,0,0\"><b data-path-to-node=\"12,0,0\" data-index-in-node=\"0\">Sức chứa linh hoạt:</b> Ngăn chính rộng rãi giúp bạn thoải mái đựng điện thoại, ví tiền, mỹ phẩm và các vật dụng cá nhân cần thiết khác.</p></li><li><p data-path-to-node=\"12,1,0\"><b data-path-to-node=\"12,1,0\" data-index-in-node=\"0\">Tay cầm tinh xảo:</b> Thiết kế tay cầm bện không chỉ tăng độ bền mà còn là điểm nhấn thủ công nghệ thuật tạo sự khác biệt so với các dòng túi thông thường.</p></li><li><p data-path-to-node=\"12,2,0\"><b data-path-to-node=\"12,2,0\" data-index-in-node=\"0\">Phù hợp nhiều dịp:</b> Thiết kế trang nhã giúp túi phù hợp cho cả đi làm hằng ngày, gặp gỡ đối tác hay tham dự các sự kiện quan trọng.</p></li><li><p data-path-to-node=\"12,3,0\"><b data-path-to-node=\"12,3,0\" data-index-in-node=\"0\">Đế túi vững chãi:</b> Cấu trúc đáy túi được gia cố giúp túi có thể đứng vững trên các bề mặt phẳng mà không bị đổ ngã.</p></li>', '<li><p data-path-to-node=\"15,0,0\"><b data-path-to-node=\"15,0,0\" data-index-in-node=\"0\">Kiểu khóa:</b> Khóa kéo miệng túi an toàn (bên trong) kết hợp nắp gập hoặc dây đai trang trí bên ngoài.</p></li><li><p data-path-to-node=\"15,1,0\"><b data-path-to-node=\"15,1,0\" data-index-in-node=\"0\">Phụ kiện đi kèm:</b> Thường đi kèm dây đeo da dài có thể tháo rời để chuyển đổi từ túi xách tay sang túi đeo chéo tiện lợi.</p></li><li><p data-path-to-node=\"15,2,0\"><b data-path-to-node=\"15,2,0\" data-index-in-node=\"0\">Kích thước:</b> Size trung bì (Medium), phù hợp với vóc dáng phụ nữ Á Đông</p></li>', '<p><b data-path-to-node=\"20,0,0\" data-index-in-node=\"0\">Thời hạn:</b> Hỗ trợ đổi trả trong vòng <b data-path-to-node=\"20,0,0\" data-index-in-node=\"36\">7 ngày</b> kể từ khi nhận hàng.</p>', 0, 0, 1, 60),
(130, 'Giày Lười Nữ Valera Black - Patent Leather Penny Loafers', 'SP00130', 'sp', '300000', 200000.0000, 30.00, 5, '260000', 20, 'product-photo-130-20260408211929-7396-0.png', '<li><p data-path-to-node=\"9,0,0\"><b data-path-to-node=\"9,0,0\" data-index-in-node=\"0\">Chất liệu:</b> Da tổng hợp phủ bóng (Patent Leather) cao cấp, có độ bắt sáng cao, chống thấm nước nhẹ và cực kỳ dễ dàng vệ sinh.</p></li><li><p data-path-to-node=\"9,1,0\"><b data-path-to-node=\"9,1,0\" data-index-in-node=\"0\">Thiết kế:</b> * Kiểu dáng Penny Loafer đặc trưng với phần quai ngang (saddle) tinh tế ở mui giày</p><p data-path-to-node=\"9,1,0\">Mũi giày bo tròn nhẹ nhàng, tạo sự thoải mái cho các đầu ngón tay khi di chuyển lâu.</p></li><li><p data-path-to-node=\"9,2,0\"><b data-path-to-node=\"9,2,0\" data-index-in-node=\"0\">Đế giày:</b> Đế đúc chắc chắn với độ cao vừa phải (khoảng 2-3cm), giúp tôn dáng nhẹ nhàng mà vẫn đảm bảo sự êm ái, vững chãi.</p></li><li><p data-path-to-node=\"9,3,0\"><b data-path-to-node=\"9,3,0\" data-index-in-node=\"0\">Màu sắc:</b> Đen bóng (Black Patent) – gam màu kinh điển, dễ dàng phối hợp với mọi loại trang phục.</p></li>', '<p>Giày Loafers Valera là sự kết hợp hoàn mỹ giữa nét đẹp cổ điển (Menswear-inspired) và sự hiện đại, trẻ trung. Với chất liệu da bóng cao cấp và phom dáng tối giản, đôi giày này mang lại vẻ ngoài thanh lịch, quyền lực cho phái đẹp trong mọi môi trường từ công sở đến các buổi dạo phố thời thượng.</p>', '<li><p data-path-to-node=\"12,0,0\"><b data-path-to-node=\"12,0,0\" data-index-in-node=\"0\">Sự tiện lợi tối đa:</b> Thiết kế giày lười (slip-on) giúp bạn tiết kiệm thời gian, dễ dàng mang vào hoặc tháo ra mà không cần buộc dây phức tạp.</p></li><li><p data-path-to-node=\"12,1,0\"><b data-path-to-node=\"12,1,0\" data-index-in-node=\"0\">Đệm lót êm ái:</b> Phần lót trong được gia công mềm mại, hỗ trợ giảm áp lực cho bàn chân, phù hợp cho những người phải di chuyển thường xuyên.</p></li><li><p data-path-to-node=\"12,2,0\"><b data-path-to-node=\"12,2,0\" data-index-in-node=\"0\">Phong cách linh hoạt:</b> Phù hợp để phối cùng quần Tây, chân váy bút chì cho phong cách công sở, hoặc kết hợp với tất cao cổ và chân váy ngắn cho phong cách Preppy trẻ trung.</p></li><li><p data-path-to-node=\"12,3,0\"><b data-path-to-node=\"12,3,0\" data-index-in-node=\"0\">Độ bền cao:</b> Chất liệu da bóng không chỉ sang trọng mà còn giúp bảo vệ đôi giày khỏi các tác động từ môi trường tốt hơn da thường.</p></li>', '<p><b>Cách chọn size:</b></p><li data-section-id=\"kunj93\" data-start=\"261\" data-end=\"313\">Đo chiều dài bàn chân (từ gót đến ngón dài nhất)\n</li><li data-section-id=\"anugdh\" data-start=\"314\" data-end=\"350\">\nĐối chiếu với bảng size bên trên\n</li><p>\n\n</p><li data-section-id=\"4j7cdn\" data-start=\"351\" data-end=\"405\">\nNếu chân rộng hoặc mu cao, nên chọn lớn hơn 1 size</li><li data-section-id=\"4j7cdn\" data-start=\"351\" data-end=\"405\"><br></li><li data-section-id=\"4j7cdn\" data-start=\"351\" data-end=\"405\"><b>Bảng size giày nữ:</b></li><li data-section-id=\"27ic78\" data-start=\"169\" data-end=\"189\">Size 35: 22.5 cm\n</li>\n<li data-section-id=\"188cgf7\" data-start=\"190\" data-end=\"210\">\nSize 36: 23.0 cm\n</li>\n<li data-section-id=\"1fd5xgn\" data-start=\"211\" data-end=\"231\">\nSize 37: 23.5 cm</li>', '<p><b data-path-to-node=\"20,0,0\" data-index-in-node=\"0\">Thời hạn:</b> Hỗ trợ đổi trả trong vòng <b data-path-to-node=\"20,0,0\" data-index-in-node=\"36\">7 ngày</b> kể từ khi nhận hàng.</p>', 0, 0, 1, 81),
(131, 'áy Ngủ Lụa Phối Ren Cao Cấp D5102', 'SP00131', 'sp', '500000', 340000.0000, 30.00, 5, '442000', 30, 'product-featured-131.png', '<p data-path-to-node=\"7\">Váy ngủ D5102 là biểu tượng của sự nữ tính và sang trọng. Với thiết kế dáng xòe nhẹ nhàng kết hợp cùng phần cúp ngực tinh xảo, sản phẩm không chỉ mang lại sự thoải mái tuyệt đối cho giấc ngủ mà còn giúp phái đẹp tự tin hơn với vẻ ngoài quyến rũ.</p><p data-path-to-node=\"8\">Phần thân váy được làm từ lụa cao cấp có độ rủ tự nhiên, phối cùng lớp lưới ren thêu hoa tỉ mỉ, tạo hiệu ứng nửa kín nửa hở đầy mê hoặc. Đây là lựa chọn lý tưởng cho những đêm lãng mạn hoặc đơn giản là tự thưởng cho bản thân một cảm giác thư thái sau ngày dài.</p>', '<p>Nâng tầm vẻ đẹp kiều diễm ngay tại phòng ngủ với mẫu váy ngủ <b data-path-to-node=\"4\" data-index-in-node=\"61\">D5102</b>. Sự kết hợp hoàn hảo giữa chất liệu lụa satin mềm mịn và ren hoa cao cấp, thiết kế tôn vinh đường cong, mang lại cảm giác nhẹ nhàng, bay bổng và đầy lôi cuốn.</p>', '<li><p data-path-to-node=\"11,0,0\"><b data-path-to-node=\"11,0,0\" data-index-in-node=\"0\">Thiết kế cúp ngực:</b> Giúp nâng form tự nhiên, tạo điểm nhấn gợi cảm.</p></li><li><p data-path-to-node=\"11,1,0\"><b data-path-to-node=\"11,1,0\" data-index-in-node=\"0\">Chi tiết ren tinh xảo:</b> Phần ren thêu hoa được điểm xuyết ở eo và chân váy tạo sự sang trọng, cao cấp.</p></li><li><p data-path-to-node=\"11,2,0\"><b data-path-to-node=\"11,2,0\" data-index-in-node=\"0\">Dây vai điệu đà:</b> Thiết kế dây mảnh phối bèo ren mềm mại, có thể điều chỉnh độ dài phù hợp với cơ thể.</p></li><li><p data-path-to-node=\"11,3,0\"><b data-path-to-node=\"11,3,0\" data-index-in-node=\"0\">Chất liệu êm ái:</b> Lụa satin mềm mượt, thoáng khí, không gây kích ứng da.</p></li><li><p data-path-to-node=\"11,4,0\"><b data-path-to-node=\"11,4,0\" data-index-in-node=\"0\">Màu sắc cơ bản:</b> </p><p data-path-to-node=\"11,4,0\"><span data-path-to-node=\"11,4,0\" data-index-in-node=\"18\" style=\"\">Trắng tinh khôi:</span> Dành cho vẻ đẹp thanh lịch, nhẹ nhàng.</p><p data-path-to-node=\"11,4,0\"><span data-path-to-node=\"11,4,1,0,0\" data-index-in-node=\"0\" style=\"\">Đen huyền bí:</span> Tôn da và tăng thêm phần quyến rũ, sắc sảo.</p></li>', '<p data-path-to-node=\"14\">Bạn có thể tham khảo bảng size chuẩn dưới đây (tùy thuộc vào chiều cao):</p><p data-path-to-node=\"14\"><b data-path-to-node=\"15,0,0\" data-index-in-node=\"0\">Size S:</b> Cân nặng từ 42kg - 48kg.</p><p data-path-to-node=\"14\"><b data-path-to-node=\"15,1,0\" data-index-in-node=\"0\">Size M:</b> Cân nặng từ 49kg - 54kg.</p><p data-path-to-node=\"14\"><b data-path-to-node=\"15,2,0\" data-index-in-node=\"0\">Size L:</b> Cân nặng từ 55kg - 60kg.</p><p data-path-to-node=\"16\"><i data-path-to-node=\"16\" data-index-in-node=\"0\">(Lưu ý: Nếu bạn có số đo vòng 1 lớn hoặc thích mặc rộng rãi thoải mái, hãy cân nhắc chọn tăng lên 1 size).</i></p>', '<p><b data-path-to-node=\"20,0,0\" data-index-in-node=\"0\">Thời gian đổi trả:</b> Trong vòng 07 ngày kể từ ngày nhận hàng.</p>', 0, 1, 1, 32);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_product_photo`
--

CREATE TABLE `tbl_product_photo` (
  `pp_id` int(11) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `p_id` int(11) NOT NULL,
  `color_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_product_photo`
--

INSERT INTO `tbl_product_photo` (`pp_id`, `photo`, `p_id`, `color_id`) VALUES
(139, 'product-photo-114-20260331193853-7949-0.png', 114, 6),
(140, 'product-photo-114-20260331193853-6667-0.png', 114, 1),
(141, 'product-photo-114-20260331193853-7118-0.png', 114, 2),
(142, 'product-photo-115-20260408093243-6345-0.png', 115, 12),
(143, 'product-photo-115-20260408093243-7278-0.png', 115, 2),
(144, 'product-photo-116-20260408194503-9443-0.png', 116, 2),
(145, 'product-photo-116-20260408194503-7673-0.png', 116, 15),
(146, 'product-photo-116-20260408194503-8248-0.png', 116, 6),
(147, 'product-photo-117-20260408194925-2887-0.png', 117, 2),
(148, 'product-photo-117-20260408194925-3595-0.png', 117, 6),
(149, 'product-photo-117-20260408194925-2131-0.png', 117, 12),
(150, 'product-photo-118-20260408195529-9102-0.png', 118, 30),
(151, 'product-photo-118-20260408195529-2328-0.png', 118, 6),
(152, 'product-photo-119-20260408200101-2680-0.png', 119, 29),
(153, 'product-photo-119-20260408200101-6791-0.png', 119, 2),
(154, 'product-photo-120-20260408200439-9634-0.png', 120, 30),
(155, 'product-photo-120-20260408200439-6870-0.png', 120, 2),
(156, 'product-photo-121-20260408200748-5910-0.png', 121, 2),
(157, 'product-photo-121-20260408200748-4610-0.png', 121, 6),
(158, 'product-photo-122-20260408201144-7605-0.png', 122, 6),
(159, 'product-photo-123-20260408201543-2235-0.png', 123, 6),
(160, 'product-photo-123-20260408201543-7055-0.png', 123, 3),
(161, 'product-photo-124-20260408201920-8634-0.png', 124, 30),
(162, 'product-photo-124-20260408201920-9476-0.png', 124, 6),
(163, 'product-photo-125-20260408202249-1808-0.png', 125, 2),
(164, 'product-photo-126-20260408202634-4338-0.png', 126, 8),
(165, 'product-photo-126-20260408202634-7047-0.png', 126, 2),
(166, 'product-photo-127-20260408203121-9008-0.png', 127, 30),
(167, 'product-photo-127-20260408203121-6808-0.png', 127, 2),
(168, 'product-photo-128-20260408203552-1960-0.png', 128, 2),
(169, 'product-photo-129-20260408211149-4480-0.png', 129, 9),
(170, 'product-photo-129-20260408211149-5480-0.png', 129, 6),
(171, 'product-photo-130-20260408211929-7396-0.png', 130, 2),
(172, 'product-photo-130-20260408211929-2590-0.png', 130, 30),
(173, 'product-photo-131-20260408212424-9287-0.png', 131, 2),
(174, 'product-photo-131-20260408212424-6521-0.png', 131, 6);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_product_variant`
--

CREATE TABLE `tbl_product_variant` (
  `pv_id` int(11) NOT NULL,
  `p_id` int(11) NOT NULL,
  `size_id` int(11) NOT NULL,
  `color_id` int(11) NOT NULL,
  `pv_qty` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_product_variant`
--

INSERT INTO `tbl_product_variant` (`pv_id`, `p_id`, `size_id`, `color_id`, `pv_qty`) VALUES
(46, 114, 1, 6, 5),
(47, 114, 2, 6, 5),
(48, 114, 3, 6, 5),
(49, 114, 1, 2, 4),
(50, 114, 2, 2, 5),
(51, 114, 4, 2, 5),
(52, 114, 1, 1, 3),
(53, 114, 2, 1, 5),
(54, 114, 3, 1, 3),
(111, 128, 4, 2, 5),
(112, 127, 1, 2, 5),
(113, 127, 1, 30, 5),
(114, 127, 2, 2, 5),
(115, 127, 2, 30, 5),
(116, 126, 1, 2, 5),
(117, 126, 1, 8, 5),
(118, 126, 2, 2, 5),
(119, 126, 2, 8, 5),
(120, 125, 1, 2, 5),
(121, 125, 3, 2, 5),
(122, 125, 4, 2, 5),
(123, 124, 1, 6, 5),
(124, 124, 1, 30, 5),
(125, 124, 2, 6, 5),
(126, 124, 2, 30, 0),
(127, 123, 1, 3, 5),
(128, 123, 1, 6, 5),
(129, 123, 2, 3, 5),
(130, 123, 2, 6, 5),
(131, 122, 2, 6, 5),
(132, 122, 3, 6, 5),
(133, 122, 4, 6, 5),
(134, 121, 1, 2, 5),
(135, 121, 1, 6, 5),
(136, 121, 2, 2, 5),
(137, 121, 2, 6, 5),
(138, 120, 2, 2, 5),
(139, 120, 2, 30, 5),
(140, 120, 3, 2, 5),
(141, 120, 4, 30, 5),
(142, 119, 1, 2, 5),
(143, 119, 1, 29, 5),
(144, 119, 2, 2, 5),
(145, 119, 2, 29, 5),
(146, 118, 1, 6, 5),
(147, 118, 1, 30, 5),
(148, 118, 2, 6, 5),
(149, 118, 2, 30, 5),
(150, 117, 1, 2, 5),
(151, 117, 1, 6, 5),
(152, 117, 1, 12, 5),
(153, 117, 2, 2, 5),
(154, 117, 2, 6, 5),
(155, 117, 2, 12, 5),
(156, 116, 1, 2, 5),
(157, 116, 1, 6, 5),
(158, 116, 1, 15, 5),
(159, 116, 2, 2, 5),
(160, 116, 2, 6, 5),
(161, 116, 2, 15, 5),
(162, 115, 2, 2, 5),
(163, 115, 2, 12, 5),
(164, 115, 3, 2, 5),
(165, 115, 3, 12, 5),
(166, 115, 4, 2, 5),
(167, 115, 4, 12, 5),
(168, 129, 49, 6, 5),
(169, 129, 49, 9, 5),
(170, 130, 12, 2, 5),
(171, 130, 13, 2, 5),
(172, 130, 12, 30, 5),
(173, 130, 13, 30, 5),
(174, 131, 2, 2, 5),
(175, 131, 3, 2, 5),
(176, 131, 4, 2, 5),
(177, 131, 2, 6, 5),
(178, 131, 3, 6, 5),
(179, 131, 4, 6, 5);

--
-- Bẫy `tbl_product_variant`
--
DELIMITER $$
CREATE TRIGGER `trg_variant_ad_sync_product_qty` AFTER DELETE ON `tbl_product_variant` FOR EACH ROW BEGIN
  UPDATE tbl_product
  SET p_qty = (
    SELECT IFNULL(SUM(pv_qty), 0)
    FROM tbl_product_variant
    WHERE p_id = OLD.p_id
  )
  WHERE p_id = OLD.p_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_variant_ai_sync_product_qty` AFTER INSERT ON `tbl_product_variant` FOR EACH ROW BEGIN
  UPDATE tbl_product
  SET p_qty = (
    SELECT IFNULL(SUM(pv_qty), 0)
    FROM tbl_product_variant
    WHERE p_id = NEW.p_id
  )
  WHERE p_id = NEW.p_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_variant_au_sync_product_qty` AFTER UPDATE ON `tbl_product_variant` FOR EACH ROW BEGIN
  UPDATE tbl_product
  SET p_qty = (
    SELECT IFNULL(SUM(pv_qty), 0)
    FROM tbl_product_variant
    WHERE p_id = NEW.p_id
  )
  WHERE p_id = NEW.p_id;

  IF OLD.p_id <> NEW.p_id THEN
    UPDATE tbl_product
    SET p_qty = (
      SELECT IFNULL(SUM(pv_qty), 0)
      FROM tbl_product_variant
      WHERE p_id = OLD.p_id
    )
    WHERE p_id = OLD.p_id;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_rating`
--

CREATE TABLE `tbl_rating` (
  `rt_id` int(11) NOT NULL,
  `p_id` int(11) NOT NULL,
  `cust_id` int(11) NOT NULL,
  `comment` mediumtext NOT NULL,
  `rating` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_service`
--

CREATE TABLE `tbl_service` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` mediumtext NOT NULL,
  `photo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_service`
--

INSERT INTO `tbl_service` (`id`, `title`, `content`, `photo`) VALUES
(5, 'Đổi trả dễ dàng', 'Hỗ trợ đổi trả nhanh chóng, thủ tục đơn giản và minh bạch.', 'service-5.png'),
(6, 'Miễn phí vận chuyển', 'Áp dụng miễn phí vận chuyển cho đơn hàng đủ điều kiện theo chính sách cửa hàng.', 'service-6.png'),
(7, 'Giao hàng nhanh', 'Đơn hàng được xử lý nhanh, giao đúng hẹn và theo dõi thuận tiện.', 'service-7.png'),
(8, 'Cam kết hài lòng', 'Chúng tôi luôn ưu tiên trải nghiệm mua sắm và sự hài lòng của khách hàng.', 'service-8.png'),
(9, 'Thanh toán an toàn', 'Nhiều hình thức thanh toán bảo mật giúp bạn yên tâm khi mua sắm.', 'service-9.png'),
(10, 'Cam kết hoàn tiền', 'Hỗ trợ hoàn tiền theo chính sách khi đơn hàng đủ điều kiện.', 'service-10.png');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_settings`
--

CREATE TABLE `tbl_settings` (
  `id` int(11) NOT NULL,
  `logo` mediumtext NOT NULL,
  `favicon` mediumtext NOT NULL,
  `footer_about` mediumtext NOT NULL,
  `footer_copyright` mediumtext NOT NULL,
  `contact_address` mediumtext NOT NULL,
  `contact_email` mediumtext NOT NULL,
  `contact_phone` mediumtext NOT NULL,
  `contact_fax` mediumtext NOT NULL,
  `contact_map_iframe` mediumtext NOT NULL,
  `receive_email` mediumtext NOT NULL,
  `receive_email_subject` mediumtext NOT NULL,
  `receive_email_thank_you_message` mediumtext NOT NULL,
  `forget_password_message` mediumtext NOT NULL,
  `total_recent_post_footer` int(10) NOT NULL,
  `total_popular_post_footer` int(10) NOT NULL,
  `total_recent_post_sidebar` int(11) NOT NULL,
  `total_popular_post_sidebar` int(11) NOT NULL,
  `total_featured_product_home` int(11) NOT NULL,
  `total_latest_product_home` int(11) NOT NULL,
  `total_popular_product_home` int(11) NOT NULL,
  `meta_title_home` mediumtext NOT NULL,
  `meta_keyword_home` mediumtext NOT NULL,
  `meta_description_home` mediumtext NOT NULL,
  `banner_login` mediumtext NOT NULL,
  `banner_registration` mediumtext NOT NULL,
  `banner_forget_password` mediumtext NOT NULL,
  `banner_reset_password` mediumtext NOT NULL,
  `banner_search` mediumtext NOT NULL,
  `banner_cart` mediumtext NOT NULL,
  `banner_checkout` mediumtext NOT NULL,
  `banner_product_category` mediumtext NOT NULL,
  `banner_blog` mediumtext NOT NULL,
  `cta_title` mediumtext NOT NULL,
  `cta_content` mediumtext NOT NULL,
  `cta_read_more_text` mediumtext NOT NULL,
  `cta_read_more_url` mediumtext NOT NULL,
  `cta_photo` mediumtext NOT NULL,
  `featured_product_title` mediumtext NOT NULL,
  `featured_product_subtitle` mediumtext NOT NULL,
  `latest_product_title` mediumtext NOT NULL,
  `latest_product_subtitle` mediumtext NOT NULL,
  `popular_product_title` mediumtext NOT NULL,
  `popular_product_subtitle` mediumtext NOT NULL,
  `testimonial_title` mediumtext NOT NULL,
  `testimonial_subtitle` mediumtext NOT NULL,
  `testimonial_photo` mediumtext NOT NULL,
  `blog_title` mediumtext NOT NULL,
  `blog_subtitle` mediumtext NOT NULL,
  `newsletter_text` mediumtext NOT NULL,
  `paypal_email` mediumtext NOT NULL,
  `stripe_public_key` mediumtext NOT NULL,
  `stripe_secret_key` mediumtext NOT NULL,
  `bank_detail` mediumtext NOT NULL,
  `home_service_on_off` int(11) NOT NULL,
  `home_welcome_on_off` int(11) NOT NULL,
  `home_featured_product_on_off` int(11) NOT NULL,
  `home_latest_product_on_off` int(11) NOT NULL,
  `home_popular_product_on_off` int(11) NOT NULL,
  `home_testimonial_on_off` int(11) NOT NULL,
  `home_blog_on_off` int(11) NOT NULL,
  `ads_above_welcome_on_off` int(1) NOT NULL,
  `ads_above_featured_product_on_off` int(1) NOT NULL,
  `ads_above_latest_product_on_off` int(1) NOT NULL,
  `ads_above_popular_product_on_off` int(1) NOT NULL,
  `ads_above_testimonial_on_off` int(1) NOT NULL,
  `ads_category_sidebar_on_off` int(1) NOT NULL,
  `cod_on_off` tinyint(1) NOT NULL DEFAULT 1,
  `paypal_on_off` tinyint(1) NOT NULL DEFAULT 0,
  `paypal_client_id` varchar(255) DEFAULT NULL,
  `paypal_client_secret` varchar(255) DEFAULT NULL,
  `paypal_env` varchar(20) NOT NULL DEFAULT 'sandbox',
  `paypal_currency` varchar(10) NOT NULL DEFAULT 'USD',
  `paypal_exchange_rate` decimal(15,6) NOT NULL DEFAULT 24000.000000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_settings`
--

INSERT INTO `tbl_settings` (`id`, `logo`, `favicon`, `footer_about`, `footer_copyright`, `contact_address`, `contact_email`, `contact_phone`, `contact_fax`, `contact_map_iframe`, `receive_email`, `receive_email_subject`, `receive_email_thank_you_message`, `forget_password_message`, `total_recent_post_footer`, `total_popular_post_footer`, `total_recent_post_sidebar`, `total_popular_post_sidebar`, `total_featured_product_home`, `total_latest_product_home`, `total_popular_product_home`, `meta_title_home`, `meta_keyword_home`, `meta_description_home`, `banner_login`, `banner_registration`, `banner_forget_password`, `banner_reset_password`, `banner_search`, `banner_cart`, `banner_checkout`, `banner_product_category`, `banner_blog`, `cta_title`, `cta_content`, `cta_read_more_text`, `cta_read_more_url`, `cta_photo`, `featured_product_title`, `featured_product_subtitle`, `latest_product_title`, `latest_product_subtitle`, `popular_product_title`, `popular_product_subtitle`, `testimonial_title`, `testimonial_subtitle`, `testimonial_photo`, `blog_title`, `blog_subtitle`, `newsletter_text`, `paypal_email`, `stripe_public_key`, `stripe_secret_key`, `bank_detail`, `home_service_on_off`, `home_welcome_on_off`, `home_featured_product_on_off`, `home_latest_product_on_off`, `home_popular_product_on_off`, `home_testimonial_on_off`, `home_blog_on_off`, `ads_above_welcome_on_off`, `ads_above_featured_product_on_off`, `ads_above_latest_product_on_off`, `ads_above_popular_product_on_off`, `ads_above_testimonial_on_off`, `ads_category_sidebar_on_off`, `cod_on_off`, `paypal_on_off`, `paypal_client_id`, `paypal_client_secret`, `paypal_env`, `paypal_currency`, `paypal_exchange_rate`) VALUES
(1, 'logo.png', 'favicon.png', '<p>Lorem ipsum dolor sit amet, omnis signiferumque in mei, mei ex enim concludaturque. Senserit salutandi euripidis no per, modus maiestatis scribentur est an.Â Ea suas pertinax has.</p>\r\n', '© 2026 - Wizard Ecommerce. Bảo lưu mọi quyền.<br>Phát triển bởi Trần Hữu Nam.<br>Website được xây dựng trong khuôn khổ môn học Phát triển Web, nhằm mục đích nghiên cứu và thực hành xây dựng hệ thống thương mại điện tử.<br>Dự án được thực hiện phục vụ mục đích học tập.', '93 Simpson Avenue\r\nHarrisburg, PA', 'support@ecommercephp.com', '+0999999999', '', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3094.020958405712!2d-84.39261378514685!3d39.151504939531584!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8841acfb8da30203%3A0x193175e741781f21!2s4293%20Simpson%20Ave%2C%20Cincinnati%2C%20OH%2045227%2C%20USA!5e0!3m2!1sen!2snp!4v1647796779407!5m2!1sen!2snp\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\"></iframe>', 'admin@gmail.com', 'Tin nhắn email từ khách truy cập trên trang thương mại điện tử', 'Cảm ơn bạn đã gửi email. Chúng tôi sẽ liên hệ lại với bạn trong thời gian ngắn.', '\r\nMột đường dẫn xác nhận sẽ được gửi đến địa chỉ email của bạn. Bạn sẽ nhận được thông tin về cách đặt lại mật khẩu trong đó.', 4, 4, 5, 5, 5, 6, 8, 'Ecommerce PHP', 'online fashion store, garments shop, online garments', 'ecommerce php project with mysql database', 'banner_login.jpg', 'banner_registration.jpg', 'banner_forget_password.jpg', 'banner_reset_password.jpg', 'banner_search.jpg', 'banner_cart.jpg', 'banner_checkout.jpg', 'banner_product_category.jpg', 'banner_blog.jpg', 'Welcome To Our Ecommerce Website', 'Lorem ipsum dolor sit amet, an labores explicari qui, eu nostrum copiosae argumentum has. Latine propriae quo no, unum ridens expetenda id sit, \r\nat usu eius eligendi singulis. Sea ocurreret principes ne. At nonumy aperiri pri, nam quodsi copiosae intellegebat et, ex deserunt euripidis usu. ', 'Read More', '#', 'cta.jpg', 'Sản phẩm nổi bật', 'Những sản phẩm mới cập nhật gần đây', 'Sản phẩm mới nhất', 'Khám phá các sản phẩm vừa lên kệ', 'Sản phẩm phổ biến', 'Các sản phẩm được khách hàng yêu thích', 'Testimonials', 'See what our clients tell about us', 'testimonial.jpg', 'Latest Blog', 'See all our latest articles and news from below', 'Sign-up to our newsletter for latest promotions and discounts.', 'admin@ecom.com', 'STRIPE_PUBLIC_KEY_PLACEHOLDER', 'STRIPE_SECRET_KEY_PLACEHOLDER', 'Bank Name: WestView Bank\r\nAccount Number: CA100270589600\r\nBranch Name: CA Branch\r\nCountry: USA', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 'PAYPAL_CLIENT_ID_PLACEHOLDER', 'PAYPAL_CLIENT_SECRET_PLACEHOLDER', 'sandbox', 'USD', 25000.000000);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_shipping_cost_all`
--

CREATE TABLE `tbl_shipping_cost_all` (
  `sca_id` int(11) NOT NULL,
  `amount` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_shipping_cost_all`
--

INSERT INTO `tbl_shipping_cost_all` (`sca_id`, `amount`) VALUES
(1, '100');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_size`
--

CREATE TABLE `tbl_size` (
  `size_id` int(11) NOT NULL,
  `size_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_size`
--

INSERT INTO `tbl_size` (`size_id`, `size_name`) VALUES
(1, 'XS'),
(2, 'S'),
(3, 'M'),
(4, 'L'),
(5, 'XL'),
(6, 'XXL'),
(7, '3XL'),
(8, '31'),
(9, '32'),
(10, '33'),
(11, '34'),
(12, '35'),
(13, '36'),
(14, '37'),
(15, '38'),
(16, '39'),
(17, '40'),
(18, '41'),
(19, '42'),
(20, '43'),
(21, '44'),
(22, '45'),
(23, '46'),
(24, '47'),
(25, '48'),
(28, '10'),
(29, '12 tháng'),
(30, '2T'),
(31, '3T'),
(32, '4T'),
(33, '5T'),
(34, '6 tuổi'),
(35, '7 tuổi'),
(36, '8 tuổi'),
(37, '10 tuổi'),
(38, '12 tuổi'),
(39, '14 tuổi'),
(40, '256 GB'),
(41, '128 GB'),
(42, '14 Plus'),
(43, '16 Plus'),
(44, '18 Plus'),
(45, '20 Plus'),
(46, '22 Plus'),
(47, '24 Plus'),
(48, '26 Plus'),
(49, 'No size');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_slider`
--

CREATE TABLE `tbl_slider` (
  `id` int(11) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `heading` varchar(255) NOT NULL,
  `content` mediumtext NOT NULL,
  `button_text` varchar(255) NOT NULL,
  `button_url` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_slider`
--

INSERT INTO `tbl_slider` (`id`, `photo`, `heading`, `content`, `button_text`, `button_url`, `position`) VALUES
(1, 'slider-1.png', 'Chào mừng bạn đến với cửa hàng trực tuyến', 'Mua sắm phụ kiện nữ mới nhất', 'Xem phụ kiện nữ', 'product-category.php?id=4&type=mid-category', 'Center'),
(2, 'slider-2.jpg', 'Giảm giá 50% cho tất cả sản phẩm', 'Ưu đãi hấp dẫn mỗi ngày, mua sắm dễ dàng với mức giá tốt nhất.', 'Xem ngay', '#', 'Center'),
(3, 'slider-3.png', 'Hỗ trợ khách hàng 24/7', 'Đội ngũ tư vấn luôn sẵn sàng hỗ trợ bạn mọi lúc, mọi nơi.', 'Liên hệ ngay', '#', 'Center');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_top_category`
--

CREATE TABLE `tbl_top_category` (
  `tcat_id` int(11) NOT NULL,
  `tcat_name` varchar(255) NOT NULL,
  `show_on_menu` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_top_category`
--

INSERT INTO `tbl_top_category` (`tcat_id`, `tcat_name`, `show_on_menu`) VALUES
(1, 'Nam', 1),
(2, 'Nữ', 1),
(3, 'Trẻ em', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tbl_user`
--

CREATE TABLE `tbl_user` (
  `id` int(10) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `role` varchar(30) NOT NULL,
  `status` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tbl_user`
--

INSERT INTO `tbl_user` (`id`, `full_name`, `email`, `phone`, `password`, `photo`, `role`, `status`) VALUES
(1, 'Administrator', 'admin@mail.com', '7777777777', 'd00f5d5217896fb7fd601412cb890830', 'user-1.png', 'Super Admin', 'Active'),
(2, 'Mèo Mù Văn Học', 'admin@gmail.com', '0999999999', '7488e331b8b64e5794da3fa4eb10ad5d', 'user-2.jpg', 'Admin', 'Active'),
(3, 'Flow Test Admin', 'flowtest.admin@example.com', '0900000000', '0e7517141fb53f21ee439b355b5a1d0a', '', 'Admin', 'Active');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `tbl_color`
--
ALTER TABLE `tbl_color`
  ADD PRIMARY KEY (`color_id`);

--
-- Chỉ mục cho bảng `tbl_customer`
--
ALTER TABLE `tbl_customer`
  ADD PRIMARY KEY (`cust_id`),
  ADD UNIQUE KEY `uq_tbl_customer_email` (`cust_email`);

--
-- Chỉ mục cho bảng `tbl_customer_address`
--
ALTER TABLE `tbl_customer_address`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `idx_customer_address_cust` (`cust_id`),
  ADD KEY `idx_customer_address_default` (`cust_id`,`is_default`);

--
-- Chỉ mục cho bảng `tbl_end_category`
--
ALTER TABLE `tbl_end_category`
  ADD PRIMARY KEY (`ecat_id`),
  ADD KEY `idx_tbl_end_category_mcat_id` (`mcat_id`);

--
-- Chỉ mục cho bảng `tbl_import_receipt`
--
ALTER TABLE `tbl_import_receipt`
  ADD PRIMARY KEY (`receipt_id`),
  ADD UNIQUE KEY `uk_receipt_code` (`receipt_code`),
  ADD KEY `idx_import_receipt_created_by` (`created_by`);

--
-- Chỉ mục cho bảng `tbl_import_receipt_item`
--
ALTER TABLE `tbl_import_receipt_item`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `idx_receipt_id` (`receipt_id`),
  ADD KEY `idx_product_id` (`p_id`),
  ADD KEY `idx_receipt_product_variant` (`receipt_id`,`p_id`,`size_id`,`color_id`);

--
-- Chỉ mục cho bảng `tbl_mid_category`
--
ALTER TABLE `tbl_mid_category`
  ADD PRIMARY KEY (`mcat_id`),
  ADD KEY `idx_tbl_mid_category_tcat_id` (`tcat_id`);

--
-- Chỉ mục cho bảng `tbl_order`
--
ALTER TABLE `tbl_order`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tbl_order_product_id` (`product_id`),
  ADD KEY `idx_tbl_order_payment_id` (`payment_id`);

--
-- Chỉ mục cho bảng `tbl_page`
--
ALTER TABLE `tbl_page`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `tbl_payment`
--
ALTER TABLE `tbl_payment`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_tbl_payment_payment_id` (`payment_id`);

--
-- Chỉ mục cho bảng `tbl_photo`
--
ALTER TABLE `tbl_photo`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `tbl_product`
--
ALTER TABLE `tbl_product`
  ADD PRIMARY KEY (`p_id`),
  ADD KEY `idx_tbl_product_ecat_id` (`ecat_id`);

--
-- Chỉ mục cho bảng `tbl_product_photo`
--
ALTER TABLE `tbl_product_photo`
  ADD PRIMARY KEY (`pp_id`),
  ADD KEY `idx_product_photo_product_color` (`p_id`,`color_id`),
  ADD KEY `idx_tbl_product_photo_color_id` (`color_id`);

--
-- Chỉ mục cho bảng `tbl_product_variant`
--
ALTER TABLE `tbl_product_variant`
  ADD PRIMARY KEY (`pv_id`),
  ADD UNIQUE KEY `uniq_product_size_color` (`p_id`,`size_id`,`color_id`),
  ADD KEY `idx_product` (`p_id`),
  ADD KEY `idx_size` (`size_id`),
  ADD KEY `idx_color` (`color_id`);

--
-- Chỉ mục cho bảng `tbl_rating`
--
ALTER TABLE `tbl_rating`
  ADD PRIMARY KEY (`rt_id`),
  ADD UNIQUE KEY `uq_tbl_rating_product_customer` (`p_id`,`cust_id`),
  ADD KEY `idx_tbl_rating_cust_id` (`cust_id`);

--
-- Chỉ mục cho bảng `tbl_service`
--
ALTER TABLE `tbl_service`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `tbl_settings`
--
ALTER TABLE `tbl_settings`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `tbl_shipping_cost_all`
--
ALTER TABLE `tbl_shipping_cost_all`
  ADD PRIMARY KEY (`sca_id`);

--
-- Chỉ mục cho bảng `tbl_size`
--
ALTER TABLE `tbl_size`
  ADD PRIMARY KEY (`size_id`);

--
-- Chỉ mục cho bảng `tbl_slider`
--
ALTER TABLE `tbl_slider`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `tbl_top_category`
--
ALTER TABLE `tbl_top_category`
  ADD PRIMARY KEY (`tcat_id`);

--
-- Chỉ mục cho bảng `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_tbl_user_email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `tbl_color`
--
ALTER TABLE `tbl_color`
  MODIFY `color_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT cho bảng `tbl_customer`
--
ALTER TABLE `tbl_customer`
  MODIFY `cust_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `tbl_customer_address`
--
ALTER TABLE `tbl_customer_address`
  MODIFY `address_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `tbl_end_category`
--
ALTER TABLE `tbl_end_category`
  MODIFY `ecat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT cho bảng `tbl_import_receipt`
--
ALTER TABLE `tbl_import_receipt`
  MODIFY `receipt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `tbl_import_receipt_item`
--
ALTER TABLE `tbl_import_receipt_item`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `tbl_mid_category`
--
ALTER TABLE `tbl_mid_category`
  MODIFY `mcat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT cho bảng `tbl_order`
--
ALTER TABLE `tbl_order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `tbl_page`
--
ALTER TABLE `tbl_page`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `tbl_payment`
--
ALTER TABLE `tbl_payment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT cho bảng `tbl_photo`
--
ALTER TABLE `tbl_photo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `tbl_product`
--
ALTER TABLE `tbl_product`
  MODIFY `p_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- AUTO_INCREMENT cho bảng `tbl_product_photo`
--
ALTER TABLE `tbl_product_photo`
  MODIFY `pp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=175;

--
-- AUTO_INCREMENT cho bảng `tbl_product_variant`
--
ALTER TABLE `tbl_product_variant`
  MODIFY `pv_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=180;

--
-- AUTO_INCREMENT cho bảng `tbl_rating`
--
ALTER TABLE `tbl_rating`
  MODIFY `rt_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `tbl_service`
--
ALTER TABLE `tbl_service`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `tbl_settings`
--
ALTER TABLE `tbl_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `tbl_shipping_cost_all`
--
ALTER TABLE `tbl_shipping_cost_all`
  MODIFY `sca_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `tbl_size`
--
ALTER TABLE `tbl_size`
  MODIFY `size_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT cho bảng `tbl_slider`
--
ALTER TABLE `tbl_slider`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `tbl_top_category`
--
ALTER TABLE `tbl_top_category`
  MODIFY `tcat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `tbl_customer_address`
--
ALTER TABLE `tbl_customer_address`
  ADD CONSTRAINT `fk_customer_address_customer` FOREIGN KEY (`cust_id`) REFERENCES `tbl_customer` (`cust_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `tbl_end_category`
--
ALTER TABLE `tbl_end_category`
  ADD CONSTRAINT `fk_tbl_end_category_mid` FOREIGN KEY (`mcat_id`) REFERENCES `tbl_mid_category` (`mcat_id`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `tbl_import_receipt`
--
ALTER TABLE `tbl_import_receipt`
  ADD CONSTRAINT `fk_import_receipt_created_by_user` FOREIGN KEY (`created_by`) REFERENCES `tbl_user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `tbl_import_receipt_item`
--
ALTER TABLE `tbl_import_receipt_item`
  ADD CONSTRAINT `fk_import_receipt_item_receipt` FOREIGN KEY (`receipt_id`) REFERENCES `tbl_import_receipt` (`receipt_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `tbl_mid_category`
--
ALTER TABLE `tbl_mid_category`
  ADD CONSTRAINT `fk_tbl_mid_category_top` FOREIGN KEY (`tcat_id`) REFERENCES `tbl_top_category` (`tcat_id`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `tbl_order`
--
ALTER TABLE `tbl_order`
  ADD CONSTRAINT `fk_tbl_order_payment` FOREIGN KEY (`payment_id`) REFERENCES `tbl_payment` (`payment_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tbl_order_product` FOREIGN KEY (`product_id`) REFERENCES `tbl_product` (`p_id`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `tbl_product`
--
ALTER TABLE `tbl_product`
  ADD CONSTRAINT `fk_tbl_product_end_category` FOREIGN KEY (`ecat_id`) REFERENCES `tbl_end_category` (`ecat_id`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `tbl_product_photo`
--
ALTER TABLE `tbl_product_photo`
  ADD CONSTRAINT `fk_tbl_product_photo_color` FOREIGN KEY (`color_id`) REFERENCES `tbl_color` (`color_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tbl_product_photo_product` FOREIGN KEY (`p_id`) REFERENCES `tbl_product` (`p_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `tbl_product_variant`
--
ALTER TABLE `tbl_product_variant`
  ADD CONSTRAINT `fk_tbl_product_variant_color` FOREIGN KEY (`color_id`) REFERENCES `tbl_color` (`color_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tbl_product_variant_product` FOREIGN KEY (`p_id`) REFERENCES `tbl_product` (`p_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tbl_product_variant_size` FOREIGN KEY (`size_id`) REFERENCES `tbl_size` (`size_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `tbl_rating`
--
ALTER TABLE `tbl_rating`
  ADD CONSTRAINT `fk_tbl_rating_customer` FOREIGN KEY (`cust_id`) REFERENCES `tbl_customer` (`cust_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tbl_rating_product` FOREIGN KEY (`p_id`) REFERENCES `tbl_product` (`p_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
