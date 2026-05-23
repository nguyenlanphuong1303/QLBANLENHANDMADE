<?php 
$action = 'pending_products';
include 'app/views/dashboard/header.php'; 
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Chi tiết sản phẩm chờ duyệt</h1>
        <a href="<?php echo BASE_URL; ?>index.php?url=Dashboard/pendingProducts#row-<?php echo $product->id; ?>" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4" style="border-radius: 10px;">
        <div class="card-body p-4">
            <div class="row">
                <!-- Product Image -->
                <div class="col-md-5 text-center mb-4">
                    <?php 
                    $pImg = $product->image;
                    $finalPImg = (strpos($pImg, 'public/') === false) ? 
                        ((strpos($pImg, 'uploads/') !== false) ? 'public/' . $pImg : 'public/uploads/' . $pImg) : 
                        $pImg;
                    ?>
                    <img src="<?php echo BASE_URL . $finalPImg; ?>" class="img-fluid rounded border shadow-sm" alt="<?php echo htmlspecialchars($product->name); ?>" style="max-height: 450px; object-fit: contain; width: 100%;">
                </div>

                <!-- Product Details -->
                <div class="col-md-7">
                    <h2 class="font-weight-bold mb-3 text-dark"><?php echo htmlspecialchars($product->name); ?></h2>
                    
                    <p class="mb-2"><strong class="text-secondary">Danh mục:</strong> <span class="badge badge-info px-2 py-1" style="font-size: 0.9rem;"><?php echo htmlspecialchars($product->category_name ?? 'N/A'); ?></span></p>
                    
                    <p class="mb-3"><strong class="text-secondary">Người đăng:</strong> <?php echo htmlspecialchars($product->seller_display_name ?? 'Thành viên'); ?> 
                        <?php if (!empty($product->shop_name)): ?>
                            <span class="text-muted">(Shop: <?php echo htmlspecialchars($product->shop_name); ?>)</span>
                        <?php endif; ?>
                    </p>

                    <div class="bg-light p-3 rounded border-left border-primary mb-4" style="border-left-width: 4px !important;">
                        <h3 class="font-weight-bold mb-0" style="color: var(--primary-color);">
                            <?php 
                            $discount = isset($product->discount_percent) ? (int)$product->discount_percent : 0;
                            $displayBasePrice = isset($minPrice) ? $minPrice : $product->price;
                            $newPrice = ($discount > 0) ? $displayBasePrice * (1 - $discount / 100) : $displayBasePrice;
                            echo number_format($newPrice, 0, ',', '.') . ' ₫'; 
                            ?>
                            <?php if ($discount > 0): ?>
                                <small class="text-muted ml-2" style="text-decoration: line-through; font-size: 1.2rem;"><?php echo number_format($displayBasePrice, 0, ',', '.'); ?> ₫</small>
                                <span class="badge badge-danger ml-2" style="font-size: 0.9rem;">-<?php echo $discount; ?>%</span>
                            <?php endif; ?>
                        </h3>
                    </div>

                    <p class="mb-2"><strong class="text-secondary"><i class="fas fa-boxes mr-1"></i> Kho:</strong> <?php echo $product->stock ?? 0; ?> sản phẩm</p>
                    <p class="mb-4"><strong class="text-secondary"><i class="fas fa-map-marker-alt mr-1"></i> Gửi từ:</strong> <?php echo htmlspecialchars($product->location ?? 'Tp. Hồ Chí Minh'); ?></p>
                    
                    <div class="mb-4">
                        <h5 class="font-weight-bold text-gray-800 border-bottom pb-2">Mô tả sản phẩm</h5>
                        <div class="p-3 bg-white rounded border mt-2 text-dark" style="line-height: 1.6; max-height: 300px; overflow-y: auto;">
                            <?php echo nl2br(htmlspecialchars($product->description)); ?>
                        </div>
                    </div>

                    <?php if (!empty($variants)): ?>
                        <div class="mb-4">
                            <h5 class="font-weight-bold text-gray-800 border-bottom pb-2">Các mẫu sản phẩm</h5>
                            <div class="d-flex flex-wrap mt-3" style="gap: 15px;">
                                <?php foreach ($variants as $variant): ?>
                                    <?php $hasVariantImage = !empty($variant->image) && $variant->image !== 'null'; ?>
                                    
                                    <?php if ($hasVariantImage): ?>
                                        <div class="border rounded p-2 text-center bg-white shadow-sm d-flex flex-column justify-content-center align-items-center" style="width: 120px; transition: transform 0.2s;">
                                            <img src="<?php echo BASE_URL . 'public/uploads/' . $variant->image; ?>" style="width: 100%; height: 90px; object-fit: cover; border-radius: 4px; margin-bottom: 8px;">
                                            <small class="d-block font-weight-bold text-dark text-truncate w-100" title="<?php echo htmlspecialchars($variant->name); ?>"><?php echo htmlspecialchars($variant->name); ?></small>
                                            <strong class="text-danger d-block mt-1" style="font-size: 0.9rem;"><?php echo number_format($variant->price > 0 ? $variant->price : $displayBasePrice, 0, ',', '.'); ?>₫</strong>
                                            <div style="font-size: 0.8rem;" class="text-muted mt-1"><i class="fas fa-box-open mr-1"></i> <?php echo $variant->stock; ?></div>
                                        </div>
                                    <?php else: ?>
                                        <div class="border rounded text-center bg-white shadow-sm d-flex justify-content-center align-items-center" style="min-width: 80px; padding: 10px 20px; transition: transform 0.2s;">
                                            <span class="d-block text-dark text-truncate" style="font-size: 1.1rem; color: #333;" title="<?php echo htmlspecialchars($variant->name); ?>"><?php echo htmlspecialchars($variant->name); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <hr class="my-4">
                    
                    <div class="d-flex flex-wrap" style="gap: 15px;">
                        <a href="<?php echo BASE_URL; ?>index.php?url=Dashboard/approveProduct/<?php echo $product->id; ?>" class="btn btn-success px-4 py-3 shadow flex-grow-1 text-center" style="border-radius: 10px; font-weight: bold; font-size: 1.1rem;" onclick="return confirm('Bạn chắc chắn muốn phê duyệt sản phẩm này?')">
                            <i class="fas fa-check-circle mr-2"></i> Duyệt sản phẩm
                        </a>
                        <button type="button" class="btn btn-warning px-4 py-3 shadow flex-grow-1 text-center btn-request-edit" style="border-radius: 10px; font-weight: bold; font-size: 1.1rem; color: #fff;">
                            <i class="fas fa-edit mr-2"></i> Yêu cầu chỉnh sửa
                        </button>
                        <button type="button" class="btn btn-danger px-4 py-3 shadow flex-grow-1 text-center btn-reject" data-id="<?php echo $product->id; ?>" style="border-radius: 10px; font-weight: bold; font-size: 1.1rem;">
                            <i class="fas fa-times-circle mr-2"></i> Từ chối duyệt
                        </button>
                    </div>

                    <!-- Vùng nhập yêu cầu chỉnh sửa (ẩn mặc định) -->
                    <div id="requestEditSection" class="mt-4 p-3 bg-light rounded border shadow-sm" style="display: none;">
                        <form action="<?php echo BASE_URL; ?>index.php?url=Dashboard/requestEditProduct/<?php echo $product->id; ?>" method="POST">
                            <div class="form-group mb-2">
                                <label class="font-weight-bold text-dark"><i class="fas fa-info-circle mr-1 text-warning"></i> Nội dung yêu cầu chỉnh sửa:</label>
                                <textarea name="edit_request_note" class="form-control" rows="3" placeholder="Nhập lý do và các mục cần người bán sửa đổi..." required style="border-radius: 8px; resize: none;"></textarea>
                            </div>
                            <div class="text-right mt-2">
                                <button type="button" class="btn btn-secondary btn-sm px-3 mr-2 btn-cancel-edit" style="border-radius: 5px;">Hủy</button>
                                <button type="submit" class="btn btn-warning btn-sm px-4 shadow-sm" style="border-radius: 5px; color: #fff; font-weight: bold;">
                                    <i class="fas fa-paper-plane mr-1"></i> Gửi yêu cầu chỉnh sửa
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="rejectForm" method="POST" action="">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header bg-danger text-white" style="border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title font-weight-bold" id="rejectModalLabel"><i class="fas fa-exclamation-triangle mr-2"></i>Từ chối duyệt sản phẩm</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label for="rejection_reason" class="font-weight-bold text-dark mb-2">Lý do từ chối <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="5" required placeholder="Nhập lý do cụ thể để người bán có thể sửa đổi (VD: Hình ảnh mờ, thông tin thiếu...)" style="border-radius: 10px; resize: none;"></textarea>
                        <small class="form-text text-muted mt-2"><i class="fas fa-info-circle mr-1"></i> Lý do này sẽ được gửi trực tiếp cho người bán để họ cập nhật.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light" style="border-radius: 0 0 15px 15px;">
                    <button type="button" class="btn btn-secondary px-4 font-weight-bold" data-dismiss="modal" style="border-radius: 8px;">Hủy bỏ</button>
                    <button type="submit" class="btn btn-danger px-4 font-weight-bold" style="border-radius: 8px;">Xác nhận từ chối</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include 'app/views/dashboard/footer.php'; ?>

<script>
$(document).ready(function() {
    $('.btn-reject').on('click', function(e) {
        e.preventDefault();
        const productId = $(this).data('id');
        const actionUrl = '<?php echo BASE_URL; ?>index.php?url=Dashboard/rejectProduct/' + productId;
        $('#rejectForm').attr('action', actionUrl);
        $('#rejectModal').modal('show');
    });

    // Handle Yêu cầu chỉnh sửa toggle
    $('.btn-request-edit').on('click', function() {
        $('#requestEditSection').slideDown();
    });

    // Handle Hủy yêu cầu chỉnh sửa
    $('.btn-cancel-edit').on('click', function() {
        $('#requestEditSection').slideUp();
        $('#requestEditSection textarea').val('');
    });
});
</script>
