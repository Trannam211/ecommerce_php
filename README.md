## Website bán thời trang trực tuyến

Dự án website bán thời trang trực tuyến được xây dựng bằng ngôn ngữ PHP và cơ sở dữ liệu MySQL. Dự án này bao gồm đầy đủ các chức năng cần thiết cho một hệ thống mua sắm trực tuyến và là đồ án học tập cho sinh viên công nghệ thông tin. Nó cung cấp nhiều tính năng cho phép người dùng quản lý sản phẩm và mua sắm trực tuyến.

<img width="1898" height="825" alt="image" src="https://github.com/user-attachments/assets/8068a2cc-8c86-421e-bbac-6ab128d71fa1" />


## Công nghệ sử dụng:

Ngôn ngữ lập trình: PHP
Cơ sở dữ liệu: MySQL
Giao diện người dùng: HTML, CSS và JavaScript. Ngoài ra, dự án có sử dụng Bootstrap để hỗ trợ bố cục giao diện và tăng khả năng tương thích trên nhiều thiết bị.
Triển khai:


## Môi trường hỗ trợ:
Dự án có thể chạy trên Windows, macOS, Linux và truy cập thông qua các trình duyệt phổ biến như Google Chrome, Microsoft Edge và Mozilla Firefox.

## Phạm vi thực hiện:
Trong phạm vi đề tài, hệ thống website bán thời trang được xây dựng theo mô hình thương mại điện tử cơ bản, bao gồm hai nhóm chức năng chính là chức năng dành cho người dùng (end-user) và chức năng dành cho quản trị viên (admin).
### Phạm vi chức năng đối với người dùng (end-user):
- Hiển thị danh sách sản phẩm, duyệt sản phẩm theo danh mục.
- Tìm kiếm và lọc sản phẩm theo nhu cầu.
- Xem chi tiết sản phẩm, hình ảnh và thông tin liên quan.
- Thêm sản phẩm vào giỏ hàng, cập nhật hoặc xóa sản phẩm trong giỏ.
- Thực hiện đặt hàng theo quy trình của hệ thống.
- Đăng ký, đăng nhập, quên mật khẩu và cập nhật thông tin tài khoản.
- Nhập và quản lý thông tin giao hàng, địa chỉ nhận hàng.
- Theo dõi lịch sử mua hàng, xem chi tiết và trạng thái đơn hàng, hủy đơn khi cần.
- Hiển thị các sản phẩm nổi bật, sản phẩm mới và sản phẩm phổ biến.
### Phạm vi chức năng đối với quản trị viên (admin):
- Quản lý sản phẩm (thêm, sửa, xóa, cập nhật thông tin).
- Quản lý danh mục sản phẩm theo nhiều cấp.
- Quản lý thuộc tính sản phẩm (màu sắc, kích cỡ, hình ảnh, giá bán).
- Quản lý nhập hàng theo lô, theo dõi tồn kho.
- Quản lý đơn hàng, trạng thái xử lý và thanh toán.
- Quản lý khách hàng và thông tin tài khoản.
- Quản lý phí vận chuyển và các nội dung hiển thị trên website.
- Thống kê, báo cáo phục vụ công tác quản lý và vận hành.

## Giao diện website:.
### Trang chủ:
#### Hiển thị sản phẩm nổi bật
<img width="1889" height="898" alt="image" src="https://github.com/user-attachments/assets/df86b1ea-75ab-4b5a-bc88-880f6f883ac3" />

#### Hiện thị sản phẩm mới
<img width="1903" height="889" alt="image" src="https://github.com/user-attachments/assets/e278e30e-86e0-4cfe-a147-9da2ef1a30d7" />

#### Hiện thị sản phẩm phổ biến
<img width="1891" height="886" alt="image" src="https://github.com/user-attachments/assets/fb624868-1c81-482e-bcc0-e627bb977ffd" />

### Trang chi tiết sản phẩm:
<img width="1908" height="905" alt="image" src="https://github.com/user-attachments/assets/248a9f5b-272c-4ed9-8336-5b277c6bf2ab" />

### Giỏ hàng:
<img width="1919" height="903" alt="image" src="https://github.com/user-attachments/assets/68da735d-22c6-497e-a46c-c2e7edd12bf1" />

### Đơn hàng:
<img width="1913" height="908" alt="image" src="https://github.com/user-attachments/assets/5b545897-c980-4e10-af7f-c3857d87b79a" />

### Dashboard Admin:
<img width="1916" height="904" alt="image" src="https://github.com/user-attachments/assets/f56a0923-9999-4758-9208-9fce45dbcd06" />

#### Quản lý sản phẩm:
<img width="1911" height="901" alt="image" src="https://github.com/user-attachments/assets/ffc4c06c-12e5-459e-9ed7-32bb00d2d286" />

#### Quản lý nhập hàng:
<img width="1900" height="903" alt="image" src="https://github.com/user-attachments/assets/e39d2d98-6a36-4557-a08a-738e4da5d1f0" />

#### Quản lý đơn hàng:
<img width="1919" height="902" alt="image" src="https://github.com/user-attachments/assets/5de8fe44-4996-4426-b23b-9a644836fa9a" />

#### Quản lý người dùng:
<img width="1898" height="601" alt="image" src="https://github.com/user-attachments/assets/08084ee7-c95f-4de1-9f6c-063b5858186a" />


## Cấu trúc dự án

eCommerce-website-in-PHP-main/
|-- admin/                     # Admin panel (quản lý sản phẩm, đơn hàng, khách hàng, slider,...)
|   |-- css/                   # CSS admin
|   |-- js/                    # JavaScript admin
|   |-- inc/                   # Config, functions, CSRF
|   `-- ...                    # Các trang CRUD và quản lý khác
|
|-- app/
|   |-- controllers/           # Controller (nếu mở rộng kiến trúc)
|   |-- models/                # Model
|   `-- shared/                # Partial frontend dùng chung (header/footer/sidebar)
|
|-- assets/                    # CSS, JS, fonts, hình ảnh, uploads
|
|-- database/
|   `-- ecommerceweb.sql       # SQL dump
|
|-- docs/
|   |-- project-info/          # Hướng dẫn cài đặt, login
|   `-- screenshots/           # Ảnh minh họa project
|
|-- frontend/                  # Trang người dùng (storefront)
|   |-- index.php              # Trang chủ
|   |-- product.php            # Trang chi tiết sản phẩm
|   |-- cart.php               # Giỏ hàng
|   |-- checkout.php           # Thanh toán
|   `-- ...                    # Các trang khác (registration, dashboard, search,...)
|
|-- payment/                   # Module thanh toán (Bank/COD/PayPal)
|
|-- scripts/                   # Script tiện ích (migrate, backup, convert DB)
|
|-- tests/                     # Test thủ công
|
|-- index.php                  # Entry root
`-- README.md                  # Tài liệu dự án

