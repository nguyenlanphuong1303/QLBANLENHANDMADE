<?php
/**
 * @var object|null $product
 * @var float|int $discount
 * @var array $reviews
 * @var bool $canBuy
 * @var array $breadcrumbs
 */
include 'app/views/shares/header.php'; 
?>
<div class="container mt-4 mb-5">


    <!-- BREADCRUMB SECTION -->
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger shadow-sm mb-4" style="border-radius: 10px; border-left: 5px solid #dc3545;">
            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>
    
    <div class="breadcrumb-search mb-4">
        <?php if (isset($breadcrumbs)): ?>
            <?php $i = 0;
            $count = count($breadcrumbs); ?>
            <?php foreach ($breadcrumbs as $label => $link): ?>
                <?php if ($i > 0): ?><span>/</span><?php endif; ?>
                <?php if ($i === $count - 1): ?>
                    <span class="active"><?php echo htmlspecialchars($label); ?></span>
                <?php else: ?>
                    <a href="<?php echo $link; ?>"><?php echo htmlspecialchars($label); ?></a>
                <?php endif; ?>
                <?php $i++; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <a href="<?php echo BASE_URL; ?>index.php?url=Product/">Trang chủ</a>
            <span>/</span>
            <span class="active">Sản phẩm</span>
        <?php endif; ?>
    </div>

    <?php if (isset($product) && $product): ?>
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-body p-4 p-md-5">
                <div class="row align-items-start">
                    <!-- IMAGE COLUMN -->
                        <div class="col-md-5 text-center mb-4 mb-md-0 position-relative">
                            
                            <!-- Wrapper for Image and Sold Out Overlay -->
                            <div class="position-relative d-inline-block w-100 <?php echo ((int)($product->stock ?? 0) <= 0) ? 'sold-out-container' : ''; ?>" style="border-radius: 15px; overflow: hidden;">
                                
                                <!-- Wishlist Heart -->
                                <?php
                                $isFav = isset($wishlistItems) && is_array($wishlistItems) && in_array($product->id, $wishlistItems);
                                $heartClass = $isFav ? 'fas fa-heart' : 'far fa-heart';
                                ?>
                                <div class="like-wrapper-detail position-absolute d-flex flex-column align-items-center" style="top: 15px; right: 25px; z-index: 10;">
                                    <button class="btn btn-light rounded-circle shadow-sm wishlist-btn-toggle" data-id="<?php echo $product->id; ?>" style="width: 45px; height: 45px; color: #ee225b; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; transition: transform 0.2s; border: none;">
                                        <i class="<?php echo $heartClass; ?>"></i>
                                    </button>
                                    <span class="like-count-detail font-weight-bold mt-1" style="color: #ee225b; font-size: 14px;" id="like-count-detail-<?php echo $product->id; ?>">
                                        <?php echo number_format($product->likes ?? 0); ?>
                                    </span>
                                </div>

                                <?php if (((int)($product->stock ?? 0) <= 0)): ?>
                                    <div class="sold-out-overlay">
                                        <img src="<?php echo BASE_URL; ?>public/images/sold_out.png" alt="Hết hàng" style="width: 80%;">
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($product->image)): ?>
                                    <?php
                                    $prodImg = $product->image;
                                    $finalProdImg = (strpos($prodImg, 'public/') === false) ?
                                        ((strpos($prodImg, 'uploads/') !== false) ? 'public/' . $prodImg : 'public/uploads/' . $prodImg) :
                                        $prodImg;
                                    ?>
                                    <img id="mainProductImage" src="<?php echo BASE_URL . htmlspecialchars($finalProdImg, ENT_QUOTES, 'UTF-8'); ?>" 
                                         class="img-fluid rounded shadow-sm <?php echo ((int)($product->stock ?? 0) <= 0) ? 'product-img-sold-out' : ''; ?>" 
                                         alt="<?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>" 
                                         style="max-height: 400px; object-fit: contain; width: 100%;">
                                <?php else: ?>
                                    <div class="bg-light d-flex flex-column align-items-center justify-content-center rounded shadow-sm mx-auto p-4" style="height: 350px; width: 100%;">
                                        <img src="<?php echo BASE_URL; ?>public/images/logolen.jpg" style="height: 60px; opacity: 0.2; margin-bottom: 20px;">
                                        <i class="fas fa-image text-muted mb-3" style="font-size: 4rem; opacity: 0.5;"></i>
                                        <span class="text-muted">Chưa có hình ảnh</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- SELLER INFO BLOCK -->
                            <div class="mt-4 p-4 bg-white shadow-sm border rounded" style="border-radius: 15px; border-left: 5px solid var(--primary-color) !important;">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="mr-3">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 55px; height: 55px; border: 2px solid #f0f0f0; overflow: hidden;">
                                            <?php if (!empty($product->shop_logo)): ?>
                                                <img src="<?php echo BASE_URL . $product->shop_logo; ?>" alt="Logo" style="width: 100%; height: 100%; object-fit: cover;">
                                            <?php else: ?>
                                                <i class="fas fa-store text-muted" style="font-size: 1.6rem;"></i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="text-left flex-grow-1">
                                        <div class="font-weight-bold mb-0" style="font-size: 1.4rem; color: var(--primary-color); line-height: 1.2;">
                                            <?php echo !empty($product->shop_name) ? htmlspecialchars($product->shop_name) : 'GÌ CŨNG MÓC'; ?>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.85rem; margin-top: 2px;">
                                            <i class="fas fa-user mr-1"></i>Người đăng: <strong><?php echo htmlspecialchars($product->seller_display_name ?? 'Thành viên'); ?></strong>
                                            <?php if (!empty($product->seller_handle)): ?>
                                                <span class="text-secondary" style="font-size: 0.8rem;">(@<?php echo htmlspecialchars($product->seller_handle); ?>)</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                            <div class="text-info small mt-1" style="font-size: 0.8rem; opacity: 0.8;">
                                                <i class="fas <?php echo ($product->seller_role === 'admin') ? 'fa-user-shield' : 'fa-user-tag'; ?> mr-1"></i>
                                                <?php echo ($product->seller_role === 'admin') ? 'Quyền Admin' : 'Quyền Seller'; ?> 
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="d-flex" style="gap: 10px;">
                                    <?php 
                                        $prodData = [
                                            'id' => $product->id,
                                            'name' => $product->name,
                                            'price' => number_format($product->price, 0, ',', '.') . '₫',
                                            'image' => $finalProdImg
                                        ];
                                    ?>
                                    <?php if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $product->user_id): ?>
                                    <button class="btn btn-outline-primary btn-sm flex-fill py-2 open-chat-with-product" 
                                            data-seller-id="<?php echo $product->user_id; ?>"
                                            data-product='<?php echo htmlspecialchars(json_encode($prodData), ENT_QUOTES, 'UTF-8'); ?>'
                                            style="border-radius: 8px; font-weight: 600; transition: all 0.2s;">
                                        <i class="fab fa-facebook-messenger mr-2"></i> Chat ngay
                                    </button>
                                    <?php endif; ?>
                                    <a href="<?php echo BASE_URL; ?>index.php?url=Shop/profile/<?php echo $product->shop_id ?: $product->user_id; ?>" 
                                       class="btn btn-light btn-sm flex-fill py-2" 
                                       style="border-radius: 8px; font-weight: 600; border: 1px solid #ddd;">
                                        <i class="fas fa-home mr-2"></i> Xem Shop
                                    </a>
                                </div>
                            </div>
                        </div>

                    <!-- DETAILS COLUMN -->
                    <div class="col-md-7 border-left pl-md-5">
                        <div class="d-flex align-items-center mb-3" style="font-size: 1rem;">
                            <span class="text-warning mr-3" style="border-right: 1px solid #ddd; padding-right: 15px;">
                                <span class="mr-1" style="text-decoration: underline; color: var(--primary-color); font-weight: bold; font-size: 1.1rem;"><?php echo number_format($product->rating ?? 0, 1); ?></span>
                                <?php
                                $rating = $product->rating ?? 0;
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= floor($rating)) echo '<i class="fas fa-star"></i>';
                                    elseif ($i == ceil($rating) && $rating - floor($rating) >= 0.5) echo '<i class="fas fa-star-half-alt"></i>';
                                    else echo '<i class="far fa-star"></i>';
                                }
                                ?>
                            </span>
                            <span class="text-muted mr-3" style="border-right: 1px solid #ddd; padding-right: 15px;">
                                <span class="text-dark font-weight-bold" style="text-decoration: underline;"><?php echo number_format($product->rating_count ?? 0); ?></span> Đánh giá
                            </span>
                            <span class="text-muted">
                                <span class="text-dark font-weight-bold"><?php echo number_format($product->sold ?? 0); ?></span> Đã bán
                            </span>
                        </div>

                        <h2 class="card-title mb-2" style="color: #333; font-weight: 700; font-size: 2.4rem;"><?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?></h2>

                        <p class="mb-4">
                            <span class="badge badge-success px-3 py-2" style="font-size: 0.95rem; border-radius: 20px; font-weight: 500; background-color: var(--primary-color);">
                                <i class="fas fa-tag mr-1"></i> <?php echo !empty($product->category_name) ? htmlspecialchars($product->category_name, ENT_QUOTES, 'UTF-8') : 'Chưa có danh mục'; ?>
                            </span>
                        </p>

                        <?php
                        $discount = isset($product->discount_percent) ? (int)$product->discount_percent : 0;
                        // Sử dụng minPrice (giá rẻ nhất) làm giá hiển thị mặc định
                        $displayBasePrice = isset($minPrice) ? $minPrice : $product->price;
                        $newPrice = ($discount > 0) ? $displayBasePrice * (1 - $discount / 100) : $displayBasePrice;
                        ?>
                        <div class="d-flex align-items-center mb-4 bg-light p-3 rounded" style="border-left: 5px solid var(--primary-color);">
                            <?php if ($discount > 0): ?>
                                <h4 class="text-muted mb-0 mr-3" id="mainProductPriceOld" style="text-decoration: line-through; font-size: 1.4rem;">
                                    <?php echo number_format($displayBasePrice, 0, ',', '.'); ?> đ
                                </h4>
                            <?php endif; ?>
                            <div class="d-flex align-items-center">
                                <h3 class="font-weight-bold mb-0" id="mainProductPrice" style="font-size: 2.6rem; color: var(--primary-color);">
                                    <?php echo number_format($newPrice, 0, ',', '.'); ?> <span style="font-size: 1.3rem; text-decoration: underline;">đ</span>
                                </h3>
                                <?php if ($newPrice >= 55000): ?>
                                    <img src="<?php echo BASE_URL; ?>public/images/freeship_new.png" style="height: 30px; margin-left:15px;" title="Miễn phí vận chuyển" alt="Free Shipping">
                                <?php endif; ?>
                            </div>
                            <?php if ($discount > 0): ?>
                                <span class="badge badge-danger ml-3" style="font-size: 1.1rem; padding: 5px 10px;">-<?php echo $discount; ?>% GIẢM</span>
                            <?php endif; ?>
                        </div>

                        <!-- PRODUCT VARIANTS (MẪU) -->
                        <?php if (!empty($variants) || (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin')): ?>
                            <div class="product-variants mb-4">
                                <h5 class="text-uppercase text-muted mb-3" style="font-size: 0.95rem; letter-spacing: 1.5px; font-weight: bold;">Mẫu</h5>
                                <div class="d-flex flex-wrap align-items-center" style="gap: 12px;" id="variantSelector">
                                    <?php if (!empty($variants)): ?>
                                        <?php foreach ($variants as $variant): ?>
                                            <?php $hasVariantImage = !empty($variant->image) && $variant->image !== 'null'; ?>
                                            <div class="variant-item p-1 border rounded d-flex align-items-center cursor-pointer position-relative <?php echo !$hasVariantImage ? 'justify-content-center px-3 py-2' : ''; ?>"
                                                data-variant-id="<?php echo $variant->id; ?>"
                                                data-variant-name="<?php echo htmlspecialchars($variant->name, ENT_QUOTES, 'UTF-8'); ?>"
                                                data-variant-image="<?php echo $hasVariantImage ? BASE_URL . 'public/uploads/' . $variant->image : ''; ?>"
                                                data-variant-price="<?php echo $variant->price; ?>"
                                                data-variant-stock="<?php echo $variant->stock; ?>"
                                                style="<?php echo $hasVariantImage ? 'min-width: 160px;' : 'min-width: 80px;'; ?> transition: all 0.2s ease; border-color: #ddd !important; position: relative; <?php echo ($variant->stock <= 0) ? 'opacity: 0.6; grayscale(1);' : ''; ?>">
                                                
                                                <?php if ($hasVariantImage): ?>
                                                    <img src="<?php echo BASE_URL . 'public/uploads/' . $variant->image; ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px;" class="mr-2">
                                                    <div class="d-flex flex-column" style="overflow: hidden;">
                                                        <span style="font-size: 1.05rem; font-weight: 600; color: #333; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 120px;"><?php echo htmlspecialchars($variant->name, ENT_QUOTES, 'UTF-8'); ?></span>
                                                    </div>
                                                <?php else: ?>
                                                    <span style="font-size: 1.1rem; font-weight: 500; color: #333;"><?php echo htmlspecialchars($variant->name, ENT_QUOTES, 'UTF-8'); ?></span>
                                                <?php endif; ?>

                                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                                    <a href="<?php echo BASE_URL; ?>index.php?url=Product/deleteVariant/<?php echo $variant->id; ?>" class="position-absolute text-danger" style="top: -8px; right: -8px; background: white; border-radius: 50%; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; border: 1px solid #ff4d4d; z-index: 10; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onclick="return confirm('Xóa mẫu này?')">
                                                        <i class="fas fa-times"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                        <button class="btn btn-outline-primary btn-sm rounded-pill px-3" id="btnAddVariant" style="height: 35px; font-size: 0.8rem; border-style: dashed;">
                                            <i class="fas fa-plus mr-1"></i> Thêm mẫu
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <!-- Admin Add Variant Form (Hidden) -->
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                    <div id="variantAddForm" class="mt-3 p-3 bg-light rounded border hidden" style="border-style: dashed !important;">
                                        <form action="<?php echo BASE_URL; ?>index.php?url=Product/addVariant" method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="product_id" value="<?php echo $product->id; ?>">
                                            <div class="row align-items-end">
                                                <div class="col-md-2 mb-2">
                                                    <label class="small font-weight-bold">Tên mẫu:</label>
                                                    <input type="text" name="variant_name" class="form-control form-control-sm" placeholder="vd: Thỏ" required>
                                                </div>
                                                <div class="col-md-2 mb-2">
                                                    <label class="small font-weight-bold">Giá mẫu (đ):</label>
                                                    <input type="number" name="variant_price" class="form-control form-control-sm" placeholder="Để 0 nếu dùng giá gốc" value="0">
                                                </div>
                                                <div class="col-md-2 mb-2">
                                                    <label class="small font-weight-bold">Kho:</label>
                                                    <input type="number" name="variant_stock" class="form-control form-control-sm" value="0">
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <label class="small font-weight-bold">Hình ảnh:</label>
                                                    <div class="d-flex align-items-center">
                                                        <img id="quickVariantPreview" src="" style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px; display: none; margin-right: 15px; border: 1px solid #ddd; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                                        <input type="file" name="variant_image" class="form-control-file form-control-sm" required onchange="previewQuickVariant(this)">
                                                    </div>
                                                </div>
                                                <div class="col-md-2 mb-2">
                                                    <button type="submit" class="btn btn-primary btn-sm btn-block">Lưu</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="mb-4">
                            <h5 class="text-uppercase text-muted mb-2" style="font-size: 0.9rem; letter-spacing: 1px; font-weight: bold;">Mô tả sản phẩm</h5>
                            <p class="card-text text-secondary" style="line-height: 1.6; font-size: 1.1rem;">
                                <?php echo nl2br(htmlspecialchars($product->description, ENT_QUOTES, 'UTF-8')); ?>
                            </p>
                        </div>

                        <p class="mb-2 text-muted" style="font-size: 0.95rem;">
                            <i class="fas fa-boxes mr-1"></i> Kho: <span class="font-weight-bold text-dark" id="displayStock"><?php echo $product->stock ?? 0; ?></span> sản phẩm có sẵn
                        </p>

                        <p class="mb-4 text-muted" style="font-size: 0.95rem;">
                            <i class="fas fa-map-marker-alt mr-1"></i> Gửi từ: <span class="font-weight-bold text-dark"><?php echo htmlspecialchars($product->location ?? 'Tp. Hồ Chí Minh', ENT_QUOTES, 'UTF-8'); ?></span>
                        </p>

                        <!-- QUANTITY SELECTOR -->
                        <?php if ((int)($product->stock ?? 0) > 0): ?>
                        <div class="d-flex align-items-center mb-4">
                            <h5 class="text-uppercase text-muted mb-0 mr-4" style="font-size: 0.95rem; letter-spacing: 1px; font-weight: bold;">Số lượng</h5>
                            <div class="d-flex align-items-center bg-light rounded-pill p-1 border">
                                <button class="btn btn-link text-dark p-0 px-3" id="btnDecreaseQty" style="text-decoration: none; font-size: 1.2rem; font-weight: bold;">-</button>
                                <input type="number" id="buyQuantity" value="1" min="1" max="<?php echo $product->stock ?? 0; ?>" class="form-control text-center border-0 bg-transparent p-0" style="width: 50px; font-weight: bold; font-size: 1.1rem; box-shadow: none;">
                                <button class="btn btn-link text-dark p-0 px-3" id="btnIncreaseQty" style="text-decoration: none; font-size: 1.2rem; font-weight: bold;">+</button>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="d-flex align-items-center flex-wrap" style="gap: 15px; margin-bottom: 25px;">
                            <?php
                            $currentUserId = $_SESSION['user_id'] ?? 0;
                            $ownerId = isset($product->user_id) ? (int)$product->user_id : 0;
                            $isOwner = ($currentUserId > 0 && $ownerId > 0 && (int)$currentUserId === $ownerId);
                            $isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
                            ?>

                            <?php if ($isOwner): ?>
                                <a href="<?php echo BASE_URL; ?>index.php?url=Product/edit/<?php echo $product->id; ?>" class="btn btn-warning px-4 py-3 shadow-sm flex-grow-1 text-center" style="border-radius: 30px; font-weight: bold; font-size: 1.1rem; white-space: nowrap; color: #222;">
                                    <i class="fas fa-edit mr-2"></i> Chỉnh sửa sản phẩm
                                </a>
                                <a href="<?php echo BASE_URL; ?>index.php?url=Product/delete/<?php echo $product->id; ?>" class="btn btn-danger px-4 py-3 shadow-sm flex-grow-1 text-center" style="border-radius: 30px; font-weight: bold; font-size: 1.1rem; white-space: nowrap; color: white;" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này không?')">
                                    <i class="fas fa-trash-alt mr-2"></i> Xóa sản phẩm
                                </a>
                            <?php else: ?>
                                <?php if (isset($product->status) && $product->status === 'pending'): ?>
                                    <div class="alert alert-info w-100 p-3 shadow-sm" style="border-radius: 15px; border-left: 5px solid #17a2b8;">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-info-circle fa-2x mr-3 text-info"></i>
                                        
                                        </div>
                                    </div>
                                <?php elseif (((int)($product->stock ?? 0) > 0)): ?>
                                    <a href="<?php echo BASE_URL; ?>index.php?url=Cart/add/<?php echo $product->id; ?>" id="btnAddToCartAjax" class="btn btn-outline-success px-4 py-3 shadow-sm flex-grow-1 text-center" style="border-radius: 30px; font-weight: bold; font-size: 1.3rem; white-space: nowrap; border-color: var(--primary-color); color: var(--primary-color);">
                                        <i class="fas fa-cart-plus mr-2"></i> Thêm vào giỏ hàng
                                    </a>
                                    <a href="<?php echo isset($_SESSION['user_id']) ? BASE_URL . 'index.php?url=Cart/buyNow/' . $product->id : 'javascript:void(0)'; ?>"
                                        class="btn px-4 py-3 shadow flex-grow-1 text-center <?php echo !isset($_SESSION['user_id']) ? 'btn-buy-now-guest' : ''; ?>"
                                        style="background-color: #0d9139ff; border: 2px solid var(--primary-color); border-radius: 30px; font-weight: bold; font-size: 1.3rem; white-space: nowrap; color: white;">
                                        <i class="fas fa-bolt mr-2"></i> Mua ngay
                                    </a>
                                <?php else: ?>
                                    <div class="alert alert-danger w-100 p-3 shadow-sm" style="border-radius: 15px; border-left: 5px solid #dc3545;">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-exclamation-circle fa-2x mr-3 text-danger"></i>
                                            <div style="font-size: 1.1rem; font-weight: 500;">
                                                Hết hàng! Bạn có thể ghé lại sau hoặc chọn mẫu khác.
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>


                                <?php if ($isAdmin): // Admin vẫn có quyền sửa mọi sản phẩm 
                                ?>
                                    <a href="<?php echo BASE_URL; ?>index.php?url=Product/edit/<?php echo $product->id; ?>" class="btn btn-warning px-4 py-3 shadow-sm text-center" style="border-radius: 30px; font-weight: bold; font-size: 1.1rem; white-space: nowrap; color: #222;">
                                        <i class="fas fa-edit mr-2"></i> Chỉnh sửa sản phẩm
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if (isset($product->status) && $product->status === 'pending' && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                <a href="<?php echo BASE_URL; ?>index.php?url=Dashboard/pendingProducts#row-<?php echo $product->id; ?>" class="btn btn-secondary px-4 py-3 shadow-sm text-center flex-grow-1" style="border-radius: 30px; font-weight: 500; white-space: nowrap;">
                                    <i class="fas fa-arrow-left mr-2"></i> Quay lại danh sách chờ duyệt
                                </a>
                            <?php else: ?>
                                <a href="<?php echo BASE_URL; ?>index.php?url=Product/" class="btn btn-secondary px-4 py-3 shadow-sm text-center <?php echo (empty($canBuy)) ? 'flex-grow-1' : ''; ?>" style="border-radius: 30px; font-weight: 500; white-space: nowrap;">
                                    <i class="fas fa-arrow-left mr-2"></i> Tiếp tục mua sắm
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="card shadow-sm border-0 mt-4" style="border-radius: 15px;">
            <div class="card-body p-4 p-md-5">
                <h4 class="mb-4" style="color: var(--primary-color); font-weight: bold;">Đánh giá sản phẩm</h4>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success_message'];
                                                        unset($_SESSION['success_message']); ?></div>
                <?php endif; ?>

                <div class="row">
                    <!-- Review List -->
                    <div class="col-md-7 mb-4">
                        <?php if (!empty($reviews)): ?>
                            <ul class="list-unstyled">
                                <?php foreach ($reviews as $review): ?>
                                    <li class="media mb-4 pb-3 border-bottom d-flex align-items-start">
                                        <div class="mr-3 flex-shrink-0" style="width: 50px; height: 50px; background-color: #ddd; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #555;">
                                            <?php echo strtoupper(substr($review->user_name ?? 'U', 0, 1)); ?>
                                        </div>
                                        <div class="media-body flex-grow-1 ml-3">
                                            <h6 class="mt-0 mb-1 font-weight-bold text-dark"><?php echo htmlspecialchars($review->user_name ?? 'Khách', ENT_QUOTES, 'UTF-8'); ?></h6>
                                            <div class="text-warning mb-2" style="font-size: 0.9rem;">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="<?php echo $i <= $review->rating ? 'fas' : 'far'; ?> fa-star"></i>
                                                <?php endfor; ?>
                                                <span class="text-muted ml-2" style="font-size: 0.8rem;"><i class="far fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($review->created_at)); ?></span>
                                            </div>
                                            <p style="font-size: 0.95rem; color: #444; margin-bottom:0; line-height: 1.5;"><?php echo nl2br(htmlspecialchars($review->comment ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted bg-light rounded shadow-sm" style="border: 1px dashed #ccc;">
                                <i class="fas fa-comment-slash fa-2x mb-2 text-warning"></i>
                                <p class="mb-0">Chưa có đánh giá nào cho sản phẩm này.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Review Form -->
                    <div class="col-md-5">
                        <div class="bg-light p-4 rounded shadow-sm">
                            <h5 class="mb-3 font-weight-bold text-dark border-bottom pb-2">Viết đánh giá</h5>
                            <?php if (!isset($_SESSION['user_id'])): ?>
                                <p class="text-muted mb-3 text-center">Bạn cần đăng nhập để gửi đánh giá.</p>
                                <a href="<?php echo BASE_URL; ?>index.php?url=Page/login" class="btn btn-outline-success btn-block shadow-sm" style="border-radius: 20px; font-weight:bold; border-color: #28a745; color: #28a745;">
                                    <i class="fas fa-sign-in-alt mr-2"></i>Đăng nhập ngay
                                </a>
                            <?php elseif (isset($hasReviewed) && $hasReviewed): ?>
                                <div class="alert alert-info py-2 text-center shadow-sm" style="border-radius: 10px;">
                                    <i class="fas fa-check-circle fa-2x mb-2 text-info"></i><br>
                                    Bạn đã đánh giá sản phẩm này. Cảm ơn sự đóng góp của bạn!
                                </div>
                            <?php else: ?>
                                <form action="<?php echo BASE_URL; ?>index.php?url=Product/submitReview" method="POST">
                                    <input type="hidden" name="product_id" value="<?php echo $product->id; ?>">

                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold mb-2 text-dark">Số sao đánh giá <span class="text-danger">*</span></label>
                                        <div class="star-rating" style="display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 5px;">
                                            <input type="radio" id="star5" name="rating" value="5" required style="display:none;" />
                                            <label for="star5" title="5 sao" style="cursor: pointer; font-size: 1.8rem; color: #ddd; margin:0; padding:0; transition: color 0.2s;"><i class="fas fa-star"></i></label>
                                            <input type="radio" id="star4" name="rating" value="4" style="display:none;" />
                                            <label for="star4" title="4 sao" style="cursor: pointer; font-size: 1.8rem; color: #ddd; margin:0; padding:0; transition: color 0.2s;"><i class="fas fa-star"></i></label>
                                            <input type="radio" id="star3" name="rating" value="3" style="display:none;" />
                                            <label for="star3" title="3 sao" style="cursor: pointer; font-size: 1.8rem; color: #ddd; margin:0; padding:0; transition: color 0.2s;"><i class="fas fa-star"></i></label>
                                            <input type="radio" id="star2" name="rating" value="2" style="display:none;" />
                                            <label for="star2" title="2 sao" style="cursor: pointer; font-size: 1.8rem; color: #ddd; margin:0; padding:0; transition: color 0.2s;"><i class="fas fa-star"></i></label>
                                            <input type="radio" id="star1" name="rating" value="1" style="display:none;" />
                                            <label for="star1" title="1 sao" style="cursor: pointer; font-size: 1.8rem; color: #ddd; margin:0; padding:0; transition: color 0.2s;"><i class="fas fa-star"></i></label>
                                        </div>
                                    </div>
                                    <style>
                                        .star-rating label:hover,
                                        .star-rating label:hover~label,
                                        .star-rating input[type="radio"]:checked~label {
                                            color: #ffc107 !important;
                                        }
                                    </style>

                                    <div class="form-group mb-4">
                                        <label for="comment" class="font-weight-bold text-dark">Viết bình luận:</label>
                                        <textarea class="form-control shadow-sm" id="comment" name="comment" rows="4" placeholder="Nhập nhận xét của bạn về sản phẩm..." style="border-radius: 10px; resize: none; border: 1px solid #ced4da;"></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-block px-4 py-2 shadow" style="background-color: var(--primary-color); border: none; border-radius: 30px; font-weight: bold; font-size:1.1rem">
                                        <i class="fas fa-paper-plane mr-1"></i> Gửi đánh giá
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <div class="alert alert-warning text-center py-5 shadow-sm" style="border-radius: 15px;">
            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
            <h4>Không tìm thấy sản phẩm!</h4>
            <p class="text-muted">Có thể sản phẩm này đã bị xóa hoặc đường dẫn không hợp lệ.</p>
            <a href="<?php echo BASE_URL; ?>index.php?url=Product/" class="btn btn-primary mt-3 px-4 py-2" style="background-color: var(--primary-color); border: none; border-radius: 30px;">Quay lại danh sách</a>
        </div>
    <?php endif; ?>
</div>

<!-- LogIn Reminder Modal -->
<div class="modal fade" id="loginReminderModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header border-0 bg-light p-4">
                <h5 class="modal-title font-weight-bold" style="color: var(--primary-color); font-size: 1.4rem;">
                    <i class="fas fa-shopping-bag mr-2"></i> Thông báo mua hàng
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="outline: none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center p-5">
                <div class="mb-4">
                    <i class="fas fa-user-circle fa-5x" style="color: #e0e0e0; background: linear-gradient(135deg, var(--primary-color) 0%, #427e59 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;"></i>
                </div>
                <h4 class="font-weight-bold mb-3" style="color: #333;">Bạn cần đăng nhập để mua hàng!</h4>
                <p class="text-muted mb-0" style="font-size: 1.1rem;">Vui lòng đăng nhập hoặc đăng ký tài khoản mới để tiếp tục mua sắm và nhận nhiều ưu đãi hơn từ cửa hàng.</p>
            </div>
            <div class="modal-footer border-0 p-4 bg-light d-flex justify-content-center">
                <button type="button" class="btn btn-secondary px-4 py-2 mr-2" data-dismiss="modal" style="border-radius: 10px; font-weight: 600;">Để sau</button>
                <a href="<?php echo BASE_URL; ?>index.php?url=Page/login" class="btn px-5 py-2" style="background-color: #ff9800; border: 2px solid var(--primary-color); border-radius: 10px; font-weight: 600; box-shadow: 0 4px 15px rgba(255,152,0,0.3); color: white;">
                    Đăng nhập ngay
                </a>
            </div>
        </div>
    </div>
</div>


<style>
    .variant-item:hover {
        border-color: var(--primary-color) !important;
        cursor: pointer;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    }

    .variant-item.active {
        border-color: var(--primary-color) !important;
        border-width: 2px !important;
    }

    .variant-item.active::after {
        content: "\f00c";
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        position: absolute;
        bottom: -1px;
        right: -1px;
        background: var(--primary-color);
        color: white;
        font-size: 0.6rem;
        padding: 2px;
        border-radius: 4px 0 0 0;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .hidden {
        display: none !important;
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
                        
                        // Cập nhật lượt thích từ phản hồi của server
                        if (response.likes !== undefined) {
                            $('#like-count-detail-' + productId).text(response.likes.toLocaleString('vi-VN'));
                        }
                    } else {
                        window.location.href = '<?php echo BASE_URL; ?>index.php?url=Page/login';
                    }
                },
                error: function() {
                    console.error("Error toggling wishlist");
                }
            });
        });
        const variantItems = document.querySelectorAll('.variant-item');
        const mainImage = document.querySelector('.col-md-5.text-center img');
        const btnAddToCart = document.querySelector('a[href*="Cart/add"]');
        const btnBuyNow = document.querySelector('a[href*="Cart/buyNow"]');
        const btnAddVariant = document.getElementById('btnAddVariant');
        const variantAddForm = document.getElementById('variantAddForm');

        if (btnAddVariant) {
            btnAddVariant.addEventListener('click', function() {
                variantAddForm.classList.toggle('hidden');
            });
        }

        variantItems.forEach(item => {
            item.addEventListener('click', function() {
                // Clear active state
                variantItems.forEach(vi => vi.classList.remove('active'));

                // Set active
                this.classList.add('active');

                // Update main image
                const newImg = this.getAttribute('data-variant-image');
                const vName = this.getAttribute('data-variant-name');
                const vId = this.getAttribute('data-variant-id');
                const vPrice = parseFloat(this.getAttribute('data-variant-price') || 0);
                const vStock = parseInt(this.getAttribute('data-variant-stock') || 0);
                const discount = <?php echo $discount ?? 0; ?>;

                if (mainImage) {
                    if (!mainImage.hasAttribute('data-original-image')) {
                        mainImage.setAttribute('data-original-image', mainImage.src);
                    }
                    if (newImg) {
                        mainImage.src = newImg;
                        mainImage.alt = vName;
                    } else {
                        mainImage.src = mainImage.getAttribute('data-original-image');
                    }
                }

                // Update Price
                const priceToUse = vPrice > 0 ? vPrice : <?php echo $product->price ?? 0; ?>;
                const finalPrice = discount > 0 ? priceToUse * (1 - discount / 100) : priceToUse;

                const priceEl = document.getElementById('mainProductPrice');
                const priceOldEl = document.getElementById('mainProductPriceOld');

                if (priceEl) {
                    priceEl.innerHTML = new Intl.NumberFormat('vi-VN').format(finalPrice) + ' <span style="font-size: 1.3rem; text-decoration: underline;">đ</span>';
                }
                if (priceOldEl) {
                    priceOldEl.innerHTML = new Intl.NumberFormat('vi-VN').format(priceToUse) + ' đ';
                }

                // Update Stock
                const stockEl = document.getElementById('displayStock');
                const quantityInput = document.getElementById('buyQuantity');
                if (stockEl) {
                    stockEl.innerText = vStock;
                }
                if (quantityInput) {
                    quantityInput.setAttribute('max', vStock);
                    if (parseInt(quantityInput.value) > vStock) {
                        quantityInput.value = vStock > 0 ? 1 : 0;
                    }
                }

                // Update Cart/Buy links to include variant_id
                if (btnAddToCart) {
                    let href = btnAddToCart.getAttribute('href');
                    const url = new URL(href, window.location.origin);
                    url.searchParams.set('variant_id', vId);
                    btnAddToCart.setAttribute('href', url.pathname + url.search);
                }
                if (btnBuyNow) {
                    let href = btnBuyNow.getAttribute('href');
                    const url = new URL(href, window.location.origin);
                    url.searchParams.set('variant_id', vId);
                    btnBuyNow.setAttribute('href', url.pathname + url.search);
                }

                // Update Sold Out Overlay
                const imgContainer = mainImage.parentElement;
                if (vStock <= 0) {
                    mainImage.classList.add('product-img-sold-out');
                    imgContainer.classList.add('sold-out-container');
                    // Add overlay if not exists
                    if (!imgContainer.querySelector('.sold-out-overlay')) {
                        const overlay = document.createElement('div');
                        overlay.className = 'sold-out-overlay';
                        overlay.innerHTML = '<img src="<?php echo BASE_URL; ?>public/images/sold_out.png" alt="Hết hàng" style="width: 80%;">';
                        imgContainer.appendChild(overlay);
                    }
                } else {
                    mainImage.classList.remove('product-img-sold-out');
                    imgContainer.classList.remove('sold-out-container');
                    const overlay = imgContainer.querySelector('.sold-out-overlay');
                    if (overlay) overlay.remove();
                }

                // Handle out of stock for specific variant
                const btnArea = document.querySelector('.d-flex.align-items-center.flex-wrap');
                const qtySelector = document.querySelector('.d-flex.align-items-center.mb-4'); // Quantity selector area
                
                if (vStock <= 0) {
                    stockEl.classList.add('text-danger');
                    stockEl.innerText = 'Hết hàng';
                    if (qtySelector) qtySelector.style.display = 'none';
                    
                    // Show out of stock alert instead of buttons
                    let alertMsg = document.getElementById('variantOutOfStockAlert');
                    if (!alertMsg) {
                        alertMsg = document.createElement('div');
                        alertMsg.id = 'variantOutOfStockAlert';
                        alertMsg.className = 'alert alert-danger w-100 p-3 shadow-sm mb-4';
                        alertMsg.style.borderRadius = '10px';
                        alertMsg.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i> Hết hàng! Mẫu này hiện không khả dụng.';
                        btnArea.parentElement.insertBefore(alertMsg, btnArea);
                    }
                    btnArea.style.display = 'none';
                } else {
                    stockEl.classList.remove('text-danger');
                    stockEl.innerText = vStock;
                    if (qtySelector) qtySelector.style.display = 'flex';
                    const alertMsg = document.getElementById('variantOutOfStockAlert');
                    if (alertMsg) alertMsg.remove();
                    btnArea.style.display = 'flex';
                }
            });
        });

        // Guest Buy Now handle
        const buyNowGuestBtn = document.querySelector('.btn-buy-now-guest');
        if (buyNowGuestBtn) {
            buyNowGuestBtn.addEventListener('click', function(e) {
                e.preventDefault();
                $('#loginReminderModal').modal('show');
            });
        }

        // Buy Now Button Logic
        const btnBuyNowActual = document.querySelector('a[href*="Cart/buyNow"]');
        if (btnBuyNowActual && !btnBuyNowActual.classList.contains('btn-buy-now-guest')) {
            btnBuyNowActual.addEventListener('click', function(e) {
                e.preventDefault();
                const qty = document.getElementById('buyQuantity') ? document.getElementById('buyQuantity').value : 1;
                let href = this.getAttribute('href');
                if (href.includes('quantity=')) {
                    href = href.replace(/quantity=\d+/, 'quantity=' + qty);
                } else {
                    href += (href.includes('?') ? '&' : '?') + 'quantity=' + qty;
                }
                window.location.href = href;
            });
        }

        // Quantity Selector Logic
        const btnDecrease = document.getElementById('btnDecreaseQty');
        const btnIncrease = document.getElementById('btnIncreaseQty');
        const inputQty = document.getElementById('buyQuantity');

        if (btnDecrease && btnIncrease && inputQty) {
            btnDecrease.addEventListener('click', function() {
                let val = parseInt(inputQty.value);
                if (val > 1) {
                    inputQty.value = val - 1;
                }
            });

            btnIncrease.addEventListener('click', function() {
                let val = parseInt(inputQty.value);
                let max = parseInt(inputQty.getAttribute('max') || 999);
                if (val < max) {
                    inputQty.value = val + 1;
                } else {
                    alert('Số lượng đã đạt giới hạn tồn kho!');
                }
            });

            inputQty.addEventListener('change', function() {
                let val = parseInt(this.value);
                let max = parseInt(this.getAttribute('max') || 999);
                if (val > max) {
                    alert('Số lượng đã đạt giới hạn tồn kho!');
                    this.value = max;
                }
                if (val < 1 || isNaN(val)) {
                    this.value = 1;
                }
            });
        }

        // XỬ LÝ THÊM VÀO GIỎ HÀNG AJAX
        const btnAddToCartAjax = document.getElementById('btnAddToCartAjax');
        if (btnAddToCartAjax) {
            btnAddToCartAjax.addEventListener('click', function(e) {
                e.preventDefault();

                const productId = "<?php echo $product->id ?? ''; ?>";
                const activeVariant = document.querySelector('.variant-item.active');
                const variantId = activeVariant ? activeVariant.getAttribute('data-variant-id') : 0;
                const quantity = document.getElementById('buyQuantity') ? parseInt(document.getElementById('buyQuantity').value) : 1;

                // Hiệu ứng loading cho nút
                const originalContent = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Đang thêm...';
                this.style.pointerEvents = 'none';

                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('variant_id', variantId);
                formData.append('quantity', quantity);

                fetch('<?php echo BASE_URL; ?>index.php?url=Cart/addAjax', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.innerHTML = originalContent;
                        this.style.pointerEvents = 'auto';

                        if (data.success) {
                            // Cập nhật số lượng trên badge Header
                            const badge = document.getElementById('cart-badge');
                            if (badge) {
                                badge.innerText = data.cartCount;
                                badge.classList.remove('d-none');

                                // Hiệu ứng nhảy nhẹ (bounce) cho badge
                                badge.animate([{
                                        transform: 'scale(1)',
                                        background: '#fff'
                                    },
                                    {
                                        transform: 'scale(1.5)',
                                        background: '#75c794'
                                    },
                                    {
                                        transform: 'scale(1)',
                                        background: '#fff'
                                    }
                                ], {
                                    duration: 400,
                                    easing: 'ease-out'
                                });
                            }

                            // Hiển thị thông báo thành công (có thể thay bằng Toast sau này)
                            // Tạm thời dùng alert đơn giản nhưng hiệu quả
                            const successAlert = document.createElement('div');
                            successAlert.className = 'ajax-cart-success';
                            successAlert.innerHTML = `<i class="fas fa-check-circle mr-2"></i> ${data.message}`;
                            document.body.appendChild(successAlert);

                            setTimeout(() => {
                                successAlert.classList.add('show');
                                setTimeout(() => {
                                    successAlert.classList.remove('show');
                                    setTimeout(() => successAlert.remove(), 500);
                                }, 2000);
                            }, 100);
                        }
                    })
                    .catch(error => {
                        this.innerHTML = originalContent;
                        this.style.pointerEvents = 'auto';
                        console.error('Error:', error);
                        alert('Có lỗi xảy ra khi thêm vào giỏ hàng!');
                    });
            });
        }
    });

    function previewQuickVariant(input) {
        const preview = document.getElementById('quickVariantPreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

<style>
    .ajax-cart-success {
        position: fixed;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        background: #28a745;
        color: white;
        padding: 12px 25px;
        border-radius: 50px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        z-index: 10001;
        font-weight: bold;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        opacity: 0;
    }

    .ajax-cart-success.show {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
    }
</style>
<?php include 'app/views/shares/footer.php'; ?>