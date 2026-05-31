<?php
require_once 'app/config/database.php';
require_once 'app/models/AddressModel.php';

class AddressApiController
{
    private $db;
    private $addressModel;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->addressModel = new AddressModel($this->db);

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
            echo json_encode(['success' => false, 'message' => 'Unauthorized: Vui lòng đăng nhập hoặc truyền X-User-Id header để thực hiện chức năng này.']);
            exit;
        }
    }

    // GET /api/address
    public function index()
    {
        $this->checkAuth();
        $userId = $this->getUserId();

        try {
            $addresses = $this->addressModel->getByUser($userId);
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'count' => count($addresses),
                'data' => $addresses
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
        }
    }

    // POST /api/address
    public function store()
    {
        $this->checkAuth();
        $userId = $this->getUserId();
        $input = $this->getJsonInput();

        $name = $input['name'] ?? '';
        $phone = $input['phone'] ?? '';
        $city = $input['city'] ?? ($input['city_text'] ?? '');
        $district = $input['district'] ?? ($input['district_text'] ?? '');
        $ward = $input['ward'] ?? ($input['ward_text'] ?? '');
        $addressLine = $input['address_line'] ?? '';

        if (empty(trim($name)) || empty(trim($phone)) || empty(trim($addressLine))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ: Tên, Số điện thoại và Địa chỉ chi tiết là bắt buộc.']);
            return;
        }

        try {
            $data = [
                'user_id' => $userId,
                'name' => $name,
                'phone' => $phone,
                'email' => $input['email'] ?? '',
                'city' => $city,
                'district' => $district,
                'ward' => $ward,
                'address_line' => $addressLine,
                'address_type' => $input['address_type'] ?? 'Nhà Riêng',
                'is_default' => isset($input['is_default']) && $input['is_default'] == 1 ? 1 : 0
            ];

            $result = $this->addressModel->add($data);
            if ($result) {
                http_response_code(201);
                echo json_encode([
                    'success' => true,
                    'message' => 'Lưu địa chỉ thành công.'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Không thể thêm địa chỉ.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
        }
    }

    // PUT /api/address/{id}
    public function update($id)
    {
        $this->checkAuth();
        $userId = $this->getUserId();

        $address = $this->addressModel->getById($id);
        if (!$address || $address->user_id != $userId) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy địa chỉ.']);
            return;
        }

        $input = $this->getJsonInput();

        $name = $input['name'] ?? $address->name;
        $phone = $input['phone'] ?? $address->phone;
        $city = $input['city'] ?? ($input['city_text'] ?? $address->city);
        $district = $input['district'] ?? ($input['district_text'] ?? $address->district);
        $ward = $input['ward'] ?? ($input['ward_text'] ?? $address->ward);
        $addressLine = $input['address_line'] ?? $address->address_line;

        if (empty(trim($name)) || empty(trim($phone)) || empty(trim($addressLine))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
            return;
        }

        try {
            $data = [
                'user_id' => $userId,
                'name' => $name,
                'phone' => $phone,
                'email' => $input['email'] ?? $address->email,
                'city' => $city,
                'district' => $district,
                'ward' => $ward,
                'address_line' => $addressLine,
                'address_type' => $input['address_type'] ?? $address->address_type,
                'is_default' => isset($input['is_default']) && $input['is_default'] == 1 ? 1 : 0
            ];

            $result = $this->addressModel->update($id, $data);
            if ($result) {
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Cập nhật địa chỉ thành công.']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Không thể cập nhật địa chỉ.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
        }
    }

    // DELETE /api/address/{id}
    public function delete($id)
    {
        $this->checkAuth();
        $userId = $this->getUserId();

        $address = $this->addressModel->getById($id);
        if (!$address || $address->user_id != $userId) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy địa chỉ để xóa.']);
            return;
        }

        try {
            $result = $this->addressModel->delete($id, $userId);
            if ($result) {
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Xóa địa chỉ thành công.']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Không thể xóa địa chỉ.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
        }
    }
}
