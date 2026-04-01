# Cấu hình XAMPP MySQL/MariaDB hỗ trợ tiếng Việt (utf8mb4)

Mục tiêu: để mọi kết nối MySQL trong XAMPP mặc định dùng **utf8mb4** (hỗ trợ tiếng Việt + emoji) và collation chuẩn.

## 1) Tìm file `my.ini`
Tùy phiên bản XAMPP, file cấu hình thường nằm ở 1 trong các đường dẫn:
- `C:\xampp\mysql\bin\my.ini`
- `C:\xampp\mysql\my.ini`

Bạn có thể mở XAMPP Control Panel → bấm **Config** (ở dòng MySQL) → chọn **my.ini**.

## 2) Sửa cấu hình
Mở `my.ini`, tìm các section sau và thêm (hoặc chỉnh) các dòng này.

### [mysqld]
Thêm/chỉnh:
```
character-set-server=utf8mb4
collation-server=utf8mb4_unicode_ci
```

Khuyến nghị (tùy chọn nhưng hay dùng) để client không “ép” charset khác:
```
skip-character-set-client-handshake
```

### [client]
Thêm/chỉnh:
```
default-character-set=utf8mb4
```

### [mysql] (nếu có)
Thêm/chỉnh:
```
default-character-set=utf8mb4
```

Gợi ý:
- Nếu `my.ini` đã có các dòng như `character-set-server=latin1` hoặc `collation-server=latin1_swedish_ci` thì **đổi** sang utf8mb4 như trên.

## 3) Restart MySQL
Trong XAMPP Control Panel:
- Stop MySQL
- Start MySQL

## 4) Kiểm tra nhanh
Vào phpMyAdmin → tab SQL chạy:
```sql
SHOW VARIABLES LIKE 'character_set%';
SHOW VARIABLES LIKE 'collation%';
```
Kỳ vọng thấy:
- `character_set_server = utf8mb4`
- `collation_server = utf8mb4_unicode_ci`

## 5) Lưu ý quan trọng về dữ liệu cũ
- Cấu hình trên giúp **từ giờ về sau** lưu/hiển thị tiếng Việt đúng.
- Nếu DB/bảng/cột trước đó tạo bằng `latin1`/`utf8` sai, dữ liệu tiếng Việt có thể đã bị lỗi.

Trong repo có sẵn script để chuyển DB hiện tại sang `utf8mb4_unicode_ci`:
- Chạy xem trước: `php scripts/convert_db_to_utf8mb4.php --dry-run`
- Chạy thật (nên backup DB trước): `php scripts/convert_db_to_utf8mb4.php`

( Script này đổi charset/collation của database và tables; không “chữa” dữ liệu đã bị mã hóa sai trước đó. )
