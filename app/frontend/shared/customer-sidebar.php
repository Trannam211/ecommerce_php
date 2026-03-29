<?php
$current_page = basename($_SERVER['PHP_SELF']);
$account_menu_items = array(
    array('href' => 'customer-profile-update.php', 'label' => 'Cập nhật hồ sơ', 'icon' => 'fa-user'),
    array('href' => 'customer-billing-shipping-update.php', 'label' => 'Địa chỉ của tôi', 'icon' => 'fa-map-marker'),
    array('href' => 'customer-password-update.php', 'label' => 'Đổi mật khẩu', 'icon' => 'fa-lock'),
    array('href' => 'customer-order.php', 'label' => 'Đơn hàng', 'icon' => 'fa-shopping-bag'),
    array('href' => 'logout.php', 'label' => 'Đăng xuất', 'icon' => 'fa-sign-out')
);
?>

<style>
    .account-layout {
        display: flex;
        flex-wrap: wrap;
        gap: 18px;
    }

    .account-sidebar-col {
        width: 100%;
    }

    .account-content-col {
        width: 100%;
    }

    .account-content-card {
        background: #fff;
        border: 1px solid #dce6f4;
        border-radius: 0;
        box-shadow: 0 6px 20px rgba(12, 32, 78, 0.06);
        padding: 18px;
    }

    .account-content-card h3 {
        margin-top: 0;
        margin-bottom: 16px;
        color: #1f3d6d;
        font-weight: 700;
    }

    .account-nav-shell {
        margin: 0;
        padding: 14px;
        background: #fff;
        border: 1px solid #dce6f4;
        border-radius: 0;
        box-shadow: 0 6px 20px rgba(12, 32, 78, 0.08);
    }

    .account-nav-title {
        margin: 0 0 10px;
        font-size: 14px;
        font-weight: 700;
        color: #1f3d6d;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .account-nav-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .account-nav-list li {
        margin: 0;
    }

    .account-nav-link {
        display: flex;
        align-items: center;
        gap: 7px;
        width: 100%;
        padding: 10px 12px;
        border-radius: 0;
        border: 1px solid #d9e3f2;
        background: #f6f9ff;
        color: #213a66;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .account-nav-link:hover,
    .account-nav-link:focus {
        text-decoration: none;
        color: #0f5aa8;
        border-color: #a9c4ea;
        background: #edf4ff;
    }

    .account-nav-link.active {
        background: #1f78d1;
        border-color: #1f78d1;
        color: #fff;
    }

    .account-welcome-card {
        margin: 0 0 16px;
        padding: 14px 16px;
        border-radius: 0;
        background: linear-gradient(135deg, #f2f8ff, #ffffff);
        border: 1px solid #dbe8fb;
        color: #1f3d6d;
    }

    .account-welcome-card strong {
        font-size: 16px;
    }

    .account-form-actions {
        margin-top: 14px;
    }

    .account-form-actions .btn {
        min-width: 120px;
    }

    @media (min-width: 992px) {
        .account-sidebar-col {
            width: calc(25% - 9px);
        }

        .account-content-col {
            width: calc(75% - 9px);
        }
    }

    @media (max-width: 767px) {
        .account-content-card {
            padding: 14px;
        }
    }
</style>

<div class="account-welcome-card">
    <strong>Xin chào, <?php echo htmlspecialchars($_SESSION['customer']['cust_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
</div>

<div class="account-nav-shell">
    <p class="account-nav-title">Tài khoản của tôi</p>
    <ul class="account-nav-list">
        <?php foreach ($account_menu_items as $item): ?>
            <?php $is_active = ($current_page === $item['href']); ?>
            <li>
                <a class="account-nav-link <?php echo $is_active ? 'active' : ''; ?>" href="<?php echo $item['href']; ?>">
                    <i class="fa <?php echo $item['icon']; ?>"></i>
                    <span><?php echo $item['label']; ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>