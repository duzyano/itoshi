<?php
// api/OrderController.php
// Handle order-related API endpoints

class OrderController {
    private $conn;
    private $counterDir;
    private $counterFile;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->counterDir = __DIR__ . '/../data';
        $this->counterFile = $this->counterDir . '/order_counter.txt';
    }
    
    /**
     * GET /api/orders
     * Retrieve all orders (with optional filtering)
     */
    public function getAllOrders() {
        try {
            $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 20;
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $offset = ($page - 1) * $limit;
            
            $stmt = $this->conn->prepare(
                "SELECT o.order_id, o.order_status_id, o.pickup_number, o.price_total, o.datetime,
                        s.name as status_name
                 FROM orders o
                 LEFT JOIN order_status s ON o.order_status_id = s.order_status_id
                 ORDER BY o.datetime DESC
                 LIMIT :limit OFFSET :offset"
            );
            
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count
            $countStmt = $this->conn->query("SELECT COUNT(*) as total FROM orders");
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $orders = [];
            foreach ($results as $row) {
                $orders[] = [
                    'order_id' => intval($row['order_id']),
                    'status_id' => intval($row['order_status_id']),
                    'status_name' => $row['status_name'],
                    'pickup_number' => $row['pickup_number'],
                    'total' => floatval($row['price_total']),
                    'datetime' => $row['datetime']
                ];
            }
            
            ResponseHelper::success([
                'orders' => $orders,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => intval($total),
                    'pages' => ceil($total / $limit)
                ]
            ], 'Orders retrieved successfully');
            
        } catch (Exception $e) {
            ResponseHelper::error('Failed to retrieve orders: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * GET /api/orders/{id}
     * Retrieve a specific order with its products
     */
    public function getOrder($id) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT o.order_id, o.order_status_id, o.pickup_number, o.price_total, o.datetime,
                        s.name as status_name
                 FROM orders o
                 LEFT JOIN order_status s ON o.order_status_id = s.order_status_id
                 WHERE o.order_id = :id"
            );
            
            $stmt->execute([':id' => intval($id)]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$order) {
                ResponseHelper::error('Order not found', 404);
            }
            
            // Get order products
            $prodStmt = $this->conn->prepare(
                "SELECT op.order_product_id, op.product_id, op.price, p.name, p.description,
                        i.filename AS image_filename
                 FROM order_product op
                 LEFT JOIN products p ON op.product_id = p.product_id
                 LEFT JOIN images i ON p.image_id = i.image_id
                 WHERE op.order_id = :order_id"
            );
            
            $prodStmt->execute([':order_id' => intval($id)]);
            $products = $prodStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $items = [];
            foreach ($products as $prod) {
                $imagePath = !empty($prod['image_filename'])
                    ? 'assets/images/' . $prod['image_filename']
                    : 'assets/images/image.png';
                
                $items[] = [
                    'order_product_id' => intval($prod['order_product_id']),
                    'product_id' => intval($prod['product_id']),
                    'name' => $prod['name'] ?? 'Unknown Product',
                    'description' => $prod['description'] ?? '',
                    'price' => floatval($prod['price']),
                    'image' => $imagePath
                ];
            }
            
            $orderData = [
                'order_id' => intval($order['order_id']),
                'status_id' => intval($order['order_status_id']),
                'status_name' => $order['status_name'],
                'pickup_number' => $order['pickup_number'],
                'total' => floatval($order['price_total']),
                'datetime' => $order['datetime'],
                'products' => $items
            ];
            
            ResponseHelper::success($orderData, 'Order retrieved successfully');
            
        } catch (Exception $e) {
            ResponseHelper::error('Failed to retrieve order: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * POST /api/orders
     * Create a new order from cart data
     */
    public function createOrder() {
        try {
            $data = ResponseHelper::getJsonPayload();
            
            // Validate required fields
            $cart = $data['cart'] ?? [];
            $subtotal = isset($data['subtotal']) ? floatval($data['subtotal']) : 0.0;
            $delivery = isset($data['delivery']) ? floatval($data['delivery']) : 0.0;
            $total = isset($data['total']) ? floatval($data['total']) : ($subtotal + $delivery);
            
            if (empty($cart) || !is_array($cart)) {
                ResponseHelper::error('Cart is required and must be an array', 400);
            }
            
            // Generate incremental order number
            $orderNumber = $this->generateOrderNumber();
            if (!$orderNumber) {
                ResponseHelper::error('Failed to generate order number', 500);
            }
            
            // Save order to database
            try {
                $this->conn->beginTransaction();
                
                $insertOrder = $this->conn->prepare(
                    'INSERT INTO orders (order_status_id, pickup_number, price_total, datetime) 
                     VALUES (:status, :pickup, :total, NOW())'
                );
                
                $status = 2; // 'Placed and paid' as default
                $pickup = str_replace('#', '', $orderNumber);
                
                $insertOrder->execute([
                    ':status' => $status,
                    ':pickup' => $pickup,
                    ':total' => $total
                ]);
                
                $insertOrderId = $this->conn->lastInsertId();
                
                // Insert order products
                $insertProduct = $this->conn->prepare(
                    'INSERT INTO order_product (order_id, product_id, price) 
                     VALUES (:order_id, :product_id, :price)'
                );
                
                $skippedItems = [];
                foreach ($cart as $item) {
                    $productId = $item['id'] ?? $item['product_id'] ?? 0;
                    $price = isset($item['price']) ? floatval($item['price']) : 0.0;
                    
                    if (intval($productId) > 0) {
                        $insertProduct->execute([
                            ':order_id' => $insertOrderId,
                            ':product_id' => intval($productId),
                            ':price' => $price
                        ]);
                    } else {
                        $skippedItems[] = $item;
                    }
                }
                
                // Log skipped items if any
                if (!empty($skippedItems)) {
                    $log = date('c') . " - Skipped items for order {$insertOrderId}: " . 
                           json_encode($skippedItems) . PHP_EOL;
                    @file_put_contents($this->counterDir . '/order_error.log', $log, FILE_APPEND | LOCK_EX);
                }
                
                $this->conn->commit();
                
                ResponseHelper::success([
                    'order_id' => intval($insertOrderId),
                    'order_number' => $orderNumber,
                    'pickup_number' => $pickup,
                    'total' => $total,
                    'status_id' => $status,
                    'datetime' => date('c'),
                    'skipped_items' => count($skippedItems)
                ], 'Order created successfully', 201);
                
            } catch (Exception $e) {
                if ($this->conn->inTransaction()) {
                    try { $this->conn->rollBack(); } catch (Exception $_) { }
                }
                throw $e;
            }
            
        } catch (Exception $e) {
            ResponseHelper::error('Failed to create order: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Generate an incremental order number using file-based counter
     */
    private function generateOrderNumber() {
        try {
            if (!is_dir($this->counterDir)) {
                @mkdir($this->counterDir, 0755, true);
            }
            
            $fp = @fopen($this->counterFile, 'c+');
            if (!$fp) {
                // Fallback to random
                return '#' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            }
            
            // Exclusive lock while reading/writing counter
            flock($fp, LOCK_EX);
            $contents = stream_get_contents($fp);
            $last = intval(trim($contents));
            $last = $last + 1;
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, (string) $last);
            fflush($fp);
            flock($fp, LOCK_UN);
            fclose($fp);
            
            return '#' . str_pad((string) $last, 4, '0', STR_PAD_LEFT);
            
        } catch (Exception $e) {
            return null;
        }
    }
}
?>
