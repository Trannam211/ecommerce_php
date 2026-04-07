<?php require_once('header.php'); ?>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
foreach ($result as $row) {
    $banner_forget_password = $row['banner_forget_password'];
}
?>

<?php
if(isset($_POST['form1'])) {

    $valid = 1;
        
    if(empty($_POST['cust_email'])) {
        $valid = 0;
        $error_message .= 'Địa chỉ email không được để trống'."\\n";
    } else {
        if (filter_var($_POST['cust_email'], FILTER_VALIDATE_EMAIL) === false) {
            $valid = 0;
            $error_message .= 'Địa chỉ email không hợp lệ'."\\n";
        } else {
            $statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_email=?");
            $statement->execute(array($_POST['cust_email']));
            $total = $statement->rowCount();                        
            if(!$total) {
                $valid = 0;
                $error_message .= 'Địa chỉ email không tồn tại trong hệ thống'."\\n";
            }
        }
    }

    if($valid == 1) {

        $statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                           
        foreach ($result as $row) {
            $forget_password_message = $row['forget_password_message'];
        }

        $token = md5(rand());
        $now = time();

        $statement = $pdo->prepare("UPDATE tbl_customer SET cust_token=?,cust_timestamp=? WHERE cust_email=?");
        $statement->execute(array($token,$now,strip_tags($_POST['cust_email'])));

        $base_url_for_mail = (string)BASE_URL;
        if(trim($base_url_for_mail) === '') {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = isset($_SERVER['HTTP_HOST']) ? trim((string)$_SERVER['HTTP_HOST']) : '';
            $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
            $root_dir = preg_replace('#/frontend$#', '', $script_dir);
            if($host !== '') {
                $base_url_for_mail = $scheme.'://'.$host.rtrim($root_dir, '/').'/';
            }
        }

        $reset_link = $base_url_for_mail.'frontend/reset-password.php?email='.urlencode((string)$_POST['cust_email']).'&token='.urlencode($token);

        $message = '<p>Để đặt lại mật khẩu, vui lòng bấm vào liên kết bên dưới.<br> <a href="'.htmlspecialchars($reset_link, ENT_QUOTES, 'UTF-8').'">Bấm vào đây</a>';
        
        $to      = $_POST['cust_email'];
        $subject = 'YÊU CẦU ĐẶT LẠI MẬT KHẨU - WIZARD ECOMMERCE';

        $mail_domain = '';
        if(trim((string)BASE_URL) !== '') {
            $parsed = @parse_url((string)BASE_URL);
            if(is_array($parsed) && isset($parsed['host'])) {
                $mail_domain = (string)$parsed['host'];
            } else {
                $mail_domain = preg_replace('#^https?://#i', '', (string)BASE_URL);
                $mail_domain = preg_replace('#/.*$#', '', $mail_domain);
            }
        }
        if(trim($mail_domain) === '') {
            $mail_domain = isset($_SERVER['HTTP_HOST']) ? trim((string)$_SERVER['HTTP_HOST']) : 'localhost';
        }

        $headers = "From: noreply@" . $mail_domain . "\r\n" .
                   "Reply-To: noreply@" . $mail_domain . "\r\n" .
                   "X-Mailer: PHP/" . phpversion() . "\r\n" . 
                   "MIME-Version: 1.0\r\n" . 
                   "Content-Type: text/html; charset=UTF-8\r\n";

        mail($to, $subject, $message, $headers);

        $success_message = $forget_password_message;
    }
}
?>

<div class="page-banner" style="background-color:#444;background-image: url(../assets/uploads/<?php echo $banner_forget_password; ?>);">
    <div class="inner">
        <h1>Quên mật khẩu</h1>
    </div>
</div>

<div class="page">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="user-content">
                    <?php
                    if($error_message != '') {
                        echo "<script>alert('".$error_message."')</script>";
                    }
                    if($success_message != '') {
                        echo "<script>alert('".$success_message."')</script>";
                    }
                    ?>
                    <form action="" method="post">
                        <?php $csrf->echoInputField(); ?>
                        <div class="row">
                            <div class="col-md-4"></div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">Địa chỉ email *</label>
                                    <input type="email" class="form-control" name="cust_email">
                                </div>
                                <div class="form-group">
                                    <label for=""></label>
                                    <input type="submit" class="btn btn-primary" value="Gửi" name="form1">
                                </div>
                                <a href="login.php" style="color:#e4144d;">Quay lại trang đăng nhập</a>
                            </div>
                        </div>                        
                    </form>
                </div>                
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>