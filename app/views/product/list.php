<?php include 'app/views/shares/header.php'; ?>
<?php 
// Ensure $current_url is defined to avoid errors in sorting/filtering
$current_url = $current_url ?? ($_GET['url'] ?? 'Product/index'); 
?>



<!-- Breadcrumbs -->
<?php if (!empty($breadcrumbs)): ?>
    <div class="container mt-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-white px-0" style="font-size: 14px;">
                <?php
                $count = count($breadcrumbs);
                $i = 1;
                foreach ($breadcrumbs as $label => $link):
                ?>
                    <?php if ($i < $count): ?>
                        <li class="breadcrumb-item"><a href="<?php echo $link; ?>" style="color: var(--primary-color); font-weight: 500;"><?php echo $label; ?></a></li>
                    <?php else: ?>
                        <li class="breadcrumb-item active text-muted" aria-current="page"><?php echo $label; ?></li>
                    <?php endif; ?>
                <?php
                    $i++;
                endforeach;
                ?>
            </ol>
        </nav>
    </div>
<?php endif; ?>

<!-- TOP SECTION: Success/Error Messages -->
<div class="container mt-3">
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 shadow-sm border-0" role="alert" style="background: #e8f5e9; color: #2e7d32; font-size: 14px;">
            <i class="fas fa-check-circle mr-2"></i> <?php echo $_SESSION['success_message'];
                                                        unset($_SESSION['success_message']); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-pill px-4 shadow-sm border-0" role="alert" style="background: #ffebee; color: #c62828; font-size: 14px;">
        <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $_SESSION['error_message'];
                                                        unset($_SESSION['error_message']); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>
</div>

<!-- HERO BANNER (Show only on Main Home or if requested) -->
<?php if (!isset($is_search_page) || !$is_search_page): ?>
    <?php if (($_GET['url'] ?? '') === 'Product/' || ($_GET['url'] ?? '') === 'Product' || empty($_GET['url'])): ?>
        <div class="container-fluid p-0">
            <div class="row no-gutters">
                <div class="col-12">
                    <div id="premiumBannerCarousel" class="carousel slide p-0 position-relative mb-4" data-ride="carousel" data-interval="3000" style="overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1); background: transparent;">
                        <ol class="carousel-indicators custom-tabs" style="bottom: 20px; margin-bottom: 0;">
                            <?php if (!empty($banners)): ?>
                                <?php foreach ($banners as $index => $banner): ?>
                                    <li data-target="#premiumBannerCarousel" data-slide-to="<?php echo $index; ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>"></li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li data-target="#premiumBannerCarousel" data-slide-to="0" class="active"></li>
                            <?php endif; ?>
                        </ol>

                        <div class="carousel-inner">
                            <?php if (!empty($banners)): ?>
                                <?php foreach ($banners as $index => $banner): ?>
                                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>" data-banner-id="<?php echo $banner->id; ?>">
                                        <?php 
                                        $ext = strtolower(pathinfo($banner->image, PATHINFO_EXTENSION));
                                        if ($ext === 'pdf'):
                                        ?>
                                            <embed src="<?php echo BASE_URL; ?>public/images/<?php echo $banner->image; ?>#toolbar=0&navpanes=0&scrollbar=0&view=FitH" type="application/pdf" width="100%" height="500px">
                                        <?php else: ?>
                                            <img src="<?php echo BASE_URL; ?>public/images/<?php echo $banner->image; ?>" class="d-block w-100" style="height: 500px; object-fit: cover;">
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="carousel-item active">
                                    <img src="<?php echo BASE_URL; ?>public/images/hero_banner_full.png" class="d-block w-100" style="height: 500px; object-fit: cover;">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
    <div class="container mt-2 mb-4">
        <div class="admin-quick-toolbar" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 16px; padding: 25px 30px; box-shadow: 0 10px 25px rgba(16, 185, 129, 0.2); display: flex; flex-direction: row; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div class="toolbar-info text-white">
                <h4 class="mb-1" style="font-weight: 800; letter-spacing: -0.5px;"><i class="fas fa-crown text-warning mr-2"></i>Khu vực Quản Trị Viên</h4>
                <p class="mb-0 text-white-50" style="font-size: 15px; font-weight: 500;">Quản lý và thêm mới sản phẩm hiển thị trên trang chủ ngay lập tức.</p>
            </div>
            <div class="toolbar-actions">
                <a href="<?php echo BASE_URL; ?>index.php?url=Product/add" class="btn btn-light rounded-pill px-4 py-2" style="font-weight: 700; color: #059669; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: transform 0.3s ease;">
                    <i class="fas fa-plus-circle mr-2"></i>Thêm Sản Phẩm Mới
                </a>
                <a href="<?php echo BASE_URL; ?>index.php?url=Dashboard/products" class="btn btn-outline-light rounded-pill px-4 py-2 ml-2" style="font-weight: 600; transition: background 0.3s ease;">
                    <i class="fas fa-list mr-2"></i>Kho Hàng
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (isset($is_home) && $is_home): ?>
    <?php
    $sections = [
        [
            'id' => 'all_products',
            'title' => 'TẤT CẢ SẢN PHẨM',
            'icon' => 'fas fa-th-large',
            'class' => 'all-products',
            'view_more_link' => BASE_URL . 'index.php?url=Product/all',
            'buy_now_link' => BASE_URL . 'index.php?url=Product/all',
            'banner' => $allBanner ?? 'hero_banner_full.png',
            'products' => $products ?? [],
            'benefits' => ['Sản phẩm đa dạng', 'Chất lượng đảm bảo', 'Ship COD toàn quốc', 'Hỗ trợ khách hàng 24/7', 'Ưu đãi cho khách quen'],
            'promo_title' => 'Sản phẩm đa dạng & chất lượng',
            'promo_main' => 'BEST SELLER'
        ],
        [
            'id' => 'handmade',
            'title' => 'SẢN PHẨM HANDMADE',
            'icon' => 'fas fa-magic',
            'class' => 'handmade',
            'view_more_link' => BASE_URL . 'index.php?url=Product/group/flowers',
            'buy_now_link' => BASE_URL . 'index.php?url=Product/group/flowers',
            'banner' => $handmadeBanner ?? 'featured_handmade.png',
            'products' => $handmadeProducts ?? [],
            'benefits' => ['Len độc quyền', 'Miễn phí sản phẩm từ 55k', 'Tư vấn nhiệt tình 24/7', '1989 sản phẩm đã bán', '100% sản phẩm thủ công'],
            'promo_title' => 'Sản phẩm handmade vippro',
            'promo_main' => 'Handmade'
        ],
        [
            'id' => 'tools',
            'title' => 'DỤNG CỤ ĐAN - MÓC',
            'icon' => 'fas fa-tools',
            'class' => 'tools',
            'view_more_link' => BASE_URL . 'index.php?url=Product/group/tools',
            'buy_now_link' => BASE_URL . 'index.php?url=Product/group/tools',
            'banner' => $toolsBanner ?? 'tools_banner_placeholder.png',
            'products' => $toolsProducts ?? [],
            'benefits' => ['Kim loại cao cấp', 'Đa dạng kích thước', 'Độ bền vượt trội', 'Tiết kiệm chi phí', 'Giao hàng nhanh'],
            'promo_title' => 'PHỤ KIỆN CHÍNH HÃNG',
            'promo_main' => 'KIM ĐAN<br>CAO CẤP'
        ],
        [
            'id' => 'flowers',
            'title' => 'HOA LEN NGHỆ THUẬT',
            'icon' => 'fas fa-seedling',
            'class' => 'flowers',
            'view_more_link' => BASE_URL . 'index.php?url=Product/group/flowers',
            'buy_now_link' => BASE_URL . 'index.php?url=Product/group/flowers',
            'banner' => $flowersBanner ?? 'flowers_banner_placeholder.png',
            'products' => $flowersProducts ?? [],
            'benefits' => ['Hoa bền vĩnh cửu', 'Màu sắc rực rỡ', 'Quà tặng ý nghĩa', 'Đóng gói cẩn thận', 'Freeship đơn lớn'],
            'promo_title' => 'QUÀ TẶNG Ý NGHĨA',
            'promo_main' => 'BÓ HOA<br>LEN ĐẸP'
        ],
        [
            'id' => 'yarn',
            'title' => 'LEN - SỢI CAO CẤP',
            'icon' => 'fas fa-wind',
            'class' => 'yarn',
            'view_more_link' => BASE_URL . 'index.php?url=Product/group/yarn',
            'buy_now_link' => BASE_URL . 'index.php?url=Product/group/yarn',
            'banner' => $yarnBanner ?? 'yarn_banner_placeholder.png',
            'products' => $yarnProducts ?? [],
            'benefits' => ['Sợi mềm không xù', 'Màu sắc Pastel', 'An toàn làn da', 'Hàng nhập chính hãng', 'Ưu đãi cho xưởng'],
            'promo_title' => 'NGUYÊN LIỆU LOẠI 1',
            'promo_main' => 'LEN SỢI<br>SOFT COTTON'
        ]
    ];

    // Đọc custom settings nếu có
    $customSettings = [];
    if (file_exists('app/config/settings.json')) {
        $customSettings = json_decode(file_get_contents('app/config/settings.json'), true) ?? [];
    }
    ?>

    <?php foreach ($sections as $sec): ?>
        <?php
        $displayTitle = !empty($customSettings['section_titles'][$sec['id']]) ? $customSettings['section_titles'][$sec['id']] : $sec['title'];
        ?>
        <div class="container mt-3" id="<?php echo $sec['id']; ?>">
            <div class="handmade-home-section <?php echo $sec['class']; ?>">
                <div class="section-header-handmade d-flex justify-content-between align-items-center mb-4">
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <h2 class="editable-section-title" data-section="<?php echo $sec['id']; ?>" title="Click để sửa tiêu đề này">
                            <i class="<?php echo $sec['icon']; ?>"></i>
                            <span class="title-text"><?php echo htmlspecialchars($displayTitle); ?></span>
                        </h2>
                    <?php else: ?>
                        <h2 class="section-title-premium">
                            <i class="<?php echo $sec['icon']; ?> mr-2"></i> <?php echo htmlspecialchars($displayTitle); ?>
                        </h2>
                    <?php endif; ?>

                    <div class="d-flex align-items-center">
                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                            <a href="<?php echo BASE_URL; ?>index.php?url=Product/manageFeatured/<?php echo $sec['id']; ?>" class="btn-admin-config">
                                <i class="fas fa-cog mr-1"></i> Cài đặt
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($sec['id'] !== 'all_products'): ?>
                    <div class="handmade-feature-grid">
                        <div class="handmade-promo-banner">
                            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                <div class="admin-banner-overlay">
                                    <form action="<?php echo BASE_URL; ?>index.php?url=Product/updateSectionBanner/<?php echo $sec['id']; ?>" method="POST" enctype="multipart/form-data" id="form-banner-<?php echo $sec['id']; ?>" class="mr-2">
                                        <input type="file" name="banner_image" class="d-none" id="input-banner-<?php echo $sec['id']; ?>" onchange="document.getElementById('form-banner-<?php echo $sec['id']; ?>').submit()" accept="image/*,application/pdf">
                                        <button type="button" class="btn-edit-banner" onclick="document.getElementById('input-banner-<?php echo $sec['id']; ?>').click()" title="Thay đổi Banner">
                                            <i class="fas fa-camera"></i>
                                        </button>
                                    </form>
                                    <a href="<?php echo BASE_URL; ?>index.php?url=Product/deleteSectionBanner/<?php echo $sec['id']; ?>" class="btn-delete-banner" title="Xóa Banner" onclick="return confirm('Bạn có chắc muốn xóa banner của mục này không? Ảnh sẽ quay về mặc định.')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?php 
                            $secExt = strtolower(pathinfo($sec['banner'], PATHINFO_EXTENSION));
                            if ($secExt === 'pdf'):
                            ?>
                                <embed src="<?php echo BASE_URL; ?>public/images/<?php echo $sec['banner']; ?>#toolbar=0&navpanes=0&scrollbar=0&view=FitH" type="application/pdf" width="100%" height="450px" class="promo-img" style="border-radius: 20px;">
                            <?php else: ?>
                                <img src="<?php echo BASE_URL; ?>public/images/<?php echo $sec['banner']; ?>" alt="<?php echo $sec['title']; ?>" class="promo-img">
                            <?php endif; ?>
                            <div class="discount-tag">-25%</div>
                            <div class="promo-overlay">
                                <div class="promo-title"><?php echo $sec['promo_title']; ?></div>
                                <div class="promo-main-title"><?php echo $sec['promo_main']; ?></div>
                                <a href="<?php echo $sec['buy_now_link']; ?>" class="btn-buy-now">MUA NGAY</a>
                            </div>
                        </div>

                        <div class="handmade-benefit-card shadow-sm">
                            <div class="benefit-header">
                                <h3>SẢN PHẨM SIÊU SALE</h3>
                            </div>
                            <div class="benefit-list">
                                <?php foreach ($sec['benefits'] as $benefit): ?>
                                    <div class="benefit-item">
                                        <i class="fas fa-check"></i> <?php echo $benefit; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <a href="<?php echo $sec['view_more_link']; ?>" class="btn-claim">
                                SỞ HỮU SẢN PHẨM
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php $justifyClass = (!empty($sec['products']) && count($sec['products']) < 4) ? 'justify-content-center' : ''; ?>
                <div class="product-display-area mt-4 px-3 p-0 <?php echo $justifyClass; ?>" style="display: flex; flex-wrap: wrap; margin: 0 -8px;">
                    <?php if (!empty($sec['products'])): ?>
                        <?php
                        $limit = ($sec['id'] === 'all_products') ? 12 : 4;
                        foreach (array_slice($sec['products'], 0, $limit) as $product): ?>
                            <div class="col-lg-3 col-md-4 col-6 p-2">
                                <div class="handmade-card-unique w-100 h-100 m-0">
                                    <div class="card-img-wrapper <?php echo ((int)($product->stock ?? 0) <= 0) ? 'sold-out-container' : ''; ?>">
                                        <?php
                                        $discount = isset($product->discount_percent) ? (int)$product->discount_percent : 0;
                                        $newPrice = ($discount > 0) ? $product->price * (1 - $discount / 100) : $product->price;
                                        $pImg = $product->image;
                                        $finalPImg = (strpos($pImg, 'public/') === false) ?
                                            ((strpos($pImg, 'uploads/') !== false) ? 'public/' . $pImg : 'public/uploads/' . $pImg) :
                                            $pImg;
                                        ?>
                                        <?php if ($discount > 0): ?>
                                            <div class="card-badge-sale">-<?php echo $discount; ?>%</div>
                                        <?php endif; ?>

                                        <?php if (((int)($product->stock ?? 0) <= 0)): ?>
                                            <div class="sold-out-overlay">
                                                <img src="<?php echo BASE_URL; ?>public/images/sold_out.png" alt="Hết hàng">
                                            </div>
                                        <?php endif; ?>

                                        <a href="<?php echo BASE_URL; ?>index.php?url=Product/show/<?php echo $product->id; ?>">
                                            <img src="<?php echo BASE_URL . $finalPImg; ?>" alt="<?php echo htmlspecialchars($product->name); ?>" class="<?php echo ((int)($product->stock ?? 0) <= 0) ? 'product-img-sold-out' : ''; ?>">
                                        </a>
                                    </div>
                                    <div class="handmade-card-body d-flex flex-column" style="padding: 12px; min-height: 170px;">
                                        <div class="card-cat-label text-truncate" style="font-size: 11px; color: #999; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.5px;"><?php echo $product->category_name; ?></div>
                                        <h5 class="card-name-unique mb-2" style="margin: 0;">
                                            <a href="<?php echo BASE_URL; ?>index.php?url=Product/show/<?php echo $product->id; ?>" style="color: #222; text-decoration: none; font-size: 15px; font-weight: 500; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 42px;">
                                                <?php echo $product->name; ?>
                                            </a>
                                        </h5>

                                        <div class="mt-auto d-flex justify-content-between align-items-end">
                                            <div>
                                                <div class="card-price-shopee d-flex align-items-center">
                                                    <span class="new-price" style="color: #ee4d2d; font-weight: 600; font-size: 1.25rem;">
                                                        <?php echo number_format($newPrice, 0, ',', '.'); ?>đ
                                                    </span>
                                                    <?php if (floatval($newPrice) >= 55000): ?>
                                                        <img src="<?php echo BASE_URL; ?>public/images/freeship_new.png" alt="FREE" title="Miễn phí vận chuyển" style="height: 24px !important; width: auto !important; vertical-align: middle !important; margin-left: 8px !important; display: inline-block !important; visibility: visible !important;">
                                                    <?php endif; ?>
                                                </div>

                                                <div class="card-social-line mt-1" style="font-size: 12.5px; color: #757575; display: flex; align-items: center;">
                                                    <span class="star-orange"><i class="fas fa-star" style="font-size: 11px; color: #ff9800;"></i> <?php echo number_format($product->rating, 1); ?></span>
                                                    <span class="card-divider mx-2" style="color: #dbdbdb;">|</span>
                                                    <span>Đã bán <?php echo number_format($product->sold); ?>+</span>
                                                </div>
                                            </div>

                                            <!-- Wishlist Heart -->
                                            <?php
                                            $isFav = isset($wishlistItems) && is_array($wishlistItems) && in_array($product->id, $wishlistItems);
                                            $heartClass = $isFav ? 'fas fa-heart' : 'far fa-heart';
                                            ?>
                                            <div class="like-wrapper d-flex flex-column align-items-center">
                                                <button class="btn btn-sm btn-light rounded-circle shadow-sm wishlist-btn-toggle" data-id="<?php echo $product->id; ?>" style="width: 32px; height: 32px; transition: transform 0.2s; border: 1px solid #ffebee;" title="Thêm vào yêu thích">
                                                    <i class="<?php echo $heartClass; ?>"></i>
                                                </button>
                                                <span class="like-count text-muted mt-1" style="font-size: 11px;" id="like-count-<?php echo $product->id; ?>">
                                                    <?php echo number_format($product->likes ?? 0); ?>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="card-bottom-info mt-2 pt-2" style="font-size: 12px; color: #888; border-top: 1px solid #f2f2f2;">
                                            <div class="d-flex align-items-center text-truncate">
                                                <i class="fas fa-truck-moving mr-2" style="color: #00bfa5; font-size: 11px;"></i> 2 - 3 ngày
                                                <span class="mx-2" style="color: #eee;">|</span>
                                                <i class="fas fa-map-marker-alt mr-2" style="color: #999; font-size: 11px;"></i> <?php echo htmlspecialchars($product->location ?? 'Tp. Hồ Chí Minh', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        </div>

                                        <?php
                                        $currentUserId = $_SESSION['user_id'] ?? 0;
                                        $ownerId = isset($product->user_id) ? (int)$product->user_id : 0;
                                        $isOwner = ($currentUserId > 0 && $ownerId > 0 && (int)$currentUserId === $ownerId);
                                        ?>
                                        <?php if ($isOwner): ?>

                                            <div class="admin-actions-mini d-flex mt-2 justify-content-end">
                                                <a href="<?php echo BASE_URL; ?>index.php?url=Product/edit/<?php echo $product->id; ?>" class="btn-admin-edit mx-1" title="Chỉnh sửa sản phẩm của bạn" style="background: #00bfa5; color: white; padding: 4px 12px; border-radius: 12px; font-size: 11px; text-decoration: none;">
                                                    <i class="fas fa-edit mr-1"></i> Chỉnh sửa sản phẩm
                                                </a>
                                            </div>
                                        <?php elseif (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                            <div class="admin-actions-mini d-flex mt-2 justify-content-end">
                                                <a href="<?php echo BASE_URL; ?>index.php?url=Product/edit/<?php echo $product->id; ?>" class="btn-admin-edit mx-1" title="Sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?php echo BASE_URL; ?>index.php?url=Product/delete/<?php echo $product->id; ?>" class="btn-admin-delete" title="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này không?')">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center w-100 py-4 text-muted" style="font-size: 16px;">
                            <i class="fas fa-box-open mb-2 fa-2x opacity-50 d-block"></i>
                            Chưa có sản phẩm nào thuộc khu vực này.
                        </p>
                    <?php endif; ?>
                </div>

                <!-- View All Link at bottom -->
                <div class="text-center mt-3 mb-5 px-3">
                    <a href="<?php echo $sec['view_more_link']; ?>" class="btn-view-more-large">
                        KHÁM PHÁ TOÀN BỘ SẢN PHẨM <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <!-- NON-HOME PAGES (Category, All, Search) -->
    <div class="container mt-3">
        <div class="row">
            <?php if (isset($show_sidebar) && $show_sidebar): ?>
                <!-- Sidebar -->
                <div class="col-lg-3 d-none d-lg-block">
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-header bg-white border-0 py-3" style="border-bottom: 2px solid #f8f9fa !important;">
                            <h6 class="mb-0 font-weight-bold" style="color: #333; letter-spacing: 0.5px;">BỘ LỌC TÌM KIẾM</h6>
                        </div>
                        <div class="card-body">
                            <form action="<?php echo BASE_URL; ?>index.php" method="GET">
                                <?php
                                // Giữ các tham số hiện tại của URL
                                $url_parts = explode('/', $_GET['url'] ?? '');
                                echo '<input type="hidden" name="url" value="' . ($_GET['url'] ?? '') . '">';
                                if (isset($_GET['q'])) echo '<input type="hidden" name="q" value="' . htmlspecialchars($_GET['q']) . '">';
                                ?>

                                <div class="filter-group mb-4">
                                    <h7 class="d-block mb-3 font-weight-bold text-muted small">KHOẢNG GIÁ</h7>
                                    <div class="price-range-inputs d-flex align-items-center mb-3">
                                        <div class="input-group input-group-sm mr-1">
                                            <input type="number" name="min_price" class="form-control" placeholder="Từ" value="<?php echo $_GET['min_price'] ?? ''; ?>" style="border-radius: 4px 0 0 4px; border: 1px solid #ddd; border-right: none;">
                                            <div class="input-group-append">
                                                <span class="input-group-text bg-white" style="border: 1px solid #ddd; border-left: none; color: #999; font-size: 11px;">₫</span>
                                            </div>
                                        </div>
                                        <span class="mx-1" style="color: #999;">-</span>
                                        <div class="input-group input-group-sm ml-1">
                                            <input type="number" name="max_price" class="form-control" placeholder="Đến" value="<?php echo $_GET['max_price'] ?? ''; ?>" style="border-radius: 4px 0 0 4px; border: 1px solid #ddd; border-right: none;">
                                            <div class="input-group-append">
                                                <span class="input-group-text bg-white" style="border: 1px solid #ddd; border-left: none; color: #999; font-size: 11px;">₫</span>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-block text-white" style="background-color: var(--primary-color); font-weight: bold; border-radius: 4px;">ÁP DỤNG</button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="<?php echo (isset($show_sidebar) && $show_sidebar) ? 'col-lg-9' : 'col-12'; ?>">
                <div class="section-title-wrapper d-flex align-items-center justify-content-between mb-4">
                    <h4 class="mb-0 font-weight-bold text-uppercase" style="color: #333; border-left: 4px solid var(--primary-color); padding-left: 15px;">
                        <?php echo $search_title ?? 'Sản phẩm'; ?>
                    </h4>
                    <div class="sort-options d-flex align-items-center">
                        <span class="mr-2 text-muted small">Sắp xếp theo:</span>
                        <?php $current_sort = $_GET['sort'] ?? 'newest'; ?>
                        <select class="form-control form-control-sm border-0 shadow-sm px-3"
                            style="border-radius: 20px; width: 170px; background: #fff; cursor: pointer;"
                            onchange="location.href = 'index.php?url=<?php echo explode('&sort=', $current_url)[0]; ?>&sort=' + this.value;"> 
                            <option value="newest" <?php echo $current_sort == 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                            <option value="price_asc" <?php echo $current_sort == 'price_asc' ? 'selected' : ''; ?>>Giá: Thấp đến Cao</option>
                            <option value="price_desc" <?php echo $current_sort == 'price_desc' ? 'selected' : ''; ?>>Giá: Cao đến Thấp</option>
                            <option value="sold" <?php echo $current_sort == 'sold' ? 'selected' : ''; ?>>Bán chạy nhất</option>
                        </select>
                    </div>
                </div>

                <div class="row no-gutters mx-n2">
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <div class="col-lg-3 col-md-4 col-6 p-2">
                                <div class="handmade-card-unique w-100 h-100 m-0">
                                    <div class="card-img-wrapper <?php echo ((int)($product->stock ?? 0) <= 0) ? 'sold-out-container' : ''; ?>">
                                        <?php
                                        $discount = isset($product->discount_percent) ? (int)$product->discount_percent : 0;
                                        $newPrice = ($discount > 0) ? $product->price * (1 - $discount / 100) : $product->price;
                                        $pImg = $product->image;
                                        $finalPImg = (strpos($pImg, 'public/') === false) ?
                                            ((strpos($pImg, 'uploads/') !== false) ? 'public/' . $pImg : 'public/uploads/' . $pImg) :
                                            $pImg;
                                        ?>
                                        <?php if ($discount > 0): ?>
                                            <div class="card-badge-sale">-<?php echo $discount; ?>%</div>
                                        <?php endif; ?>

                                        <?php if (((int)($product->stock ?? 0) <= 0)): ?>
                                            <div class="sold-out-overlay">
                                                <img src="<?php echo BASE_URL; ?>public/images/sold_out.png" alt="Hết hàng">
                                            </div>
                                        <?php endif; ?>

                                        <a href="<?php echo BASE_URL; ?>index.php?url=Product/show/<?php echo $product->id; ?>">
                                            <img src="<?php echo BASE_URL . $finalPImg; ?>" alt="<?php echo htmlspecialchars($product->name); ?>" class="<?php echo ((int)($product->stock ?? 0) <= 0) ? 'product-img-sold-out' : ''; ?>">
                                        </a>
                                    </div>
                                    <div class="handmade-card-body d-flex flex-column" style="padding: 12px; min-height: 170px;">
                                        <div class="card-cat-label text-truncate" style="font-size: 11px; color: #999; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.5px;"><?php echo $product->category_name; ?></div>
                                        <h5 class="card-name-unique mb-2" style="margin: 0;">
                                            <a href="<?php echo BASE_URL; ?>index.php?url=Product/show/<?php echo $product->id; ?>" style="color: #222; text-decoration: none; font-size: 15px; font-weight: 500; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 42px;">
                                                <?php echo $product->name; ?>
                                            </a>
                                        </h5>

                                        <div class="mt-auto d-flex justify-content-between align-items-end">
                                            <div>
                                                <div class="card-price-shopee d-flex align-items-center">
                                                    <span class="new-price" style="color: #ee4d2d; font-weight: 600; font-size: 1.25rem;">
                                                        <?php echo number_format($newPrice, 0, ',', '.'); ?>đ
                                                    </span>
                                                    <?php if (floatval($newPrice) >= 55000): ?>
                                                        <img src="<?php echo BASE_URL; ?>public/images/freeship_new.png" alt="FREE" title="Miễn phí vận chuyển" style="height: 24px !important; width: auto !important; vertical-align: middle !important; margin-left: 8px !important; display: inline-block !important; visibility: visible !important;">
                                                    <?php endif; ?>
                                                </div>

                                                <div class="card-social-line mt-1" style="font-size: 12.5px; color: #757575; display: flex; align-items: center;">
                                                    <span class="star-orange"><i class="fas fa-star" style="font-size: 11px; color: #ff9800;"></i> <?php echo number_format($product->rating, 1); ?></span>
                                                    <span class="card-divider mx-2" style="color: #dbdbdb;">|</span>
                                                    <span>Đã bán <?php echo number_format($product->sold); ?>+</span>
                                                </div>
                                            </div>

                                            <!-- Wishlist Heart -->
                                            <?php
                                            $isFav = isset($wishlistItems) && is_array($wishlistItems) && in_array($product->id, $wishlistItems);
                                            $heartClass = $isFav ? 'fas fa-heart' : 'far fa-heart';
                                            ?>
                                            <button class="btn btn-sm btn-light rounded-circle shadow-sm wishlist-btn-toggle mb-1" data-id="<?php echo $product->id; ?>" style="width: 32px; height: 32px; transition: transform 0.2s; border: 1px solid #ffebee;" title="Thêm vào yêu thích">
                                                <i class="<?php echo $heartClass; ?>"></i>
                                            </button>
                                        </div>



                                        <div class="card-bottom-info mt-2 pt-2" style="font-size: 12px; color: #888; border-top: 1px solid #f2f2f2;">
                                            <div class="d-flex align-items-center text-truncate">
                                                <i class="fas fa-truck-moving mr-2" style="color: #00bfa5; font-size: 11px;"></i> 2 - 3 ngày
                                                <span class="mx-2" style="color: #eee;">|</span>
                                                <i class="fas fa-map-marker-alt mr-2" style="color: #999; font-size: 11px;"></i> <?php echo htmlspecialchars($product->location ?? 'Tp. Hồ Chí Minh', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                            <?php
                                            $currentUserId = $_SESSION['user_id'] ?? 0;
                                            $ownerId = isset($product->user_id) ? (int)$product->user_id : 0;
                                            $isOwner = ($currentUserId > 0 && $ownerId > 0 && (int)$currentUserId === $ownerId);
                                            ?>
                                            <?php if ($isOwner): ?>
                                                <div class="admin-actions-mini d-flex mt-2 justify-content-end">
                                                    <a href="<?php echo BASE_URL; ?>index.php?url=Product/edit/<?php echo $product->id; ?>" class="btn-admin-edit mx-1" title="Chỉnh sửa sản phẩm của bạn" style="background: #00bfa5; color: white; padding: 4px 12px; border-radius: 12px; font-size: 11px; text-decoration: none;">
                                                        <i class="fas fa-edit mr-1"></i> Chỉnh sửa sản phẩm
                                                    </a>
                                                </div>
                                            <?php elseif (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                                <div class="admin-actions-mini d-flex mt-2 justify-content-end">
                                                    <a href="<?php echo BASE_URL; ?>index.php?url=Product/edit/<?php echo $product->id; ?>" class="btn-admin-edit mx-1" title="Sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="<?php echo BASE_URL; ?>index.php?url=Product/delete/<?php echo $product->id; ?>" class="btn-admin-delete" title="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này không?')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <img src="https://deo.shopeemobile.com/shopee/shopee-pcmall-live-sg/assets/a60759ad1dabe909c46a817ecbf71878.png" style="width: 150px; opacity: 0.5;" class="mb-3">
                            <h5 class="text-muted">Rất tiếc, không tìm thấy sản phẩm nào!</h5>
                            <p class="text-muted small">Thử sử dụng các từ khóa chung hơn hoặc xóa bộ lọc.</p>
                            <a href="<?php echo BASE_URL; ?>index.php?url=Product/" class="btn btn-outline-success rounded-pill px-4 mt-2">Quay lại Trang Chủ</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php // Xóa bỏ đoạn mã từ dòng 327 đến 481 cũ (Grid lặp lại) 
?>

<script>
    function updatePriceDisplay(val) {
        document.getElementById('maxPriceLabel').innerText = parseInt(val).toLocaleString('vi-VN');
    }

    // Inline Edit Section Title for Admin
    document.addEventListener('DOMContentLoaded', function() {
        const editableTitles = document.querySelectorAll('.editable-section-title');
        editableTitles.forEach(title => {
            title.addEventListener('click', function() {
                const sectionId = this.getAttribute('data-section');
                const currentText = this.querySelector('.title-text').innerText;

                const newTitle = prompt('Nhập tên tiêu đề mới cho mục này:', currentText);

                if (newTitle && newTitle !== currentText) {
                    const formData = new FormData();
                    formData.append('section_id', sectionId);
                    formData.append('title', newTitle);

                    fetch('<?php echo BASE_URL; ?>index.php?url=Product/updateSectionTitleAjax', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                this.querySelector('.title-text').innerText = newTitle;
                                // Thêm hiệu ứng nháy xanh báo hiệu thành công
                                this.style.color = '#75c794';
                                setTimeout(() => this.style.color = '', 1000);
                            } else {
                                alert('Lỗi: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Đã xảy ra lỗi khi cập nhật tiêu đề.');
                        });
                }
            });
        });
    });
</script>

<style>
    .product-card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12) !important;
        border: 1px solid var(--primary-color) !important;
    }

    /* New Card Styles */
    .card-location-badge {
        color: #888;
        font-size: 11px;
        margin-top: 4px;
    }

    .card-social-line {
        display: flex;
        align-items: center;
        font-size: 11px;
        color: #888;
        margin-top: 5px;
    }

    .card-social-line .star-orange {
        color: #ff9800;
        margin-right: 3px;
    }

    .card-divider {
        margin: 0 5px;
        color: #ddd;
    }

    .freeship-icon-container {
        position: absolute;
        bottom: 5px;
        left: 5px;
        z-index: 10;
    }

    .freeship-img {
        height: 18px;
        width: auto;
        filter: drop-shadow(0 2px 2px rgba(0, 0, 0, 0.1));
    }

    .freeship-img-mini {
        height: 15px;
        width: auto;
        filter: drop-shadow(0 1px 1px rgba(0, 0, 0, 0.1));
    }

    /* Adjust card body to fill space without buttons */
    .handmade-card-body,
    .product-card-body {
        padding-bottom: 12px !important;
    }

    .btn-filter {
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 5px 15px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s;
    }

    .btn-filter:hover {
        background: var(--nav-dark-green);
    }

    .hero-banner .carousel-indicators li {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.5);
        border: none;
    }

    .hero-banner .carousel-indicators li.active {
        background-color: #fff;
        width: 25px;
        border-radius: 5px;
    }

    .section-title-premium {
        font-size: 24px;
        font-weight: 700;
        color: #333;
        margin: 0;
        position: relative;
        padding-left: 15px;
        display: flex;
        align-items: center;
        border-left: 5px solid var(--primary-color);
    }

    .btn-view-more-pill {
        background: transparent;
        color: var(--primary-color) !important;
        border: 1px solid var(--primary-color);
        padding: 4px 15px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none !important;
    }

    .btn-view-more-pill:hover {
        background: var(--primary-color);
        color: white !important;
        box-shadow: 0 4px 10px rgba(117, 199, 148, 0.3);
    }

    .btn-admin-config {
        color: var(--primary-color) !important;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none !important;
        padding: 3px 12px;
        border: 1.5px solid var(--primary-color);
        border-radius: 20px;
        margin-left: 15px;
        transition: all 0.3s ease;
        background: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .btn-admin-config:hover {
        background-color: var(--primary-color);
        color: white !important;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transform: translateY(-1px);
    }

    .section-header-handmade {
        margin-bottom: 25px;
    }

    .wishlist-btn-toggle {
        color: #ccc !important;
        /* Mặc định màu xám trắng khi chưa thích */
    }

    .wishlist-btn-toggle i.fas {
        color: #ee225b !important;
        /* Màu đỏ khi đã thích */
    }

    .wishlist-btn-toggle:hover {
        transform: scale(1.1);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('.wishlist-btn-toggle').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const btn = $(this);
            const icon = btn.find('i');
            const productId = btn.data('id');

            $.ajax({
                url: '<?php echo BASE_URL; ?>index.php?url=Wishlist/toggle',
                type: 'POST',
                data: {
                    product_id: productId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        if (response.action === 'added') {
                            icon.removeClass('far').addClass('fas');
                        } else {
                            icon.removeClass('fas').addClass('far');
                        }
                        
                        // Cập nhật lượt thích từ phản hồi server
                        if (response.likes !== undefined) {
                            $('#like-count-' + productId).text(response.likes.toLocaleString('vi-VN'));
                        }
                    } else {
                        window.location.href = '<?php echo BASE_URL; ?>index.php?url=Page/login';
                    }
                }
            });
        });
    });
</script>

<?php include 'app/views/shares/footer.php'; ?>