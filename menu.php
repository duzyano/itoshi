<?php 
require_once 'connection.php';

// Haal producten op uit de database, met categorie- en image-gegevens
$products = [];
$categories_list = [];

try {
    $stmt = $conn->prepare(
        "SELECT p.*, c.name AS category_name, i.filename AS image_filename
         FROM products p
         JOIN categories c ON p.category_id = c.category_id
         LEFT JOIN images i ON p.image_id = i.image_id
         ORDER BY c.name, p.name"
    );
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $row) {
        $rawCategory = $row['category_name'] ?? 'uncategorized';
        $categoryKey = strtolower(trim($rawCategory));
        $categoryKey = str_replace(' ', '_', $categoryKey);

        if (!isset($products[$categoryKey])) {
            $products[$categoryKey] = [];
            $categories_list[] = ['key' => $categoryKey, 'display' => $rawCategory];
        }

        $imageFilename = $row['image_filename'] ?? '';
        $imagePath = !empty($imageFilename) ? 'assets/images/' . $imageFilename : 'assets/images/image.png';

        $dietary = '';
        if ($row['is_vegan'] == 1) $dietary = 'VG';
        elseif ($row['is_vlees'] == 1) $dietary = 'V';

        $products[$categoryKey][] = [
            'product_id' => $row['product_id'],
            'name' => $row['name'],
            'description' => $row['description'],
            'price' => $row['price'],
            'kcal' => $row['kcal'],
            'dietary' => $dietary,
            'image' => $imagePath,
            'category' => $rawCategory  // Add category to product data
        ];
    }
} catch (PDOException $e) {
    die("Fout bij ophalen producten: " . $e->getMessage());
}

// Upsells - AANGEPAST: inclusief category van het upsell product
$upsellRules = [];
try {
    $stmt = $conn->prepare(
        "SELECT u.product_id, u.upsell_product_id,
                p.name AS upsell_name, p.price AS upsell_price, 
                p.description AS upsell_desc, i.filename AS upsell_image,
                c.name AS upsell_category
         FROM upsells u
         JOIN products p ON u.upsell_product_id = p.product_id
         LEFT JOIN images i ON p.image_id = i.image_id
         LEFT JOIN categories c ON p.category_id = c.category_id"
    );
    $stmt->execute();
    $upsellResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($upsellResults as $row) {
        if (!isset($upsellRules[$row['product_id']])) $upsellRules[$row['product_id']] = [];

        $imagePath = !empty($row['upsell_image']) ? 'assets/images/' . $row['upsell_image'] : 'assets/images/image.png';
        $upsellRules[$row['product_id']][] = [
            'id' => $row['upsell_product_id'],
            'name' => $row['upsell_name'],
            'price' => $row['upsell_price'],
            'description' => $row['upsell_desc'],
            'image' => $imagePath,
            'category' => $row['upsell_category']  // BELANGRIJK: category toevoegen
        ];
    }
} catch (PDOException $e) {
    $upsellRules = [];
}

?>

<!doctype html>
<html lang="nl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Menu - Happy Herbivore</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/menu.css">
</head>
<body class="menu-page">

<?php include 'includes/header.php'; ?>

<!-- Category Navigation -->
<nav class="category-nav">
  <div class="category-nav-container">
    <?php
    $categoryIcons = [
      'breakfast' => '🌅',
      'lunch_dinner' => '🍽️',
      'wraps_sandwiches' => '🌯',
      'sides' => '🥗',
      'dips' => '🥫',
      'drinks' => '🥤'
    ];

    foreach ($categories_list as $cat):
        $icon = $categoryIcons[$cat['key']] ?? '🍴';
        $label = str_replace('_',' ',$cat['display']);
    ?>
      <button class="category-btn" data-category="<?php echo htmlspecialchars($cat['key']); ?>">
        <span class="cat-icon"><?php echo $icon; ?></span>
        <span class="cat-label"><?php echo htmlspecialchars($label); ?></span>
      </button>
    <?php endforeach; ?>
  </div>
</nav>

<!-- Main Menu -->
<main class="menu-main">
  <div class="menu-container">
    <?php foreach ($products as $categoryKey => $items): ?>
      <section class="menu-section" data-category="<?php echo $categoryKey; ?>">
        <h2 class="section-title" data-category="<?php echo $categoryKey; ?>">
          <?php echo str_replace('_',' ',$categoryKey); ?>
        </h2>
        <div class="products-grid">
          <?php foreach ($items as $product): ?>
            <div class="product-card">
              <div class="product-header">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <?php if(!empty($product['dietary'])): ?>
                  <span class="dietary-badge <?php echo strtolower($product['dietary']); ?>">
                    <?php echo htmlspecialchars($product['dietary']); ?>
                  </span>
                <?php endif; ?>
              </div>
              <div class="product-image">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
              </div>
              <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
              <div class="product-footer">
                <div class="product-meta">
                  <span class="price">€<?php echo number_format($product['price'],2); ?></span>
                  <span class="kcal"><?php echo htmlspecialchars($product['kcal']); ?> kcal</span>
                </div>
                <button class="add-btn" 
                        data-id="<?php echo htmlspecialchars($product['product_id']); ?>"
                        data-name="<?php echo htmlspecialchars($product['name']); ?>"
                        data-price="<?php echo htmlspecialchars($product['price']); ?>"
                        data-image="<?php echo htmlspecialchars($product['image']); ?>">
                  + Add
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endforeach; ?>
  </div>
</main>

<!-- Live cart container -->
<div class="live-cart" id="live-cart"></div>

<!-- Upsell Data - INCLUSIEF CATEGORY -->
<script id="upsell-data" type="application/json">
  <?php echo json_encode($upsellRules); ?>
</script>

<script src="assets/language.js"></script>
<script src="assets/kiosk.js"></script>

</body>
</html>