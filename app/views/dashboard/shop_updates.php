<?php include 'app/views/dashboard/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Duyệt thông tin Shop</h1>
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

    <div class="card shadow mb-4">
        <div class="card-body">
            <?php if (empty($updates)): ?>
                <div class="text-center p-5">
                    <i class="fas fa-check-circle text-success mb-3" style="font-size: 3rem;"></i>
                    <h5>Không có yêu cầu cập nhật thông tin shop nào.</h5>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th>Cửa hàng</th>
                                <th>Thông tin cũ</th>
                                <th>Thông tin mới</th>
                                <th>Hình ảnh mới</th>
                                <th>Ngày gửi</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($updates as $req): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($req->old_name); ?></strong><br>
                                        <small class="text-muted">Chủ shop: <?php echo htmlspecialchars($req->seller_name); ?></small>
                                    </td>
                                    <td>Tên: <?php echo htmlspecialchars($req->old_name); ?></td>
                                    <td>
                                        <strong>Tên mới:</strong> <?php echo htmlspecialchars($req->new_name); ?><br>
                                        <small><strong>Mô tả mới:</strong> <?php echo htmlspecialchars($req->new_description ?? 'Không có'); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($req->new_logo): ?>
                                            <a href="<?php echo BASE_URL . $req->new_logo; ?>" target="_blank" class="badge badge-info">Xem Logo mới</a>
                                        <?php endif; ?>
                                        <?php if ($req->new_banner): ?>
                                            <a href="<?php echo BASE_URL . $req->new_banner; ?>" target="_blank" class="badge badge-primary">Xem Banner mới</a>
                                        <?php endif; ?>
                                        <?php if (!$req->new_logo && !$req->new_banner): ?>
                                            <span class="text-muted small">Không đổi ảnh</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($req->created_at)); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?php echo BASE_URL; ?>index.php?url=Dashboard/approveShopUpdate/<?php echo $req->id; ?>" 
                                               class="btn btn-sm btn-success" 
                                               onclick="return confirm('Bạn chắc chắn muốn duyệt yêu cầu này?');">
                                                <i class="fas fa-check"></i> Duyệt
                                            </a>
                                            <a href="#" 
                                               class="btn btn-sm btn-warning btn-reject" data-id="<?php echo $req->id; ?>" style="color: #fff; font-weight: 500;">
                                                <i class="fas fa-edit"></i> YC chỉnh sửa
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>index.php?url=Dashboard/deleteShopUpdate/<?php echo $req->id; ?>" 
                                               class="btn btn-sm btn-danger text-white"
                                               onclick="return confirm('Bạn chắc chắn muốn xóa yêu cầu này?');">
                                                <i class="fas fa-trash"></i> Xóa yêu cầu
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'app/views/dashboard/footer.php'; ?>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="rejectForm" method="POST" action="">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="rejectModalLabel"><i class="fas fa-edit mr-2"></i>Yêu cầu chỉnh sửa thông tin Shop</h5>
                    <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rejection_reason" class="font-weight-bold">Lý do yêu cầu chỉnh sửa:</label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" required placeholder="Nhập lý do cụ thể để người bán có thể sửa đổi..."></textarea>
                        <small class="form-text text-muted">Nội dung này sẽ được gửi kèm thông báo cho người bán.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning" style="font-weight: 500;">Gửi yêu cầu</button>
                </div>
            </div>
        </form>
    </div>
</div>


<script>
$(document).ready(function() {
    $('.btn-reject').on('click', function(e) {
        e.preventDefault();
        const updateId = $(this).data('id');
        const actionUrl = '<?php echo BASE_URL; ?>index.php?url=Dashboard/rejectShopUpdate/' + updateId;
        $('#rejectForm').attr('action', actionUrl);
        $('#rejectModal').modal('show');
    });
});
</script>
