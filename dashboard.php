<?php require_once('header.php'); ?>

<?php
// Check if the customer is logged in or not
if(!isset($_SESSION['customer'])) {
    safe_redirect(BASE_URL.'logout.php');
} else {
    // If customer is logged in, but admin make him inactive, then force logout this user.
    $statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_id=? AND cust_status=?");
    $statement->execute(array($_SESSION['customer']['cust_id'],0));
    $total = $statement->rowCount();
    if($total) {
        safe_redirect(BASE_URL.'logout.php');
    }
}
?>

<div class="page">
    <div class="container">
        <div class="account-layout">
            <div class="account-sidebar-col"> 
                <?php require_once('customer-sidebar.php'); ?>
            </div>
            <div class="account-content-col">
                <div class="user-content account-content-card">
                    <h3 class="text-center">
                        Tài khoản của tôi
                    </h3>
                </div>                
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>