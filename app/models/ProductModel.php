<?php
class ProductModel
{
    private $conn;
    private $table_name = "product";
    public function __construct($db)
    {
        $this->conn = $db;
        if (!$this->conn) {
            return;
        }
        // Auto-migrate: check if 'section' column exists in banners table
        try {
            $this->conn->exec("ALTER TABLE banners ADD COLUMN section VARCHAR(50) DEFAULT 'hero'");
        } catch (PDOException $e) { /* ignore */
        }

        // Auto-migrate: featured product fields
        try {
            $this->conn->exec("ALTER TABLE " . $this->table_name . " ADD COLUMN is_featured TINYINT(1) DEFAULT 0");
        } catch (PDOException $e) { /* ignore */
        }

        try {
            $this->conn->exec("ALTER TABLE " . $this->table_name . " ADD COLUMN display_order INT DEFAULT 0");
        } catch (PDOException $e) { /* ignore */
        }

        try {
            $this->conn->exec("ALTER TABLE " . $this->table_name . " ADD COLUMN discount_percent INT DEFAULT 0");
        } catch (PDOException $e) { /* ignore */
        }

        try {
            $this->conn->exec("ALTER TABLE " . $this->table_name . " ADD COLUMN location VARCHAR(255) DEFAULT 'Tp. Hồ Chí Minh'");
        } catch (PDOException $e) { /* ignore */
        }

        try {
            $this->conn->exec("ALTER TABLE " . $this->table_name . " ADD COLUMN status VARCHAR(20) DEFAULT 'approved'");
        } catch (PDOException $e) { /* ignore */
        }

        try {
            $this->conn->exec("ALTER TABLE " . $this->table_name . " ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        } catch (PDOException $e) { /* ignore */
        }

        try {
            $this->conn->exec("ALTER TABLE " . $this->table_name . " ADD COLUMN rejection_reason TEXT");
        } catch (PDOException $e) { /* ignore */
        }

        try {
            $this->conn->exec("ALTER TABLE " . $this->table_name . " ADD COLUMN likes INT DEFAULT 0");
        } catch (PDOException $e) { /* ignore */
        }

        try {
            $this->conn->exec("CREATE TABLE IF NOT EXISTS product_likes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                product_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_user_product (user_id, product_id)
            )");
        } catch (PDOException $e) { /* ignore */
        }
    }

    public function getPendingProducts()
    {
        $query = "SELECT p.*, c.name as category_name, u.name as seller_name
        FROM " . $this->table_name . " p
        LEFT JOIN category c ON p.category_id = c.id
        LEFT JOIN user u ON p.user_id = u.id
        WHERE p.status = 'pending'
        ORDER BY p.id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getProductsBySeller($seller_id)
    {
        $query = "SELECT p.*, c.name as category_name
        FROM " . $this->table_name . " p
        LEFT JOIN category c ON p.category_id = c.id
        WHERE p.user_id = :seller_id
        ORDER BY p.id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':seller_id', $seller_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function updateProductStatus($id, $status, $reason = null)
    {
        $query = "UPDATE " . $this->table_name . " SET status = :status, rejection_reason = :reason WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':reason', $reason);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    public function getProducts($minPrice = null, $maxPrice = null, $sort = 'id')
    {
        $query = "SELECT p.*, c.name as category_name, u.name as seller_name
        FROM " . $this->table_name . " p
        LEFT JOIN category c ON p.category_id = c.id
        LEFT JOIN user u ON p.user_id = u.id
        WHERE p.status = 'approved'";

        if ($minPrice !== null) {
            $query .= " AND p.price >= :minPrice";
        }
        if ($maxPrice !== null) {
            $query .= " AND p.price <= :maxPrice";
        }

        if ($sort === 'sold' || $sort === 'sold_only') {
            if ($sort === 'sold_only') {
                $query .= " AND p.sold > 0";
            }
            $query .= " ORDER BY p.sold DESC, p.id DESC";
        } else {
            $query .= " ORDER BY p.id DESC";
        }

        $stmt = $this->conn->prepare($query);
        if ($minPrice !== null) $stmt->bindParam(':minPrice', $minPrice);
        if ($maxPrice !== null) $stmt->bindParam(':maxPrice', $maxPrice);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Lấy các sản phẩm được tích chọn hiển thị trang chủ
    public function getFeaturedProducts()
    {
        $query = "SELECT p.id, p.name, p.description, p.price, p.discount_percent, p.image, p.stock, p.sold, p.rating, p.rating_count, p.display_order, p.location, c.name as category_name
        FROM " . $this->table_name . " p
        LEFT JOIN category c ON p.category_id = c.id
        WHERE p.is_featured = 1 AND p.status = 'approved'
        ORDER BY p.display_order = 0, p.display_order ASC, p.id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Cập nhật danh sách hiển thị
    public function updateFeaturedProducts($featured_ids, $orders)
    {
        try {
            $this->conn->beginTransaction();
            // Xóa tất cả trạng thái hiển thị
            $this->conn->exec("UPDATE " . $this->table_name . " SET is_featured = 0, display_order = 0");

            // Cập nhật lại
            if (!empty($featured_ids) && is_array($featured_ids)) {
                $query = "UPDATE " . $this->table_name . " SET is_featured = 1, display_order = :display_order WHERE id = :id";
                $stmt = $this->conn->prepare($query);

                foreach ($featured_ids as $id) {
                    $order = isset($orders[$id]) ? (int)$orders[$id] : 0;
                    $stmt->bindValue(':display_order', $order, PDO::PARAM_INT);
                    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                    $stmt->execute();
                }
            }
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function searchProducts($keyword)
    {
        $query = "SELECT p.*, c.name as category_name, u.name as seller_name, s.name as shop_name
        FROM " . $this->table_name . " p
        LEFT JOIN category c ON p.category_id = c.id
        LEFT JOIN user u ON p.user_id = u.id
        LEFT JOIN shops s ON p.user_id = s.seller_id
        WHERE p.name LIKE :keyword 
           OR p.description LIKE :keyword 
           OR c.name LIKE :keyword
           OR s.name LIKE :keyword";
        $stmt = $this->conn->prepare($query);
        $searchKeyword = "%{$keyword}%";
        $stmt->bindParam(':keyword', $searchKeyword);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function searchProductsFiltered($keyword, $minPrice = null, $maxPrice = null, $user_id = null, $sort = 'newest')
    {
        $query = "SELECT p.*, c.name as category_name, s.name as shop_name
        FROM " . $this->table_name . " p
        LEFT JOIN category c ON p.category_id = c.id
        LEFT JOIN shops s ON p.user_id = s.seller_id
        WHERE (p.name LIKE :keyword1 
           OR p.description LIKE :keyword2 
           OR c.name LIKE :keyword3
           OR s.name LIKE :keyword4)
           AND p.status = 'approved'";

        if ($minPrice !== null) {
            $query .= " AND p.price >= :minPrice";
        }
        if ($maxPrice !== null) {
            $query .= " AND p.price <= :maxPrice";
        }

        if ($user_id !== null) {
            $query .= " AND p.user_id = :user_id";
        }

        switch ($sort) {
            case 'price_asc':
                $query .= " ORDER BY p.price ASC, p.id DESC";
                break;
            case 'price_desc':
                $query .= " ORDER BY p.price DESC, p.id DESC";
                break;
            case 'sold':
                $query .= " ORDER BY p.sold DESC, p.id DESC";
                break;
            case 'newest':
            default:
                $query .= " ORDER BY p.id DESC";
                break;
        }

        $stmt = $this->conn->prepare($query);
        $searchKeyword = "%{$keyword}%";
        $stmt->bindParam(':keyword1', $searchKeyword);
        $stmt->bindParam(':keyword2', $searchKeyword);
        $stmt->bindParam(':keyword3', $searchKeyword);
        $stmt->bindParam(':keyword4', $searchKeyword);

        if ($minPrice !== null) {
            $stmt->bindParam(':minPrice', $minPrice);
        }
        if ($maxPrice !== null) {
            $stmt->bindParam(':maxPrice', $maxPrice);
        }
        if ($user_id !== null) {
            $stmt->bindParam(':user_id', $user_id);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getMaxPrice()
    {
        $query = "SELECT MAX(price) as max_price FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['max_price'] ?? 1000000;
    }
    public function getProductsByCategory($category_id, $minPrice = null, $maxPrice = null)
    {
        $query = "SELECT p.id, p.name, p.description, p.price, p.discount_percent, p.image, p.stock, p.sold, p.rating, p.rating_count, p.location, c.name as category_name
        FROM " . $this->table_name . " p
        LEFT JOIN category c ON p.category_id = c.id
        WHERE p.category_id = :category_id AND p.status = 'approved'";

        if ($minPrice !== null) {
            $query .= " AND p.price >= :minPrice";
        }
        if ($maxPrice !== null) {
            $query .= " AND p.price <= :maxPrice";
        }

        $query .= " ORDER BY p.id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $category_id);
        if ($minPrice !== null) $stmt->bindParam(':minPrice', $minPrice);
        if ($maxPrice !== null) $stmt->bindParam(':maxPrice', $maxPrice);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    public function getProductById($id)
    {
        $query = "SELECT p.*, c.name as category_name, u.role as seller_role, u.name as seller_display_name, u.username as seller_handle, s.id as shop_id, s.name as shop_name, s.logo as shop_logo
        FROM " . $this->table_name . " p
        LEFT JOIN category c ON p.category_id = c.id
        LEFT JOIN user u ON p.user_id = u.id
        LEFT JOIN shops s ON p.user_id = s.seller_id
        WHERE p.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result;
    }
    public function addProduct($name, $description, $price, $category_id, $image, $stock = 0, $sold = 0, $rating = 0.0, $discount_percent = 0, $location = 'Tp. Hồ Chí Minh', $user_id = 1)
    {
        $errors = [];
        if (empty($name)) {
            $errors['name'] = 'Tên sản phẩm không được để trống';
        }
        if (!is_numeric($price) || $price < 0) {
            $errors['price'] = 'Giá sản phẩm không hợp lệ';
        }
        if (count($errors) > 0) {
            return $errors;
        }
        $query = "INSERT INTO " . $this->table_name . " (name, description, price, discount_percent, category_id, image, stock, sold, rating, location, user_id, status) VALUES (:name, :description, :price, :discount_percent, :category_id, :image, :stock, :sold, :rating, :location, :user_id, :status)";
        $stmt = $this->conn->prepare($query);
        $name = htmlspecialchars(strip_tags($name));
        $description = htmlspecialchars(strip_tags($description));
        $price = htmlspecialchars(strip_tags($price));
        $category_id = htmlspecialchars(strip_tags($category_id));
        $image = htmlspecialchars(strip_tags($image));
        $location = !empty($location) ? htmlspecialchars(strip_tags($location)) : 'Tp. Hồ Chí Minh';
        
        // Mặc định là pending cho người bán, admin thì có thể set approved luôn (nếu muốn)
        $status = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') ? 'approved' : 'pending';

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':discount_percent', $discount_percent);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':sold', $sold);
        $stmt->bindParam(':rating', $rating);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':status', $status);
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    public function updateProduct($id, $name, $description, $price, $category_id, $image, $stock = 0, $sold = 0, $rating = 0.0, $discount_percent = 0, $location = 'Tp. Hồ Chí Minh', $status = 'approved')
    {
        $query = "UPDATE " . $this->table_name . " SET name=:name, description=:description, price=:price, discount_percent=:discount_percent, category_id=:category_id, image=:image, stock=:stock, sold=:sold, rating=:rating, location=:location, status=:status, rejection_reason=NULL WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $name = htmlspecialchars(strip_tags($name));
        $description = htmlspecialchars(strip_tags($description));
        $price = htmlspecialchars(strip_tags($price));
        $category_id = htmlspecialchars(strip_tags($category_id));
        $image = htmlspecialchars(strip_tags($image));
        $location = !empty($location) ? htmlspecialchars(strip_tags($location)) : 'Tp. Hồ Chí Minh';
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':discount_percent', $discount_percent);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':sold', $sold);
        $stmt->bindParam(':rating', $rating);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':status', $status);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    public function deleteProduct($id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function decreaseStockAndIncreaseSold($id, $quantity)
    {
        $query = "UPDATE " . $this->table_name . " SET stock = GREATEST(0, stock - :quantity), sold = sold + :quantity WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function updateProductRating($product_id)
    {
        $query = "SELECT AVG(rating) as avg_rating, COUNT(id) as rating_count FROM product_reviews WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $avg_rating = $row['avg_rating'] ? round($row['avg_rating'], 1) : 0.0;
        $rating_count = $row['rating_count'] ? $row['rating_count'] : 0;

        $updateQuery = "UPDATE " . $this->table_name . " SET rating = :rating, rating_count = :rating_count WHERE id = :id";
        $updateStmt = $this->conn->prepare($updateQuery);
        $updateStmt->bindParam(':rating', $avg_rating);
        $updateStmt->bindParam(':rating_count', $rating_count);
        $updateStmt->bindParam(':id', $product_id);

        return $updateStmt->execute();
    }

    // Banners methods
    public function getBanners($section = 'hero')
    {
        $query = "SELECT * FROM banners WHERE section = :section ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':section', $section);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getBannerById($id)
    {
        $query = "SELECT * FROM banners WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function addBanner($image, $section = 'hero', $qr_image = null)
    {
        $query = "INSERT INTO banners (image, section, qr_image) VALUES (:image, :section, :qr_image)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':section', $section);
        $stmt->bindParam(':qr_image', $qr_image);
        return $stmt->execute();
    }

    public function updateSectionBanner($section, $image, $qr_image = null)
    {
        // Check if banner for this section already exists
        $query = "SELECT id FROM banners WHERE section = :section LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':section', $section);
        $stmt->execute();
        $existing = $stmt->fetch(PDO::FETCH_OBJ);

        if ($existing) {
            $query = "UPDATE banners SET image = :image, qr_image = :qr_image WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':image', $image);
            $stmt->bindParam(':qr_image', $qr_image);
            $stmt->bindParam(':id', $existing->id);
        } else {
            $query = "INSERT INTO banners (image, section, qr_image) VALUES (:image, :section, :qr_image)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':image', $image);
            $stmt->bindParam(':section', $section);
            $stmt->bindParam(':qr_image', $qr_image);
        }
        return $stmt->execute();
    }

    public function updateBannerQR($id, $qr_image)
    {
        $query = "UPDATE banners SET qr_image = :qr_image WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':qr_image', $qr_image);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function updateBannerQRPosition($id, $position)
    {
        $query = "UPDATE banners SET qr_position = :position WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function deleteBanner($id)
    {
        $query = "DELETE FROM banners WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function deleteBannerBySection($section)
    {
        // 1. Get the banner info to delete the physical file
        $query = "SELECT image FROM banners WHERE section = :section LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':section', $section);
        $stmt->execute();
        $banner = $stmt->fetch(PDO::FETCH_OBJ);

        if ($banner) {
            $file_path = "public/images/" . $banner->image;
            if (file_exists($file_path)) {
                @unlink($file_path);
            }
            // 2. Delete the record from the database
            $query = "DELETE FROM banners WHERE section = :section";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':section', $section);
            return $stmt->execute();
        }
        return false;
    }
    /**
     * Lấy sản phẩm theo danh sách ID cụ thể, sắp xếp theo thứ tự tùy chỉnh
     */
    public function getProductsByIds($ids, $orders = [])
    {
        if (empty($ids)) return [];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $query = "SELECT p.*, c.name as category_name
                  FROM " . $this->table_name . " p
                  LEFT JOIN category c ON p.category_id = c.id
                  WHERE p.id IN ($placeholders)";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($ids);
        $products = $stmt->fetchAll(PDO::FETCH_OBJ);

        // Sắp xếp theo thứ tự orders
        if (!empty($orders)) {
            usort($products, function ($a, $b) use ($orders) {
                $orderA = isset($orders[$a->id]) ? (int)$orders[$a->id] : 999;
                $orderB = isset($orders[$b->id]) ? (int)$orders[$b->id] : 999;
                return $orderA - $orderB;
            });
        }

        return $products;
    }

    public function getProductsByCategoryNames($names, $minPrice = null, $maxPrice = null, $sort = 'newest')
    {
        if (empty($names)) return [];
        $placeholders = implode(',', array_fill(0, count($names), '?'));

        $query = "SELECT p.*, c.name as category_name
                  FROM " . $this->table_name . " p
                  LEFT JOIN category c ON p.category_id = c.id
                  WHERE TRIM(c.name) IN ($placeholders)";

        if ($minPrice !== null) {
            $query .= " AND p.price >= ?";
        }
        if ($maxPrice !== null) {
            $query .= " AND p.price <= ?";
        }
        switch ($sort) {
            case 'price_asc':
                $query .= " ORDER BY p.price ASC, p.id DESC";
                break;
            case 'price_desc':
                $query .= " ORDER BY p.price DESC, p.id DESC";
                break;
            case 'sold':
                $query .= " ORDER BY p.sold DESC, p.id DESC";
                break;
            case 'newest':
            default:
                $query .= " ORDER BY p.id DESC";
                break;
        }

        $params = $names;
        if ($minPrice !== null) $params[] = $minPrice;
        if ($maxPrice !== null) $params[] = $maxPrice;

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Lấy báo cáo chi tiết các sản phẩm đã bán kèm thông tin người mua
     */
    public function getSoldDetailsReport()
    {
        $query = "SELECT 
                    p.name as product_name, 
                    p.image as product_image,
                    od.price as sold_price, 
                    od.quantity,
                    COALESCE(o.recipient_name, u.name) as recipient_name, 
                    COALESCE(o.recipient_phone, u.phone) as recipient_phone, 
                    COALESCE(o.recipient_address, u.address) as recipient_address, 
                    o.created_at as sale_date,
                    o.id as order_id,
                    u_s.name as seller_name
                  FROM order_detail od
                  JOIN product p ON od.product_id = p.id
                  JOIN orders o ON od.order_id = o.id
                  LEFT JOIN user u ON o.user_id = u.id
                  LEFT JOIN user u_s ON p.user_id = u_s.id
                  ORDER BY o.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getSoldDetailsReportBySellerId($sellerId)
    {
        $query = "SELECT 
                    p.name as product_name, 
                    p.image as product_image,
                    od.price as sold_price, 
                    od.quantity,
                    COALESCE(o.recipient_name, u.name) as recipient_name, 
                    COALESCE(o.recipient_phone, u.phone) as recipient_phone, 
                    COALESCE(o.recipient_address, u.address) as recipient_address, 
                    o.created_at as sale_date,
                    o.id as order_id,
                    u_s.name as seller_name
                  FROM order_detail od
                  JOIN product p ON od.product_id = p.id
                  JOIN orders o ON od.order_id = o.id
                  LEFT JOIN user u ON o.user_id = u.id
                  LEFT JOIN user u_s ON p.user_id = u_s.id
                  WHERE p.user_id = :seller_id
                  ORDER BY o.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':seller_id', $sellerId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function toggleLike($productId, $userId)
    {
        // Kiểm tra xem đã thích chưa
        $query = "SELECT id FROM product_likes WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        $liked = $stmt->fetch();

        if ($liked) {
            // Đã thích -> Bỏ thích
            $query = "DELETE FROM product_likes WHERE user_id = :user_id AND product_id = :product_id";
            $action = 'unliked';
        } else {
            // Chưa thích -> Thêm thích
            $query = "INSERT INTO product_likes (user_id, product_id) VALUES (:user_id, :product_id)";
            $action = 'liked';
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':product_id', $productId);
        
        if ($stmt->execute()) {
            // Cập nhật số lượng likes trong bảng product (để cache/hiển thị nhanh)
            $countQuery = "UPDATE " . $this->table_name . " SET likes = (SELECT COUNT(*) FROM product_likes WHERE product_id = :product_id) WHERE id = :product_id";
            $countStmt = $this->conn->prepare($countQuery);
            $countStmt->bindParam(':product_id', $productId);
            $countStmt->execute();

            // Lấy lại số lượt thích mới
            $query = "SELECT likes FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'action' => $action,
                'likes' => $row['likes']
            ];
        }
        return false;
    }

    public function getUserLikedProductIds($userId)
    {
        $query = "SELECT product_id FROM product_likes WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getProductsByShop($shopId)
    {
        $query = "SELECT p.*, c.name as category_name
        FROM " . $this->table_name . " p
        LEFT JOIN category c ON p.category_id = c.id
        JOIN shops s ON p.user_id = s.seller_id
        WHERE s.id = :shop_id AND p.status = 'approved'
        ORDER BY p.id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':shop_id', $shopId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
