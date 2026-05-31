<?php
require_once 'app/config/database.php';
require_once 'app/models/ProductModel.php';
require_once 'app/models/CategoryModel.php';
require_once 'app/models/VariantModel.php';

class ProductApiController
{
    private $db;
    private $productModel;
    private $variantModel;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->productModel = new ProductModel($this->db);
        $this->variantModel = new VariantModel($this->db);

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

    private function getUserRole()
    {
        return $_SESSION['user_role'] ?? $_SERVER['HTTP_X_USER_ROLE'] ?? $_GET['user_role'] ?? '';
    }

    private function checkAuth()
    {
        if ($this->getUserId() === null) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized: Vui lòng đăng nhập hoặc truyền X-User-Id header.']);
            exit;
        }
    }

    private function checkStaff()
    {
        $this->checkAuth();
        $role = $this->getUserRole();
        if ($role !== 'admin' && $role !== 'seller') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Forbidden: Bạn không có quyền thực hiện chức năng này.']);
            exit;
        }
    }

    // GET /api/products
    public function index()
    {
        $keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
        $minPrice = (isset($_GET['min_price']) && $_GET['min_price'] !== '') ? (int)$_GET['min_price'] : null;
        $maxPrice = (isset($_GET['max_price']) && $_GET['max_price'] !== '') ? (int)$_GET['max_price'] : null;
        $sellerId = isset($_GET['seller_id']) ? (int)$_GET['seller_id'] : null;
        $sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';

        try {
            $products = $this->productModel->searchProductsFiltered($keyword, $minPrice, $maxPrice, $sellerId, $sort);
            
            // Format URL for image
            foreach ($products as $p) {
                if (!empty($p->image)) {
                    $pImg = $p->image;
                    $finalPImg = (strpos($pImg, 'public/') === false) ?
                        ((strpos($pImg, 'uploads/') !== false) ? 'public/' . $pImg : 'public/uploads/' . $pImg) :
                        $pImg;
                    $p->image_url = BASE_URL . $finalPImg;
                } else {
                    $p->image_url = BASE_URL . 'public/images/placeholder.png';
                }
            }

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'count' => count($products),
                'data' => $products
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
        }
    }

    // GET /api/products/{id}
    public function show($id)
    {
        try {
            $product = $this->productModel->getProductById($id);
            if (!$product) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm.']);
                return;
            }

            $variants = $this->variantModel->getVariantsByProductId($id);
            
            // Format image
            if (!empty($product->image)) {
                $pImg = $product->image;
                $finalPImg = (strpos($pImg, 'public/') === false) ?
                    ((strpos($pImg, 'uploads/') !== false) ? 'public/' . $pImg : 'public/uploads/' . $pImg) :
                    $pImg;
                $product->image_url = BASE_URL . $finalPImg;
            } else {
                $product->image_url = BASE_URL . 'public/images/placeholder.png';
            }

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => [
                    'product' => $product,
                    'variants' => $variants
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
        }
    }

    // POST /api/products
    public function store()
    {
        $this->checkStaff();
        $input = $this->getJsonInput();

        $name = $input['name'] ?? '';
        $description = $input['description'] ?? '';
        $price = isset($input['price']) ? (int)$input['price'] : 0;
        $categoryId = isset($input['category_id']) ? (int)$input['category_id'] : null;
        $stock = isset($input['stock']) ? (int)$input['stock'] : 0;
        $discountPercent = isset($input['discount_percent']) ? (int)$input['discount_percent'] : 0;
        $location = $input['location'] ?? 'Tp. Hồ Chí Minh';

        if (empty(trim($name)) || $price <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ: Tên sản phẩm và giá (>0) là bắt buộc.']);
            return;
        }

        try {
            $image = '';
            // Handle uploaded file if present
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image = $this->uploadImage($_FILES['image']);
            } elseif (!empty($input['image_url'])) {
                // Allow reference image name directly in JSON body
                $image = basename($input['image_url']);
            }

            $userId = $this->getUserId();
            $result = $this->productModel->addProduct(
                $name, $description, $price, $categoryId, $image, 
                $stock, 0, 0.0, $discountPercent, $location, $userId
            );

            if (!is_array($result) && $result > 0) {
                // If variants are provided in the payload
                if (isset($input['variants']) && is_array($input['variants'])) {
                    foreach ($input['variants'] as $v) {
                        $vName = $v['name'] ?? '';
                        $vPrice = isset($v['price']) ? (int)$v['price'] : $price;
                        $vStock = isset($v['stock']) ? (int)$v['stock'] : 0;
                        $vImg = $v['image'] ?? '';
                        if (!empty(trim($vName))) {
                            $this->variantModel->addVariant($result, $vName, $vImg, $vPrice, $vStock);
                        }
                    }
                }

                http_response_code(201);
                echo json_encode([
                    'success' => true,
                    'message' => 'Thêm sản phẩm thành công.',
                    'product_id' => $result
                ]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'errors' => is_array($result) ? $result : 'Lỗi khi lưu sản phẩm.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
        }
    }

    // PUT /api/products/{id}
    public function update($id)
    {
        $this->checkStaff();
        
        $product = $this->productModel->getProductById($id);
        if (!$product) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm cần cập nhật.']);
            return;
        }

        // Security check
        $userId = $this->getUserId();
        $userRole = $this->getUserRole();
        if ($userRole !== 'admin' && $product->user_id != $userId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Forbidden: Bạn không sở hữu sản phẩm này.']);
            return;
        }

        $input = $this->getJsonInput();

        $name = $input['name'] ?? $product->name;
        $description = $input['description'] ?? $product->description;
        $price = isset($input['price']) ? (int)$input['price'] : $product->price;
        $categoryId = isset($input['category_id']) ? (int)$input['category_id'] : $product->category_id;
        $stock = isset($input['stock']) ? (int)$input['stock'] : $product->stock;
        $discountPercent = isset($input['discount_percent']) ? (int)$input['discount_percent'] : $product->discount_percent;
        $location = $input['location'] ?? $product->location;
        $status = $input['status'] ?? $product->status;

        if (empty(trim($name)) || $price <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ: Tên sản phẩm và giá (>0) là bắt buộc.']);
            return;
        }

        try {
            $image = $product->image;
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image = $this->uploadImage($_FILES['image']);
            } elseif (isset($input['image_url'])) {
                $image = basename($input['image_url']);
            }

            $edit = $this->productModel->updateProduct(
                $id, $name, $description, $price, $categoryId, $image,
                $stock, $product->sold, $product->rating, $discountPercent, $location, $status
            );

            if ($edit) {
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Cập nhật sản phẩm thành công.']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Không thể cập nhật sản phẩm.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
        }
    }

    // DELETE /api/products/{id}
    public function delete($id)
    {
        $this->checkStaff();

        $product = $this->productModel->getProductById($id);
        if (!$product) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm cần xóa.']);
            return;
        }

        // Security check
        $userId = $this->getUserId();
        $userRole = $this->getUserRole();
        if ($userRole !== 'admin' && $product->user_id != $userId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Forbidden: Bạn không sở hữu sản phẩm này.']);
            return;
        }

        try {
            if ($this->productModel->deleteProduct($id)) {
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Xóa sản phẩm thành công.']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Không thể xóa sản phẩm khỏi cơ sở dữ liệu.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
        }
    }

    private function uploadImage($file)
    {
        $target_dir = "public/uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $newFileName = uniqid('prod_') . '_' . time() . '.' . $imageFileType;
        $target_file = $target_dir . $newFileName;

        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            return $newFileName;
        }
        throw new Exception("Lỗi upload file ảnh.");
    }
}
