<?php
// Bắt đầu session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cấu hình Base URL
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
$base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);
define('BASE_URL', $base_url);

// Include các helper và model cần thiết
require_once 'app/config/database.php';
require_once 'app/helpers/SessionHelper.php';
require_once 'app/models/ProductModel.php';

// Lấy thông tin URL để routing
$url = $_GET['url'] ?? '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// REST API Routing Block
if (isset($url[0]) && strtolower($url[0]) === 'api') {
    header('Content-Type: application/json; charset=utf-8');
    
    $resource = isset($url[1]) && $url[1] != '' ? strtolower($url[1]) : '';
    $apiControllers = [
        'products'   => 'ProductApiController',
        'categories' => 'CategoryApiController',
        'cart'       => 'CartApiController',
        'wishlist'   => 'WishlistApiController',
        'address'    => 'AddressApiController'
    ];

    if (!array_key_exists($resource, $apiControllers)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'API resource not found']);
        exit;
    }

    $controllerName = $apiControllers[$resource];
    if (!file_exists('app/controllers/' . $controllerName . '.php')) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => "API Controller file app/controllers/{$controllerName}.php not found"]);
        exit;
    }

    require_once 'app/controllers/' . $controllerName . '.php';
    $controller = new $controllerName();

    $method = $_SERVER['REQUEST_METHOD'];
    $subResource = isset($url[2]) && $url[2] != '' ? strtolower($url[2]) : '';
    $id = isset($url[2]) && $url[2] != '' ? (int)$url[2] : null;

    // REST API Sub-routing
    if ($resource === 'products') {
        if ($method === 'GET' && $id === null) {
            $controller->index();
        } elseif ($method === 'GET' && $id !== null) {
            $controller->show($id);
        } elseif ($method === 'POST') {
            $controller->store();
        } elseif ($method === 'PUT' && $id !== null) {
            $controller->update($id);
        } elseif ($method === 'DELETE' && $id !== null) {
            $controller->delete($id);
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
        }
    } 
    elseif ($resource === 'categories') {
        if ($method === 'GET') {
            $controller->index();
        } elseif ($method === 'POST') {
            $controller->store();
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
        }
    } 
    elseif ($resource === 'cart') {
        if ($method === 'GET' && empty($subResource)) {
            $controller->index();
        } elseif ($method === 'POST' && $subResource === 'add') {
            $controller->add();
        } elseif ($method === 'PUT' && $subResource === 'update') {
            $controller->update();
        } elseif ($method === 'DELETE' && $subResource === 'remove') {
            $itemId = isset($url[3]) ? (int)$url[3] : 0;
            $controller->remove($itemId);
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
        }
    } 
    elseif ($resource === 'wishlist') {
        if ($method === 'GET' && empty($subResource)) {
            $controller->index();
        } elseif ($method === 'POST' && $subResource === 'add') {
            $controller->add();
        } elseif ($method === 'DELETE' && $subResource === 'remove') {
            $itemId = isset($url[3]) ? (int)$url[3] : 0;
            $controller->remove($itemId);
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
        }
    } 
    elseif ($resource === 'address') {
        if ($method === 'GET' && $id === null) {
            $controller->index();
        } elseif ($method === 'POST' && $id === null) {
            $controller->store();
        } elseif ($method === 'PUT' && $id !== null) {
            $controller->update($id);
        } elseif ($method === 'DELETE' && $id !== null) {
            $controller->delete($id);
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
        }
    }
    exit;
}

// Xác định Controller (mặc định là ProductController)
$controllerName = isset($url[0]) && $url[0] != '' ? ucfirst($url[0]) . 'Controller' : 'ProductController';

// Xác định Action (mặc định là index)
$action = isset($url[1]) && $url[1] != '' ? $url[1] : 'index';

// Kiểm tra xem controller có tồn tại không
if (!file_exists('app/controllers/' . $controllerName . '.php')) {
    die('Controller not found: ' . htmlspecialchars($controllerName));
}

require_once 'app/controllers/' . $controllerName . '.php';
$controller = new $controllerName();

// Kiểm tra xem action trong controller có tồn tại không
if (!method_exists($controller, $action)) {
    die('Action not found: ' . htmlspecialchars($action));
}

// Gọi action với các tham số còn lại trong URL
call_user_func_array([$controller, $action], array_slice($url, 2));