<?php
require_once 'connection.php';  // Gebruik de databaseverbinding in plaats van products.php

// Haal producten op uit de database, met categorie- en image-gegevens
$products = [];
try {
  // JOIN products met categories en images (images tabel bevat bestandsnamen)
  $stmt = $conn->prepare(
    "SELECT p.*, c.name AS category_name, i.filename AS image_filename
     FROM products p
     JOIN categories c ON p.category_id = c.category_id
     LEFT JOIN images i ON p.image_id = i.image_id
     ORDER BY c.name, p.name"
  );
  $stmt->execute();
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Groepeer resultaten per genormaliseerde categorie-key (lowercase, spaties -> underscores)
  foreach ($results as $row) {
    $rawCategory = $row['category_name'] ?? 'uncategorized';
    $categoryKey = strtolower(trim($rawCategory));
    $categoryKey = str_replace(' ', '_', $categoryKey);

    if (!isset($products[$categoryKey])) {
      $products[$categoryKey] = [];
    }

    // Bepaal image pad: gebruik images.filename wanneer beschikbaar, anders fallback
    $imageFilename = $row['image_filename'] ?? '';
    if (!empty($imageFilename)) {
      $imagePath = 'assets/images/' . $imageFilename;
    } else {
      $imagePath = 'assets/images/image.png';
    }

    $products[$categoryKey][] = [
      'name' => $row['name'],
      'description' => $row['description'],
      'price' => $row['price'],
      'kcal' => $row['kcal'],
      'dietary' => $row['dietary'] ?? '',
      'image' => $imagePath
    ];
  }
} catch (PDOException $e) {
  die("Fout bij ophalen producten: " . $e->getMessage());
}

// Flatten products voor eenvoudigere iteratie (zoals in je originele code)
$all_products = [];
foreach ($products as $category => $items) {
  $all_products = array_merge($all_products, $items);
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

  <!-- Main Menu Content -->
  <main class="menu-main">
    <div class="menu-container">
      <!-- Breakfast Section -->
      <section class="menu-section">
        <h2 class="section-title">Breakfast</h2>
        <div class="products-grid">
          <?php if (isset($products['breakfast'])): ?>
            <?php foreach ($products['breakfast'] as $product): ?>
              <div class="product-card">
                <div class="product-header">
                  <h3>
                    <?php echo htmlspecialchars($product['name']); ?>
                  </h3>
                  <span class="dietary-badge <?php echo strtolower($product['dietary']); ?>">
                    <?php echo htmlspecialchars($product['dietary']); ?>
                  </span>
                </div>
                <?php if (!empty($product['image'])): ?>
                  <div class="product-image">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>"
                      alt="<?php echo htmlspecialchars($product['name']); ?>">
                  </div>
                <?php endif; ?>
                <p class="product-description">
                  <?php echo htmlspecialchars($product['description']); ?>
                </p>
                <div class="product-footer">
                  <div class="product-meta">
                    <span class="price">€
                      <?php echo number_format($product['price'], 2); ?>
                    </span>
                    <span class="kcal">
                      <?php echo htmlspecialchars($product['kcal']); ?> kcal
                    </span>
                  </div>
                  <button class="add-btn" data-name="<?php echo htmlspecialchars($product['name']); ?>"
                    data-price="<?php echo htmlspecialchars($product['price']); ?>"
                    data-image="<?php echo htmlspecialchars($product['image']); ?>">
                    + Add
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>Geen producten beschikbaar in deze categorie.</p>
          <?php endif; ?>
        </div>
      </section>

      <!-- Lunch & Dinner Section -->
      <section class="menu-section">
        <h2 class="section-title">Lunch & Dinner</h2>
        <div class="products-grid">
          <?php if (isset($products['lunch_dinner'])): ?>
            <?php foreach ($products['lunch_dinner'] as $product): ?>
              <div class="product-card">
                <div class="product-header">
                  <h3>
                    <?php echo htmlspecialchars($product['name']); ?>
                  </h3>
                  <span class="dietary-badge <?php echo strtolower($product['dietary']); ?>">
                    <?php echo htmlspecialchars($product['dietary']); ?>
                  </span>
                </div>
                <?php if (!empty($product['image'])): ?>
                  <div class="product-image">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>"
                      alt="<?php echo htmlspecialchars($product['name']); ?>">
                  </div>
                <?php endif; ?>
                <p class="product-description">
                  <?php echo htmlspecialchars($product['description']); ?>
                </p>
                <div class="product-footer">
                  <div class="product-meta">
                    <span class="price">€
                      <?php echo number_format($product['price'], 2); ?>
                    </span>
                    <span class="kcal">
                      <?php echo htmlspecialchars($product['kcal']); ?> kcal
                    </span>
                  </div>
                  <button class="add-btn" data-name="<?php echo htmlspecialchars($product['name']); ?>"
                    data-price="<?php echo htmlspecialchars($product['price']); ?>"
                    data-image="<?php echo htmlspecialchars($product['image']); ?>">
                    + Add
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>Geen producten beschikbaar in deze categorie.</p>
          <?php endif; ?>
        </div>
      </section>

      <!-- Wraps & Sandwiches Section -->
      <section class="menu-section">
        <h2 class="section-title">Wraps & Sandwiches</h2>
        <div class="products-grid">
          <?php if (isset($products['wraps_sandwiches'])): ?>
            <?php foreach ($products['wraps_sandwiches'] as $product): ?>
              <div class="product-card">
                <div class="product-header">
                  <h3>
                    <?php echo htmlspecialchars($product['name']); ?>
                  </h3>
                  <span class="dietary-badge <?php echo strtolower($product['dietary']); ?>">
                    <?php echo htmlspecialchars($product['dietary']); ?>
                  </span>
                </div>
                <?php if (!empty($product['image'])): ?>
                  <div class="product-image">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>"
                      alt="<?php echo htmlspecialchars($product['name']); ?>">
                  </div>
                <?php endif; ?>
                <p class="product-description">
                  <?php echo htmlspecialchars($product['description']); ?>
                </p>
                <div class="product-footer">
                  <div class="product-meta">
                    <span class="price">€
                      <?php echo number_format($product['price'], 2); ?>
                    </span>
                    <span class="kcal">
                      <?php echo htmlspecialchars($product['kcal']); ?> kcal
                    </span>
                  </div>
                  <button class="add-btn" data-name="<?php echo htmlspecialchars($product['name']); ?>"
                    data-price="<?php echo htmlspecialchars($product['price']); ?>"
                    data-image="<?php echo htmlspecialchars($product['image']); ?>">
                    + Add
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>Geen producten beschikbaar in deze categorie.</p>
          <?php endif; ?>
        </div>
      </section>

      <!-- Sides Section -->
      <section class="menu-section">
        <h2 class="section-title">Sides</h2>
        <div class="products-grid">
          <?php if (isset($products['sides'])): ?>
            <?php foreach ($products['sides'] as $product): ?>
              <div class="product-card">
                <div class="product-header">
                  <h3>
                    <?php echo htmlspecialchars($product['name']); ?>
                  </h3>
                  <span class="dietary-badge <?php echo strtolower($product['dietary']); ?>">
                    <?php echo htmlspecialchars($product['dietary']); ?>
                  </span>
                </div>
                <?php if (!empty($product['image'])): ?>
                  <div class="product-image">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>"
                      alt="<?php echo htmlspecialchars($product['name']); ?>">
                  </div>
                <?php endif; ?>
                <p class="product-description">
                  <?php echo htmlspecialchars($product['description']); ?>
                </p>
                <div class="product-footer">
                  <div class="product-meta">
                    <span class="price">€
                      <?php echo number_format($product['price'], 2); ?>
                    </span>
                    <span class="kcal">
                      <?php echo htmlspecialchars($product['kcal']); ?> kcal
                    </span>
                  </div>
                  <button class="add-btn" data-name="<?php echo htmlspecialchars($product['name']); ?>"
                    data-price="<?php echo htmlspecialchars($product['price']); ?>"
                    data-image="<?php echo htmlspecialchars($product['image']); ?>">
                    + Add
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>Geen producten beschikbaar in deze categorie.</p>
          <?php endif; ?>
        </div>
      </section>

      <!-- Dips Section -->
      <section class="menu-section">
        <h2 class="section-title">Dips</h2>
        <div class="products-grid">
          <?php if (isset($products['dips'])): ?>
            <?php foreach ($products['dips'] as $product): ?>
              <div class="product-card">
                <div class="product-header">
                  <h3>
                    <?php echo htmlspecialchars($product['name']); ?>
                  </h3>
                  <span class="dietary-badge <?php echo strtolower($product['dietary']); ?>">
                    <?php echo htmlspecialchars($product['dietary']); ?>
                  </span>
                </div>
                <?php if (!empty($product['image'])): ?>
                  <div class="product-image">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>"
                      alt="<?php echo htmlspecialchars($product['name']); ?>">
                  </div>
                <?php endif; ?>
                <p class="product-description">
                  <?php echo htmlspecialchars($product['description']); ?>
                </p>
                <div class="product-footer">
                  <div class="product-meta">
                    <span class="price">€
                      <?php echo number_format($product['price'], 2); ?>
                    </span>
                    <span class="kcal">
                      <?php echo htmlspecialchars($product['kcal']); ?> kcal
                    </span>
                  </div>
                  <button class="add-btn" data-name="<?php echo htmlspecialchars($product['name']); ?>"
                    data-price="<?php echo htmlspecialchars($product['price']); ?>"
                    data-image="<?php echo htmlspecialchars($product['image']); ?>">
                    + Add
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>Geen producten beschikbaar in deze categorie.</p>
          <?php endif; ?>
        </div>
      </section>

      <!-- Drinks Section -->
      <section class="menu-section">
        <h2 class="section-title">Drinks</h2>
        <div class="products-grid">
          <?php if (isset($products['drinks'])): ?>
            <?php foreach ($products['drinks'] as $product): ?>
              <div class="product-card">
                <div class="product-header">
                  <h3>
                    <?php echo htmlspecialchars($product['name']); ?>
                  </h3>
                  <span class="dietary-badge <?php echo strtolower($product['dietary']); ?>">
                    <?php echo htmlspecialchars($product['dietary']); ?>
                  </span>
                </div>
                <?php if (!empty($product['image'])): ?>
                  <div class="product-image">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>"
                      alt="<?php echo htmlspecialchars($product['name']); ?>">
                  </div>
                <?php endif; ?>
                <p class="product-description">
                  <?php echo htmlspecialchars($product['description']); ?>
                </p>
                <div class="product-footer">
                  <div class="product-meta">
                    <span class="price">€
                      <?php echo number_format($product['price'], 2); ?>
                    </span>
                    <span class="kcal">
                      <?php echo htmlspecialchars($product['kcal']); ?> kcal
                    </span>
                  </div>
                  <button class="add-btn" data-name="<?php echo htmlspecialchars($product['name']); ?>"
                    data-price="<?php echo htmlspecialchars($product['price']); ?>"
                    data-image="<?php echo htmlspecialchars($product['image']); ?>">
                    + Add
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>Geen producten beschikbaar in deze categorie.</p>
          <?php endif; ?>
        </div>
      </section>

    </div>
  </main>
  <?php include 'includes/footer.php'; ?>
  <script src="assets/kiosk.js"></script>
</body>

</html>