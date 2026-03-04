<?php
// menu.php - Now loads products from API
// Products are fetched client-side from /api/products
include 'includes/language.php';
$all_products = []; // Placeholder for compatibility with cart.js
?>

<!doctype html>
<html lang="<?php echo $current_language; ?>">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?php echo t('menu_title'); ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/menu.css">
</head>

<body class="menu-page">
  <?php include 'includes/header.php'; ?>

  <!-- Main Menu Content - Loaded from API -->
  <main class="menu-main">
    <div class="menu-container" id="menu-container">
      <!-- Products loaded by JavaScript from /api/products -->
      <div style="text-align: center; padding: 40px;">
        <p><?php echo t('loading_products'); ?></p>
      </div>
    </div>
  </main>
  <?php include 'includes/footer.php'; ?>
  <script src="assets/language.js"></script>
  <script>
    // Pass current language to menu-api-loader.js
    window.currentLanguage = '<?php echo $current_language; ?>';
  </script>
  <script src="assets/menu-api-loader.js"></script>
  <script src="assets/kiosk.js"></script>
</body>

</html>