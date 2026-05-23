<?php
class ShopUpdateModel {
    private $conn;
    private $table_name = "shop_update_requests";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (shop_id, new_name, new_description, new_logo, new_banner, status) 
                  VALUES (:shop_id, :new_name, :new_description, :new_logo, :new_banner, 'pending')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':shop_id', $data['shop_id']);
        $stmt->bindParam(':new_name', $data['new_name']);
        $stmt->bindParam(':new_description', $data['new_description']);
        $stmt->bindParam(':new_logo', $data['new_logo']);
        $stmt->bindParam(':new_banner', $data['new_banner']);
        return $stmt->execute();
    }

    public function getPendingByShopId($shop_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE shop_id = :shop_id AND status = 'pending' ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':shop_id', $shop_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getAllPending() {
        $query = "SELECT r.*, s.seller_id, u.name as seller_name, s.name as old_name 
                  FROM " . $this->table_name . " r
                  JOIN shops s ON r.shop_id = s.id
                  JOIN user u ON s.seller_id = u.id
                  WHERE r.status = 'pending'
                  ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getById($id) {
        $query = "SELECT r.*, s.seller_id FROM " . $this->table_name . " r JOIN shops s ON r.shop_id = s.id WHERE r.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
