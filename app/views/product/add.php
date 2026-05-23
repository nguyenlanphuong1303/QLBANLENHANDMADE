<?php
$action = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') ? 'products' : 'add_product';
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
    .v-card {
        width: 100px;
        text-align: center;
        border: 1px solid #e7e7e7;
        border-radius: 6px;
        background: #fff;
        padding: 6px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    }
    .v-card-img {
        width: 88px;
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border-radius: 4px;
        position: relative;
        background: #fafafa;
        margin: 0 auto 6px;
    }
    .v-card-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .v-card-label { font-size: 13px; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .v-placeholder {
        width: 36px;
        height: 36px;
        border-radius: 4px;
        border: 1px dashed #ccc;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #666;
        font-size: 12px;
        background: #fff;
    }
    .v-preview-container { position: relative; }
    .v-remove-btn {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #fff;
        border-radius: 50%;
        border: 1px solid #f5c6cb;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #d9534f;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(0,0,0,0.06);
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
            <h2 class="edit-form-title">Thêm Sản Phẩm Mới</h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" style="border-radius: 8px; border-left: 4px solid #dc3545;">
                    <ul class="mb-0 pl-3">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form id="productForm" method="POST" action="<?php echo BASE_URL; ?>index.php?url=Product/save" enctype="multipart/form-data">

                <div class="custom-form-group">
                    <label for="name" class="custom-label">Tên sản phẩm:</label>
                    <input type="text" id="name" name="name" class="form-control custom-input" value="<?php echo htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div class="custom-form-group">
                    <label for="description" class="custom-label">Mô tả chi tiết:</label>
                    <textarea id="description" name="description" class="form-control custom-input" rows="5"><?php echo htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="form-group mb-0">
                            <label for="price" class="custom-label">Giá gốc / Giá niêm yết (VNĐ) <span class="text-danger">*</span></label>
                            <div class="input-with-icon" style="position: relative;">
                                <input type="text" id="price" name="price" style="font-size: 18px;" class="form-control custom-input text-success font-weight-bold" oninput="formatPriceInput(this)" required placeholder="Nhập giá (VD: 650.000 đ)" value="<?php echo htmlspecialchars($_POST['price'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <small id="priceError" class="text-danger mt-1" style="display:none; font-weight: 500;"><i class="fas fa-exclamation-circle"></i> Giá trị phải là số lớn hơn 0</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="custom-form-group">
                            <label for="discount_percent" class="custom-label" style="color: #ee225b;"><i class="fas fa-tags"></i> Giảm giá (%):</label>
                            <input type="number" id="discount_percent" name="discount_percent" class="form-control custom-input font-weight-bold" style="color: #ee225b; font-size: 16px;" value="<?php echo htmlspecialchars($_POST['discount_percent'] ?? '0', ENT_QUOTES, 'UTF-8'); ?>" min="0" max="100">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="custom-form-group">
                            <label for="stock" class="custom-label">Số lượng kho:</label>
                            <input type="number" id="stock" name="stock" class="form-control custom-input" value="<?php echo htmlspecialchars($_POST['stock'] ?? '0', ENT_QUOTES, 'UTF-8'); ?>" min="0">
                        </div>
                    </div>
                </div>
    

                <div class="custom-form-group">
                    <label for="category_id" class="custom-label">Danh mục:</label>
                    <select id="category_id" name="category_id" class="form-control custom-input" required>
                        <option value="" disabled <?php if (!isset($_POST['category_id'])) echo 'selected'; ?>>-- Chọn danh mục --</option>
                        <?php if (isset($categories) && (is_array($categories) || is_object($categories))): ?>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category->id; ?>" <?php if (isset($_POST['category_id']) && $_POST['category_id'] == $category->id) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($category->name, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="custom-form-group">
                    <label for="location" class="custom-label">Địa chỉ người bán:</label>
                    <input type="text" id="location" name="location" class="form-control custom-input" placeholder="Ví dụ: Tp. Hồ Chí Minh, Đà Nẵng..." value="<?php echo htmlspecialchars($_POST['location'] ?? 'Tp. Hồ Chí Minh', ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="custom-form-group">
                    <label for="image" class="custom-label">Hình ảnh sản phẩm chính:</label>
                    <input type="file" id="image" name="image" class="form-control-file" style="padding: 10px 0; font-size: 16px;" accept="image/*" onchange="previewImage(this)">

                    <!-- Vùng Hiển Thị Ảnh Xem Trước -->
                    <div id="imagePreviewContainer" class="mt-3 text-center" style="display: none; background: #f8f9fa; border: 1px dashed #ced4da; padding: 15px; border-radius: 8px;">
                        <div style="position: relative; display: inline-block;">
                            <button type="button" class="btn btn-sm btn-danger position-absolute" style="top: -10px; right: -10px; border-radius: 50%; width: 28px; height: 28px; padding: 0; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.3); z-index: 10;" onclick="clearImagePreview()" title="Hủy chọn ảnh này">
                                <i class="fas fa-times"></i>
                            </button>
                            <img id="imagePreview" src="" alt="Xem trước" style="max-height: 150px; border-radius: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); cursor: zoom-in;" title="Click để xem ảnh lớn!" onclick="document.getElementById('fullImage').src=this.src; document.getElementById('fullImageOverlay').style.display='flex';">
                        </div>
                        <div class="text-muted mt-2" style="font-size: 13px;"><i class="fas fa-search-plus"></i> Click vào ảnh để phóng to</div>
                    </div>
                </div>

                <!-- PHẦN THÊM PHÂN LOẠI SẢN PHẨM (VARIANTS) -->
                <div class="custom-form-group mt-5" style="border-top: 2px dashed #eee; padding-top: 20px;">
                    <div class="d-flex justify-content-between align-items-center mb-3 pt-4">
                        <label class="custom-label mb-0" style="color: var(--primary-color); font-size: 22px;">
                            <i class="fas fa-layer-group mr-2"></i> Phân loại mẫu sản phẩm
                        </label>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="addVariantRow()">
                            <i class="fas fa-plus mr-1"></i> Thêm mẫu mới
                        </button>
                    </div>

                    <div id="variantRowsContainer">
                        <!-- Các dòng variant sẽ được thêm vào đây bằng JS -->
                    </div>
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
    // Chức năng hiển thị ảnh xem trước
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

    // Chức năng xóa ảnh đang chọn ở nút X
    function clearImagePreview() {
        const input = document.getElementById('image');
        input.value = ""; // Xóa dữ liệu file khỏi thẻ input file

        document.getElementById('imagePreviewContainer').style.display = 'none';
        document.getElementById('imagePreview').src = "";
    }

    // Format chuẩn Shopee có báo đỏ Border và thêm chữ đ
    function formatPriceInput(input) {
        let cursorPos = input.selectionStart;
        let oldVal = input.value;

        // Lấy chỉ các ký tự số
        let numberVal = input.value.replace(/\D/g, '');

        // Xóa số 0 ở đầu
        if (numberVal.length > 0) {
            numberVal = parseInt(numberVal, 10).toString();
        }

        const errorMsg = document.getElementById('priceError');
        if (input.id === 'price' && (!numberVal || parseInt(numberVal) <= 0)) {
            input.classList.add('is-invalid');
            input.style.border = "2px solid #ff424f"; // Viền đỏ Shopee
            input.style.background = "#fff5f5";
            if (errorMsg) errorMsg.style.display = "block";
        } else {
            input.classList.remove('is-invalid');
            input.style.border = "1px solid #ddd";
            input.style.background = "#fff";
            if (errorMsg) errorMsg.style.display = "none";
        }

        if (!numberVal) {
            input.value = '';
            return;
        }

        // Format số với dấu chấm
        let formatted = numberVal.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        input.value = formatted + " đ";

        // Tính toán lại vị trí con trỏ
        if (cursorPos >= oldVal.length - 2) {
            cursorPos = input.value.length - 2;
        }

        try {
            input.setSelectionRange(cursorPos, cursorPos);
        } catch (e) {}
    }

    // --- PHẦN XỬ LÝ VARIANT (MẪU SẢN PHẨM) ---
    function addVariantRow() {
        const container = document.getElementById('variantRowsContainer');
        const rowId = Date.now();
        const rowHtml = `
        <div class="variant-row mb-4 p-3 border rounded shadow-sm position-relative bg-light" id="variant-row-${rowId}" style="border-left: 5px solid #75c794 !important;">
            <button type="button" class="btn btn-sm btn-outline-danger position-absolute" style="top: 10px; right: 10px; border-radius: 50%; width: 30px; height: 30px; padding: 0;" onclick="removeVariantRow(${rowId})" title="Xóa mẫu này">
                <i class="fas fa-times"></i>
            </button>
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label class="small font-weight-bold ml-1"><i class="fas fa-pen mr-1"></i>Tên phân loại:</label>
                    <input type="text" name="variant_names[]" class="form-control form-control-sm custom-input" style="font-size: 14px; padding: 8px 12px;" placeholder="vd: Màu xanh, Size XL" required>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="small font-weight-bold ml-1"><i class="fas fa-tag mr-1"></i>Giá riêng (đ):</label>
                    <input type="text" name="variant_prices[]" class="form-control form-control-sm custom-input text-success font-weight-bold" style="font-size: 14px; padding: 8px 12px;" placeholder="0 = dùng giá gốc" oninput="formatPriceInput(this)">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="small font-weight-bold ml-1"><i class="fas fa-boxes mr-1"></i>Kho:</label>
                    <input type="number" name="variant_stocks[]" class="form-control form-control-sm custom-input" style="font-size: 14px; padding: 8px 12px;" value="0" min="0">
                </div>
                <div class="col-md-4 mb-2">
                    <label class="small font-weight-bold ml-1"><i class="fas fa-image mr-1"></i>Ảnh mẫu:</label>
                    <div class="d-flex align-items-center">
                        <div id="v-card-${rowId}" class="v-card mr-2">
                            <div class="v-card-img">
                                <div id="v-placeholder-${rowId}" class="v-placeholder">No</div>
                                <img id="v-preview-${rowId}" src="" style="display:none; width:100%; height:100%; object-fit:cover; border-radius:4px; border:1px solid #ddd;">
                                <button type="button" id="v-remove-${rowId}" class="v-remove-btn" title="Xóa ảnh" style="display:none;" onclick="clearVariantImage('${rowId}')"><i class="fas fa-times" style="font-size:12px;"></i></button>
                            </div>
                            <div id="v-card-label-${rowId}" class="v-card-label">Mẫu</div>
                        </div>
                        <input type="file" id="v-input-${rowId}" name="variant_images[]" class="form-control-file variant-image-input" style="font-size: 13px;" accept="image/*" onchange="previewVariantImage(this, '${rowId}')">
                    </div>
                </div>
            </div>
        </div>
    `;
        container.insertAdjacentHTML('beforeend', rowHtml);
        // Attach name -> label binding for the new row
        const nameInput = document.querySelector(`#variant-row-${rowId} input[name="variant_names[]"]`);
        const cardLabel = document.getElementById(`v-card-label-${rowId}`);
        if (nameInput && cardLabel) {
            const updateLabel = () => { cardLabel.textContent = nameInput.value || 'Mẫu'; };
            nameInput.addEventListener('input', updateLabel);
            updateLabel();
        }
    }

    // Chức năng preview ảnh cho variant
    function previewVariantImage(input, rowId) {
        const previewImg = document.getElementById('v-preview-' + rowId);
        const placeholder = document.getElementById('v-placeholder-' + rowId);
        const removeBtn = document.getElementById('v-remove-' + rowId);

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (previewImg) {
                    previewImg.src = e.target.result;
                    previewImg.style.display = 'block';
                }
                if (placeholder) placeholder.style.display = 'none';
                if (removeBtn) removeBtn.style.display = 'flex';
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            if (previewImg) previewImg.style.display = 'none';
            if (placeholder) placeholder.style.display = 'inline-flex';
            if (removeBtn) removeBtn.style.display = 'none';
        }
    }

    function clearVariantImage(rowId) {
        const input = document.getElementById('v-input-' + rowId);
        const previewImg = document.getElementById('v-preview-' + rowId);
        const placeholder = document.getElementById('v-placeholder-' + rowId);
        const removeBtn = document.getElementById('v-remove-' + rowId);
        if (input) input.value = '';
        if (previewImg) {
            previewImg.src = '';
            previewImg.style.display = 'none';
        }
        if (removeBtn) removeBtn.style.display = 'none';
        if (placeholder) placeholder.style.display = 'inline-flex';
    }

    function removeVariantRow(rowId) {
        if (confirm('Xóa mẫu này khỏi danh sách?')) {
            const row = document.getElementById('variant-row-' + rowId);
            if (row) row.remove();
        }
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

                // Xử lý giá của từng variant trước khi submit
                const vPriceInputs = document.querySelectorAll('input[name="variant_prices[]"]');
                vPriceInputs.forEach(input => {
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