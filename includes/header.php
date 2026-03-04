<?php 
include 'language.php';
$languages = getAvailableLanguages();
?>
 <!-- Header -->
  <header class="menu-header">
    <div class="header-container">
      <a href="home.php" class="logo-link">
        <div class="header-logo">
          <img src="assets/images/image.png" alt="Happy Herbivore logo" class="header-logo-img">
        </div>
      </a>
      <div class="header-cart">
        <button class="cart-btn" aria-label="<?php echo t('shopping_cart'); ?>">
          <img src="assets/images/cart.png" alt="<?php echo t('shopping_cart'); ?>" class="cart-icon-img">
          <span class="cart-count" id="cart-count">0</span>
        </button>
        <div class="cart-dropdown" id="cart-dropdown" aria-hidden="true">
          <div class="cart-items" id="cart-items"><?php echo t('empty_cart'); ?></div>
          <div class="cart-actions">
            <a href="shoppingcart.php" class="view-cart"><?php echo t('your_cart'); ?></a>
          </div>
        </div>
      </div>
      <div class="header-text">
        <h1>Happy Herbivore</h1>
        <p><?php echo t('plant_based_menu'); ?></p>
      </div>
      <div class="header-language">
        <div class="language-selector">
          <span class="language-label"><?php echo t('language'); ?>:</span>
          <?php foreach ($languages as $code => $name): ?>
            <a href="?lang=<?php echo $code; ?>" class="lang-btn <?php echo ($current_language === $code ? 'active' : ''); ?>" title="<?php echo $name; ?>">
              <?php echo strtoupper($code); ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </header>
