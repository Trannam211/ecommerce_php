<?php require_once('header.php'); ?>

<?php
// Check if the customer is logged in or not
if(!isset($_SESSION['customer'])) {
    header('location: '.BASE_URL.'logout.php');
    exit;
} else {
    // If customer is logged in, but admin make him inactive, then force logout this user.
    $statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_id=? AND cust_status=?");
    $statement->execute(array($_SESSION['customer']['cust_id'],0));
    $total = $statement->rowCount();
    if($total) {
        header('location: '.BASE_URL.'logout.php');
        exit;
    }
}
?>

<?php
if(!isset($error_message)) {
    $error_message = '';
}
if(!isset($success_message)) {
    $success_message = '';
}

$max_avatar_size = 2 * 1024 * 1024; // 2MB

// Refresh customer session data to include new profile fields.
$statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_id=? LIMIT 1");
$statement->execute(array($_SESSION['customer']['cust_id']));
$current_customer = $statement->fetch(PDO::FETCH_ASSOC);
if($current_customer) {
    $_SESSION['customer'] = $current_customer;
}

if (isset($_POST['form1'])) {

    $valid = 1;
    $cust_gender = isset($_POST['cust_gender']) ? trim(strip_tags($_POST['cust_gender'])) : '';
    $cust_dob = isset($_POST['cust_dob']) ? trim($_POST['cust_dob']) : '';
    $cust_photo = isset($_SESSION['customer']['cust_photo']) ? $_SESSION['customer']['cust_photo'] : '';

    if(empty($_POST['cust_name'])) {
        $valid = 0;
        $error_message .= "Bạn chưa nhập họ và tên.<br>";
    }

    if(empty($_POST['cust_phone'])) {
        $valid = 0;
        $error_message .= "Bạn chưa nhập số điện thoại.<br>";
    }

    if($cust_gender !== '' && !in_array($cust_gender, array('Nam','Nữ','Khác'))) {
        $valid = 0;
        $error_message .= "Giới tính không hợp lệ.<br>";
    }

    $cust_dob_db = null;
    if($cust_dob !== '') {
        $dob_date = DateTime::createFromFormat('Y-m-d', $cust_dob);
        $is_valid_dob = $dob_date && $dob_date->format('Y-m-d') === $cust_dob;
        if(!$is_valid_dob) {
            $valid = 0;
            $error_message .= "Ngày sinh không hợp lệ. Gợi ý định dạng: YYYY-MM-DD.<br>";
        } else {
            $cust_dob_db = $cust_dob;
        }
    }

    if(isset($_FILES['cust_photo']) && isset($_FILES['cust_photo']['name']) && $_FILES['cust_photo']['name'] !== '') {
        if($_FILES['cust_photo']['error'] !== 0) {
            $valid = 0;
            if($_FILES['cust_photo']['error'] === UPLOAD_ERR_INI_SIZE || $_FILES['cust_photo']['error'] === UPLOAD_ERR_FORM_SIZE) {
                $error_message .= "Ảnh đại diện vượt quá dung lượng cho phép (tối đa 2MB).<br>";
            } elseif($_FILES['cust_photo']['error'] === UPLOAD_ERR_PARTIAL) {
                $error_message .= "Ảnh đại diện tải lên chưa hoàn tất. Vui lòng thử lại.<br>";
            } elseif($_FILES['cust_photo']['error'] === UPLOAD_ERR_NO_FILE) {
                $error_message .= "Không tìm thấy tệp ảnh để tải lên.<br>";
            } else {
                $error_message .= "Tải ảnh đại diện thất bại do lỗi hệ thống. Vui lòng thử lại.<br>";
            }
        } else {
            $allowed_ext = array('jpg','jpeg','png','gif','webp');
            $photo_name = $_FILES['cust_photo']['name'];
            $photo_ext = strtolower(pathinfo($photo_name, PATHINFO_EXTENSION));
            if(!in_array($photo_ext, $allowed_ext)) {
                $valid = 0;
                $error_message .= "Ảnh đại diện phải có định dạng jpg, jpeg, png, gif hoặc webp.<br>";
            }
            if((int)$_FILES['cust_photo']['size'] > $max_avatar_size) {
                $valid = 0;
                $error_message .= "Ảnh đại diện vượt quá 2MB. Vui lòng chọn ảnh nhỏ hơn.<br>";
            }
        }
    }

    if($valid == 1) {
        if(isset($_FILES['cust_photo']) && isset($_FILES['cust_photo']['name']) && $_FILES['cust_photo']['name'] !== '') {
            $new_photo_name = 'customer-'.$_SESSION['customer']['cust_id'].'-'.time().'.'.$photo_ext;
            $target_path = __DIR__ . '/assets/uploads/' . $new_photo_name;
            if(!move_uploaded_file($_FILES['cust_photo']['tmp_name'], $target_path)) {
                $valid = 0;
                $error_message .= "Không thể lưu ảnh đại diện.<br>";
            } else {
                $cust_photo = $new_photo_name;
            }
        }
    }

    if($valid == 1) {
        // update data into the database
        $statement = $pdo->prepare("UPDATE tbl_customer SET cust_name=?, cust_phone=?, cust_gender=?, cust_dob=?, cust_photo=? WHERE cust_id=?");
        $statement->execute(array(
                    strip_tags($_POST['cust_name']),
                    strip_tags($_POST['cust_phone']),
                    $cust_gender,
                    $cust_dob_db,
                    $cust_photo,
                    $_SESSION['customer']['cust_id']
                ));  
       
        $success_message = "Cập nhật hồ sơ thành công.";

        $_SESSION['customer']['cust_name'] = $_POST['cust_name'];
        $_SESSION['customer']['cust_phone'] = $_POST['cust_phone'];
        $_SESSION['customer']['cust_gender'] = $cust_gender;
        $_SESSION['customer']['cust_dob'] = $cust_dob_db;
        $_SESSION['customer']['cust_photo'] = $cust_photo;
    }
}
?>

<style>
    .profile-form-shell .form-group {
        margin-bottom: 16px;
    }

    .profile-form-shell .form-control {
        border-radius: 0;
        border-color: #d8e0ec;
        min-height: 42px;
    }

    .profile-form-shell textarea.form-control {
        min-height: 64px;
    }

    .profile-form-shell label {
        font-weight: 600;
        color: #243a5a;
        margin-bottom: 6px;
    }

    .profile-avatar-block {
        border: 1px solid #dbe5f3;
        background: #fbfdff;
        padding: 14px;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .profile-avatar-preview {
        width: 96px;
        height: 96px;
        object-fit: cover;
        border: 1px solid #cfdcf0;
        background: #fff;
        flex-shrink: 0;
    }

    .profile-avatar-fields {
        flex: 1;
        min-width: 220px;
    }

    .profile-avatar-fields .form-control {
        max-width: 420px;
    }

    .profile-grid-row {
        margin-top: 4px;
    }

    @media (max-width: 767px) {
        .profile-avatar-block {
            align-items: flex-start;
        }

        .profile-avatar-preview {
            width: 84px;
            height: 84px;
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
                    <h3>
                        Cập nhật hồ sơ
                    </h3>
                    <?php
                    if($error_message != '') {
                        echo "<div class='error' style='padding: 10px;background:#f1f1f1;margin-bottom:20px;'>".$error_message."</div>";
                    }
                    if($success_message != '') {
                        echo "<div class='success' style='padding: 10px;background:#f1f1f1;margin-bottom:20px;'>".$success_message."</div>";
                    }
                    ?>
                    <form action="" method="post" enctype="multipart/form-data" class="profile-form-shell">
                        <?php $csrf->echoInputField(); ?>
                        <div class="profile-avatar-block">
                            <div>
                                <?php $avatar_file = (isset($_SESSION['customer']['cust_photo']) && $_SESSION['customer']['cust_photo'] !== '') ? $_SESSION['customer']['cust_photo'] : 'default-avatar.svg'; ?>
                                <img src="assets/uploads/<?php echo htmlspecialchars($avatar_file, ENT_QUOTES, 'UTF-8'); ?>" alt="Avatar" class="profile-avatar-preview">
                            </div>
                            <div class="profile-avatar-fields">
                                <label for="cust_photo" style="display:block;">Ảnh đại diện</label>
                                <input type="file" class="form-control" name="cust_photo" id="cust_photo" accept=".jpg,.jpeg,.png,.gif,.webp">
                            </div>
                        </div>

                        <div class="row profile-grid-row">
                            <div class="col-md-6 form-group">
                                <label for="">Họ và tên *</label>
                                <input type="text" class="form-control" name="cust_name" value="<?php echo $_SESSION['customer']['cust_name']; ?>">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="">Email *</label>
                                <input type="text" class="form-control" name="" value="<?php echo $_SESSION['customer']['cust_email']; ?>" disabled>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="">Số điện thoại *</label>
                                <input type="text" class="form-control" name="cust_phone" value="<?php echo $_SESSION['customer']['cust_phone']; ?>">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="cust_gender">Giới tính</label>
                                <select name="cust_gender" id="cust_gender" class="form-control">
                                    <?php $current_gender = isset($_SESSION['customer']['cust_gender']) ? $_SESSION['customer']['cust_gender'] : ''; ?>
                                    <option value="" <?php echo $current_gender === '' ? 'selected' : ''; ?>>Chưa chọn</option>
                                    <option value="Nam" <?php echo $current_gender === 'Nam' ? 'selected' : ''; ?>>Nam</option>
                                    <option value="Nữ" <?php echo $current_gender === 'Nữ' ? 'selected' : ''; ?>>Nữ</option>
                                    <option value="Khác" <?php echo $current_gender === 'Khác' ? 'selected' : ''; ?>>Khác</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="cust_dob">Ngày sinh</label>
                                <input type="date" class="form-control" name="cust_dob" id="cust_dob" value="<?php echo isset($_SESSION['customer']['cust_dob']) ? htmlspecialchars($_SESSION['customer']['cust_dob'], ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="YYYY-MM-DD">
                            </div>
                        </div>
                        <div class="account-form-actions">
                            <input type="submit" class="btn btn-primary" value="Cập nhật" name="form1">
                        </div>
                    </form>
                </div>                
            </div>
        </div>
    </div>
</div>


<?php require_once('footer.php'); ?>