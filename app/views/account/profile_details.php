<?php
// app/views/account/profile_details.php
?>
<div class="account-wrapper">
    <!-- Sidebar -->
    <?php include 'app/views/shares/account_sidebar.php'; ?>

    <!-- Main Content -->
    <main class="account-main">
        <div class="profile-card">
            <div class="profile-card-header">
                <h2 class="profile-title">Hồ Sơ Của Tôi</h2>
                <p class="profile-subtitle">Quản lý thông tin hồ sơ để bảo mật tài khoản</p>
            </div>
            
            <div class="profile-card-body">
                <form action="<?php echo BASE_URL; ?>index.php?url=Page/updateProfile" method="POST" class="profile-form">
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success" style="font-size: 14px;"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger" style="font-size: 14px;"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                    <?php endif; ?>

                    <div class="profile-form-left">
                        <div class="form-group-row">
                            <label>Tên đăng nhập</label>
                            <input type="text" name="username" value="<?php echo htmlspecialchars($user->username ?? ''); ?>" class="form-control-minimal" placeholder="Thiết lập tên đăng nhập">
                        </div>
                        <div class="form-group-row">
                            <label>Tên</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($user->name); ?>" class="form-control-minimal">
                        </div>
                        <div class="form-group-row">
                            <label>Email</label>
                            <div class="form-input-text">
                                <?php echo htmlspecialchars($user->email ?? 'Chưa thiết lập'); ?>
                                <a href="javascript:void(0)" class="btn-change-link" data-toggle="modal" data-target="#emailModal">Thay đổi</a>
                            </div>
                        </div>
                        <div class="form-group-row">
                            <label>Số điện thoại</label>
                            <div class="form-input-text">
                                <?php echo htmlspecialchars($user->phone ?? 'Chưa thiết lập'); ?>
                                <a href="javascript:void(0)" class="btn-change-link" data-toggle="modal" data-target="#phoneModal">Thay đổi</a>
                            </div>
                        </div>
                        <div class="form-group-row">
                            <label>Giới tính</label>
                            <div class="gender-radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="gender" value="nam" <?php echo ($user->gender ?? '') === 'nam' ? 'checked' : ''; ?>> Nam
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="gender" value="nu" <?php echo ($user->gender ?? '') === 'nu' ? 'checked' : ''; ?>> Nữ
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="gender" value="khac" <?php echo ( ($user->gender ?? '') === 'khac' || empty($user->gender) ) ? 'checked' : ''; ?>> Khác
                                </label>
                            </div>
                        </div>
                        <div class="form-group-row">
                            <label>Ngày sinh</label>
                            <div class="dob-selectors">
                                <input type="date" name="dob" value="<?php echo $user->dob ?? ''; ?>" class="form-control-minimal">
                            </div>
                        </div>
                        <div class="form-group-row" style="margin-top: 30px;">
                            <label></label>
                            <button type="submit" class="btn-save-profile">Lưu</button>
                        </div>
                    </div>
                </form>

                <div class="profile-form-right">
                    <div class="avatar-upload-section">
                        <div class="avatar-preview-large">
                            <?php 
                            $avatar = !empty($user->avatar) ? 
                                      BASE_URL . 'public/uploads/avatars/' . $user->avatar : 
                                      'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=random&color=fff';
                            ?>
                            <img src="<?php echo $avatar; ?>" alt="Large Avatar" id="largeAvatarPreview">
                        </div>
                        <form action="<?php echo BASE_URL; ?>index.php?url=Page/uploadAvatar" method="POST" enctype="multipart/form-data" id="avatarUploadForm">
                            <input type="file" name="avatar" id="avatarInput" style="display: none;" onchange="document.getElementById('avatarUploadForm').submit()">
                            <button type="button" class="btn-select-image" onclick="document.getElementById('avatarInput').click()">Chọn ảnh</button>
                        </form>
                        <div class="upload-requirements">
                            Dung lượng file tối đa 1 MB<br>
                            Định dạng: .JPEG, .PNG
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal Đổi Email -->
<div class="modal fade" id="emailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 4px; border: none;">
            <div class="modal-header" style="border-bottom: none; padding: 25px 30px 10px;">
                <h5 class="modal-title" style="font-size: 20px; color: #333;">Thay đổi Email</h5>
            </div>
            <div class="modal-body" style="padding: 10px 30px 25px;">
                <p style="color: #666; font-size: 14px; margin-bottom: 20px;">Vui lòng nhập địa chỉ email mới của bạn.</p>
                <input type="email" id="newEmailInput" class="form-control-minimal" style="max-width: none;" placeholder="Email mới" value="<?php echo htmlspecialchars($user->email ?? ''); ?>">
                <div id="emailError" style="color: #ee225b; font-size: 12px; margin-top: 5px; display: none;">Email không hợp lệ.</div>
            </div>
            <div class="modal-footer" style="border-top: none; padding: 0 30px 25px; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-cancel-modal" data-dismiss="modal">Trở Lại</button>
                <button type="button" class="btn-confirm-modal" onclick="updateEmail()">Xác Nhận</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Đổi SĐT -->
<div class="modal fade" id="phoneModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 4px; border: none;">
            <div class="modal-header" style="border-bottom: none; padding: 25px 30px 10px;">
                <h5 class="modal-title" style="font-size: 20px; color: #333;">Thay đổi Số điện thoại</h5>
            </div>
            <div class="modal-body" style="padding: 10px 30px 25px;">
                <p style="color: #666; font-size: 14px; margin-bottom: 20px;">Vui lòng nhập số điện thoại mới của bạn.</p>
                <input type="text" id="newPhoneInput" class="form-control-minimal" style="max-width: none;" placeholder="Số điện thoại mới" value="<?php echo htmlspecialchars($user->phone ?? ''); ?>">
                <div id="phoneError" style="color: #ee225b; font-size: 12px; margin-top: 5px; display: none;">Số điện thoại không hợp lệ.</div>
            </div>
            <div class="modal-footer" style="border-top: none; padding: 0 30px 25px; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-cancel-modal" data-dismiss="modal">Trở Lại</button>
                <button type="button" class="btn-confirm-modal" onclick="updatePhone()">Xác Nhận</button>
            </div>
        </div>
    </div>
</div>

<script>
function updateEmail() {
    const email = document.getElementById('newEmailInput').value;
    if (!email || !email.includes('@')) {
        document.getElementById('emailError').style.display = 'block';
        return;
    }
    
    fetch('<?php echo BASE_URL; ?>index.php?url=Page/updateEmail', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'email=' + encodeURIComponent(email)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Lỗi khi cập nhật Email.');
        }
    });
}

function updatePhone() {
    const phone = document.getElementById('newPhoneInput').value;
    if (!phone || phone.length < 10) {
        document.getElementById('phoneError').style.display = 'block';
        return;
    }
    
    fetch('<?php echo BASE_URL; ?>index.php?url=Page/updatePhone', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'phone=' + encodeURIComponent(phone)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Lỗi khi cập nhật Số điện thoại.');
        }
    });
}
</script>

<style>
.profile-card {
    background: #fff;
    padding: 30px;
    border-radius: 4px;
    box-shadow: 0 1px 2px rgba(0,0,0,.1);
}
.profile-card-header {
    border-bottom: 1px solid #efefef;
    padding-bottom: 20px;
    margin-bottom: 30px;
}
.profile-title {
    font-size: 18px;
    font-weight: 500;
    color: #333;
    margin-bottom: 5px;
}
.profile-subtitle {
    font-size: 14px;
    color: #555;
    margin: 0;
}
.profile-card-body {
    display: flex;
    gap: 40px;
}
.profile-form {
    flex: 1;
}
.profile-form-right {
    width: 280px;
    border-left: 1px solid #efefef;
    padding-left: 40px;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.form-group-row {
    display: flex;
    align-items: center;
    margin-bottom: 25px;
}
.form-group-row label {
    width: 150px;
    text-align: right;
    margin-right: 20px;
    color: rgba(85,85,85,.8);
    font-size: 14px;
}
.form-input-text {
    font-size: 14px;
    color: #333;
    display: flex;
    align-items: center;
    gap: 15px;
}
.btn-change-link {
    color: #05a;
    text-decoration: underline !important;
    font-size: 13px;
    cursor: pointer;
}
.form-control-minimal {
    border: 1px solid rgba(0,0,0,.14);
    padding: 10px;
    font-size: 14px;
    border-radius: 2px;
    width: 100%;
    max-width: 400px;
    outline: none;
}
.form-control-minimal:focus {
    border-color: rgba(0,0,0,.54);
}
.gender-radio-group {
    display: flex;
    gap: 20px;
}
.radio-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    cursor: pointer;
}
.btn-save-profile {
    background: #ee225b;
    color: #fff;
    border: none;
    padding: 10px 30px;
    border-radius: 2px;
    font-size: 14px;
    cursor: pointer;
    transition: background .2s;
}
.btn-save-profile:hover {
    background: #d41d4e;
}
.avatar-preview-large {
    width: 200px;
    height: 200px;
    border-radius: 50%;
    overflow: hidden;
    margin-bottom: 20px;
    border: 1px solid rgba(0,0,0,.09);
}
.avatar-preview-large img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-upload-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 150%;
}
#avatarUploadForm {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 150%;
}
.btn-select-image {
    background: #fff;
    border: 1px solid rgba(0,0,0,.09);
    padding: 8px 20px;
    font-size: 14px;
    color: #555;
    border-radius: 2px;
    margin-bottom: 15px;
    cursor: pointer;
    width: fit-content;
}
.upload-requirements {
    font-size: 13px;
    color: #999;
    line-height: 1.5;
    text-align: center;
    width: 100%;
}

/* Modal styles */
.btn-cancel-modal {
    background: #fff;
    border: 1px solid rgba(0,0,0,.09);
    color: #555;
    padding: 10px 20px;
    font-size: 14px;
    border-radius: 2px;
}
.btn-confirm-modal {
    background: #ee225b;
    border: none;
    color: #fff;
    padding: 10px 20px;
    font-size: 14px;
    border-radius: 2px;
}

@media (max-width: 768px) {
    .profile-card-body {
        flex-direction: column-reverse;
    }
    .profile-form-right {
        border-left: none;
        padding-left: 0;
        border-bottom: 1px solid #efefef;
        padding-bottom: 30px;
        margin-bottom: 30px;
        width: 100%;
    }
    .form-group-row {
        flex-direction: column;
        align-items: flex-start;
    }
    .form-group-row label {
        width: 100%;
        text-align: left;
        margin-bottom: 10px;
    }
}
</style>
