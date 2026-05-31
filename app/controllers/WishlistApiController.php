<?php
require_once 'app/config/database.php';
require_once 'app/models/WishlistModel.php';
require_once 'app/models/ProductModel.php';

class WishlistApiController
{
    private $db;
    private $wishlistModel;
    private $productModel;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->wishlistModel = new WishlistModel($this->db);
        $this->productModel = new ProductModel($this->db);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function getJsonInput()
    {
        $rawInput = file_get_contents('php://input');
        $jsonData = json_decode($rawInput, true);
        return is_array($jsonData) ? $jsonData : $_POST;
    }

    private function getUserId()
    {
        return $_SESSION['user_id'] ?? $_SERVER['HTTP_X_USER_ID'] ?? $_GET['user_id'] ?? null;
    }

    private function checkAuth()
    {
        if ($this->getUserId() === null) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized: Vui lòng đăng nhập hoặc truyền X-User-Id header để sử dụng chức năng yêu thích.']);
            exit;
        }
    }

    // GET /api/wishlist
    public function index()
    {
        $this->checkAuth();
        $userId = $this->getUserId();

        try {
            $wishlist = $this->wishlistModel->getUserWishlist($userId);
            
            // Format product image URLs
            foreach ($wishlist as $item) {
                if (!empty($item->image)) {
                    $itemImg = $item->image;
                    $finalImg = (strpos($itemImg, 'public/') === false) ?
                        ((strpos($itemImg, 'uploads/') !== false) ? 'public/' . $itemImg : 'public/uploads/' . $itemImg) :
                        $itemImg;
                    $item->image_url = BASE_URL . $finalImg;
                } else {
                    $item->image_url = BASE_URL . 'public/images/placeholder.png';
                }
            }

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'count' => count($wishlist),
                'data' => $wishlist
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
        }
    }

    // POST /api/wishlist/add
    public function add()
    {
        $this->checkAuth();
        $userId = $this->getUserId();
        $input = $this->getJsonInput();

        $productId = isset($input['product_id']) ? (int)$input['product_id'] : 0;

        if ($productId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ: product_id là bắt buộc.']);
            return;
        }

        try {
            // Check if product exists
            $product = $this->productModel->getProductById($productId);
            if (!$product) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm.']);
                return;
            }

            $result = $this->wishlistModel->addWishlist($userId, $productId);
            if ($result) {
                // Sync likes count on product table
                $query = "UPDATE product SET likes = (SELECT COUNT(*) FROM product_likes WHERE product_id = :product_id) WHERE id = :product_id";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':product_id', $productId);
                $stmt->execute();

                // Get new likes count
                $stmtLikes = $this->db->prepare("SELECT likes FROM product WHERE id = :id");
                $stmtLikes->bindParam(':id', $productId);
                $stmtLikes->execute();
                $likes = $stmtLikes->fetch(PDO::FETCH_OBJ)->likes ?? 0;

                // Sync session cache
                $_SESSION['wishlist_items'] = $this->wishlistModel->getUserWishlistIds($userId);

                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Đã thêm sản phẩm vào danh sách yêu thích.',
                    'likes' => $likes
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Không thể lưu sản phẩm yêu thích.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
        }
    }

    // DELETE /api/wishlist/remove/{id}
    public function remove($id)
    {
        $this->checkAuth();
        $userId = $this->getUserId();
        $productId = (int)$id;

        if ($productId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ: ID sản phẩm là bắt buộc.']);
            return;
        }

        try {
            if (!$this->wishlistModel->isFavorited($userId, $productId)) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Sản phẩm này chưa có trong danh sách yêu thích.']);
                return;
            }

            $result = $this->wishlistModel->removeWishlist($userId, $productId);
            if ($result) {
                // Sync likes count on product table
                $query = "UPDATE product SET likes = (SELECT COUNT(*) FROM product_likes WHERE product_id = :product_id) WHERE id = :product_id";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':product_id', $productId);
                $stmt->execute();

                // Sync session cache
                $_SESSION['wishlist_items'] = $this->wishlistModel->getUserWishlistIds($userId);

                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Đã xóa sản phẩm khỏi danh sách yêu thích.'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Không thể xóa sản phẩm yêu thích.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
        }
    }
}
