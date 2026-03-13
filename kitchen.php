<?php
// kitchen.php - Kitchen Display System for Happy Herbivore
require_once 'connection.php';

// Haal alle bestellingen op (hier simuleren we met een eenvoudige tabel)
// In productie zou je een orders tabel hebben met status
$orders = [];

// Voor demo: genereer enkele test bestellingen
$testOrders = [
    [
        'order_id' => 1001,
        'order_number' => '0042',
        'time' => date('H:i', strtotime('-5 minutes')),
        'status' => 'pending',
        'items' => [
            ['name' => 'Vegan Burger', 'quantity' => 2],
            ['name' => 'Sweet Potato Fries', 'quantity' => 1],
            ['name' => 'Green Smoothie', 'quantity' => 2]
        ]
    ],
    [
        'order_id' => 1002,
        'order_number' => '0043',
        'time' => date('H:i', strtotime('-2 minutes')),
        'status' => 'pending',
        'items' => [
            ['name' => 'Buddha Bowl', 'quantity' => 1],
            ['name' => 'Hummus Wrap', 'quantity' => 1]
        ]
    ],
    [
        'order_id' => 1003,
        'order_number' => '0044',
        'time' => date('H:i'),
        'status' => 'pending',
        'items' => [
            ['name' => 'Tofu Scramble', 'quantity' => 3],
            ['name' => 'Oat Milk Latte', 'quantity' => 3],
            ['name' => 'Avocado Toast', 'quantity' => 2]
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Display - Happy Herbivore</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;700;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans', sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: white;
            min-height: 100vh;
            padding: 20px;
        }

        .kitchen-header {
            background: linear-gradient(135deg, #053631 0%, #0a5a4f 100%);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .kitchen-header h1 {
            font-size: 2.5rem;
            font-weight: 900;
            color: #8cd003;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-stats {
            display: flex;
            gap: 20px;
        }

        .stat-box {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px 25px;
            border-radius: 10px;
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 900;
            color: #ff7520;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #deff78;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        .order-card {
            background: white;
            color: #053631;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
        }

        .order-card.preparing {
            border: 4px solid #ff7520;
        }

        .order-card.ready {
            border: 4px solid #8cd003;
            opacity: 0.7;
        }

        .order-header {
            background: linear-gradient(135deg, #ff7520 0%, #ff9147 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-number {
            font-size: 2.5rem;
            font-weight: 900;
            letter-spacing: 2px;
            font-family: 'Courier New', monospace;
        }

        .order-time {
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .time-badge {
            background: rgba(255, 255, 255, 0.3);
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 700;
        }

        .order-items {
            padding: 25px;
        }

        .order-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            margin-bottom: 12px;
            background: linear-gradient(135deg, #fafafa 0%, #ffffff 100%);
            border-radius: 10px;
            border-left: 5px solid #8cd003;
            transition: all 0.2s ease;
        }

        .order-item:hover {
            background: linear-gradient(135deg, #deff78 0%, rgba(140, 208, 3, 0.2) 100%);
            transform: translateX(5px);
        }

        .item-quantity {
            background: #053631;
            color: white;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 900;
            flex-shrink: 0;
        }

        .item-name {
            flex: 1;
            font-size: 1.2rem;
            font-weight: 700;
            color: #053631;
        }

        .order-actions {
            padding: 20px;
            background: #f5f5f5;
            display: flex;
            gap: 10px;
        }

        .action-btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-start {
            background: linear-gradient(135deg, #ff7520 0%, #ff9147 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 117, 32, 0.4);
        }

        .btn-start:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 117, 32, 0.6);
        }

        .btn-ready {
            background: linear-gradient(135deg, #8cd003 0%, #7ab200 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(140, 208, 3, 0.4);
        }

        .btn-ready:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(140, 208, 3, 0.6);
        }

        .btn-complete {
            background: linear-gradient(135deg, #053631 0%, #0a5a4f 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(5, 54, 49, 0.4);
        }

        .btn-complete:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(5, 54, 49, 0.6);
        }

        .empty-state {
            text-align: center;
            padding: 100px 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            border: 3px dashed rgba(255, 255, 255, 0.2);
        }

        .empty-state-icon {
            font-size: 5rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state-text {
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .filter-tabs {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            background: rgba(255, 255, 255, 0.1);
            padding: 10px;
            border-radius: 15px;
        }

        .filter-tab {
            flex: 1;
            padding: 15px;
            background: transparent;
            border: 2px solid transparent;
            border-radius: 10px;
            color: white;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-tab:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .filter-tab.active {
            background: linear-gradient(135deg, #8cd003 0%, #7ab200 100%);
            border-color: #8cd003;
        }

        @media (max-width: 768px) {
            .orders-grid {
                grid-template-columns: 1fr;
            }

            .kitchen-header {
                flex-direction: column;
                gap: 20px;
            }

            .header-stats {
                width: 100%;
                justify-content: space-around;
            }
        }
    </style>
</head>
<body>
    <div class="kitchen-header">
        <h1>👨‍🍳 Kitchen Display</h1>
        <div class="header-stats">
            <div class="stat-box">
                <div class="stat-number" id="pendingCount">0</div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-box">
                <div class="stat-number" id="preparingCount">0</div>
                <div class="stat-label">Preparing</div>
            </div>
            <div class="stat-box">
                <div class="stat-number" id="readyCount">0</div>
                <div class="stat-label">Ready</div>
            </div>
        </div>
    </div>

    <div class="filter-tabs">
        <button class="filter-tab active" onclick="filterOrders('all')">All Orders</button>
        <button class="filter-tab" onclick="filterOrders('pending')">Pending</button>
        <button class="filter-tab" onclick="filterOrders('preparing')">Preparing</button>
        <button class="filter-tab" onclick="filterOrders('ready')">Ready</button>
    </div>

    <div class="orders-grid" id="ordersGrid">
        <?php foreach ($testOrders as $order): ?>
            <div class="order-card" data-status="<?php echo $order['status']; ?>" data-order-id="<?php echo $order['order_id']; ?>">
                <div class="order-header">
                    <div class="order-number">#<?php echo $order['order_number']; ?></div>
                    <div class="order-time">
                        <span class="time-badge"><?php echo $order['time']; ?></span>
                    </div>
                </div>
                <div class="order-items">
                    <?php foreach ($order['items'] as $item): ?>
                        <div class="order-item">
                            <div class="item-quantity"><?php echo $item['quantity']; ?>×</div>
                            <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="order-actions">
                    <button class="action-btn btn-start" onclick="updateStatus(<?php echo $order['order_id']; ?>, 'preparing')">
                        🔥 Start
                    </button>
                    <button class="action-btn btn-ready" onclick="updateStatus(<?php echo $order['order_id']; ?>, 'ready')">
                        ✅ Ready
                    </button>
                    <button class="action-btn btn-complete" onclick="updateStatus(<?php echo $order['order_id']; ?>, 'completed')">
                        📦 Complete
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        let currentFilter = 'all';

        function updateStatus(orderId, status) {
            const card = document.querySelector(`[data-order-id="${orderId}"]`);
            
            if (status === 'completed') {
                // Fade out and remove
                card.style.opacity = '0';
                card.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    card.remove();
                    updateCounts();
                    checkEmptyState();
                }, 300);
            } else {
                // Update status
                card.setAttribute('data-status', status);
                card.className = 'order-card ' + status;
                updateCounts();
            }

            // In production, send to backend
            console.log(`Order ${orderId} status updated to: ${status}`);
        }

        function filterOrders(filter) {
            currentFilter = filter;
            
            // Update active tab
            document.querySelectorAll('.filter-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            event.target.classList.add('active');

            // Filter cards
            const cards = document.querySelectorAll('.order-card');
            cards.forEach(card => {
                if (filter === 'all') {
                    card.style.display = 'block';
                } else {
                    const status = card.getAttribute('data-status');
                    card.style.display = status === filter ? 'block' : 'none';
                }
            });

            checkEmptyState();
        }

        function updateCounts() {
            const statuses = ['pending', 'preparing', 'ready'];
            statuses.forEach(status => {
                const count = document.querySelectorAll(`[data-status="${status}"]`).length;
                document.getElementById(status + 'Count').textContent = count;
            });
        }

        function checkEmptyState() {
            const grid = document.getElementById('ordersGrid');
            const visibleCards = Array.from(document.querySelectorAll('.order-card'))
                .filter(card => card.style.display !== 'none');

            if (visibleCards.length === 0) {
                grid.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">✨</div>
                        <div class="empty-state-text">Alle bestellingen zijn afgehandeld!</div>
                    </div>
                `;
            }
        }

        // Auto-refresh every 30 seconds (in production)
        setInterval(() => {
            console.log('Checking for new orders...');
            // location.reload(); // Uncomment in production
        }, 30000);

        // Initialize counts
        updateCounts();

        // Play sound on new order (optional)
        function playNewOrderSound() {
            const audio = new Audio('data:audio/wav;base64,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GQ0ZDRkNGQ0ZDRkNGQ0aDBoMGg0ZDRkNGQ0ZDRkNGQ0aDBoMGg0ZDRkNGQ0ZDRkNGQ0ZDBoMGg0ZDRkNGQ0ZDRkNGQ0ZDBoMGg0ZDRkNGQ0ZDRkNGQ0ZDBoMGg0ZDRkNGQ0ZDRkNGQ0ZDBoMGg0ZDRkNGQ0ZDRkNGQ0ZDBoMGg0ZDRkNGQ0ZDRkNGQ0ZDBoMGg0ZDRkNGQ0ZDRkNGQ0ZDBoMGg0ZDRkNGQ0ZDRkNGQ0ZDBoMGg0ZDRkNGQ0ZDRkNGQ0ZDBoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDBoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDBoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDBoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDBoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDBoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDBoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZDRoMGg0ZDRkNGQ0ZDRkNGQ0ZA==');
            audio.play();
        }
    </script>
</body>
</html>