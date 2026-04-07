<?php require_once('header.php'); ?>

<?php
// Check if the customer is logged in or not
if(!isset($_SESSION['customer'])) {
    safe_redirect('logout.php');
} else {
    // If customer is logged in, but admin make him inactive, then force logout this user.
    $statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_id=? AND cust_status=?");
    $statement->execute(array($_SESSION['customer']['cust_id'],0));
    $total = $statement->rowCount();
    if($total) {
        safe_redirect('logout.php');
    }
}
?>

<?php
$cust_id = (int)$_SESSION['customer']['cust_id'];

if(!isset($error_message)) {
    $error_message = '';
}
if(!isset($success_message)) {
    $success_message = '';
}

if(isset($_POST['action_address'])) {
    $action = trim($_POST['action_address']);
    $address_id = isset($_POST['address_id']) ? (int)$_POST['address_id'] : 0;

    if($address_id > 0) {
        $statement = $pdo->prepare("SELECT address_id FROM tbl_customer_address WHERE address_id=? AND cust_id=? LIMIT 1");
        $statement->execute(array($address_id, $cust_id));
        $existing_address = $statement->fetch(PDO::FETCH_ASSOC);

        if($existing_address) {
            if($action === 'set_default') {
                $pdo->prepare("UPDATE tbl_customer_address SET is_default=0 WHERE cust_id=?")->execute(array($cust_id));
                $pdo->prepare("UPDATE tbl_customer_address SET is_default=1 WHERE address_id=? AND cust_id=?")->execute(array($address_id, $cust_id));
                $success_message = 'Đã cập nhật địa chỉ mặc định.';
            }

            if($action === 'delete') {
                $statement = $pdo->prepare("SELECT is_default FROM tbl_customer_address WHERE address_id=? AND cust_id=? LIMIT 1");
                $statement->execute(array($address_id, $cust_id));
                $target_address = $statement->fetch(PDO::FETCH_ASSOC);

                $pdo->prepare("DELETE FROM tbl_customer_address WHERE address_id=? AND cust_id=?")->execute(array($address_id, $cust_id));

                if($target_address && (int)$target_address['is_default'] === 1) {
                    $statement = $pdo->prepare("SELECT address_id FROM tbl_customer_address WHERE cust_id=? ORDER BY address_id ASC LIMIT 1");
                    $statement->execute(array($cust_id));
                    $first_address = $statement->fetch(PDO::FETCH_ASSOC);
                    if($first_address) {
                        $pdo->prepare("UPDATE tbl_customer_address SET is_default=1 WHERE address_id=? AND cust_id=?")
                            ->execute(array((int)$first_address['address_id'], $cust_id));
                    }
                }

                $success_message = 'Đã xóa địa chỉ.';
            }
        }
    }
}

if(isset($_POST['form1'])) {
    $valid = 1;
    $address_id = isset($_POST['address_id']) ? (int)$_POST['address_id'] : 0;
    $current_is_default = isset($_POST['current_is_default']) ? (int)$_POST['current_is_default'] : 0;
    $set_default = isset($_POST['set_default']) ? 1 : 0;

    $receiver_name = trim(strip_tags($_POST['receiver_name']));
    $receiver_phone = trim(strip_tags($_POST['receiver_phone']));
    $address_line = trim(strip_tags($_POST['address_line']));
    $city = trim(strip_tags($_POST['city']));
    $district = trim(strip_tags($_POST['district']));
    $ward = trim(strip_tags($_POST['ward']));

    if($receiver_name === '' || $receiver_phone === '' || $address_line === '' || $city === '' || $district === '' || $ward === '') {
        $valid = 0;
        $error_message = 'Vui lòng nhập đầy đủ thông tin địa chỉ.';
    }

    if($valid == 1) {
        $statement = $pdo->prepare("SELECT COUNT(*) AS total FROM tbl_customer_address WHERE cust_id=?");
        $statement->execute(array($cust_id));
        $address_count = (int)$statement->fetch(PDO::FETCH_ASSOC)['total'];

        if($address_id > 0) {
            $statement = $pdo->prepare("SELECT address_id FROM tbl_customer_address WHERE address_id=? AND cust_id=? LIMIT 1");
            $statement->execute(array($address_id, $cust_id));
            $exists = $statement->fetch(PDO::FETCH_ASSOC);

            if(!$exists) {
                $valid = 0;
                $error_message = 'Địa chỉ không tồn tại hoặc không thuộc tài khoản của bạn.';
            }

            if($valid == 1) {
                $new_default_value = ($set_default === 1 || $current_is_default === 1) ? 1 : 0;

                if($set_default === 1) {
                    $pdo->prepare("UPDATE tbl_customer_address SET is_default=0 WHERE cust_id=?")->execute(array($cust_id));
                }

                $statement = $pdo->prepare("UPDATE tbl_customer_address SET receiver_name=?, receiver_phone=?, address_line=?, city=?, district=?, ward=?, is_default=? WHERE address_id=? AND cust_id=?");
                $statement->execute(array(
                    $receiver_name,
                    $receiver_phone,
                    $address_line,
                    $city,
                    $district,
                    $ward,
                    $new_default_value,
                    $address_id,
                    $cust_id
                ));

                $success_message = 'Cập nhật địa chỉ thành công.';
            }
        } else {
            $new_default_value = ($set_default === 1 || $address_count === 0) ? 1 : 0;

            if($new_default_value === 1) {
                $pdo->prepare("UPDATE tbl_customer_address SET is_default=0 WHERE cust_id=?")->execute(array($cust_id));
            }

            $statement = $pdo->prepare("INSERT INTO tbl_customer_address (cust_id, receiver_name, receiver_phone, address_line, city, district, ward, is_default) VALUES (?,?,?,?,?,?,?,?)");
            $statement->execute(array(
                $cust_id,
                $receiver_name,
                $receiver_phone,
                $address_line,
                $city,
                $district,
                $ward,
                $new_default_value
            ));

            $success_message = 'Thêm địa chỉ mới thành công.';
        }
    }
}

$statement = $pdo->prepare("SELECT * FROM tbl_customer_address WHERE cust_id=? ORDER BY is_default DESC, address_id DESC");
$statement->execute(array($cust_id));
$address_list = $statement->fetchAll(PDO::FETCH_ASSOC);

$form_address = array(
    'address_id' => 0,
    'receiver_name' => '',
    'receiver_phone' => '',
    'address_line' => '',
    'city' => '',
    'district' => '',
    'ward' => '',
    'is_default' => 0
);

if(isset($_POST['form1']) && $error_message !== '') {
    $form_address = array(
        'address_id' => isset($_POST['address_id']) ? (int)$_POST['address_id'] : 0,
        'receiver_name' => isset($_POST['receiver_name']) ? trim($_POST['receiver_name']) : '',
        'receiver_phone' => isset($_POST['receiver_phone']) ? trim($_POST['receiver_phone']) : '',
        'address_line' => isset($_POST['address_line']) ? trim($_POST['address_line']) : '',
        'city' => isset($_POST['city']) ? trim($_POST['city']) : '',
        'district' => isset($_POST['district']) ? trim($_POST['district']) : '',
        'ward' => isset($_POST['ward']) ? trim($_POST['ward']) : '',
        'is_default' => isset($_POST['set_default']) ? 1 : 0
    );
}
?>

<style>
    .address-book-card {
        border: 1px solid #dce6f4;
        background: #fff;
        border-radius: 0;
        padding: 18px;
        box-shadow: 0 8px 20px rgba(12, 32, 78, 0.06);
    }

    .address-book-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        border-bottom: 1px solid #e8eef8;
        padding-bottom: 12px;
        margin-bottom: 14px;
    }

    .address-book-head h3 {
        margin: 0;
        color: #1f3d6d;
        font-size: 24px;
        font-weight: 700;
    }

    .address-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 12px;
    }

    .address-item {
        border: 1px solid #dce6f4;
        padding: 12px;
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .address-summary-main {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        font-size: 20px;
        color: #172b4d;
    }

    .address-summary-main strong {
        font-weight: 700;
    }

    .address-summary-main .phone {
        color: #2f3c53;
        font-size: 18px;
    }

    .address-default-badge {
        display: inline-block;
        margin-top: 4px;
        border: 1px solid #ee4d2d;
        color: #ee4d2d;
        padding: 2px 8px;
        font-size: 12px;
        font-weight: 600;
    }

    .address-summary-line {
        color: #2f3c53;
        font-size: 16px;
        line-height: 1.55;
    }

    .address-actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .address-actions form {
        margin: 0;
    }

    .address-actions .btn {
        border-radius: 0;
        padding: 5px 10px;
    }

    .address-edit-panel {
        border-top: 1px dashed #cbd8ec;
        margin-top: 14px;
        padding-top: 14px;
        display: none;
    }

    .address-edit-panel .form-control {
        border-radius: 0;
        border-color: #d8e0ec;
        min-height: 42px;
    }

    .address-edit-panel textarea.form-control {
        min-height: 64px;
    }

    .address-edit-panel.open {
        display: block;
    }

    .address-update-btn,
    .address-add-btn {
        min-width: 172px;
    }

    .address-add-btn {
        min-width: 168px;
        background: #ee4d2d;
        border-color: #ee4d2d;
    }

    .address-add-btn:hover,
    .address-add-btn:focus {
        background: #d84529;
        border-color: #d84529;
    }

    @media (max-width: 767px) {
        .address-book-head {
            flex-direction: column;
            align-items: flex-start;
        }

        .address-update-btn,
        .address-add-btn {
            width: 100%;
        }

        .address-item {
            flex-direction: column;
        }

        .address-actions {
            width: 100%;
            justify-content: flex-start;
            flex-wrap: wrap;
        }
    }
</style>

<div class="page">
    <div class="container">
        <div class="account-layout">
            <div class="account-sidebar-col"> 
                <?php require_once('customer-sidebar.php'); ?>
            </div>
            <div class="account-content-col">
                <div class="user-content account-content-card">
                    <?php
                    if($error_message != '') {
                        echo "<div class='error' style='padding: 10px;background:#f1f1f1;margin-bottom:20px;'>".$error_message."</div>";
                    }
                    if($success_message != '') {
                        echo "<div class='success' style='padding: 10px;background:#f1f1f1;margin-bottom:20px;'>".$success_message."</div>";
                    }
                    ?>
                    <div class="address-book-card">
                        <div class="address-book-head">
                            <h3>Địa chỉ của tôi</h3>
                            <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end;">
                                <button type="button" class="btn btn-primary address-add-btn" id="createNewAddress">Thêm địa chỉ mới</button>
                            </div>
                        </div>
                        <div class="address-list">
                            <?php if(count($address_list) === 0): ?>
                                <div class="address-item">
                                    <div class="address-summary-line">Bạn chưa có địa chỉ nào. Hãy thêm địa chỉ mới để tiếp tục mua hàng.</div>
                                </div>
                            <?php endif; ?>

                            <?php foreach($address_list as $item): ?>
                                <div class="address-item">
                                    <div>
                                        <div class="address-summary-main">
                                            <strong><?php echo htmlspecialchars($item['receiver_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                            <span class="phone"><?php echo htmlspecialchars($item['receiver_phone'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            <?php if((int)$item['is_default'] === 1): ?>
                                                <span class="address-default-badge">Mặc định</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="address-summary-line"><?php echo nl2br(htmlspecialchars($item['address_line'], ENT_QUOTES, 'UTF-8')); ?></div>
                                        <div class="address-summary-line"><?php echo htmlspecialchars($item['ward'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="address-summary-line"><?php echo htmlspecialchars($item['district'], ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars($item['city'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    </div>
                                    <div class="address-actions">
                                        <?php if((int)$item['is_default'] !== 1): ?>
                                            <form action="" method="post">
                                                <?php $csrf->echoInputField(); ?>
                                                <input type="hidden" name="action_address" value="set_default">
                                                <input type="hidden" name="address_id" value="<?php echo (int)$item['address_id']; ?>">
                                                <button type="submit" class="btn btn-default">Mặc định</button>
                                            </form>
                                        <?php endif; ?>

                                        <button
                                            type="button"
                                            class="btn btn-primary edit-address-btn"
                                            data-address-id="<?php echo (int)$item['address_id']; ?>"
                                            data-name="<?php echo htmlspecialchars($item['receiver_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-phone="<?php echo htmlspecialchars($item['receiver_phone'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-line="<?php echo htmlspecialchars($item['address_line'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-city="<?php echo htmlspecialchars($item['city'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-district="<?php echo htmlspecialchars($item['district'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-ward="<?php echo htmlspecialchars($item['ward'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-is-default="<?php echo (int)$item['is_default']; ?>"
                                        >Sửa</button>

                                        <form action="" method="post" onsubmit="return confirm('Bạn có chắc muốn xóa địa chỉ này?');">
                                            <?php $csrf->echoInputField(); ?>
                                            <input type="hidden" name="action_address" value="delete">
                                            <input type="hidden" name="address_id" value="<?php echo (int)$item['address_id']; ?>">
                                            <button type="submit" class="btn btn-danger">Xóa</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="address-edit-panel<?php echo (isset($_POST['form1']) || count($address_list) === 0) ? ' open' : ''; ?>" id="addressEditPanel">
                            <form action="" method="post">
                                <?php $csrf->echoInputField(); ?>
                                <input type="hidden" name="address_id" id="address_id" value="<?php echo (int)$form_address['address_id']; ?>">
                                <input type="hidden" name="current_is_default" id="current_is_default" value="<?php echo (int)$form_address['is_default']; ?>">
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="">Họ và tên</label>
                                        <input type="text" class="form-control" name="receiver_name" id="receiver_name" value="<?php echo htmlspecialchars($form_address['receiver_name'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="">Số điện thoại</label>
                                        <input type="text" class="form-control" name="receiver_phone" id="receiver_phone" value="<?php echo htmlspecialchars($form_address['receiver_phone'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                    <div class="col-md-12 form-group">
                                        <label for="">Địa chỉ đầy đủ</label>
                                        <textarea name="address_line" id="address_line" class="form-control" cols="30" rows="3" style="height:92px;"><?php echo htmlspecialchars($form_address['address_line'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="">Quận/Huyện</label>
                                        <input type="text" class="form-control" name="district" id="district" value="<?php echo htmlspecialchars($form_address['district'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="">Phường/Xã</label>
                                        <input type="text" class="form-control" name="ward" id="ward" value="<?php echo htmlspecialchars($form_address['ward'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="">Thành phố</label>
                                        <input type="text" class="form-control" name="city" id="city" value="<?php echo htmlspecialchars($form_address['city'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                    <div class="col-md-6 form-group" style="display:flex;align-items:center;min-height:74px;">
                                        <label style="margin:0;display:flex;gap:8px;align-items:center;cursor:pointer;">
                                            <input type="checkbox" name="set_default" id="set_default" value="1" <?php echo ((int)$form_address['is_default'] === 1) ? 'checked' : ''; ?>>
                                            Đặt làm địa chỉ mặc định
                                        </label>
                                    </div>
                                </div>
                                <div class="account-form-actions">
                                    <input type="submit" class="btn btn-primary" value="Lưu địa chỉ" name="form1">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>                
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        var addBtn = document.getElementById('createNewAddress');
        var editPanel = document.getElementById('addressEditPanel');
        if (!editPanel) {
            return;
        }

        function openEditPanel(scrollToPanel) {
            editPanel.classList.add('open');
            if (scrollToPanel) {
                editPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }

        function setFormValues(data) {
            document.getElementById('address_id').value = data.addressId || 0;
            document.getElementById('current_is_default').value = data.isDefault || 0;
            document.getElementById('receiver_name').value = data.name || '';
            document.getElementById('receiver_phone').value = data.phone || '';
            document.getElementById('address_line').value = data.line || '';
            document.getElementById('city').value = data.city || '';
            document.getElementById('district').value = data.district || '';
            document.getElementById('ward').value = data.ward || '';
            document.getElementById('set_default').checked = Number(data.isDefault || 0) === 1;
        }

        if (addBtn) {
            addBtn.addEventListener('click', function() {
                setFormValues({
                    addressId: 0,
                    name: '',
                    phone: '',
                    line: '',
                    city: '',
                    district: '',
                    ward: '',
                    isDefault: 0
                });
                openEditPanel(true);
            });
        }

        var editButtons = document.querySelectorAll('.edit-address-btn');
        Array.prototype.forEach.call(editButtons, function(btn) {
            btn.addEventListener('click', function() {
                setFormValues({
                    addressId: this.getAttribute('data-address-id') || 0,
                    name: this.getAttribute('data-name') || '',
                    phone: this.getAttribute('data-phone') || '',
                    line: this.getAttribute('data-line') || '',
                    city: this.getAttribute('data-city') || '',
                    district: this.getAttribute('data-district') || '',
                    ward: this.getAttribute('data-ward') || '',
                    isDefault: this.getAttribute('data-is-default') || 0
                });
                openEditPanel(true);
            });
        });
    })();
</script>


<?php require_once('footer.php'); ?>