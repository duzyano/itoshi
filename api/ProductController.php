<?php
// api/ProductController.php
// Handle product-related API endpoints

class ProductController {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * GET /api/products
     * Retrieve all products grouped by category with pagination support
     */
    public function getAllProducts() {
        try {
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 50;
            $offset = ($page - 1) * $limit;
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            
                 // Build query
$query = "SELECT p.product_id, p.name, p.description, p.price, p.kcal, p.available, p.is_vlees, p.is_vegan, 
                         c.category_id, c.name AS category_name, i.filename AS image_filename
                     FROM products p
                     JOIN categories c ON p.category_id = c.category_id
                     LEFT JOIN images i ON p.image_id = i.image_id";
            
            $params = [];
            
            // Apply search filter
            if (!empty($search)) {
                $query .= " WHERE p.name LIKE :search OR p.description LIKE :search";
                $params[':search'] = '%' . $search . '%';
            }
            
            $query .= " ORDER BY c.name, p.name LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count for pagination
            $countQuery = "SELECT COUNT(*) as total FROM products p";
            if (!empty($search)) {
                $countQuery .= " WHERE p.name LIKE :search OR p.description LIKE :search";
            }
            
            $countStmt = $this->conn->prepare($countQuery);
            if (!empty($search)) {
                $countStmt->bindValue(':search', '%' . $search . '%');
            }
            $countStmt->execute();
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Format results
            $products = [];
            $grouped = [];
            
            foreach ($results as $row) {
                $imagePath = !empty($row['image_filename']) 
                    ? 'assets/images/' . $row['image_filename']
                    : 'assets/images/image.png';
                
                $product = [
                    'id' => $row['product_id'],
                    'product_id' => $row['product_id'],
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'price' => floatval($row['price']),
                    'kcal' => $row['kcal'] ? intval($row['kcal']) : null,
                    'available' => isset($row['available']) ? intval($row['available']) : 0,
                    'image' => $imagePath,
                    'is_vlees' => isset($row['is_vlees']) ? intval($row['is_vlees']) : 0,
                    'is_vegan' => isset($row['is_vegan']) ? intval($row['is_vegan']) : 0,
                    'category_id' => $row['category_id'],
                    'category_name' => $row['category_name']
                ];
                
                $products[] = $product;
                
                // Group by category
                $catKey = strtolower(str_replace(' ', '_', $row['category_name']));
                if (!isset($grouped[$catKey])) {
                    $grouped[$catKey] = [
                        'name' => $row['category_name'],
                        'items' => []
                    ];
                }
                $grouped[$catKey]['items'][] = $product;
            }
            
            ResponseHelper::success([
                'products' => $products,
                'grouped' => $grouped,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => intval($total),
                    'pages' => ceil($total / $limit)
                ]
            ], 'Products retrieved successfully');
            
        } catch (Exception $e) {
            ResponseHelper::error('Failed to retrieve products: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * GET /api/products/{id}
     * Retrieve a single product by ID
     */
    public function getProduct($id) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT p.product_id, p.name, p.description, p.price, p.kcal, p.available, p.is_vlees, p.is_vegan,
                        c.category_id, c.name AS category_name, i.filename AS image_filename
                 FROM products p
                 JOIN categories c ON p.category_id = c.category_id
                 LEFT JOIN images i ON p.image_id = i.image_id
                 WHERE p.product_id = :id"
            );
            
            $stmt->execute([':id' => intval($id)]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$row) {
                ResponseHelper::error('Product not found', 404);
            }
            
            $imagePath = !empty($row['image_filename'])
                ? 'assets/images/' . $row['image_filename']
                : 'assets/images/image.png';
            
            $product = [
                'id' => $row['product_id'],
                'product_id' => $row['product_id'],
                'name' => $row['name'],
                'description' => $row['description'],
                'price' => floatval($row['price']),
                'kcal' => $row['kcal'] ? intval($row['kcal']) : null,
                'available' => isset($row['available']) ? intval($row['available']) : 0,
                'image' => $imagePath,
                'is_vlees' => isset($row['is_vlees']) ? intval($row['is_vlees']) : 0,
                'is_vegan' => isset($row['is_vegan']) ? intval($row['is_vegan']) : 0,
                'category_id' => $row['category_id'],
                'category_name' => $row['category_name']
            ];
            
            ResponseHelper::success($product, 'Product retrieved successfully');
            
        } catch (Exception $e) {
            ResponseHelper::error('Failed to retrieve product: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * GET /api/products/{id}/related
     * Retrieve related products from the same category
     */
    public function getRelatedProducts($id) {
        try {
            // Get the product's category first
            $stmt = $this->conn->prepare(
                "SELECT category_id FROM products WHERE product_id = :id"
            );
            $stmt->execute([':id' => intval($id)]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$product) {
                ResponseHelper::error('Product not found', 404);
            }
            
            $limit = isset($_GET['limit']) ? min(10, max(1, intval($_GET['limit']))) : 5;
            
            // Get related products (same category, excluding current)
            $stmt = $this->conn->prepare(
                "SELECT p.product_id, p.name, p.description, p.price, p.kcal, p.available, p.is_vlees, p.is_vegan,
                        c.name AS category_name, i.filename AS image_filename
                 FROM products p
                 JOIN categories c ON p.category_id = c.category_id
                 LEFT JOIN images i ON p.image_id = i.image_id
                 WHERE p.category_id = :cat_id AND p.product_id != :id
                 ORDER BY p.name
                 LIMIT :limit"
            );
            
            $stmt->bindValue(':cat_id', $product['category_id'], PDO::PARAM_INT);
            $stmt->bindValue(':id', intval($id), PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $products = [];
            foreach ($results as $row) {
                $imagePath = !empty($row['image_filename'])
                    ? 'assets/images/' . $row['image_filename']
                    : 'assets/images/image.png';
                
                $products[] = [
                    'id' => $row['product_id'],
                    'product_id' => $row['product_id'],
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'price' => floatval($row['price']),
                    'kcal' => $row['kcal'] ? intval($row['kcal']) : null,
                    'available' => isset($row['available']) ? intval($row['available']) : 0,
                    'image' => $imagePath,
                    'is_vlees' => isset($row['is_vlees']) ? intval($row['is_vlees']) : 0,
                    'is_vegan' => isset($row['is_vegan']) ? intval($row['is_vegan']) : 0,
                    'category_name' => $row['category_name']
                ];
            }
            
            ResponseHelper::success($products, 'Related products retrieved successfully');
            
        } catch (Exception $e) {
            ResponseHelper::error('Failed to retrieve related products: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * GET /api/categories
     * Retrieve all categories
     */
    public function getAllCategories() {
        try {
            $stmt = $this->conn->prepare(
                "SELECT c.category_id, c.name, COUNT(p.product_id) as product_count
                 FROM categories c
                 LEFT JOIN products p ON c.category_id = p.category_id
                 GROUP BY c.category_id, c.name
                 ORDER BY c.name"
            );
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $categories = [];
            foreach ($results as $row) {
                $categories[] = [
                    'id' => intval($row['category_id']),
                    'category_id' => intval($row['category_id']),
                    'name' => $row['name'],
                    'product_count' => intval($row['product_count'])
                ];
            }
            
            ResponseHelper::success($categories, 'Categories retrieved successfully');
            
        } catch (Exception $e) {
            ResponseHelper::error('Failed to retrieve categories: ' . $e->getMessage(), 500);
        }
    }
}
?>
