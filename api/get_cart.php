<?php
require_once '../connection.php';
header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("SELECT op.product_id, op.quantity, p.name, p.price, i.filename as image 
                          FROM order_product op
                          JOIN products p ON op.product_id=p.product_id
                          LEFT JOIN images i ON p.image_id=i.image_id
                          WHERE op.session_id=1");
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total = 0;
    foreach ($items as &$i) {
        $i['image'] = $i['image'] ? 'assets/images/' . $i['image'] : 'assets/images/image.png';
        $total += $i['price'] * $i['quantity'];
    }
    echo json_encode(['items' => $items, 'total' => $total]);
} catch (PDOException $e) {
    echo json_encode(['items' => [], 'total' => 0]);
}