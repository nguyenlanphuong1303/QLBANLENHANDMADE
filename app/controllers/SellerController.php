<?php
require_once 'app/config/database.php';
require_once 'app/models/SellerRequestModel.php';
require_once 'app/models/NotificationModel.php';
require_once 'app/models/OrderModel.php';
require_once 'app/models/ProductModel.php';
require_once 'app/models/CategoryModel.php';
require_once 'app/models/ShopModel.php';
require_once 'app/models/ShopUpdateModel.php';

class SellerController
{
    private $db;
    private $sellerRequestModel;
    private $notificationModel;
    private $orderModel;
    private $productModel;
    private $categoryModel;
    private $shopModel;
    private $shopUpdateModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'index.php?url=Page/login');
            exit;
        }

        $database = new Database();
        $this->db = $database->getConnection();
        $this->sellerRequestModel = new SellerRequestModel($this->db);
        $this->notificationModel = new NotificationModel($this->db);
        $this->orderModel = new OrderModel($this->db);
        $this->productModel = new ProductModel($this->db);
        $this->categoryModel = new CategoryModel($this->db);
        $this->shopModel = new ShopModel($this->db);
        $this->shopUpdateModel = new ShopUpdateModel($this->db);
    }

    public function register()
    {
        $userId = $_SESSION['user_id'];
        $existingRequest = $this->sellerRequestModel->getLatestRequestByUserId($userId);

        // If already a seller, redirect to their products
        if ($_SESSION['user_role'] === 'seller') {
            header('Location: ' . BASE_URL . 'index.php?url=Product/myProducts');
            exit;
        }

        require_once 'app/views/shares/header.php';
        require_once 'app/views/account/seller_onboarding.php';
        require_once 'app/views/shares/footer.php';
    }

    public function submitRegistration()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];

            // Handle file upload for identity proof
            $identityProof = '';
            if (isset($_FILES['identity_proof']) && $_FILES['identity_proof']['error'] === 0) {
                $targetDir = "public/uploads/verification/";
                if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

                $fileName = time() . '_' . $_FILES['identity_proof']['name'];
                $targetFile = $targetDir . $fileName;
                if (move_uploaded_file($_FILES['identity_proof']['tmp_name'], $targetFile)) {
                    $identityProof = $fileName;
                }
            }

            $data = [
                'user_id' => $userId,
                'shop_name' => $_POST['shop_name'],
                'shop_description' => $_POST['shop_description'],
                'product_types' => $_POST['product_types'],
                'portfolio_links' => $_POST['portfolio_links'],
                'bank_account' => $_POST['bank_account'],
                'identity_proof' => $identityProof
            ];

            if ($this->sellerRequestModel->create($data)) {
                // Notify Admins
                $query = "SELECT id FROM user WHERE role = 'admin'";
                $stmt = $this->db->prepare($query);
                $stmt->execute();
                $admins = $stmt->fetchAll(PDO::FETCH_OBJ);
                foreach ($admins as $admin) {
                    $this->notificationModel->create(
                        $admin->id,
                        'Yêu cầu đăng ký Seller mới',
                        'Người dùng ' . $_SESSION['user_name'] . ' vừa gửi yêu cầu mở shop: ' . $data['shop_name'],
                        'seller_request',
                        'index.php?url=Dashboard/manageSellers'
                    );
                }

                $_SESSION['success_message'] = "Yêu cầu của bạn đã được gửi và đang chờ xét duyệt!";
            } else {
                $_SESSION['error_message'] = "Có lỗi xảy ra, vui lòng thử lại sau.";
            }
        }
        header('Location: ' . BASE_URL . 'index.php?url=Seller/register');
        exit;
    }

    public function quickRequestPermission()
    {
        header('Content-Type: application/json');

        $userId = $_SESSION['user_id'];
        $userName = $_SESSION['user_name'];

        // 1. Notify Admins
        $query = "SELECT id FROM user WHERE role = 'admin'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $admins = $stmt->fetchAll(PDO::FETCH_OBJ);

        $success = true;
        foreach ($admins as $admin) {
            if (!$this->notificationModel->create(
                $admin->id,
                'Yêu cầu phân quyền Seller',
                'Người dùng ' . $userName . ' muốn trở thành người bán (Seller).',
                'seller_request',
                'index.php?url=Dashboard/manageSellers'
            )) {
                $success = false;
            }
        }

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Yêu cầu của bạn đã được gửi tới Quản trị viên!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi gửi yêu cầu.']);
        }
        exit;
    }

    public function index()
    {
        if ($_SESSION['user_role'] !== 'seller') {
            header('Location: ' . BASE_URL . 'index.php?url=Seller/register');
            exit;
        }

        $sellerId = $_SESSION['user_id'];

        // 1. Total Products
        $stmtProducts = $this->db->prepare("SELECT COUNT(*) as total FROM product WHERE user_id = :seller_id");
        $stmtProducts->bindParam(':seller_id', $sellerId);
        $stmtProducts->execute();
        $totalProducts = $stmtProducts->fetch(PDO::FETCH_OBJ)->total ?? 0;

        // 2. Total Sold (sum of 'sold' column in product table)
        $stmtSold = $this->db->prepare("SELECT SUM(sold) as total_sold FROM product WHERE user_id = :seller_id");
        $stmtSold->bindParam(':seller_id', $sellerId);
        $stmtSold->execute();
        $totalSold = $stmtSold->fetch(PDO::FETCH_OBJ)->total_sold ?? 0;

        // 3. Total Orders (distinct orders containing seller's products)
        $stmtOrders = $this->db->prepare("SELECT COUNT(DISTINCT od.order_id) as total_orders 
                                         FROM order_detail od 
                                         JOIN product p ON od.product_id = p.id 
                                         WHERE p.user_id = :seller_id");
        $stmtOrders->bindParam(':seller_id', $sellerId);
        $stmtOrders->execute();
        $totalOrders = $stmtOrders->fetch(PDO::FETCH_OBJ)->total_orders ?? 0;

        // 4. Total Revenue (sum of items price * quantity for non-cancelled orders)
        $stmtRevenue = $this->db->prepare("SELECT SUM(od.price * od.quantity) as total_revenue 
                                          FROM order_detail od 
                                          JOIN product p ON od.product_id = p.id 
                                          JOIN orders o ON od.order_id = o.id 
                                          WHERE p.user_id = :seller_id AND o.status != 'cancelled'");
        $stmtRevenue->bindParam(':seller_id', $sellerId);
        $stmtRevenue->execute();
        $totalRevenue = $stmtRevenue->fetch(PDO::FETCH_OBJ)->total_revenue ?? 0;

        // 5. Wallet Balance
        $stmtWallet = $this->db->prepare("SELECT balance FROM seller_wallets WHERE seller_id = :seller_id");
        $stmtWallet->bindParam(':seller_id', $sellerId);
        $stmtWallet->execute();
        $walletRow = $stmtWallet->fetch(PDO::FETCH_OBJ);
        $walletBalance = $walletRow ? $walletRow->balance : 0.00;

        $action = 'index';
        require_once 'app/views/seller/index.php';
    }

    public function orders()
    {
        if ($_SESSION['user_role'] !== 'seller') {
            header('Location: ' . BASE_URL);
            exit;
        }
        $sellerId = $_SESSION['user_id'];
        $orders = $this->orderModel->getOrdersBySellerId($sellerId);
        $action = 'orders';
        require_once 'app/views/seller/orders.php';
    }

    public function pendingProducts()
    {
        if ($_SESSION['user_role'] !== 'seller') {
            header('Location: ' . BASE_URL);
            exit;
        }
        $sellerId = $_SESSION['user_id'];
        // Use common my_products view but filter by status in controller
        $allProducts = $this->productModel->getProductsBySeller($sellerId);
        $products = array_filter($allProducts, function ($p) {
            return $p->status === 'pending';
        });
        $action = 'pending_products';
        require_once 'app/views/product/my_products.php';
    }

    public function soldProducts()
    {
        if ($_SESSION['user_role'] !== 'seller') {
            header('Location: ' . BASE_URL);
            exit;
        }
        $sellerId = $_SESSION['user_id'];

        $query = "SELECT od.*, p.name as product_name, p.image, o.created_at as sale_date, o.id as order_id, u.name as buyer_name
                  FROM order_detail od 
                  JOIN product p ON od.product_id = p.id 
                  JOIN orders o ON od.order_id = o.id 
                  LEFT JOIN user u ON o.user_id = u.id
                  WHERE p.user_id = :seller_id AND o.status = 'completed'
                  ORDER BY o.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':seller_id', $sellerId);
        $stmt->execute();
        $soldItems = $stmt->fetchAll(PDO::FETCH_OBJ);

        $action = 'sold_products';
        require_once 'app/views/seller/sold_products.php';
    }

    public function categories()
    {
        if ($_SESSION['user_role'] !== 'seller') {
            header('Location: ' . BASE_URL);
            exit;
        }
        $categories = $this->categoryModel->getCategories();
        $action = 'categories';
        require_once 'app/views/seller/categories.php';
    }

    public function orderDetail($id)
    {
        if ($_SESSION['user_role'] !== 'seller') {
            header('Location: ' . BASE_URL);
            exit;
        }
        $sellerId = $_SESSION['user_id'];

        // Security check: Does this order contain at least one product from this seller?
        $query = "SELECT COUNT(*) FROM order_detail od 
                  JOIN product p ON od.product_id = p.id 
                  WHERE od.order_id = :order_id AND p.user_id = :seller_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':order_id', $id);
        $stmt->bindParam(':seller_id', $sellerId);
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            die('Access Denied: You do not have permission to view this order.');
        }

        // Fetch order basic info
        $query = "SELECT o.*, u.name as user_name, u.email, 
                  COALESCE(o.recipient_name, u.name) as display_name, 
                  COALESCE(o.recipient_phone, u.phone) as display_phone, 
                  COALESCE(o.recipient_address, u.address) as display_address 
                  FROM orders o 
                  LEFT JOIN user u ON o.user_id = u.id 
                  WHERE o.id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $order = $stmt->fetch(PDO::FETCH_OBJ);

        // Fetch order items (only show items from this seller for privacy, or all? Usually all)
        // I'll show all for now to maintain order integrity
        $query = "SELECT od.*, p.name as product_name, p.image 
                  FROM order_detail od 
                  JOIN product p ON od.product_id = p.id 
                  WHERE od.order_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_OBJ);

        $action = 'orders';
        require_once 'app/views/seller/order_detail.php';
    }

    public function updateOrderStatus($id, $status)
    {
        if ($_SESSION['user_role'] !== 'seller') {
            header('Location: ' . BASE_URL);
            exit;
        }
        $sellerId = $_SESSION['user_id'];

        // Security check: Does this order contain at least one product from this seller?
        $query = "SELECT COUNT(*) FROM order_detail od 
                  JOIN product p ON od.product_id = p.id 
                  WHERE od.order_id = :order_id AND p.user_id = :seller_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':order_id', $id);
        $stmt->bindParam(':seller_id', $sellerId);
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            die('Access Denied: You do not have permission to update this order.');
        }

        if ($this->orderModel->updateStatus($id, $status)) {
            // Update 'sold' count in product table if status is completed
            if ($status === 'completed') {
                $stmtItems = $this->db->prepare("SELECT product_id, quantity FROM order_detail WHERE order_id = :id");
                $stmtItems->bindParam(':id', $id);
                $stmtItems->execute();
                $items = $stmtItems->fetchAll(PDO::FETCH_OBJ);

                foreach ($items as $item) {
                    $this->db->prepare("UPDATE product SET sold = sold + :qty WHERE id = :pid")
                        ->execute([':qty' => $item->quantity, ':pid' => $item->product_id]);
                }

                // Process commission and wallets
                $this->orderModel->processOrderCommission($id);
            }

            $_SESSION['success_message'] = "Cập nhật trạng thái đơn hàng thành công.";
        } else {
            $_SESSION['error_message'] = "Có lỗi xảy ra khi cập nhật.";
        }

        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? (BASE_URL . 'index.php?url=Seller/orders'));
        exit;
    }

    public function settings()
    {
        if ($_SESSION['user_role'] !== 'seller') {
            header('Location: ' . BASE_URL . 'index.php?url=Dashboard');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $shop = $this->shopModel->getBySellerId($userId);

        if (!$shop) {
            // Auto-create shop if not exists (for legacy sellers)
            $this->shopModel->create([
                'seller_id' => $userId,
                'name' => $_SESSION['user_name'] . ' Shop',
                'description' => 'Cửa hàng của ' . $_SESSION['user_name']
            ]);
            $shop = $this->shopModel->getBySellerId($userId);
        }

        // Check for pending updates
        $pendingUpdate = $this->shopUpdateModel->getPendingByShopId($shop->id);

        $action = 'settings';
        require_once 'app/views/dashboard/header.php';
        require_once 'app/views/seller/settings.php';
        require_once 'app/views/dashboard/footer.php';
    }

    public function submitShopUpdate()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['user_role'] === 'seller') {
            $userId = $_SESSION['user_id'];
            $shop = $this->shopModel->getBySellerId($userId);
            if (!$shop) {
                $_SESSION['error_message'] = "Không tìm thấy shop.";
                header('Location: ' . BASE_URL . 'index.php?url=Seller/settings');
                exit;
            }

            // check if there's already a pending request
            $pending = $this->shopUpdateModel->getPendingByShopId($shop->id);
            if ($pending) {
                // If there's already a pending request, we will replace it (delete old, create new)
                $this->shopUpdateModel->delete($pending->id);
            }

            $newName = $_POST['name'] ?? $shop->name;
            $newDesc = $_POST['description'] ?? $shop->description;

            $newLogo = $shop->logo;
            $newBanner = $shop->banner;

            $targetDir = "public/uploads/shops/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
                $fileName = time() . '_logo_' . $_FILES['logo']['name'];
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetDir . $fileName)) {
                    $newLogo = $targetDir . $fileName;
                }
            }

            if (isset($_FILES['banner']) && $_FILES['banner']['error'] === 0) {
                $fileName = time() . '_banner_' . $_FILES['banner']['name'];
                if (move_uploaded_file($_FILES['banner']['tmp_name'], $targetDir . $fileName)) {
                    $newBanner = $targetDir . $fileName;
                }
            }

            $data = [
                'shop_id' => $shop->id,
                'new_name' => $newName,
                'new_description' => $newDesc,
                'new_logo' => $newLogo,
                'new_banner' => $newBanner
            ];

            if ($this->shopUpdateModel->create($data)) {
                // Notify Admins
                $query = "SELECT id FROM user WHERE role = 'admin'";
                $stmt = $this->db->prepare($query);
                $stmt->execute();
                $admins = $stmt->fetchAll(PDO::FETCH_OBJ);
                foreach ($admins as $admin) {
                    $this->notificationModel->create(
                        $admin->id,
                        'Yêu cầu cập nhật thông tin Shop',
                        'Cửa hàng ' . $shop->name . ' yêu cầu cập nhật thông tin.',
                        'shop_update',
                        'index.php?url=Dashboard/shopUpdates'
                    );
                }

                $_SESSION['success_message'] = "Yêu cầu cập nhật đã được gửi và đang chờ Admin duyệt.";
            } else {
                $_SESSION['error_message'] = "Có lỗi xảy ra khi gửi yêu cầu.";
            }
            header('Location: ' . BASE_URL . 'index.php?url=Seller/settings');
            exit;
        }
    }

    public function wallet()
    {
        if ($_SESSION['user_role'] !== 'seller') {
            header('Location: ' . BASE_URL);
            exit;
        }

        $sellerId = $_SESSION['user_id'];

        // Get or create wallet
        $stmtWallet = $this->db->prepare("SELECT * FROM seller_wallets WHERE seller_id = :seller_id");
        $stmtWallet->bindParam(':seller_id', $sellerId);
        $stmtWallet->execute();
        $wallet = $stmtWallet->fetch(PDO::FETCH_OBJ);

        if (!$wallet) {
            $stmtCreate = $this->db->prepare("INSERT INTO seller_wallets (seller_id, balance, total_earned) VALUES (:seller_id, 0, 0)");
            $stmtCreate->bindParam(':seller_id', $sellerId);
            $stmtCreate->execute();

            $stmtWallet->execute();
            $wallet = $stmtWallet->fetch(PDO::FETCH_OBJ);
        }

        // Get transaction history
        $stmtTxns = $this->db->prepare("SELECT * FROM wallet_transactions WHERE seller_id = :seller_id ORDER BY created_at DESC");
        $stmtTxns->bindParam(':seller_id', $sellerId);
        $stmtTxns->execute();
        $transactions = $stmtTxns->fetchAll(PDO::FETCH_OBJ);

        // Get withdrawal requests
        $stmtWdrs = $this->db->prepare("SELECT * FROM withdrawal_requests WHERE seller_id = :seller_id ORDER BY created_at DESC");
        $stmtWdrs->bindParam(':seller_id', $sellerId);
        $stmtWdrs->execute();
        $withdrawals = $stmtWdrs->fetchAll(PDO::FETCH_OBJ);

        // Get total pending/processing withdrawal amount
        $stmtProcessing = $this->db->prepare("SELECT SUM(amount) FROM withdrawal_requests WHERE seller_id = :seller_id AND status IN ('pending', 'processing')");
        $stmtProcessing->bindParam(':seller_id', $sellerId);
        $stmtProcessing->execute();
        $amountProcessing = floatval($stmtProcessing->fetchColumn() ?: 0);

        $action = 'seller_wallet';
        require_once 'app/views/dashboard/header.php';
        require_once 'app/views/seller/wallet.php';
        require_once 'app/views/dashboard/footer.php';
    }

    public function submitWithdrawal()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['user_role'] === 'seller') {
            $sellerId = $_SESSION['user_id'];
            $amount = floatval($_POST['amount'] ?? 0);
            $bankName = trim($_POST['bank_name'] ?? '');
            $bankAccount = trim($_POST['bank_account'] ?? '');
            $bankOwner = trim($_POST['bank_owner'] ?? '');

            // Fetch wallet balance
            $stmtWallet = $this->db->prepare("SELECT * FROM seller_wallets WHERE seller_id = :seller_id");
            $stmtWallet->bindParam(':seller_id', $sellerId);
            $stmtWallet->execute();
            $wallet = $stmtWallet->fetch(PDO::FETCH_OBJ);

            if (!$wallet || $amount <= 0 || $amount > $wallet->balance) {
                $_SESSION['error_message'] = "Số dư không đủ hoặc số tiền rút không hợp lệ.";
                header('Location: ' . BASE_URL . 'index.php?url=Seller/wallet');
                exit;
            }

            if (empty($bankName) || empty($bankAccount) || empty($bankOwner)) {
                $_SESSION['error_message'] = "Vui lòng nhập đầy đủ thông tin tài khoản ngân hàng.";
                header('Location: ' . BASE_URL . 'index.php?url=Seller/wallet');
                exit;
            }

            try {
                $this->db->beginTransaction();

                // Create request code
                $requestCode = 'WDR-' . date('Ymd') . '-' . sprintf("%05d", rand(1, 99999)) . '-' . $sellerId;

                // 1. Insert into withdrawal_requests
                $stmtInsert = $this->db->prepare("INSERT INTO withdrawal_requests 
                    (request_code, seller_id, wallet_id, amount, bank_name, bank_account, bank_owner, status) 
                    VALUES 
                    (:code, :seller_id, :wallet_id, :amount, :bank_name, :bank_account, :bank_owner, 'pending')");
                $stmtInsert->execute([
                    ':code' => $requestCode,
                    ':seller_id' => $sellerId,
                    ':wallet_id' => $wallet->id,
                    ':amount' => $amount,
                    ':bank_name' => $bankName,
                    ':bank_account' => $bankAccount,
                    ':bank_owner' => $bankOwner
                ]);
                $requestId = $this->db->lastInsertId();

                // 2. Deduct from wallet balance
                $stmtDeduct = $this->db->prepare("UPDATE seller_wallets SET balance = balance - :amount WHERE id = :id");
                $stmtDeduct->execute([
                    ':amount' => $amount,
                    ':id' => $wallet->id
                ]);

                // 3. Record transaction in wallet_transactions as 'withdrawal' with status 'pending'
                $txnCode = 'TXN-' . date('Ymd') . '-' . sprintf("%05d", rand(1, 99999)) . '-W' . $requestId;
                $stmtTxn = $this->db->prepare("INSERT INTO wallet_transactions 
                    (transaction_code, wallet_id, seller_id, type, amount, balance_before, balance_after, note, status) 
                    VALUES 
                    (:code, :wallet_id, :seller_id, 'withdrawal', :amount, :before, :after, :note, 'pending')");
                $stmtTxn->execute([
                    ':code' => $txnCode,
                    ':wallet_id' => $wallet->id,
                    ':seller_id' => $sellerId,
                    ':amount' => -$amount,
                    ':before' => $wallet->balance,
                    ':after' => $wallet->balance - $amount,
                    ':note' => 'Yêu cầu rút tiền #' . $requestCode,
                    ':status' => 'pending'
                ]);

                // 4. Notify admin
                require_once 'app/models/NotificationModel.php';
                $notif = new NotificationModel($this->db);
                $queryAdmin = "SELECT id FROM user WHERE role = 'admin'";
                $stmtAdmin = $this->db->prepare($queryAdmin);
                $stmtAdmin->execute();
                $admins = $stmtAdmin->fetchAll(PDO::FETCH_OBJ);
                foreach ($admins as $admin) {
                    $notif->create(
                        $admin->id,
                        'Yêu cầu rút tiền mới',
                        'Seller ' . $_SESSION['user_name'] . ' vừa yêu cầu rút ' . number_format($amount, 0, ',', '.') . ' ₫ về ngân hàng.',
                        'withdrawal_request',
                        'index.php?url=Dashboard/manageWithdrawals'
                    );
                }

                $this->db->commit();
                $_SESSION['success_message'] = "Yêu cầu rút tiền đã được gửi thành công và đang chờ duyệt.";
            } catch (Exception $e) {
                $this->db->rollBack();
                error_log("Error in submitWithdrawal: " . $e->getMessage());
                $_SESSION['error_message'] = "Có lỗi xảy ra trong quá trình xử lý: " . $e->getMessage();
            }
            header('Location: ' . BASE_URL . 'index.php?url=Seller/wallet');
            exit;
        }
    }
}
