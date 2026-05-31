<?php
require_once 'app/config/database.php';
require_once 'app/models/CartModel.php';
require_once 'app/models/ProductModel.php';

class CartApiController
{
    private $db;
    private $cartModel;
    private $productModel;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->cartModel = new CartModel($this->db);
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
            echo json_encode(['success' => false, 'message' => 'Unauthorized: Vui lòng đăng nhập hoặc truyền X-User-Id header để sử dụng giỏ hàng.']);
            exit;
        }
    }

    // GET /api/cart
    public function index()
    {
        $this->checkAuth();
        $userId = $this->getUserId();

        try {
            $cartItems = $this->cartModel->getItems($userId);
            
            // Format response and resolve image URLs
            $formattedItems = [];
            $totalAmount = 0;
            $totalQuantity = 0;

            foreach ($cartItems as $key => $item) {
                if (!empty($item['image'])) {
                    $itemImg = $item['image'];
                    $finalImg = (strpos($itemImg, 'public/') === false) ?
                        ((strpos($itemImg, 'uploads/') !== false) ? 'public/' . $itemImg : 'public/uploads/' . $itemImg) :
                        $itemImg;
                    $item['image_url'] = BASE_URL . $finalImg;
                } else {
                    $item['image_url'] = BASE_URL . 'public/images/placeholder.png';
                }
                
                $totalAmount += $item['price'] * $item['quantity'];
                $totalQuantity += $item['quantity'];
                $formattedItems[] = $item;
            }

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => [
                    'items' => $formattedItems,
                    'total_amount' => $totalAmount,
                    'total_quantity' => $totalQuantity
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
        }
    }

    // POST /api/cart/add
    public function add()
    {
        $this->checkAuth();
        $userId = $this->getUserId();
        $input = $this->getJsonInput();

        $productId = isset($input['product_id']) ? (int)$input['product_id'] : 0;
        $variantId = isset($input['variant_id']) ? (int)$input['variant_id'] : 0;
        $quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;

        if ($productId <= 0 || $quantity <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ: product_id và quantity (>0) là bắt buộc.']);
            return;
        }

        try {
            $product = $this->productModel->getProductById($productId);
            if (!$product) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm.']);
                return;
            }

            // Security: check if buyer is the owner of the product
            if (isset($product->user_id) && $userId == $product->user_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Không thể mua sản phẩm của chính bạn.']);
                return;
            }

            // Stock validation
            $stock = $product->stock;
            if ($variantId > 0) {
                require_once 'app/models/VariantModel.php';
                $vModel = new VariantModel($this->db);
                $variant = $vModel->getVariantById($variantId);
                if ($variant) {
                    $stock = $variant->stock;
                }
            }

            if ($quantity > $stock) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Số lượng vượt quá tồn kho trong hệ thống (' . $stock . ' còn lại).']);
                return;
            }

            $result = $this->cartModel->addItem($userId, $productId, $variantId, $quantity);
            if ($result) {
                // Return updated cart counts
                $cartItems = $this->cartModel->getItems($userId);
                $cartCount = array_sum(array_column($cartItems, 'quantity'));

                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Đã thêm sản phẩm vào giỏ hàng thành công.',
                    'cartCount' => $cartCount, // Legacy compat
                    'cart_count' => $cartCount
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Không thể thêm sản phẩm vào giỏ hàng.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
        }
    }

    // PUT /api/cart/update
    public function update()
    {
        $this->checkAuth();
        $userId = $this->getUserId();
        $input = $this->getJsonInput();

        $productId = 0;
        $variantId = 0;
        $quantity = isset($input['quantity']) ? (int)$input['quantity'] : 0;

        // Check if Cart Key was passed (e.g. "id" = "1_0" or "1")
        if (isset($input['id'])) {
            $parts = explode('_', $input['id']);
            $productId = (int)$parts[0];
            $variantId = isset($parts[1]) ? (int)$parts[1] : 0;
        } else {
            $productId = isset($input['product_id']) ? (int)$input['product_id'] : 0;
            $variantId = isset($input['variant_id']) ? (int)$input['variant_id'] : 0;
        }

        if ($productId <= 0 || $quantity < 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
            return;
        }

        try {
            $product = $this->productModel->getProductById($productId);
            if (!$product) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm.']);
                return;
            }

            // Check stock if quantity is positive
            if ($quantity > 0) {
                $stock = $product->stock;
                if ($variantId > 0) {
                    require_once 'app/models/VariantModel.php';
                    $vModel = new VariantModel($this->db);
                    $variant = $vModel->getVariantById($variantId);
                    if ($variant) {
                        $stock = $variant->stock;
                    }
                }

                if ($quantity > $stock) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Số lượng vượt quá tồn kho còn lại (' . $stock . ').']);
                    return;
                }
            }

            $result = $this->cartModel->updateQuantity($userId, $productId, $quantity, $variantId);
            if ($result) {
                $cartItems = $this->cartModel->getItems($userId);
                $totalAmount = 0;
                $cartCount = 0;
                $itemSubtotal = 0;
                $cartKey = $productId . ($variantId > 0 ? '_' . $variantId : '');

                foreach ($cartItems as $key => $item) {
                    $totalAmount += $item['price'] * $item['quantity'];
                    $cartCount += $item['quantity'];
                    if ($key === $cartKey) {
                        $itemSubtotal = $item['price'] * $item['quantity'];
                    }
                }

                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Cập nhật số lượng thành công.',
                    'itemSubtotal' => number_format($itemSubtotal, 0, ',', '.') . ' ₫', // Legacy compat
                    'totalAmount' => number_format($totalAmount, 0, ',', '.') . ' ₫', // Legacy compat
                    'cartCount' => $cartCount, // Legacy compat
                    'data' => [
                        'item_subtotal' => $itemSubtotal,
                        'total_amount' => $totalAmount,
                        'cart_count' => $cartCount
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Không thể cập nhật số lượng giỏ hàng.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
        }
    }

    // DELETE /api/cart/remove/{id}
    public function remove($id)
    {
        $this->checkAuth();
        $userId = $this->getUserId();

        if (empty($id)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ: Cart Key là bắt buộc.']);
            return;
        }

        try {
            $parts = explode('_', $id);
            $productId = (int)$parts[0];
            $variantId = isset($parts[1]) ? (int)$parts[1] : 0;

            $result = $this->cartModel->removeItem($userId, $productId, $variantId);
            if ($result) {
                $cartItems = $this->cartModel->getItems($userId);
                $totalAmount = 0;
                $cartCount = 0;
                foreach ($cartItems as $item) {
                    $totalAmount += $item['price'] * $item['quantity'];
                    $cartCount += $item['quantity'];
                }

                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Đã xóa sản phẩm khỏi giỏ hàng.',
                    'totalAmount' => number_format($totalAmount, 0, ',', '.') . ' ₫', // Legacy compat
                    'cartCount' => $cartCount, // Legacy compat
                    'data' => [
                        'total_amount' => $totalAmount,
                        'cart_count' => $cartCount
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Không thể xóa sản phẩm khỏi giỏ hàng.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
        }
    }
}
