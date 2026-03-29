<?php require_once('header.php'); ?>
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
            $error_message .= 'Email không tồn tại trong hệ thống.<br>';
        } else {
            //using MD5 form
            if( $row_password != md5($cust_password) ) {
                $error_message .= 'Mật khẩu không đúng.<br>';
            } else {
                if($cust_status == 0) {
                    $error_message .= 'Tài khoản chưa được kích hoạt. Vui lòng kiểm tra email để xác minh.<br>';
                } else {
                    $_SESSION['customer'] = $row;
                    header("location: ".BASE_URL."index.php");
                }
            }
            
        }
    }
}
?>

<div class="page-banner" style="background-color:#444;background-image: url(assets/uploads/<?php echo $banner_login; ?>);">
    <div class="inner">
        <h1>Đăng nhập</h1>
    </div>
</div>

<div class="page">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="user-content">

                    
                    <form action="" method="post">
                        <?php $csrf->echoInputField(); ?>                  
                        <div class="row">
                            <div class="col-md-4"></div>
                            <div class="col-md-4">
                                <?php
                                if($error_message != '') {
                                    echo "<div class='error' style='padding: 10px;background:#f1f1f1;margin-bottom:20px;'>".$error_message."</div>";
                                }
                                if($success_message != '') {
                                    echo "<div class='success' style='padding: 10px;background:#f1f1f1;margin-bottom:20px;'>".$success_message."</div>";
                                }
                                ?>
                                <div class="form-group">
                                    <label for="">Email *</label>
                                    <input type="email" class="form-control" name="cust_email" placeholder="Nhập địa chỉ email">
                                </div>
                                <div class="form-group">
                                    <label for="">Mật khẩu *</label>
                                    <input type="password" class="form-control" name="cust_password" placeholder="Nhập mật khẩu">
                                </div>
                                <div class="form-group">
                                    <label for=""></label>
                                    <input type="submit" class="btn btn-success" value="Đăng nhập" name="form1">
                                </div>
                                <a href="forget-password.php" style="color:#e4144d;">Quên mật khẩu?</a>
                            </div>
                        </div>                        
                    </form>
                </div>                
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>