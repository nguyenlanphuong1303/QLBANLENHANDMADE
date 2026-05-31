<?php
// app/views/shares/account_sidebar.php
$activePage = $_GET['url'] ?? '';

// Fallback if $user is not passed from controller
if (!isset($user) || empty($user)) {
    if (isset($_SESSION['user_id'])) {
        $user = (object)[
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'] ?? 'Người dùng',
            'avatar' => $_SESSION['user_avatar'] ?? null
        ];
    }
}
?>
<aside class="account-sidebar">
    <div class="sidebar-user-info">
        <?php
        if (isset($user)):
            $avatar = !empty($user->avatar) ?
                BASE_URL . 'public/uploads/avatars/' . $user->avatar :
                'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=random&color=fff';
        ?>
            <img src="<?php echo $avatar; ?>" alt="Avatar" class="sidebar-avatar">
            <div class="sidebar-username-group">
                <span class="sidebar-username"><?php echo htmlspecialchars($user->name); ?></span>
                <a href="<?php echo BASE_URL; ?>index.php?url=Page/profile" class="sidebar-edit-profile">
                    <i class="fas fa-pen"></i> Sửa hồ sơ
                </a>
            </div>
        <?php endif; ?>
    </div>

    <ul class="sidebar-menu">
        <li class="sidebar-menu-item">
            <a href="<?php echo BASE_URL; ?>index.php?url=Page/profile" class="sidebar-menu-parent <?php echo (strpos($activePage, 'Page/profile') !== false || strpos($activePage, 'Page/bank') !== false || strpos($activePage, 'Address') !== false || strpos($activePage, 'Page/password') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-user-circle text-primary"></i>
                <span class="parent-text">Tài khoản của tôi</span>
            </a>
            <ul class="sidebar-submenu" style="<?php echo (strpos($activePage, 'Page/profile') !== false || strpos($activePage, 'Page/bank') !== false || strpos($activePage, 'Address') !== false || strpos($activePage, 'Page/password') !== false) || empty($activePage) ? 'display: block;' : ''; ?>">
                <li>
                    <a href="<?php echo BASE_URL; ?>index.php?url=Page/profile" class="<?php echo $activePage === 'Page/profile' ? 'active' : ''; ?>">
                        <i class="fas fa-user-edit mr-2" style="font-size: 13px; width: 15px;"></i> Hồ sơ
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>index.php?url=Page/bank" class="<?php echo $activePage === 'Page/bank' ? 'active' : ''; ?>">
                        <i class="fas fa-university mr-2" style="font-size: 13px; width: 15px;"></i> Ngân hàng
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>index.php?url=Address/index" class="<?php echo (strpos($activePage, 'Address') !== false) || empty($activePage) ? 'active' : ''; ?>">
                        <i class="fas fa-map-marker-alt mr-2" style="font-size: 13px; width: 15px; color: #ee225b;"></i> Địa chỉ
                    </a>
                </li>
                <li>
                    <a href="<?php echo BASE_URL; ?>index.php?url=Page/password" class="<?php echo $activePage === 'Page/password' ? 'active' : ''; ?>">
                        <i class="fas fa-key mr-2" style="font-size: 13px; width: 15px;"></i> Đổi mật khẩu
                    </a>
                </li>
            </ul>
        </li>
        <li class="sidebar-menu-item">
            <a href="<?php echo BASE_URL; ?>index.php?url=Page/orders" class="sidebar-menu-link <?php echo $activePage === 'Page/orders' ? 'active' : ''; ?>">
                <i class="fas fa-file-alt text-danger"></i> Đơn hàng đã mua
            </a>
        </li>
        <li class="sidebar-menu-item">
            <a href="<?php echo BASE_URL; ?>index.php?url=Wishlist/index" class="sidebar-menu-link <?php echo $activePage === 'Wishlist/index' ? 'active' : ''; ?>">
                <i class="fas fa-heart" style="color: #ee225b;"></i> Sản phẩm yêu thích
            </a>
        </li>
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <li class="sidebar-menu-item">
                <a href="<?php echo BASE_URL; ?>index.php?url=Dashboard" class="sidebar-menu-link">
                    <i class="fas fa-chart-line text-success"></i> Dashboard Admin
                </a>
            </li>

        <?php endif; ?>

        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'seller'): ?>
            <li class="sidebar-menu-item">
                <a href="<?php echo BASE_URL; ?>index.php?url=Product/myProducts" class="sidebar-menu-link <?php echo $activePage === 'Product/myProducts' ? 'active' : ''; ?>">
                    <i class="fas fa-store text-warning"></i> Quản lý Shop
                </a>
            </li>
        <?php endif; ?>
    </ul>
</aside>

<style>
    .sidebar-submenu {
        list-style: none;
        padding-left: 32px;
        margin-top: 5px;
        display: none;
    }

    .sidebar-submenu li a {
        display: block;
        padding: 8px 0;
        font-size: 14px;
        color: rgba(0, 0, 0, .65);
        text-decoration: none;
        transition: color 0.2s;
    }

    .sidebar-submenu li a:hover,
    .sidebar-submenu li a.active {
        color: #ee225b;
    }

    .sidebar-menu-parent {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px 0;
        cursor: pointer;
        text-decoration: none !important;
    }

    .sidebar-menu-parent:hover .parent-text {
        color: #ee225b;
    }

    .sidebar-menu-parent .parent-text {
        font-size: 14px;
        color: #333;
        font-weight: 500;
        transition: color 0.2s;
    }

    .sidebar-menu-parent.active .parent-text {
        font-weight: 600;
    }

    .sidebar-user-info {
        display: flex;
        padding: 15px 0;
        border-bottom: 1px solid #efefef;
        margin-bottom: 15px;
        align-items: center;
        gap: 15px;
    }

    .sidebar-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .sidebar-username {
        font-weight: 600;
        font-size: 14px;
        color: #333;
        display: block;
        margin-bottom: 4px;
    }

    .sidebar-edit-profile {
        font-size: 12px;
        color: #888;
        text-decoration: none !important;
    }

    .sidebar-menu-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        font-size: 14px;
        color: #333;
        text-decoration: none !important;
        transition: color 0.2s;
    }

    .sidebar-menu-link i {
        font-size: 16px;
        width: 20px;
        text-align: center;
    }

    .sidebar-menu-link:hover,
    .sidebar-menu-link.active {
        color: #ee225b;
    }

    .btn-request-permission {
        background: #f0f2f5;
        color: #1c1e21;
        border: none;
        padding: 10px 20px;
        border-radius: 50px;
        font-size: 13.5px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        width: 100%;
        margin-top: 10px;
        display: flex;
        justify-content: center;
        align-items: center;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    .btn-request-permission:hover {
        background: #e4e6eb;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .btn-request-permission:active {
        transform: translateY(0);
    }

    .btn-request-permission:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }
</style>