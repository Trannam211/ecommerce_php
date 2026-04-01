<?php
// Bootstrap without rendering HTML so redirects work reliably.
require_once(__DIR__ . '/admin/inc/config.php');
require_once(__DIR__ . '/admin/inc/functions.php');
require_once(__DIR__ . '/admin/inc/CSRF_Protect.php');
if(session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$csrf = new CSRF_Protect();

$error_message = '';

if(isset($_POST['form1'])) {
    if(empty($_POST['cust_email']) || empty($_POST['cust_password'])) {
        $error_message = 'Vui lòng nhập email và mật khẩu.<br>';
    } else {
        $cust_email = strip_tags($_POST['cust_email']);
        $cust_password = strip_tags($_POST['cust_password']);

        $statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_email=?");
        $statement->execute(array($cust_email));
        $total = $statement->rowCount();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach($result as $row) {
            $cust_status = $row['cust_status'];
            $row_password = $row['cust_password'];
        }

        if($total==0) {
            $error_message .= 'Địa chỉ email không tồn tại trong hệ thống.<br>';
        } else {
            if($row_password != md5($cust_password)) {
                $error_message .= 'Mật khẩu không đúng.<br>';
            } else {
                if($cust_status == 0) {
                    $error_message .= 'Tài khoản chưa được kích hoạt. Vui lòng kiểm tra địa chỉ email để xác minh.<br>';
                } else {
                    $_SESSION['customer'] = $row;
                    safe_redirect(BASE_URL."index.php");
                }
            }
        }
    }
}

require_once('header.php');
?>
<!-- fetching row banner login -->
<?php
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
foreach ($result as $row) {
    $banner_login = $row['banner_login'];
}
?>
<!-- login form -->
<?php
// Login POST is handled before rendering the header.
?>

<div class="page-banner" style="background-color:#444;background-image: url(assets/uploads/<?php echo $banner_login; ?>);">
    <div class="inner">
        <h1>Đăng nhập</h1>
    </div>
</div>

<style>
    .login-shell {
        max-width: 560px;
        margin: 0 auto;
    }

    .login-card {
        background: #fff;
        border: 1px solid #e7eaf0;
        border-radius: 0;
        box-shadow: 0 12px 30px rgba(15, 35, 95, 0.08);
        padding: 24px;
    }

    .login-card .form-group {
        margin-bottom: 16px;
    }

    .login-card label {
        font-weight: 600;
        color: #23344f;
        margin-bottom: 7px;
    }

    .login-card .form-control {
        border-color: #d8e0ec;
        border-radius: 0;
        min-height: 42px;
        box-shadow: none;
    }

    .login-password-wrap {
        position: relative;
    }

    .login-password-wrap .form-control {
        padding-right: 46px;
    }

    .login-toggle-password {
        position: absolute;
        top: 1px;
        right: 1px;
        width: 40px;
        height: 40px;
        border: 0;
        border-left: 1px solid #d8e0ec;
        background: #f8fafc;
        border-radius: 0;
        color: #355579;
        padding: 0;
    }

    .login-toggle-password:hover,
    .login-toggle-password:focus {
        background: #edf3fb;
        outline: none;
    }

    .login-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 2px;
    }

    .login-btn {
        min-width: 140px;
        border-radius: 0;
        font-weight: 700;
        letter-spacing: 0.2px;
    }

    .login-register-hint {
        margin: 16px 0 0;
        color: #5d6f89;
        text-align: center;
    }

    .login-register-hint a {
        color: #1f78d1;
        font-weight: 700;
    }

    @media (max-width: 767px) {
        .login-card {
            padding: 16px;
        }

        .login-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .login-btn {
            width: 100%;
        }
    }
</style>

<div class="page">
    <div class="container">
        <div class="login-shell">
            <div class="login-card">
                <?php
                if($error_message != '') {
                    echo "<div class='error' style='padding: 12px;background:#fff4f4;border:1px solid #f3c9c9;margin-bottom:16px;'>".$error_message."</div>";
                }
                if($success_message != '') {
                    echo "<div class='success' style='padding: 12px;background:#f2fbf5;border:1px solid #b9e6c6;margin-bottom:16px;'>".$success_message."</div>";
                }
                ?>

                <form action="" method="post">
                    <?php $csrf->echoInputField(); ?>
                    <div class="form-group">
                        <label for="cust_email">Địa chỉ email *</label>
                        <input type="email" class="form-control" id="cust_email" name="cust_email" placeholder="Nhập địa chỉ email" required>
                    </div>
                    <div class="form-group">
                        <label for="cust_password">Mật khẩu *</label>
                        <div class="login-password-wrap">
                            <input type="password" class="form-control" id="cust_password" name="cust_password" placeholder="Nhập mật khẩu" required>
                            <button class="login-toggle-password" type="button" aria-label="Hiện hoặc ẩn mật khẩu">
                                <i class="fa fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="login-actions">
                        <input type="submit" class="btn btn-success login-btn" value="Đăng nhập" name="form1">
                        <a href="forget-password.php" style="color:#e4144d;font-weight:600;">Quên mật khẩu?</a>
                    </div>
                </form>

                <p class="login-register-hint">
                    Chưa có tài khoản?
                    <a href="registration.php">Đăng ký ngay</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var toggleBtn = document.querySelector('.login-toggle-password');
        var passwordInput = document.getElementById('cust_password');

        if (!toggleBtn || !passwordInput) {
            return;
        }

        toggleBtn.addEventListener('click', function() {
            var icon = toggleBtn.querySelector('i');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                if (icon) {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            } else {
                passwordInput.type = 'password';
                if (icon) {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }
        });
    });
</script>

<?php require_once('footer.php'); ?>