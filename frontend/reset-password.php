<?php require_once('header.php'); ?>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
foreach ($result as $row) {
    $banner_reset_password = $row['banner_reset_password'];
}
?>

<?php
if( !isset($_GET['email']) || !isset($_GET['token']) )
{
    safe_redirect('login.php');
}

$statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_email=? AND cust_token=?");
$statement->execute(array($_GET['email'],$_GET['token']));
$row = $statement->fetch(PDO::FETCH_ASSOC);
if(!$row)
{
    safe_redirect('login.php');
}
$saved_time = $row['cust_timestamp'];

$error_message2 = '';
if(time() - $saved_time > 86400)
{
    $error_message2 = 'Liên kết đặt lại mật khẩu đã hết hạn (24 giờ). Vui lòng thử lại.';
}

if(isset($_POST['form1'])) {

    $valid = 1;
    
    if( empty($_POST['cust_new_password']) || empty($_POST['cust_re_password']) )
    {
        $valid = 0;
        $error_message .= 'Vui lòng nhập mật khẩu mới và xác nhận mật khẩu'.'\\n';
    }
    else
    {
        if($_POST['cust_new_password'] != $_POST['cust_re_password'])
        {
            $valid = 0;
            $error_message .= 'Mật khẩu nhập lại không khớp'.'\\n';
        }
    }   

    if($valid == 1) {

        $cust_new_password = trim((string)$_POST['cust_new_password']);

        ensure_customer_password_storage($pdo);
        $cust_new_password_hash = password_hash($cust_new_password, PASSWORD_DEFAULT);
        if($cust_new_password_hash === false) {
            $valid = 0;
            $error_message .= 'Không thể tạo mật khẩu an toàn. Vui lòng thử lại.'.'\\n';
        } else {
        $statement = $pdo->prepare("UPDATE tbl_customer SET cust_password=?, cust_token=?, cust_timestamp=? WHERE cust_email=?");
        $statement->execute(array($cust_new_password_hash,'','',$_GET['email']));
        
        safe_redirect('reset-password-success.php');
        }
    }

    
}
?>

<div class="page-banner" style="background-color:#444;background-image: url(../assets/uploads/<?php echo $banner_reset_password; ?>);">
    <div class="inner">
        <h1>Đổi mật khẩu</h1>
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
                    ?>
                    <?php if($error_message2 != ''): ?>
                        <div class="error"><?php echo $error_message2; ?></div>
                    <?php else: ?>
                        <form action="" method="post">
                            <?php $csrf->echoInputField(); ?>
                            <div class="row">
                                <div class="col-md-4"></div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Mật khẩu mới *</label>
                                        <input type="password" class="form-control" name="cust_new_password">
                                    </div>
                                    <div class="form-group">
                                        <label for="">Nhập lại mật khẩu mới *</label>
                                        <input type="password" class="form-control" name="cust_re_password">
                                    </div>
                                    <div class="form-group">
                                        <label for=""></label>
                                        <input type="submit" class="btn btn-primary" value="Đổi mật khẩu" name="form1">
                                    </div>
                                </div>
                            </div>                        
                        </form>
                    <?php endif; ?>
                    
                </div>                
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>