<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'app/config/database.php';
require_once 'app/models/ProductModel.php';
require_once 'app/models/CategoryModel.php';

$db = (new Database())->getConnection();
$productModel = new ProductModel($db);
$categoryModel = new CategoryModel($db);

// IMAGE RECOVERY: Tự động sửa lỗi mất ảnh trong Session bằng cách đồng bộ với Database
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $id => $item) {
        if (empty($item['image'])) {
            $p_sync = $productModel->getProductById($id);
            if ($p_sync) {
                $_SESSION['cart'][$id]['image'] = $p_sync->image;
            }
        }
    }
}

$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
$allCategories = $categoryModel->getCategories();

// Load User data and Wishlist cache for current user if not loaded
if (isset($_SESSION['user_id'])) {
    require_once 'app/models/WishlistModel.php';
    require_once 'app/models/UserModel.php';

    $userModel = new UserModel($db);
    $wishlistModel = new WishlistModel($db);

    // Refresh role in case it was changed (e.g. seller approval)
    $currUser = $userModel->getUserById($_SESSION['user_id']);
    if ($currUser) {
        $_SESSION['user_role'] = $currUser->role;
    }

    $_SESSION['wishlist_items'] = $wishlistModel->getUserWishlistIds($_SESSION['user_id']);
}
$wishlistItems = $_SESSION['wishlist_items'] ?? [];
?>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GÌ CŨNG MÓC</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- Custom CSS -->
    <link href="<?php echo BASE_URL; ?>public/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>public/css/profile.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>public/css/orders.css?v=<?php echo time(); ?>" rel="stylesheet">
    <?php if (empty($_GET['url']) || strpos($_GET['url'], 'Product') === 0): ?>
        <link href="<?php echo BASE_URL; ?>public/css/handmade.css?v=<?php echo time(); ?>" rel="stylesheet">
    <?php endif; ?>
    <!-- jQuery loaded early so all inline scripts can use it -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>

<body>

    <header class="heaven-header">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="container d-flex justify-content-between align-items-center py-1">
                <div class="top-left fas fa-phone-alt font-weight-bold text-uppercase" style="font-size: 16px; letter-spacing: 1px;">
                    0964.325.348
                </div>
                <div class="top-center d-none d-md-block" style="font-size: 16px;">
                    <i class="far fa-clock"></i> hoạt động 08.00 - 20.00 &nbsp;
                </div>
                <div class="top-right d-flex align-items-center" style="font-size: 16px;">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="user-profile-header mx-3">
                            <a href="<?php echo BASE_URL; ?>index.php?url=Page/orders" class="avatar-container" title="Xem đơn hàng của tôi">
                                <?php
                                $avatar = !empty($_SESSION['user_avatar']) ?
                                    BASE_URL . 'public/uploads/avatars/' . $_SESSION['user_avatar'] :
                                    'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['user_name']) . '&background=random&color=fff';
                                ?>
                                <img src="<?php echo $avatar; ?>" alt="Avatar" class="avatar-image" id="profileAvatar">
                                <div class="avatar-overlay">
                                    <i class="fas fa-camera"></i>
                                </div>
                            </a>

                            <!-- Profile Dropdown -->
                            <div class="profile-dropdown">
                                <div class="profile-dropdown-header">
                                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                                    <span class="user-role"><?php echo isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'Khách hàng'; ?></span>
                                </div>
                                <div class="profile-dropdown-list">
                                    <a href="<?php echo BASE_URL; ?>index.php?url=Page/profile" class="profile-dropdown-item" style="color: #75c794; font-weight: bold;">
                                        <i class="fas fa-user-circle"></i> Tài khoản của tôi
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>index.php?url=Page/orders" class="profile-dropdown-item" style="color: #75c794; font-weight: bold;">
                                        <i class="fas fa-shopping-bag"></i> Đơn hàng của tôi
                                    </a>

                                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                        <a href="<?php echo BASE_URL; ?>index.php?url=Dashboard" class="profile-dropdown-item" style="color: #75c794; font-weight: bold;">
                                            <i class="fas fa-chart-line"></i> Dashboard Admin
                                        </a>

                                    <?php endif; ?>

                                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'seller'): ?>
                                        <a href="<?php echo BASE_URL; ?>index.php?url=Seller" class="profile-dropdown-item" style="color: #75c794; font-weight: bold;">
                                            <i class="fas fa-store"></i> Dashboard Seller
                                        </a>
                                    <?php endif; ?>

                                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user'): ?>
                                        <a href="<?php echo BASE_URL; ?>index.php?url=Seller/register" class="profile-dropdown-item" style="color: #75c794; font-weight: bold;">
                                            <i class="fas fa-hand-holding-heart"></i> Trở thành người bán
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <div class="profile-dropdown-footer">
                                    <a href="<?php echo BASE_URL; ?>index.php?url=Page/logout" class="logout-btn-link">
                                        <div class="logout-btn">
                                            <i class="fas fa-sign-out-alt"></i> Đăng xuất
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>index.php?url=Page/register" class="small text-white mr-3" style="text-decoration: none; font-weight: 500;">Đăng ký</a>
                        <a href="<?php echo BASE_URL; ?>index.php?url=Page/login" class="login-register-btn mr-3">Đăng nhập</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Middle Bar -->
        <div class="middle-bar bg-white">
            <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between py-2">
                <a href="<?php echo BASE_URL; ?>index.php?url=Product/" class="red-logo mb-2 mb-md-0">
                    <img src="<?php echo BASE_URL; ?>public/images/logolen.jpg" alt="GÌ CŨNG MÓC" class="header-logo-img">
                </a>

                <form class="red-search-form w-100" action="<?php echo BASE_URL; ?>index.php" method="GET" id="mainSearchForm">
                    <input type="hidden" name="url" value="Product/search">
                    <input type="text" name="q" id="mainSearchInput" placeholder="Tìm kiếm sản phẩm..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>" autocomplete="off">
                    <button type="submit"><i class="fas fa-search"></i></button>
                    <!-- Search Results Dropdown -->
                    <div id="searchAutocompleteResults" class="search-results-dropdown"></div>
                </form>
            </div>
        </div>
        <!-- Sticky Nav Bar -->
        <div class="nav-bar-wrapper" style="background-color: var(--nav-dark-green);">
            <div class="container d-flex align-items-center">
                <div class="hamburger-menu-modern mr-3 d-md-none" id="menu-toggle">
                    <i class="fas fa-bars text-white"></i>
                </div>
                <div class="menu-overlay d-md-none" id="menu-overlay"></div>
                <ul class="nav-menu-modern d-none d-md-flex m-0 p-0" id="nav-menu">
                    <li><a href="<?php echo BASE_URL; ?>index.php?url=Product/" class="text-white">Trang Chủ</a></li>

                    <li class="has-mega-menu">
                        <a href="<?php echo BASE_URL; ?>index.php?url=Product/group/handmade" class="text-white">Sản Phẩm Len <i class="fas fa-chevron-down px-1" style="font-size: 10px;"></i></a>
                        <?php
                        $listHandmade = array_filter($allCategories ?? [], fn($c) => in_array($c->name, [
                            'Búp bê len',
                            'Thú bông len',
                            'Túi len',
                            'Nón, khăn len',
                            'Đồ gia dụng handmade'
                        ]));
                        if (!empty($listHandmade)):
                        ?>
                            <!-- Mega Menu -->
                            <div class="mega-menu-container">
                                <ul class="mega-list">
                                    <?php foreach ($listHandmade as $cat): ?>
                                        <li><a href="<?php echo BASE_URL; ?>index.php?url=Product/category/<?php echo $cat->id; ?>"><?php echo htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8'); ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </li>
                    <li><a href="<?php echo BASE_URL; ?>index.php?url=Product/group/keychain">Móc Khóa</a></li>

                    <li class="has-mega-menu">
                        <a href="<?php echo BASE_URL; ?>index.php?url=Product/group/flowers">Hoa Len <i class="fas fa-chevron-down px-1" style="font-size: 10px;"></i></a>
                        <?php
                        $listFlowers = array_filter($allCategories ?? [], fn($c) => in_array($c->name, [
                            'Hoa lẻ ',
                            'Hoa bó',
                            'Hoa mix ngẫu nhiên'
                        ]));
                        if (!empty($listFlowers)):
                        ?>
                            <!-- Mega Menu for Flowers -->
                            <div class="mega-menu-container">
                                <ul class="mega-list">
                                    <?php foreach ($listFlowers as $cat): ?>
                                        <li><a href="<?php echo BASE_URL; ?>index.php?url=Product/category/<?php echo $cat->id; ?>"><?php echo htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8'); ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </li>
                    <li class="has-mega-menu">
                        <a href="<?php echo BASE_URL; ?>index.php?url=Product/group/yarn">Len - Sợi <i class="fas fa-chevron-down px-1" style="font-size: 10px;"></i></a>
                        <?php
                        $listYarn = array_filter($allCategories ?? [], fn($c) => in_array(trim($c->name), [
                            'Sợi tự nhiên',
                            'Sợi tổng hợp',
                            'Sợi chuyên móc thú bông'
                        ]));
                        if (!empty($listYarn)):
                        ?>
                            <!-- Mega Menu for Yarn -->
                            <div class="mega-menu-container">
                                <ul class="mega-list">
                                    <?php foreach ($listYarn as $cat): ?>
                                        <li><a href="<?php echo BASE_URL; ?>index.php?url=Product/category/<?php echo $cat->id; ?>"><?php echo htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8'); ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </li>
                    <li class="has-mega-menu">
                        <a href="<?php echo BASE_URL; ?>index.php?url=Product/group/tools">Dụng cụ Đan - Móc <i class="fas fa-chevron-down px-1" style="font-size: 10px;"></i></a>
                        <?php
                        $listTools = array_filter($allCategories ?? [], fn($c) => in_array($c->name, [
                            'Kim móc các loại',
                            'Kim đan các loại',
                            'Phụ kiện hỗ trợ đan móc len',
                            'Dụng cụ đan – móc',
                        ]));
                        if (!empty($listTools)):
                        ?>
                            <!-- Mega Menu for Tools -->
                            <div class="mega-menu-container">
                                <ul class="mega-list">
                                    <?php foreach ($listTools as $cat): ?>
                                        <li><a href="<?php echo BASE_URL; ?>index.php?url=Product/category/<?php echo $cat->id; ?>"><?php echo htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8'); ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </li>
                    <li><a href="<?php echo BASE_URL; ?>index.php?url=Page/about">Giới thiệu</a></li>
                    <li><a href="<?php echo BASE_URL; ?>index.php?url=Page/contact">Liên Hệ</a></li>
                </ul>

                <div class="nav-actions d-flex align-items-center">

                    <!-- Notification Bell -->
                    <div class="notification-wrapper position-relative mr-3" style="cursor: pointer;">
                        <a href="#" class="cart-btn-red" style="display: flex; align-items: center; justify-content: center; text-decoration: none;" title="Thông báo">
                            <i class="fas fa-bell"></i>
                            <span id="notification-badge" class="position-absolute badge badge-pill badge-light text-danger shadow-sm d-none" style="top: -8px; right: -8px; border: 1px solid var(--primary-color); font-size: 0.75rem; padding: 4px 6px;">0</span>
                        </a>
                        <!-- Notification Dropdown -->
                        <div class="mini-notification-dropdown shadow" id="notification-dropdown">
                            <div class="notification-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="notification-title"><i class="fas fa-bell mr-2" style="color: var(--noti-primary-green);"></i>Thông báo</span>
                                    <div class="d-flex align-items-center">
                                        <button id="toggle-noti-theme" class="btn btn-link text-muted p-0 mr-3" style="outline: none; box-shadow: none;" title="Chuyển chế độ tối">
                                            <i class="far fa-moon" style="font-size: 1.05rem;"></i>
                                        </button>
                                        <a href="#" class="mark-all-read small" style="font-weight: 700; color: var(--noti-primary-green); text-decoration: none;">Đánh dấu tất cả đã đọc</a>
                                    </div>
                                </div>
                                <div class="notification-filters" id="notification-filters">
                                    <div class="filter-chip active" data-filter="all">
                                        <i class="fas fa-list-ul"></i> Tất cả
                                        <span class="badge-count d-none" id="badge-all">0</span>
                                    </div>
                                    <div class="filter-chip" data-filter="seller_request">
                                        <i class="fas fa-user-shield"></i> <?php echo (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'seller') ? 'Yêu cầu cập nhật' : 'Yêu cầu phân quyền'; ?>
                                        <span class="badge-count d-none" id="badge-seller_request">0</span>
                                    </div>
                                    <div class="filter-chip" data-filter="order">
                                        <i class="fas fa-shopping-basket"></i> Đơn hàng
                                        <span class="badge-count d-none" id="badge-order">0</span>
                                    </div>
                                    <div class="filter-chip" data-filter="review">
                                        <i class="fas fa-star"></i> Đánh giá
                                        <span class="badge-count d-none" id="badge-review">0</span>
                                    </div>
                                </div>
                            </div>
                            <div class="notification-items-container" id="notification-list">
                                <div class="p-4 text-center text-muted">Không có thông báo mới</div>
                            </div>
                        </div>
                    </div>

                    <div class="cart-wrapper-red position-relative">
                        <a href="<?php echo BASE_URL; ?>index.php?url=Cart/index" class="cart-btn-red" style="display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-shopping-cart"></i>
                            <span id="cart-badge" class="position-absolute badge badge-pill badge-light text-success shadow-sm <?php echo $cartCount > 0 ? '' : 'd-none'; ?>" style="top: -8px; right: -8px; border: 1px solid var(--primary-color); font-size: 0.75rem; padding: 4px 6px;"><?php echo $cartCount; ?></span>
                        </a>

                        <!-- Mini Cart Dropdown -->
                        <div class="mini-cart-dropdown shadow">
                            <?php if (empty($_SESSION['cart'])): ?>
                                <div class="p-4 text-center text-muted">Chưa có sản phẩm nào trong giỏ hàng</div>
                            <?php else: ?>
                                <div class="mini-cart-items p-3">
                                    <?php
                                    $subtotal = 0;
                                    foreach ($_SESSION['cart'] as $id => $item):
                                        $subtotal += ($item['price'] * $item['quantity']);
                                    ?>
                                        <div class="mini-cart-item d-flex align-items-center mb-3">
                                            <div class="item-img mr-3">
                                                <?php if (!empty($item['image'])): ?>
                                                    <?php
                                                    $itemImg = $item['image'];
                                                    // Hỗ trợ cả file cũ (uploads/...) và file mới (basename)
                                                    $finalItemImg = (strpos($itemImg, 'public/') === false) ?
                                                        ((strpos($itemImg, 'uploads/') !== false) ? 'public/' . $itemImg : 'public/uploads/' . $itemImg) :
                                                        $itemImg;
                                                    ?>
                                                    <img src="<?php echo BASE_URL . htmlspecialchars($finalItemImg, ENT_QUOTES, 'UTF-8'); ?>" alt="p" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                                <?php else: ?>
                                                    <div class="bg-light d-flex align-items-center justify-content-center border-radius-4" style="width: 50px; height: 50px;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="item-details flex-grow-1">
                                                <h6 class="mb-0 text-primary-link" style="font-size: 0.9rem; color: #3b82f6;"><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></h6>
                                                <small class="text-muted"><?php echo $item['quantity']; ?> × <?php echo number_format($item['price'], 0, ',', '.'); ?> ₫</small>
                                            </div>
                                            <a href="<?php echo BASE_URL; ?>index.php?url=Cart/remove/<?php echo $id; ?>" class="text-muted ml-2 remove-mini-item">
                                                <i class="far fa-times-circle"></i>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mini-cart-footer p-3 border-top">
                                    <div class="d-flex justify-content-center mb-3">
                                        <span class="text-muted mr-2">Tổng số phụ:</span>
                                        <span class="font-weight-bold" style="color: #666;"><?php echo number_format($subtotal, 0, ',', '.'); ?> ₫</span>
                                    </div>
                                    <a href="<?php echo BASE_URL; ?>index.php?url=Cart/index" class="btn btn-success btn-block mb-2 py-2" style="background-color: var(--primary-color); border: none; font-weight: bold; border-radius: 4px;">Xem giỏ hàng</a>
                                    <a href="<?php echo BASE_URL; ?>index.php?url=Cart/checkout" id="btnMiniCheckout" class="btn btn-info btn-block py-2" style="background-color: var(--nav-dark-green); border: none; font-weight: bold; border-radius: 4px;">Thanh toán</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menu-toggle');
            const menuClose = document.getElementById('menu-close');
            const navMenu = document.getElementById('nav-menu');
            const menuOverlay = document.getElementById('menu-overlay');
            const hasMegaMenu = document.querySelectorAll('.has-mega-menu');

            // Toggle Menu
            function toggleMenu() {
                if (navMenu) navMenu.classList.toggle('active');
                if (menuOverlay) menuOverlay.classList.toggle('active');
                document.body.style.overflow = (navMenu && navMenu.classList.contains('active')) ? 'hidden' : '';
            }

            if (menuToggle) menuToggle.addEventListener('click', toggleMenu);
            if (menuClose) menuClose.addEventListener('click', toggleMenu);
            if (menuOverlay) menuOverlay.addEventListener('click', toggleMenu);

            // Toggle Mega Menu on Mobile
            hasMegaMenu.forEach(item => {
                const link = item.querySelector('a');
                if (link) {
                    link.addEventListener('click', function(e) {
                        if (window.innerWidth < 768) {
                            e.preventDefault();
                            item.classList.toggle('open');
                        }
                    });
                }
            });

            // Live Search Logic
            const searchInput = document.getElementById('mainSearchInput');
            const resultsDropdown = document.getElementById('searchAutocompleteResults');
            let debounceTimer;

            function highlightText(text, q) {
                if (!q) return text;
                const regex = new RegExp(`(${q})`, 'gi');
                return text.replace(regex, '<b>$1</b>');
            }

            if (searchInput && resultsDropdown) {
                searchInput.addEventListener('input', function() {
                    const query = this.value.trim();
                    clearTimeout(debounceTimer);

                    if (query.length < 1) {
                        resultsDropdown.innerHTML = '';
                        resultsDropdown.classList.remove('active');
                        return;
                    }

                    debounceTimer = setTimeout(() => {
                        fetch(`<?php echo BASE_URL; ?>index.php?url=Product/searchAjax&q=${encodeURIComponent(query)}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.length > 0) {
                                    let html = '';
                                    data.forEach(item => {
                                        const highlightedName = highlightText(item.name, query);
                                        html += `
                                            <a href="${item.url}" class="ajax-search-item">
                                                <div class="ajax-item-thumb">
                                                    <img src="${item.image}" alt="p">
                                                </div>
                                                <div class="ajax-item-info">
                                                    <div class="ajax-item-name">${highlightedName}</div>
                                                    <div class="ajax-item-price-group">
                                                        ${item.old_price ? `<span class="ajax-item-old-price">${item.old_price}</span>` : ''}
                                                        <span class="ajax-item-new-price">${item.price}</span>
                                                    </div>
                                                </div>
                                            </a>
                                        `;
                                    });
                                    resultsDropdown.innerHTML = html;
                                    resultsDropdown.classList.add('active');
                                } else {
                                    resultsDropdown.innerHTML = '';
                                    resultsDropdown.classList.remove('active');
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching search results:', error);
                            });
                    }, 300);
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!searchInput.contains(e.target) && !resultsDropdown.contains(e.target)) {
                        resultsDropdown.classList.remove('active');
                    }
                });

                // Re-open if clicking back on input with value
                searchInput.addEventListener('focus', function() {
                    if (this.value.trim().length > 0 && resultsDropdown.innerHTML !== '') {
                        resultsDropdown.classList.add('active');
                    }
                });
            }

            // Notification Polling & Modern UI System
            const notificationBadge = document.getElementById('notification-badge');
            const notificationDropdown = document.getElementById('notification-dropdown');
            let currentFilter = 'all';
            let cachedNotifications = [];

            // Theme Preference Persistence
            const savedNotiTheme = localStorage.getItem('noti-dark-mode');
            if (savedNotiTheme === 'true') {
                $(notificationDropdown).addClass('dark-noti');
                $('#toggle-noti-theme i').removeClass('far fa-moon').addClass('fas fa-sun');
            }

            $('#toggle-noti-theme').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(notificationDropdown).toggleClass('dark-noti');
                const isDark = $(notificationDropdown).hasClass('dark-noti');
                localStorage.setItem('noti-dark-mode', isDark);
                if (isDark) {
                    $(this).find('i').removeClass('far fa-moon').addClass('fas fa-sun');
                } else {
                    $(this).find('i').removeClass('fas fa-sun').addClass('far fa-moon');
                }
            });

            function showSkeletons() {
                const notificationList = document.getElementById('notification-list');
                if (!notificationList) return;
                notificationList.innerHTML = `
                    <div class="skeleton-noti-item">
                        <div class="skeleton-avatar"></div>
                        <div class="skeleton-details">
                            <div class="skeleton-line title"></div>
                            <div class="skeleton-line text"></div>
                            <div class="skeleton-line time"></div>
                        </div>
                    </div>
                    <div class="skeleton-noti-item">
                        <div class="skeleton-avatar"></div>
                        <div class="skeleton-details">
                            <div class="skeleton-line title"></div>
                            <div class="skeleton-line text"></div>
                            <div class="skeleton-line time"></div>
                        </div>
                    </div>
                    <div class="skeleton-noti-item">
                        <div class="skeleton-avatar"></div>
                        <div class="skeleton-details">
                            <div class="skeleton-line title"></div>
                            <div class="skeleton-line text"></div>
                            <div class="skeleton-line time"></div>
                        </div>
                    </div>
                `;
            }

            function renderNotifications(notifications) {
                const notificationList = document.getElementById('notification-list');
                if (!notificationList) return;

                // Dynamically normalize legacy/system-type notification categories based on link or content
                notifications.forEach(item => {
                    const link = item.link || '';
                    const message = item.message || '';
                    
                    // Normalize Order legacy notifications
                    if (message.includes('Có đơn đặt hàng mới #') || message.includes('Đơn hàng #') || message.includes('Yêu cầu trả hàng mới cho đơn hàng #')) {
                        item.type = 'order';
                        
                        // Dynamically rewrite legacy non-specific link to the specific order's detail page
                        if (link.includes('Dashboard/orders') || !link) {
                            const match = message.match(/(?:đơn đặt hàng mới #|Đơn hàng #|cho đơn hàng #)(\d+)/i);
                            if (match && match[1]) {
                                item.link = 'index.php?url=Dashboard/orderDetail/' + match[1];
                            }
                        }
                    }
                    
                    if (item.type === 'system' || !item.type) {
                        const msgLower = message.toLowerCase();
                        const linkLower = link.toLowerCase();
                        if (
                            linkLower.includes('dashboard/shopupdates') || 
                            msgLower.includes('cập nhật thông tin shop') || 
                            msgLower.includes('yêu cầu cập nhật thông tin') ||
                            msgLower.includes('cập nhật thông tin cửa hàng') ||
                            msgLower.includes('yêu cầu cập nhật thông tin cửa hàng') ||
                            (msgLower.includes('đã được admin phê duyệt') && msgLower.includes('cập nhật'))
                        ) {
                            item.type = 'shop_update';
                        } else if (
                            linkLower.includes('managesellers') || 
                            msgLower.includes('yêu cầu mở shop') || 
                            msgLower.includes('trở thành người bán')
                        ) {
                            item.type = 'seller_request';
                        }
                    }
                });

                // 1. Calculate and update pill badges (unread count per category)
                let counts = { all: 0, order: 0, seller_request: 0, review: 0 };
                notifications.forEach(item => {
                    if (item.is_read == 0 || item.is_read == '0') {
                        counts.all++;
                        const type = item.type || 'system';
                        if (['order', 'shipping', 'cancel', 'payment'].includes(type)) {
                            counts.order++;
                        } else if (['seller_request', 'shop_update'].includes(type)) {
                            counts.seller_request++;
                        } else if (type === 'review') {
                            counts.review++;
                        }
                    }
                });

                // Update DOM badges
                Object.keys(counts).forEach(key => {
                    const badge = document.getElementById(`badge-${key}`);
                    if (badge) {
                        if (counts[key] > 0) {
                            badge.textContent = counts[key];
                            badge.classList.remove('d-none');
                        } else {
                            badge.classList.add('d-none');
                        }
                    }
                });

                // Update top bell badge
                if (counts.all > 0) {
                    notificationBadge.textContent = counts.all;
                    notificationBadge.classList.remove('d-none');
                } else {
                    notificationBadge.classList.add('d-none');
                }

                // 2. Filter notifications based on current selected filter
                const filtered = notifications.filter(item => {
                    const type = item.type || 'system';
                    if (currentFilter === 'all') return true;
                    if (currentFilter === 'order') return ['order', 'shipping', 'cancel', 'payment'].includes(type);
                    if (currentFilter === 'seller_request') return ['seller_request', 'shop_update'].includes(type);
                    if (currentFilter === 'review') return type === 'review';
                    return type === currentFilter;
                });

                // 3. Render items
                if (filtered.length > 0) {
                    let html = '';
                    html += '<div class="notification-section-title">Mới nhất</div>';
                    filtered.forEach(item => {
                        let itemLink = item.link || '#';
                        if (itemLink.includes('Admin/manageSellers')) {
                            itemLink = itemLink.replace('Admin/manageSellers', 'Dashboard/manageSellers');
                        }
                        const link = itemLink !== '#' ? '<?php echo BASE_URL; ?>' + itemLink : '#';
                        const isUnread = (item.is_read == 0 || item.is_read == '0');
                        const unreadClass = isUnread ? 'unread' : '';

                        // Type to Icon Mapping
                        const type = item.type || 'system';
                        const iconMapping = {
                            'order': 'fa-shopping-basket',
                            'chat': 'fa-comment-dots',
                            'approved': 'fa-check-double',
                            'rejected': 'fa-times-circle',
                            'profile': 'fa-user-cog',
                            'payment': 'fa-credit-card',
                            'system': 'fa-info-circle',
                            'promotion': 'fa-tag',
                            'review': 'fa-star',
                            'seller_request': 'fa-store-alt',
                            'shop_update': 'fa-store-alt',
                            'shipping': 'fa-truck',
                            'wishlist': 'fa-heart',
                            'inventory': 'fa-warehouse',
                            'cancel': 'fa-ban',
                            'voucher': 'fa-ticket-alt'
                        };
                        const iconClass = iconMapping[type] || 'fa-bell';

                        // Default or Custom Avatar
                        const avatarUrl = item.sender_avatar ?
                            '<?php echo BASE_URL; ?>public/uploads/avatars/' + item.sender_avatar :
                            'https://ui-avatars.com/api/?name=System&background=24ab65&color=fff';

                        // Notification Card Markup (Premium rounded, hover effects)
                        html += `
                            <a href="${link}" class="notification-item ${unreadClass} mark-read-btn" data-id="${item.id}" data-type="${type}">
                                <div class="noti-avatar-wrapper">
                                    <img src="${avatarUrl}" class="noti-avatar" alt="User">
                                    <div class="noti-type-icon ${type}">
                                        <i class="fas ${iconClass}"></i>
                                    </div>
                                </div>
                                <div class="noti-details">
                                    <div class="noti-message">${item.message}</div>
                                    <div class="noti-time">
                                        <i class="far fa-clock"></i> ${item.created_at}
                                    </div>
                                </div>
                                ${item.thumbnail ? `<img src="<?php echo BASE_URL; ?>public/uploads/products/${item.thumbnail}" class="noti-thumb" alt="Product">` : ''}
                                ${isUnread ? '<div class="unread-dot"></div>' : ''}
                            </a>
                        `;
                    });
                    notificationList.innerHTML = html;
                } else {
                    notificationList.innerHTML = `
                        <div class="p-5 text-center text-muted">
                            <i class="fas fa-bell-slash d-block mb-3" style="font-size: 2rem; opacity: 0.3; color: var(--noti-primary-green);"></i>
                            Không có thông báo nào thuộc mục này
                        </div>
                    `;
                }
            }

            function fetchNotifications(showLoader = false) {
                if (showLoader) {
                    showSkeletons();
                }
                $.ajax({
                    url: '<?php echo BASE_URL; ?>index.php?url=Notification/getUnread',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            cachedNotifications = response.notifications || [];
                            renderNotifications(cachedNotifications);
                        }
                    }
                });
            }

            if (notificationBadge && notificationDropdown) {
                // Initial load with skeleton effect
                fetchNotifications(true);
                // Poll in the background every 15s without resetting the loading skeletons
                setInterval(fetchNotifications, 15000);

                // Toggle notification dropdown
                $('.notification-wrapper > a').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $(notificationDropdown).toggleClass('active');
                    $('.mini-cart-dropdown').removeClass('active');
                });

                // Close dropdown on click outside
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('.notification-wrapper').length) {
                        $(notificationDropdown).removeClass('active');
                    }
                });

                // Prevent click propagation inside dropdown unless clicking interactive elements
                $(notificationDropdown).on('click', function(e) {
                    if (!$(e.target).closest('.filter-chip, .mark-read-btn, .mark-all-read, #toggle-noti-theme').length) {
                        e.stopPropagation();
                    }
                });

                // Pill Filter Interaction (Simulate Loading on Tab Switch)
                $(notificationDropdown).on('click', '.filter-chip', function() {
                    const filter = $(this).data('filter');
                    if (currentFilter === filter) return;

                    currentFilter = filter;
                    $('.filter-chip').removeClass('active');
                    $(this).addClass('active');

                    // Micro-animation: Quick 350ms skeleton loader when tab shifts
                    showSkeletons();
                    setTimeout(() => {
                        renderNotifications(cachedNotifications);
                    }, 350);
                });

                // Mark single notification as read
                $(notificationDropdown).on('click', '.mark-read-btn', function(e) {
                    const id = $(this).data('id');
                    const href = $(this).attr('href');
                    if (id) {
                        e.preventDefault();
                        $.ajax({
                            url: '<?php echo BASE_URL; ?>index.php?url=Notification/markRead',
                            type: 'POST',
                            data: { id: id },
                            success: function() {
                                if (href && href !== '#') {
                                    window.location.href = href;
                                } else {
                                    fetchNotifications();
                                }
                            }
                        });
                    }
                });

                // Mark all notifications as read
                $(notificationDropdown).on('click', '.mark-all-read', function(e) {
                    e.preventDefault();
                    showSkeletons();
                    $.ajax({
                        url: '<?php echo BASE_URL; ?>index.php?url=Notification/markAllRead',
                        type: 'POST',
                        success: function() {
                            fetchNotifications();
                        }
                    });
                });
            }
        });
    </script>

    <div class="container">