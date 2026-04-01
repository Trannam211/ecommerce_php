<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
    $valid = 1;

    $cust_name = trim(strip_tags(isset($_POST['cust_name']) ? $_POST['cust_name'] : ''));
    $cust_email = trim(strip_tags(isset($_POST['cust_email']) ? $_POST['cust_email'] : ''));
    $cust_phone = trim(strip_tags(isset($_POST['cust_phone']) ? $_POST['cust_phone'] : ''));
    $cust_password = isset($_POST['cust_password']) ? $_POST['cust_password'] : '';
    $cust_status = (isset($_POST['cust_status']) && (int)$_POST['cust_status'] === 0) ? 0 : 1;

    $city = trim(strip_tags(isset($_POST['city']) ? $_POST['city'] : ''));
    $district = trim(strip_tags(isset($_POST['district']) ? $_POST['district'] : ''));
    $ward = trim(strip_tags(isset($_POST['ward']) ? $_POST['ward'] : ''));
    $address_line = trim(strip_tags(isset($_POST['address_line']) ? $_POST['address_line'] : ''));

    if($cust_name === '') {
        $valid = 0;
        $error_message .= "Tên người dùng không được để trống<br>";
    }

    if($cust_email === '') {
        $valid = 0;
        $error_message .= "Email không được để trống<br>";
    } elseif(filter_var($cust_email, FILTER_VALIDATE_EMAIL) === false) {
        $valid = 0;
        $error_message .= "Email không hợp lệ<br>";
    } else {
        $statement = $pdo->prepare("SELECT cust_id FROM tbl_customer WHERE cust_email=?");
        $statement->execute(array($cust_email));
        if($statement->rowCount() > 0) {
            $valid = 0;
            $error_message .= "Email này đã tồn tại<br>";
        }
    }

    if($cust_phone === '') {
        $valid = 0;
        $error_message .= "Số điện thoại không được để trống<br>";
    }

    if($cust_password === '') {
        $valid = 0;
        $error_message .= "Mật khẩu không được để trống<br>";
    } elseif(strlen($cust_password) < 6) {
        $valid = 0;
        $error_message .= "Mật khẩu phải có ít nhất 6 ký tự<br>";
    }

    $hasAddressInput = ($city !== '' || $district !== '' || $ward !== '' || $address_line !== '');
    if($hasAddressInput) {
        if($city === '' || $district === '' || $ward === '' || $address_line === '') {
            $valid = 0;
            $error_message .= "Nếu nhập địa chỉ, bạn cần điền đủ Tỉnh/Thành phố, Quận/Huyện, Phường/Xã và Địa chỉ chi tiết<br>";
        }
    }

    if($valid == 1) {
        $cust_datetime = date('Y-m-d H:i:s');
        $cust_timestamp = time();

        $statement = $pdo->prepare("INSERT INTO tbl_customer (
                                        cust_name,
                                        cust_email,
                                        cust_phone,
                                        cust_password,
                                        cust_token,
                                        cust_datetime,
                                        cust_timestamp,
                                        cust_status
                                    ) VALUES (?,?,?,?,?,?,?,?)");
        $statement->execute(array(
            $cust_name,
            $cust_email,
            $cust_phone,
            md5($cust_password),
            '',
            $cust_datetime,
            $cust_timestamp,
            $cust_status
        ));

        $new_customer_id = (int)$pdo->lastInsertId();

        if($hasAddressInput) {
            $statement = $pdo->prepare("INSERT INTO tbl_customer_address (cust_id, receiver_name, receiver_phone, address_line, city, district, ward, is_default) VALUES (?,?,?,?,?,?,?,1)");
            $statement->execute(array(
                $new_customer_id,
                $cust_name,
                $cust_phone,
                $address_line,
                $city,
                $district,
                $ward
            ));
        }

        $success_message = 'Đã thêm người dùng thành công.';

        $_POST = array();
    }
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Thêm người dùng</h1>
    </div>
    <div class="content-header-right">
        <a href="customer.php" class="btn btn-primary btn-sm">Xem danh sách</a>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">

            <?php if($error_message): ?>
            <div class="callout callout-danger">
                <p><?php echo $error_message; ?></p>
            </div>
            <?php endif; ?>

            <?php if($success_message): ?>
            <div class="callout callout-success">
                <p><?php echo $success_message; ?></p>
            </div>
            <?php endif; ?>

            <form class="form-horizontal" action="" method="post">
                <div class="box box-info">
                    <div class="box-body">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Tên người dùng <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="cust_name" value="<?php echo isset($_POST['cust_name']) ? htmlspecialchars($_POST['cust_name'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Email <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="email" class="form-control" name="cust_email" value="<?php echo isset($_POST['cust_email']) ? htmlspecialchars($_POST['cust_email'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Số điện thoại <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="cust_phone" value="<?php echo isset($_POST['cust_phone']) ? htmlspecialchars($_POST['cust_phone'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Mật khẩu <span>*</span></label>
                            <div class="col-sm-6">
                                <input type="password" class="form-control" name="cust_password">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Trạng thái</label>
                            <div class="col-sm-3">
                                <select class="form-control" name="cust_status">
                                    <option value="1" <?php echo (!isset($_POST['cust_status']) || (int)$_POST['cust_status'] === 1) ? 'selected' : ''; ?>>Hoạt động</option>
                                    <option value="0" <?php echo (isset($_POST['cust_status']) && (int)$_POST['cust_status'] === 0) ? 'selected' : ''; ?>>Tạm khóa</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Tỉnh/Thành phố</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="city" value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Quận/Huyện</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="district" value="<?php echo isset($_POST['district']) ? htmlspecialchars($_POST['district'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Phường/Xã</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="ward" value="<?php echo isset($_POST['ward']) ? htmlspecialchars($_POST['ward'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group form-group-top">
                            <label class="col-sm-2 control-label">Địa chỉ chi tiết</label>
                            <div class="col-sm-6">
                                <textarea class="form-control" name="address_line" rows="3"><?php echo isset($_POST['address_line']) ? htmlspecialchars($_POST['address_line'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label"></label>
                            <div class="col-sm-6" style="margin-top:12px;">
                                <button type="submit" class="btn btn-success" name="form1">Thêm người dùng</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
</section>

<?php require_once('footer.php'); ?>
