<?php
require_once 'app/config/database.php';
require_once 'app/models/CategoryModel.php';

class CategoryApiController
{
    private $db;
    private $categoryModel;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->categoryModel = new CategoryModel($this->db);

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

    private function checkAdmin()
    {
        if ($this->getUserId() === null) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized: Vui lòng đăng nhập hoặc truyền X-User-Id.']);
            exit;
        }
        $role = $this->getUserRole();
        if ($role !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Forbidden: Chỉ quản trị viên mới thực hiện được chức năng này.']);
            exit;
        }
    }

    // GET /api/categories
    public function index()
    {
        try {
            $categories = $this->categoryModel->getCategories();
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'count' => count($categories),
                'data' => $categories
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
        }
    }

    // POST /api/categories
    public function store()
    {
        $this->checkAdmin();
        $input = $this->getJsonInput();

        $name = $input['name'] ?? '';
        $description = $input['description'] ?? '';

        if (empty(trim($name))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ: Tên danh mục là bắt buộc.']);
            return;
        }

        try {
            $result = $this->categoryModel->addCategory($name, $description);
            if ($result) {
                http_response_code(201);
                echo json_encode([
                    'success' => true,
                    'message' => 'Thêm danh mục thành công.'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Không thể thêm danh mục.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
        }
    }
}
