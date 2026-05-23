<?php 
$action = 'pending_products';
include 'app/views/dashboard/header.php'; 
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Sản phẩm chờ duyệt</h1>
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
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>Ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Người bán</th>
                            <th>Danh mục</th>
                            <th>Giá</th>
                            <th>Ngày đăng</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="8" class="text-center">Không có sản phẩm nào đang chờ duyệt.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <tr id="row-<?php echo $product->id; ?>">
                                    <td><?php echo $product->id; ?></td>
                                    <td>
                                        <?php 
                                        $pImg = $product->image;
                                        $finalPImg = (strpos($pImg, 'public/') === false) ? 
                                            ((strpos($pImg, 'uploads/') !== false) ? 'public/' . $pImg : 'public/uploads/' . $pImg) : 
                                            $pImg;
                                        ?>
                                        <img src="<?php echo BASE_URL . $finalPImg; ?>" alt="<?php echo $product->name; ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($product->name); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo substr(htmlspecialchars($product->description), 0, 50); ?>...</small>
                                    </td>
                                    <td><?php echo htmlspecialchars($product->seller_name ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($product->category_name ?? 'N/A'); ?></td>
                                    <td class="text-danger font-weight-bold"><?php echo number_format($product->price, 0, ',', '.'); ?> ₫</td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($product->created_at)); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?php echo BASE_URL; ?>index.php?url=Dashboard/approveProduct/<?php echo $product->id; ?>" class="btn btn-success btn-sm" onclick="return confirm('Phê duyệt sản phẩm này?')">
                                                <i class="fas fa-check"></i> Duyệt
                                            </a>
                                            <a href="#" class="btn btn-danger btn-sm btn-reject" data-id="<?php echo $product->id; ?>">
                                                <i class="fas fa-times"></i> Từ chối
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>index.php?url=Dashboard/pendingProducts/pin/<?php echo $product->id; ?>" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i> Xem
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="rejectForm" method="POST" action="">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="rejectModalLabel"><i class="fas fa-exclamation-triangle mr-2"></i>Từ chối sản phẩm</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rejection_reason" class="font-weight-bold">Lý do từ chối:</label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" required placeholder="Nhập lý do cụ thể để người bán có thể sửa đổi..."></textarea>
                        <small class="form-text text-muted">Lý do này sẽ được hiển thị cho người bán.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xác nhận từ chối</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('.btn-reject').on('click', function(e) {
        e.preventDefault();
        const productId = $(this).data('id');
        const actionUrl = '<?php echo BASE_URL; ?>index.php?url=Dashboard/rejectProduct/' + productId;
        $('#rejectForm').attr('action', actionUrl);
        $('#rejectModal').modal('show');
    });
});
</script>

<?php include 'app/views/dashboard/footer.php'; ?>
