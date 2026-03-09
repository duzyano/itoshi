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

      <div class="header-text">
        <h1>Happy Herbivore</h1>
        <p><?php echo t('plant_based_menu'); ?></p>
      </div>
      <?php if (basename($_SERVER['SCRIPT_NAME']) === 'menu.php'): ?>
        </div>
      </div>
      <?php endif; ?>
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
