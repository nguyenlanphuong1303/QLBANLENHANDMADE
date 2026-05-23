<?php
$action = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') ? 'products' : 'my_products';
include 'app/views/dashboard/header.php';
?>

<style>
    .edit-page-wrapper {
        background-color: #f5f5f5;
        padding: 50px 0;
        min-height: calc(100vh - 200px);
    }

    .edit-form-card {
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 40px 50px;
        max-width: 900px;
        margin: 0 auto;
    }

    .edit-form-title {
        font-weight: 700;
        color: #333;
        margin-bottom: 35px;
        text-align: center;
        position: relative;
        padding-bottom: 15px;
    }

    .edit-form-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 4px;
        background-color: #75c794;
        border-radius: 2px;
    }

    .custom-form-group {
        margin-bottom: 20px;
    }

    .custom-label {
        font-weight: 600;
        margin-bottom: 8px;
        color: #444;
        display: inline-block;
        font-size: 18px;
    }

    .custom-input {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 12px 15px;
        transition: all 0.3s ease;
        width: 100%;
        background-color: #fff;
        font-size: 18px;
    }

    .custom-input:focus {
        border-color: #75c794;
        box-shadow: 0 0 0 4px rgba(117, 199, 148, 0.15);
        outline: none;
    }

    select.custom-input {
        height: auto !important;
        appearance: auto;
        cursor: pointer;
    }

    .custom-file-input {
        padding: 10px 0;
    }

    .btn-save-custom {
        background-color: #75c794;
        border: none;
        border-radius: 8px;
        padding: 12px 28px;
        font-weight: 600;
        color: white;
        transition: all 0.3s;
        font-size: 18px;
        cursor: pointer;
    }

    .btn-save-custom:hover {
        background-color: #5ba878;
        color: white;
        box-shadow: 0 4px 8px rgba(91, 168, 120, 0.3);
    }

    .btn-back-custom {
        background-color: #f0f0f0;
        color: #555;
        border: none;
        border-radius: 8px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s;
        text-decoration: none !important;
        display: inline-block;
        font-size: 18px;
    }

    .btn-back-custom:hover {
        background-color: #e4e4e4;
        color: #333;
    }

    .action-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin-top: 35px;
        padding-top: 25px;
        border-top: 1px solid #eee;
    }

    .current-image-preview {
        background: #f8f9fa;
        border: 1px dashed #ced4da;
        padding: 15px;
        border-radius: 8px;
        display: inline-block;
        text-align: center;
    }

    .current-image-preview img {
        max-height: 120px;
        border-radius: 6px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    @media (max-width: 768px) {
        .edit-form-card {
            padding: 25px 20px;
        }

        .action-buttons {
            flex-direction: column-reverse;
        }

        .btn-back-custom,
        .btn-save-custom {
            width: 100%;
            text-align: center;
        }
    }
</style>

<div class="edit-page-wrapper">
    <div class="container">
        <div class="edit-form-card">
            <h2 class="edit-form-title">Sửa Sản Phẩm</h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" style="border-radius: 8px; border-left: 4px solid #dc3545;">
                    <ul class="mb-0 pl-3">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (isset($product->status) && $product->status === 'rejected' && !empty($product->rejection_reason)): ?>
                <div class="alert alert-warning mb-4 shadow-sm" style="border-radius: 10px; border-left: 5px solid #ff9800; background-color: #fff8e1;">
                    <h5 class="alert-heading font-weight-bold" style="color: #f57c00;"><i class="fas fa-exclamation-circle mr-2"></i>Yêu cầu chỉnh sửa từ Admin</h5>
                    <hr style="border-top-color: #ffe0b2;">
                    <p class="mb-0" style="color: #424242; font-size: 1.05rem; white-space: pre-wrap;"><?php echo htmlspecialchars($product->rejection_reason, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            <?php endif; ?>

            <form id="productForm" method="POST" action="<?php echo BASE_URL; ?>index.php?url=Product/update" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $product->id ?? ''; ?>">

                <div class="custom-form-group">
                    <label for="name" class="custom-label">Tên sản phẩm:</label>
                    <input type="text" id="name" name="name" class="form-control custom-input"
                        value="<?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div class="custom-form-group">
                    <label for="description" class="custom-label">Mô tả chi tiết:</label>
                    <textarea id="description" name="description" class="form-control custom-input" rows="5"><?php echo htmlspecialchars($product->description, ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="form-group mb-0">
                            <label for="price" class="custom-label">Giá gốc / Giá niêm yết (VNĐ) <span class="text-danger">*</span></label>
                            <div class="input-with-icon" style="position: relative;">
                                <input type="text" id="price" name="price" style="font-size: 18px;" class="form-control custom-input text-success font-weight-bold" oninput="formatPriceInput(this)" value="<?php echo number_format($product->price ?? 0, 0, ',', '.'); ?> đ" required placeholder="Nhập giá (VD: 650.000 đ)">
                            </div>
                            <small id="priceError" class="text-danger mt-1" style="display:none; font-weight: 500;"><i class="fas fa-exclamation-circle"></i> Giá trị phải là số lớn hơn 0</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="custom-form-group">
                            <label for="discount_percent" class="custom-label" style="color: #ee225b;"><i class="fas fa-tags"></i> Giảm giá (%):</label>
                            <input type="number" id="discount_percent" name="discount_percent" class="form-control custom-input font-weight-bold" style="color: #ee225b; font-size: 16px;" value="<?php echo isset($product->discount_percent) ? htmlspecialchars($product->discount_percent, ENT_QUOTES, 'UTF-8') : '0'; ?>" min="0" max="100">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-form-group">
                            <label for="stock" class="custom-label">Số lượng kho:</label>
                            <input type="number" id="stock" name="stock" class="form-control custom-input" value="<?php echo $product->stock ?? 0; ?>" min="0">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-form-group">
                            <label for="sold" class="custom-label">Đã bán:</label>
                            <input type="number" id="sold" name="sold" class="form-control custom-input" value="<?php echo $product->sold ?? 0; ?>" min="0">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-form-group">
                            <label for="rating" class="custom-label">Đánh giá (0-5):</label>
                            <input type="number" id="rating" name="rating" class="form-control custom-input" value="<?php echo $product->rating ?? 0; ?>" step="0.1" min="0" max="5">
                        </div>
                    </div>
                </div>

                <div class="custom-form-group">
                    <label for="category_id" class="custom-label">Danh mục:</label>
                    <select id="category_id" name="category_id" class="form-control custom-input" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category->id; ?>" <?php echo $category->id == $product->category_id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category->name, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="custom-form-group">
                    <label for="location" class="custom-label">Địa chỉ người bán:</label>
                    <input type="text" id="location" name="location" class="form-control custom-input" placeholder="Ví dụ: Tp. Hồ Chí Minh, Đà Nẵng..." value="<?php echo htmlspecialchars($product->location ?? 'Tp. Hồ Chí Minh', ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="custom-form-group">
                    <label for="image" class="custom-label">Hình ảnh sản phẩm (chọn nếu muốn đổi ảnh mới):</label>
                    <?php if (!empty($product->image)): ?>
                        <?php
                        $imgSrc = $product->image;
                        if (strpos($imgSrc, 'public/') === false) {
                            $imgSrc = (strpos($imgSrc, 'uploads/') !== false) ? 'public/' . $imgSrc : 'public/uploads/' . $imgSrc;
                        }
                        ?>
                        <div id="currentImageContainer" class="mb-3 text-left">
                            <div class="current-image-preview" style="position: relative; display: inline-block; background: #f8f9fa; border: 1px dashed #ced4da; padding: 15px; border-radius: 8px;">
                                <button type="button" class="btn btn-sm btn-danger position-absolute" style="top: -10px; right: -10px; border-radius: 50%; width: 28px; height: 28px; padding: 0; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.3); z-index: 10;" onclick="removeExistingImage()" title="Xóa ảnh hiện tại">
                                    <i class="fas fa-times"></i>
                                </button>
                                <img src="<?php echo BASE_URL . $imgSrc; ?>" alt="Current image" style="max-height: 150px; border-radius: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); cursor: zoom-in;" title="Click để xem ảnh lớn!" onclick="document.getElementById('fullImage').src=this.src; document.getElementById('fullImageOverlay').style.display='flex';">
                                <div class="text-muted mt-2 text-center" style="font-size: 13px;"><i class="fas fa-search-plus"></i> Ảnh hiện tại</div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <input type="hidden" id="remove_existing_image" name="remove_existing_image" value="0">
                    <input type="file" id="image" name="image" class="form-control-file" style="padding: 10px 0; font-size: 16px;" accept="image/*" onchange="previewImage(this)">

                    <!-- Vùng Hiển Thị Ảnh Xem Trước Mới -->
                    <div id="imagePreviewContainer" class="mt-3 text-center" style="display: none; background: #f8f9fa; border: 1px dashed #ced4da; padding: 15px; border-radius: 8px;">
                        <div style="position: relative; display: inline-block;">
                            <button type="button" class="btn btn-sm btn-danger position-absolute" style="top: -10px; right: -10px; border-radius: 50%; width: 28px; height: 28px; padding: 0; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.3); z-index: 10;" onclick="clearImagePreview()" title="Hủy chọn ảnh này">
                                <i class="fas fa-times"></i>
                            </button>
                            <img id="imagePreview" src="" alt="Xem trước" style="max-height: 150px; border-radius: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); cursor: zoom-in;" title="Click để xem ảnh lớn!" onclick="document.getElementById('fullImage').src=this.src; document.getElementById('fullImageOverlay').style.display='flex';">
                        </div>
                        <div class="text-muted mt-2" style="font-size: 13px;"><i class="fas fa-search-plus"></i> Ảnh mới chọn (Click để phóng to)</div>
                    </div>
                    <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($product->image ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <!-- PHẦN QUẢN LÝ PHÂN LOẠI SẢN PHẨM (VARIANTS) -->
                <div class="custom-form-group mt-5" style="border-top: 2px dashed #eee; pt-4;">
                    <div class="d-flex justify-content-between align-items-center mb-3 pt-4">
                        <label class="custom-label mb-0" style="color: var(--primary-color); font-size: 22px;">
                            <i class="fas fa-layer-group mr-2"></i> Quản lý mẫu sản phẩm
                        </label>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="addVariantRow()">
                            <i class="fas fa-plus mr-1"></i> Thêm mẫu mới
                        </button>
                    </div>

                    <div id="variantRowsContainer">
                        <?php if (!empty($variants)): ?>
                            <?php foreach ($variants as $v): ?>
                                <div class="variant-row mb-4 p-3 border rounded shadow-sm position-relative bg-light" id="variant-row-existing-<?php echo $v->id; ?>" style="border-left: 5px solid #75c794 !important;">
                                    <input type="hidden" name="existing_variant_ids[]" value="<?php echo $v->id; ?>">
                                    <button type="button" class="btn btn-sm btn-outline-danger position-absolute" style="top: 10px; right: 10px; border-radius: 50%; width: 30px; height: 30px; padding: 0;" onclick="removeExistingVariant(<?php echo $v->id; ?>)" title="Xóa mẫu này">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <div class="row">
                                        <div class="col-md-3 mb-2">
                                            <label class="small font-weight-bold ml-1"><i class="fas fa-pen mr-1"></i>Tên mẫu:</label>
                                            <input type="text" name="existing_variant_names[<?php echo $v->id; ?>]" class="form-control form-control-sm custom-input" style="font-size: 14px; padding: 8px 12px;" value="<?php echo htmlspecialchars($v->name, ENT_QUOTES, 'UTF-8'); ?>" required>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label class="small font-weight-bold ml-1"><i class="fas fa-tag mr-1"></i>Giá riêng (đ):</label>
                                            <input type="text" name="existing_variant_prices[<?php echo $v->id; ?>]" class="form-control form-control-sm custom-input text-success font-weight-bold" style="font-size: 14px; padding: 8px 12px;" value="<?php echo number_format($v->price ?? 0, 0, ',', '.'); ?> đ" oninput="formatPriceInput(this)">
                                        </div>
                                        <div class="col-md-2 mb-2">
                                            <label class="small font-weight-bold ml-1"><i class="fas fa-boxes mr-1"></i>Kho:</label>
                                            <input type="number" name="existing_variant_stocks[<?php echo $v->id; ?>]" class="form-control form-control-sm custom-input" style="font-size: 14px; padding: 8px 12px;" value="<?php echo $v->stock ?? 0; ?>" min="0">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label class="small font-weight-bold ml-1"><i class="fas fa-image mr-1"></i>Ảnh (chọn để đổi):</label>
                                            <div class="d-flex align-items-center">
                                                <img id="v-preview-exist-<?php echo $v->id; ?>" src="<?php echo BASE_URL . 'public/uploads/' . $v->image; ?>" style="height: 38px; width: 38px; object-fit: cover; border-radius: 4px; margin-right: 10px; border: 1px solid #ddd;">
                                                <input type="file" name="existing_variant_images[<?php echo $v->id; ?>]" class="form-control-file" style="font-size: 12px;" accept="image/*" onchange="previewExistingVariantImage(this, '<?php echo $v->id; ?>')">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="deleted_variant_ids" id="deleted_variant_ids" value="">
                </div>

                <div class="action-buttons d-flex justify-content-end align-items-center" style="gap: 15px;">
                    <button type="button" class="btn btn-outline-danger" style="border-radius: 8px; padding: 12px 24px; font-weight: 600; font-size: 18px; border-width: 2px;" onclick="if(confirm('Bạn có chắc chắn muốn xóa sạch tất cả thông tin đang nhập không?')) { document.getElementById('productForm').reset(); }">
                        <i class="fas fa-trash-alt mr-2"></i>Xóa tất cả
                    </button>
                    <a href="<?php echo BASE_URL; ?>index.php?url=<?php echo (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') ? 'Dashboard/products' : 'Product/myProducts'; ?>" class="btn-back-custom">
                        <i class="fas fa-arrow-left mr-2"></i>Quay lại
                    </a>
                    <button type="submit" class="btn-save-custom">
                        <i class="fas fa-paper-plane mr-2"></i>Gửi yêu cầu đăng sản phẩm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // --- LOGIC XỬ LÝ ẢNH ---
    function previewImage(input) {
        const previewContainer = document.getElementById('imagePreviewContainer');
        const previewImage = document.getElementById('imagePreview');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                previewContainer.style.display = 'inline-block';
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            previewImage.src = "";
            previewContainer.style.display = 'none';
        }
    }

    function clearImagePreview() {
        const input = document.getElementById('image');
        input.value = "";
        document.getElementById('imagePreviewContainer').style.display = 'none';
        document.getElementById('imagePreview').src = "";
    }

    function removeExistingImage() {
        if (confirm('Bạn có chắc chắn muốn xóa ảnh hiện tại không?')) {
            const container = document.getElementById('currentImageContainer');
            if (container) container.style.display = 'none';
            document.getElementById('remove_existing_image').value = '1';
        }
    }

    // Preview cho variant CŨ đang sửa
    function previewExistingVariantImage(input, variantId) {
        const imgElement = document.getElementById('v-preview-exist-' + variantId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imgElement.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Preview cho variant MỚI thêm
    function previewNewVariantImage(input, rowId) {
        const previewContainer = document.getElementById('v-new-preview-container-' + rowId);
        const previewImg = document.getElementById('v-new-preview-' + rowId);

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewContainer.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            previewContainer.style.display = 'none';
        }
    }

    let deletedVariantIds = [];

    function removeExistingVariant(variantId) {
        if (confirm('Xóa mẫu này khỏi sản phẩm? Lưu ý: Cần bấm Lưu để hoàn tất.')) {
            const row = document.getElementById('variant-row-existing-' + variantId);
            if (row) {
                row.remove();
                deletedVariantIds.push(variantId);
                document.getElementById('deleted_variant_ids').value = deletedVariantIds.join(',');
            }
        }
    }

    function addVariantRow() {
        const container = document.getElementById('variantRowsContainer');
        const rowId = Date.now();
        const rowHtml = `
        <div class="variant-row mb-4 p-3 border rounded shadow-sm position-relative bg-light" id="variant-row-new-${rowId}" style="border-left: 5px solid #ff9800 !important;">
            <button type="button" class="btn btn-sm btn-outline-danger position-absolute" style="top: 10px; right: 10px; border-radius: 50%; width: 30px; height: 30px; padding: 0;" onclick="removeNewVariantRow(${rowId})" title="Xóa">
                <i class="fas fa-times"></i>
            </button>
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label class="small font-weight-bold ml-1">Tên mẫu mới:</label>
                    <input type="text" name="new_variant_names[]" class="form-control form-control-sm custom-input" required>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="small font-weight-bold ml-1">Giá riêng (đ):</label>
                    <input type="text" name="new_variant_prices[]" class="form-control form-control-sm custom-input text-success font-weight-bold" oninput="formatPriceInput(this)">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="small font-weight-bold ml-1">Kho:</label>
                    <input type="number" name="new_variant_stocks[]" class="form-control form-control-sm custom-input" value="0">
                </div>
                <div class="col-md-4 mb-2">
                    <label class="small font-weight-bold ml-1">Ảnh mẫu:</label>
                    <div class="d-flex align-items-center">
                        <div id="v-new-preview-container-${rowId}" class="mr-2" style="display: none;">
                            <img id="v-new-preview-${rowId}" src="" style="width: 38px; height: 38px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;">
                        </div>
                        <input type="file" name="new_variant_images[]" class="form-control-file" style="font-size: 13px;" accept="image/*" required onchange="previewNewVariantImage(this, '${rowId}')">
                    </div>
                </div>
            </div>
        </div>
    `;
        container.insertAdjacentHTML('beforeend', rowHtml);
    }

    function removeNewVariantRow(rowId) {
        const row = document.getElementById('variant-row-new-' + rowId);
        if (row) row.remove();
    }

    function removeExistingImage() {
        if (confirm('Bạn có chắc muốn xóa ảnh hiện tại của sản phẩm này? Ảnh sẽ chính thức bị xóa khi bạn lưu lại.')) {
            const container = document.getElementById('currentImageContainer');
            if (container) container.style.display = 'none';
            const removeInput = document.getElementById('remove_existing_image');
            if (removeInput) removeInput.value = '1';
        }
    }

    function clearImagePreview() {
        const input = document.getElementById('image');
        if (input) input.value = '';
        const previewContainer = document.getElementById('imagePreviewContainer');
        if (previewContainer) previewContainer.style.display = 'none';
        const previewImage = document.getElementById('imagePreview');
        if (previewImage) previewImage.src = '';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const pForm = document.getElementById('productForm');
        if (pForm) {
            pForm.addEventListener('submit', function() {
                // Xử lý giá sản phẩm chính
                const priceInput = document.getElementById('price');
                if (priceInput && priceInput.value) {
                    priceInput.value = priceInput.value.replace(/\D/g, '');
                }

                // Xử lý giá của các variant đang có
                const vExistPriceInputs = document.querySelectorAll('input[name^="existing_variant_prices"]');
                vExistPriceInputs.forEach(input => {
                    if (input.value) {
                        input.value = input.value.replace(/\D/g, '');
                    }
                });

                // Xử lý giá của các variant mới
                const vNewPriceInputs = document.querySelectorAll('input[name="new_variant_prices[]"]');
                vNewPriceInputs.forEach(input => {
                    if (input.value) {
                        input.value = input.value.replace(/\D/g, '');
                    }
                });
            });
        }
    });
</script>

<!-- Full Image Overlay Modal -->
<div id="fullImageOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.85); z-index: 10000; align-items: center; justify-content: center; cursor: zoom-out;" onclick="this.style.display='none'">
    <span style="position: absolute; top: 20px; right: 30px; color: white; font-size: 40px; font-weight: bold; cursor: pointer;">&times;</span>
    <img id="fullImage" src="" style="max-width: 90%; max-height: 90%; border-radius: 12px; box-shadow: 0 5px 30px rgba(0,0,0,0.5);">
</div>

<?php include 'app/views/dashboard/footer.php'; ?>