# Tài liệu Hướng dẫn Kiểm thử REST API trên Postman

Tài liệu này hướng dẫn chi tiết cách kiểm thử toàn bộ 14 endpoint REST API của hệ thống **QLBANDOHANDMADE** bằng Postman.

Hệ thống sử dụng Session ID thông qua Cookie (`PHPSESSID`) để xác thực các yêu cầu cần đăng nhập. Khi bạn gọi API đăng nhập, Postman sẽ tự động lưu Cookie này và gửi kèm trong các yêu cầu API tiếp theo.

---

## 1. Thiết lập Đăng nhập (Lấy Session trên Postman)

Để kiểm thử các API yêu cầu đăng nhập (như Giỏ hàng, Yêu thích, Địa chỉ, và Quản lý sản phẩm), trước hết bạn cần thực hiện một request Đăng nhập trong Postman:

- **HTTP Method**: `POST`
- **URL**: `http://localhost/QLBANDOHANDMADE/Page/processLogin`
- **Kiểu Body**: `x-www-form-urlencoded` hoặc `form-data`
- **Các tham số truyền lên**:
  - `identifier`: Email hoặc số điện thoại đăng nhập (ví dụ: `admin@gmail.com` hoặc `0987654321`)
  - `password`: Mật khẩu của tài khoản (ví dụ: `123456`)

> [!NOTE]
> Sau khi gửi thành công, Postman sẽ tự động lưu cookie `PHPSESSID`. Các request API sau đó của bạn sẽ tự động được đính kèm cookie này để giữ trạng thái đăng nhập.

> [!TIP]
> **Phương pháp test nhanh không cần đăng nhập (Bypass cho Developer)**:
> Nếu gặp khó khăn về Cookie Session trong Postman, bạn có thể truyền trực tiếp ID người dùng và Vai trò thông qua Headers hoặc Query Parameters của request để kiểm thử nhanh:
> - **Cách 1: Thêm Header**:
>   - `X-User-Id`: ID người dùng cần giả lập (ví dụ: `3`)
>   - `X-User-Role`: Vai trò người dùng (ví dụ: `admin` hoặc `seller` hoặc `customer`)
> - **Cách 2: Thêm Query Parameter**:
>   - Thêm `?user_id=3&user_role=admin` vào URL (ví dụ: `http://localhost/QLBANDOHANDMADE/api/cart?user_id=3`)

---

## 2. Nhóm 1: Quản lý Sản phẩm (Products)

### 2.1. Lấy danh sách sản phẩm (Truy cập công khai)
- **HTTP Method**: `GET`
- **URL**: `http://localhost/QLBANDOHANDMADE/api/products`
- **Query Parameters (Tùy chọn)**:
  - `q`: Từ khóa tìm kiếm sản phẩm (ví dụ: `gốm`, `túi`)
  - `min_price`: Giá tối thiểu
  - `max_price`: Giá tối đa
  - `seller_id`: Lọc sản phẩm của người bán cụ thể
  - `sort`: Cách sắp xếp (`newest`, `oldest`, `price_asc`, `price_desc`, `sales_desc`)
- **Phản hồi mẫu (200 OK)**:
  ```json
  {
    "success": true,
    "count": 2,
    "data": [
      {
        "id": 1,
        "name": "Bình Gốm Trang Trí Bát Tràng",
        "description": "Sản phẩm thủ công vẽ tay tinh xảo",
        "price": 250000,
        "category_id": 2,
        "image": "prod_example_1.jpg",
        "stock": 20,
        "discount_percent": 10,
        "location": "Hà Nội",
        "user_id": 2,
        "image_url": "http://localhost/QLBANDOHANDMADE/public/uploads/prod_example_1.jpg"
      }
    ]
  }
  ```

### 2.2. Chi tiết sản phẩm (Truy cập công khai)
- **HTTP Method**: `GET`
- **URL**: `http://localhost/QLBANDOHANDMADE/api/products/{id}` (ví dụ: `http://localhost/QLBANDOHANDMADE/api/products/1`)
- **Phản hồi mẫu (200 OK)**:
  ```json
  {
    "success": true,
    "data": {
      "product": {
        "id": 1,
        "name": "Bình Gốm Trang Trí Bát Tràng",
        "description": "Sản phẩm thủ công vẽ tay tinh xảo",
        "price": 250000,
        "category_id": 2,
        "image": "prod_example_1.jpg",
        "stock": 20,
        "discount_percent": 10,
        "location": "Hà Nội",
        "user_id": 2,
        "image_url": "http://localhost/QLBANDOHANDMADE/public/uploads/prod_example_1.jpg"
      },
      "variants": [
        {
          "id": 1,
          "product_id": 1,
          "name": "Màu xanh ngọc",
          "price": 250000,
          "stock": 8,
          "image": ""
        },
        {
          "id": 2,
          "product_id": 1,
          "name": "Màu men lam",
          "price": 270000,
          "stock": 12,
          "image": ""
        }
      ]
    }
  }
  ```

### 2.3. Thêm sản phẩm mới (Yêu cầu quyền Admin/Seller)
- **HTTP Method**: `POST`
- **URL**: `http://localhost/QLBANDOHANDMADE/api/products`
- **Headers**: `Content-Type: application/json`
- **Body mẫu (JSON)**:
  ```json
  {
    "name": "Túi Tỏi Handmade Đan Len",
    "description": "Túi len móc tay nhỏ xinh đựng tỏi cho bé yêu tránh gió",
    "price": 45000,
    "category_id": 3,
    "stock": 50,
    "discount_percent": 0,
    "location": "Tp. Hồ Chí Minh",
    "image_url": "tui_toi.jpg",
    "variants": [
      {
        "name": "Màu Đỏ",
        "price": 45000,
        "stock": 25
      },
      {
        "name": "Màu Vàng",
        "price": 45000,
        "stock": 25
      }
    ]
  }
  ```
- **Phản hồi mẫu (201 Created)**:
  ```json
  {
    "success": true,
    "message": "Thêm sản phẩm thành công.",
    "product_id": 12
  }
  ```

### 2.4. Cập nhật sản phẩm (Yêu cầu quyền Admin hoặc người bán sở hữu)
- **HTTP Method**: `PUT`
- **URL**: `http://localhost/QLBANDOHANDMADE/api/products/{id}` (ví dụ: `http://localhost/QLBANDOHANDMADE/api/products/12`)
- **Headers**: `Content-Type: application/json`
- **Body mẫu (JSON)**:
  ```json
  {
    "name": "Túi Tỏi Handmade Đan Len (Cập nhật)",
    "price": 49000,
    "stock": 40,
    "discount_percent": 5
  }
  ```
- **Phản hồi mẫu (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Cập nhật sản phẩm thành công."
  }
  ```

### 2.5. Xóa sản phẩm (Yêu cầu quyền Admin hoặc người bán sở hữu)
- **HTTP Method**: `DELETE`
- **URL**: `http://localhost/QLBANDOHANDMADE/api/products/{id}` (ví dụ: `http://localhost/QLBANDOHANDMADE/api/products/12`)
- **Phản hồi mẫu (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Xóa sản phẩm thành công."
  }
  ```

---

## 3. Nhóm 2: Quản lý Danh mục (Categories)

### 3.1. Lấy danh sách danh mục (Truy cập công khai)
- **HTTP Method**: `GET`
- **URL**: `http://localhost/QLBANDOHANDMADE/api/categories`
- **Phản hồi mẫu (200 OK)**:
  ```json
  {
    "success": true,
    "count": 3,
    "data": [
      {
        "id": 1,
        "name": "Đồ Đan Len",
        "description": "Các sản phẩm làm từ sợi len móc tay"
      },
      {
        "id": 2,
        "name": "Gốm Sứ Mỹ Nghệ",
        "description": "Gốm sứ Bát Tràng nghệ thuật"
      }
    ]
  }
  ```

### 3.2. Thêm danh mục mới (Yêu cầu quyền Admin)
- **HTTP Method**: `POST`
- **URL**: `http://localhost/QLBANDOHANDMADE/api/categories`
- **Headers**: `Content-Type: application/json`
- **Body mẫu (JSON)**:
  ```json
  {
    "name": "Trang Sức Handmade",
    "description": "Vòng tay, khuyên tai đính đá thủ công tinh xảo"
  }
  ```
- **Phản hồi mẫu (201 Created)**:
  ```json
  {
    "success": true,
    "message": "Thêm danh mục thành công."
  }
  ```

---

## 4. Nhóm 3: Quản lý Giỏ hàng (Cart)

*Lưu ý: Yêu cầu đăng nhập trước khi gọi.*

### 4.1. Xem danh sách giỏ hàng
- **HTTP Method**: `GET`
- **URL**: `http://localhost/QLBANDOHANDMADE/api/cart`
- **Phản hồi mẫu (200 OK)**:
  ```json
  {
    "success": true,
    "data": {
      "items": [
        {
          "product_id": 1,
          "variant_id": 2,
          "quantity": 2,
          "name": "Bình Gốm Trang Trí Bát Tràng",
          "price": 270000,
          "image": "prod_example_1.jpg",
          "variant_name": "Màu men lam",
          "image_url": "http://localhost/QLBANDOHANDMADE/public/uploads/prod_example_1.jpg"
        }
      ],
      "total_amount": 540000,
      "total_quantity": 2
    }
  }
  ```

### 4.2. Thêm sản phẩm vào giỏ hàng
- **HTTP Method**: `POST`
- **URL**: `http://localhost/QLBANDOHANDMADE/api/cart/add`
- **Headers**: `Content-Type: application/json`
- **Body mẫu (JSON)**:
  ```json
  {
    "product_id": 1,
    "variant_id": 2,
    "quantity": 1
  }
  ```
- **Phản hồi mẫu (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Đã thêm sản phẩm vào giỏ hàng thành công.",
    "cartCount": 3,
    "cart_count": 3
  }
  ```

### 4.3. Cập nhật số lượng sản phẩm trong giỏ
- **HTTP Method**: `PUT`
- **URL**: `http://localhost/QLBANDOHANDMADE/api/cart/update`
- **Headers**: `Content-Type: application/json`
- **Body mẫu (JSON)**:
  ```json
  {
    "product_id": 1,
    "variant_id": 2,
    "quantity": 5
  }
  ```
  *(Hoặc dùng định dạng ID rút gọn: `{"id": "1_2", "quantity": 5}`)*
- **Phản hồi mẫu (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Cập nhật số lượng thành công.",
    "itemSubtotal": "1.350.000 ₫",
    "totalAmount": "1.350.000 ₫",
    "cartCount": 5,
    "data": {
      "item_subtotal": 1350000,
      "total_amount": 1350000,
      "cart_count": 5
    }
  }
  ```

### 4.4. Xóa sản phẩm khỏi giỏ hàng
- **HTTP Method**: `DELETE`
- **URL**: `http://localhost/QLBANDOHANDMADE/api/cart/remove/{cart_key}` (Ví dụ: `http://localhost/QLBANDOHANDMADE/api/cart/remove/1_2` cho product_id 1 và variant_id 2. Nếu không có variant thì truyền `1_0`).
- **Phản hồi mẫu (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Đã xóa sản phẩm khỏi giỏ hàng.",
    "totalAmount": "0 ₫",
    "cartCount": 0,
    "data": {
      "total_amount": 0,
      "cart_count": 0
    }
  }
  ```

---

## 5. Nhóm 4: Quản lý Yêu thích (Wishlist)

*Lưu ý: Yêu cầu đăng nhập trước khi gọi.*

### 5.1. Xem danh sách yêu thích
- **HTTP Method**: `GET`
- **URL**: `http://localhost/QLBANDOHANDMADE/api/wishlist`
- **Phản hồi mẫu (200 OK)**:
  ```json
  {
    "success": true,
    "count": 1,
    "data": [
      {
        "id": 1,
        "name": "Bình Gốm Trang Trí Bát Tràng",
        "price": 250000,
        "image": "prod_example_1.jpg",
        "image_url": "http://localhost/QLBANDOHANDMADE/public/uploads/prod_example_1.jpg"
      }
    ]
  }
  ```

### 5.2. Thêm vào danh sách yêu thích
- **HTTP Method**: `POST`
- **URL**: `http://localhost/QLBANDOHANDMADE/api/wishlist/add`
- **Headers**: `Content-Type: application/json`
- **Body mẫu (JSON)**:
  ```json
  {
    "product_id": 1
  }
  ```
- **Phản hồi mẫu (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Đã thêm sản phẩm vào danh sách yêu thích.",
    "likes": 12
  }
  ```

### 5.3. Xóa khỏi danh sách yêu thích
- **HTTP Method**: `DELETE`
- **URL**: `http://localhost/QLBANDOHANDMADE/api/wishlist/remove/{product_id}` (ví dụ: `http://localhost/QLBANDOHANDMADE/api/wishlist/remove/1`)
- **Phản hồi mẫu (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Đã xóa sản phẩm khỏi danh sách yêu thích."
  }
  ```

---

## 6. Nhóm 5: Quản lý Địa chỉ (Address)

*Lưu ý: Yêu cầu đăng nhập trước khi gọi.*

### 6.1. Xem danh sách địa chỉ nhận hàng
- **HTTP Method**: `GET`
- **URL**: `http://localhost/QLBANDOHANDMADE/api/address`
- **Phản hồi mẫu (200 OK)**:
  ```json
  {
    "success": true,
    "count": 1,
    "data": [
      {
        "id": 5,
        "user_id": 3,
        "name": "Nguyễn Văn An",
        "phone": "0987654321",
        "email": "an.nguyen@gmail.com",
        "city": "Thành phố Hồ Chí Minh",
        "district": "Quận 1",
        "ward": "Phường Bến Nghé",
        "address_line": "123 Lê Lợi",
        "address_type": "Nhà Riêng",
        "is_default": 1
      }
    ]
  }
  ```

### 6.2. Thêm địa chỉ mới
- **HTTP Method**: `POST`
- **URL**: `http://localhost/QLBANDOHANDMADE/api/address`
- **Headers**: `Content-Type: application/json`
- **Body mẫu (JSON)**:
  ```json
  {
    "name": "Nguyễn Văn An",
    "phone": "0987654321",
    "email": "an.nguyen@gmail.com",
    "city": "Thành phố Hồ Chí Minh",
    "district": "Quận 1",
    "ward": "Phường Bến Nghé",
    "address_line": "123 Lê Lợi",
    "address_type": "Nhà Riêng",
    "is_default": 1
  }
  ```
- **Phản hồi mẫu (201 Created)**:
  ```json
  {
    "success": true,
    "message": "Lưu địa chỉ thành công."
  }
  ```

### 6.3. Cập nhật địa chỉ nhận hàng
- **HTTP Method**: `PUT`
- **URL**: `http://localhost/QLBANDOHANDMADE/api/address/{id}` (ví dụ: `http://localhost/QLBANDOHANDMADE/api/address/5`)
- **Headers**: `Content-Type: application/json`
- **Body mẫu (JSON)**:
  ```json
  {
    "name": "Nguyễn Văn An (Cập nhật)",
    "phone": "0911223344",
    "address_line": "456 Lê Lợi"
  }
  ```
- **Phản hồi mẫu (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Cập nhật địa chỉ thành công."
  }
  ```

### 6.4. Xóa địa chỉ nhận hàng
- **HTTP Method**: `DELETE`
- **URL**: `http://localhost/QLBANDOHANDMADE/api/address/{id}` (ví dụ: `http://localhost/QLBANDOHANDMADE/api/address/5`)
- **Phản hồi mẫu (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Xóa địa chỉ thành công."
  }
  ```
