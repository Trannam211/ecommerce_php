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
if (isset($_POST['form1'])) {

    $valid = 1;
    $cust_old_password = isset($_POST['cust_old_password']) ? trim((string)$_POST['cust_old_password']) : '';
    $cust_password = isset($_POST['cust_password']) ? trim((string)$_POST['cust_password']) : '';
    $cust_re_password = isset($_POST['cust_re_password']) ? trim((string)$_POST['cust_re_password']) : '';

    if( empty($cust_old_password) || empty($cust_password) || empty($cust_re_password) ) {
        $valid = 0;
        $error_message .= "Vui lòng nhập đầy đủ mật khẩu cũ, mật khẩu mới và xác nhận mật khẩu mới.<br>";
    }

    if(!empty($cust_password) && !empty($cust_re_password)) {
        if($cust_password != $cust_re_password) {
            $valid = 0;
            $error_message .= "Mật khẩu xác nhận không khớp.<br>";
        }
    }

    if(!empty($cust_old_password)) {
        $statement = $pdo->prepare("SELECT cust_password FROM tbl_customer WHERE cust_id=? LIMIT 1");
        $statement->execute(array($_SESSION['customer']['cust_id']));
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        $stored_password = $row ? (string)$row['cust_password'] : '';
        $stored_password_trimmed = trim($stored_password);
        $looks_like_password_hash = (
            strpos($stored_password_trimmed, '$2y$') === 0 ||
            strpos($stored_password_trimmed, '$2a$') === 0 ||
            strpos($stored_password_trimmed, '$2b$') === 0 ||
            strpos($stored_password_trimmed, '$argon2') === 0
        );

        $old_password_ok = false;
        if(function_exists('password_verify') && $looks_like_password_hash) {
            $old_password_ok = password_verify($cust_old_password, $stored_password_trimmed);
        } elseif(preg_match('/^[a-f0-9]{32}$/i', $stored_password_trimmed)) {
            $old_password_ok = hash_equals(strtolower($stored_password_trimmed), md5($cust_old_password));
        } else {
            $old_password_ok = hash_equals($stored_password_trimmed, $cust_old_password);
        }

        if(!$row || !$old_password_ok) {
            $valid = 0;
            $error_message .= "Mật khẩu cũ không chính xác.<br>";
        }
    }

    if(!empty($cust_old_password) && !empty($cust_password) && hash_equals($cust_old_password, $cust_password)) {
        $valid = 0;
        $error_message .= "Mật khẩu mới phải khác mật khẩu cũ.<br>";
    }
    
    if($valid == 1) {

        ensure_customer_password_storage($pdo);

        $new_password_hash = password_hash($cust_password, PASSWORD_DEFAULT);
        if($new_password_hash === false) {
            $valid = 0;
            $error_message .= "Không thể tạo mật khẩu an toàn. Vui lòng thử lại.<br>";
        } else {

        // update data into the database
        $statement = $pdo->prepare("UPDATE tbl_customer SET cust_password=? WHERE cust_id=?");
        $statement->execute(array($new_password_hash,$_SESSION['customer']['cust_id']));
        
        $_SESSION['customer']['cust_password'] = $new_password_hash;

        $success_message = "Đổi mật khẩu thành công.";

        }
    }
}
?>

<style>
    .password-update-form {
        max-width: 440px;
        margin: 0 auto;
    }

    .password-update-form .form-group {
        margin-bottom: 18px;
    }

    .password-update-form label {
        font-weight: 600;
        color: #243a5a;
        margin-bottom: 7px;
    }

    .password-field {
        position: relative;
    }

    .password-update-form .form-control {
        border-radius: 0;
        border-color: #d8e0ec;
        min-height: 42px;
    }

    .password-field .form-control {
        padding-right: 48px;
    }

    .toggle-password-btn {
        position: absolute;
        top: 1px;
        right: 1px;
        width: 40px;
        height: 40px;
        border: 0;
        border-left: 1px solid #d8e0ec;
        background: #f8fafc;
        color: #355579;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .toggle-password-btn:hover,
    .toggle-password-btn:focus {
        background: #edf3fb;
        outline: none;
    }

    .password-update-form .account-form-actions {
        margin-top: 8px;
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
                    <h3 class="text-center">
                        Đổi mật khẩu
                    </h3>
                    <form action="" method="post" class="password-update-form">
                        <?php $csrf->echoInputField(); ?>
                        <div class="row">
                            <div class="col-md-12">
                                <?php
                                if($error_message != '') {
                                    echo "<div class='error' style='padding: 10px;background:#f1f1f1;margin-bottom:20px;'>".$error_message."</div>";
                                }
                                if($success_message != '') {
                                    echo "<div class='success' style='padding: 10px;background:#f1f1f1;margin-bottom:20px;'>".$success_message."</div>";
                                }
                                ?>
                                <div class="form-group">
                                    <label for="">Mật khẩu cũ *</label>
                                    <div class="password-field">
                                        <input type="password" class="form-control" id="cust_old_password" name="cust_old_password">
                                        <button class="toggle-password-btn" type="button" data-target="#cust_old_password" aria-label="Hiện hoặc ẩn mật khẩu cũ">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="">Mật khẩu mới *</label>
                                    <div class="password-field">
                                        <input type="password" class="form-control" id="cust_password" name="cust_password">
                                        <button class="toggle-password-btn" type="button" data-target="#cust_password" aria-label="Hiện hoặc ẩn mật khẩu mới">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="">Nhập lại mật khẩu mới *</label>
                                    <div class="password-field">
                                        <input type="password" class="form-control" id="cust_re_password" name="cust_re_password">
                                        <button class="toggle-password-btn" type="button" data-target="#cust_re_password" aria-label="Hiện hoặc ẩn xác nhận mật khẩu mới">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="account-form-actions">
                                    <input type="submit" class="btn btn-primary" value="Cập nhật" name="form1">
                                </div>
                            </div>
                        </div>
                        
                    </form>
                </div>                
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        var toggles = document.querySelectorAll('.toggle-password-btn');
        Array.prototype.forEach.call(toggles, function(btn) {
            btn.addEventListener('click', function() {
                var target = document.querySelector(btn.getAttribute('data-target'));
                var icon = btn.querySelector('i');
                if (!target) {
                    return;
                }
                if (target.type === 'password') {
                    target.type = 'text';
                    if (icon) {
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    }
                } else {
                    target.type = 'password';
                    if (icon) {
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                }
            });
        });
    })();
</script>


<?php require_once('footer.php'); ?>