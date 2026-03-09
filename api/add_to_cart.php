<?php
require_once '../connection.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$product_id = intval($data['product_id'] ?? 0);
if(!$product_id) exit(json_encode(['success'=>false,'msg'=>'No product_id']));

try {
    // Check of er een lopende order bestaat
    $stmt = $conn->prepare("SELECT order_id FROM orders WHERE order_status=1 LIMIT 1");
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$order){
        // Maak nieuwe order
        $stmt = $conn->prepare("INSERT INTO orders (order_status) VALUES (1)");
        $stmt->execute();
        $order_id = $conn->lastInsertId();
    } else {
        $order_id = $order['order_id'];
    }

    // Voeg product toe of verhoog quantity
    $stmt = $conn->prepare("SELECT quantity FROM order_product WHERE order_id=:oid AND product_id=:pid");
    $stmt->execute(['oid'=>$order_id,'pid'=>$product_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if($existing){
        $stmt = $conn->prepare("UPDATE order_product SET quantity=quantity+1 WHERE order_id=:oid AND product_id=:pid");
        $stmt->execute(['oid'=>$order_id,'pid'=>$product_id]);
    } else {
        $stmt = $conn->prepare("INSERT INTO order_product (order_id, product_id, quantity) VALUES (:oid,:pid,1)");
        $stmt->execute(['oid'=>$order_id,'pid'=>$product_id]);
    }

    echo json_encode(['success'=>true]);
}catch(PDOException $e){
    echo json_encode(['success'=>false,'msg'=>$e->getMessage()]);
}