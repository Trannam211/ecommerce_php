<?php require_once('header.php'); ?>

<style>
    .payment-success-wrap {
        max-width: 760px;
        margin: 18px auto;
        border: 1px solid #dfe7f1;
        background: #fff;
        padding: 20px 18px;
        text-align: center;
    }

    .payment-success-title {
        margin: 0 0 10px;
        color: #1f2d3d;
        font-size: 24px;
        font-weight: 700;
    }

    .payment-success-text {
        margin: 0 0 20px;
        color: #4a5a6b;
        font-size: 14px;
        line-height: 1.6;
    }

    .payment-success-btn {
        min-width: 170px;
    }

    @media (max-width: 767px) {
        .payment-success-title {
            font-size: 20px;
        }

        .payment-success-wrap {
            padding: 16px 14px;
        }
    }
</style>

<div class="page">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="payment-success-wrap">
                    <h3 class="payment-success-title">Chúc mừng! Thanh toán thành công.</h3>
                    <p class="payment-success-text">
                        Đơn hàng của bạn đã được ghi nhận trong hệ thống.<br>
                        Bạn có thể theo dõi trạng thái xử lý tại trang đơn hàng.
                    </p>
                    <a href="customer-order.php" class="btn btn-success payment-success-btn">Xem đơn hàng</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>