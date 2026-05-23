<?php 
$pendingUpdate = $pendingUpdate ?? null; 
if (!isset($shop)) {
    $shop = new stdClass();
    $shop->name = '';
    $shop->description = '';
    $shop->logo = '';
    $shop->banner = '';
}
?>
<div class="container-fluid">
    <h2 class="mb-4">Thông tin Shop</h2>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle mr-2"></i> <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <?php if ($pendingUpdate): ?>
        <div id="pendingScreen" class="card shadow-sm border-0 mb-5 text-center" style="border-radius: 15px; background: #fffdf5; border-top: 5px solid #ffc107 !important;">
            <div class="card-body p-5">
                <i class="fas fa-clock fa-4x text-warning mb-4"></i>
                <h3 class="mb-3">Yêu cầu đang chờ duyệt</h3>
                <p class="text-muted mb-4" style="font-size: 1.1rem;">
                    Bạn đã gửi yêu cầu cập nhật thông tin Shop vào lúc <strong><?php echo date('d/m/Y H:i', strtotime($pendingUpdate->created_at)); ?></strong>.<br>
                    Admin đang xem xét yêu cầu của bạn. Xin vui lòng chờ!
                </p>
                
                <div class="mx-auto text-left bg-white p-4 rounded shadow-sm border mb-4" style="max-width: 600px;">
                    <h5 class="border-bottom pb-3 mb-4"><i class="fas fa-info-circle mr-2 text-primary"></i>Thông tin Shop mới chờ duyệt</h5>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted font-weight-bold">Tên Shop:</div>
                        <div class="col-sm-8 text-dark"><?php echo htmlspecialchars($pendingUpdate->new_name); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted font-weight-bold">Mô tả:</div>
                        <div class="col-sm-8 text-dark"><?php echo nl2br(htmlspecialchars($pendingUpdate->new_description ?? 'Chưa cập nhật')); ?></div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 mt-2 pt-3 border-top text-muted small text-center">
                            <i class="fas fa-image mr-1"></i> Các thay đổi về hình ảnh (Logo, Banner) cũng đã được ghi nhận.
                        </div>
                    </div>
                </div>
                
                <button type="button" class="btn btn-outline-primary px-4 py-2 rounded-pill shadow-sm font-weight-bold" onclick="showEditForm()">
                    <i class="fas fa-edit mr-2"></i> Chỉnh sửa lại Shop
                </button>
            </div>
        </div>
    <?php endif; ?>

    <div id="editFormScreen" class="card shadow-sm border-0 mb-5" style="border-radius: 15px; <?php echo $pendingUpdate ? 'display: none;' : ''; ?>">
        <div class="card-body p-4 p-md-5">
            <form action="<?php echo BASE_URL; ?>index.php?url=Seller/submitShopUpdate" method="POST" enctype="multipart/form-data">
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group mb-4">
                            <label class="font-weight-bold">Tên Shop <span class="text-danger">*</span></label>
                            <?php $display_name = $pendingUpdate ? $pendingUpdate->new_name : $shop->name; ?>
                            <input type="text" name="name" class="form-control form-control-lg" value="<?php echo htmlspecialchars($display_name); ?>" required> 
                        </div>

                        <div class="form-group mb-4">
                            <label class="font-weight-bold">Mô tả Shop</label>
                            <?php $display_desc = $pendingUpdate ? $pendingUpdate->new_description : $shop->description; ?>
                            <textarea name="description" class="form-control" rows="5"><?php echo htmlspecialchars($display_desc ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="form-group mb-4">
                            <label class="font-weight-bold d-block">Ảnh đại diện (Logo)</label>
                            <?php 
                            $target_logo = $pendingUpdate ? $pendingUpdate->new_logo : $shop->logo;
                            $logoUrl = !empty($target_logo) ? (strpos($target_logo, 'http') === 0 ? $target_logo : BASE_URL . $target_logo) : BASE_URL . 'public/images/logolen.jpg'; 
                            ?>
                            
                            <div class="position-relative d-inline-block" style="cursor: pointer;" onclick="document.getElementById('logoUpload').click()">
                                <img id="logoPreview" src="<?php echo $logoUrl; ?>" class="rounded-circle shadow-sm mb-3" style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #f8f9fa;">
                                <div class="position-absolute d-flex justify-content-center align-items-center" style="bottom: 15px; right: 0; background: var(--primary-color); color: white; width: 35px; height: 35px; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.2);" title="Thay đổi Logo">
                                    <i class="fas fa-camera"></i>
                                </div>
                                <input type="file" name="logo" id="logoUpload" accept="image/*" class="d-none" onchange="previewImage(this, 'logoPreview')">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label class="font-weight-bold d-block">Ảnh bìa (Banner)</label>
                    <?php $target_banner = $pendingUpdate ? $pendingUpdate->new_banner : $shop->banner; ?>
                    
                    <div class="position-relative rounded shadow-sm mb-3 overflow-hidden" style="cursor: pointer; border: 2px dashed #ddd; width: 100%; height: 250px; background: #f8f9fa;" onclick="document.getElementById('bannerUpload').click()">
                        <?php if (!empty($target_banner)): ?>
                            <img id="bannerPreview" src="<?php echo (strpos($target_banner, 'http') === 0 ? $target_banner : BASE_URL . $target_banner); ?>" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                            <div id="bannerPlaceholder" class="d-flex align-items-center justify-content-center flex-column" style="width: 100%; height: 100%; display: none !important;">
                                <i class="fas fa-image fa-3x text-muted mb-2"></i>
                                <span class="text-muted">Nhấn để chọn ảnh bìa mới</span>
                            </div>
                        <?php else: ?>
                            <img id="bannerPreview" src="" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                            <div id="bannerPlaceholder" class="d-flex align-items-center justify-content-center flex-column" style="width: 100%; height: 100%;">
                                <i class="fas fa-image fa-3x text-muted mb-2"></i>
                                <span class="text-muted">Nhấn để chọn ảnh bìa mới</span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="position-absolute d-flex justify-content-center align-items-center" style="bottom: 15px; right: 15px; background: rgba(0,0,0,0.6); color: white; padding: 8px 15px; border-radius: 5px;" title="Thay đổi Banner">
                            <i class="fas fa-camera mr-2"></i> Thay đổi ảnh bìa
                        </div>
                        <input type="file" name="banner" id="bannerUpload" accept="image/*" class="d-none" onchange="previewBanner(this)">
                    </div>
                </div>

                <div class="text-right mt-5">
                    <?php if ($pendingUpdate): ?>
                        <button type="button" class="btn btn-lg btn-light px-4 mr-2" style="border-radius: 30px;" onclick="hideEditForm()">Hủy chỉnh sửa</button>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-lg px-5 text-white" style="background-color: var(--primary-color); border-radius: 30px; box-shadow: 0 4px 10px rgba(238, 34, 91, 0.3);">
                        <i class="fas fa-paper-plane mr-2"></i> <?php echo $pendingUpdate ? 'Cập nhật lại yêu cầu' : 'Gửi yêu cầu cập nhật'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function showEditForm() {
        document.getElementById('pendingScreen').style.display = 'none';
        document.getElementById('editFormScreen').style.display = 'block';
    }

    function hideEditForm() {
        document.getElementById('pendingScreen').style.display = 'block';
        document.getElementById('editFormScreen').style.display = 'none';
    }

    function previewImage(input, previewId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById(previewId).src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function previewBanner(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var preview = document.getElementById('bannerPreview');
                preview.src = e.target.result;
                preview.style.display = 'block';
                
                var placeholder = document.getElementById('bannerPlaceholder');
                if (placeholder) {
                    placeholder.style.setProperty('display', 'none', 'important');
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Tự động lưu nội dung đang nhập để không bị mất khi reload
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const nameInput = document.querySelector('input[name="name"]');
        const descInput = document.querySelector('textarea[name="description"]');
        
        <?php if (!$pendingUpdate): ?>
        // 1. Phục hồi dữ liệu nếu có
        if (sessionStorage.getItem('shop_name_draft')) {
            nameInput.value = sessionStorage.getItem('shop_name_draft');
        }
        if (sessionStorage.getItem('shop_desc_draft')) {
            descInput.value = sessionStorage.getItem('shop_desc_draft');
        }
        
        // 2. Lưu dữ liệu khi người dùng gõ
        if (nameInput) {
            nameInput.addEventListener('input', function() {
                sessionStorage.setItem('shop_name_draft', this.value);
            });
        }
        if (descInput) {
            descInput.addEventListener('input', function() {
                sessionStorage.setItem('shop_desc_draft', this.value);
            });
        }
        
        // 3. Xóa bộ nhớ tạm khi submit thành công
        if (form) {
            form.addEventListener('submit', function() {
                // Không xóa ngay vì nếu lỗi validation server thì mất, 
                // nhưng ở đây submit là post, server redirect. 
                // Ta có thể xóa trước khi submit.
                sessionStorage.removeItem('shop_name_draft');
                sessionStorage.removeItem('shop_desc_draft');
            });
        }
        <?php endif; ?>
    });
</script>
