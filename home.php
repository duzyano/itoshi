<?php include 'includes/language.php'; ?>
<!doctype html>
<html lang="<?php echo $current_language; ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Happy Herbivore</title>

  <!-- Noto Sans from Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css"> 
</head>
<body>
  <main class="hero">
    <div class="logo-wrap">
      <!-- Animated leaf (SVG) -->
      <div class="leaf" aria-hidden="true">
        <svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" role="img">
          <circle cx="32" cy="32" r="32" fill="none"></circle>
          <path d="M20 36c6-12 24-18 28-12-6 6-8 22-20 24-6-2-9-8-8-12z" fill="#fff"/>
        </svg>
      </div>
      <!-- Logo image (animated) -->
      <img class="logo-img" src="assets/images/image.png" alt="logo image" aria-hidden="true">
    </div>

    <div class="logo-text">
      <h1><?php echo t('welcome'); ?></h1>
      <p class="lead"><?php echo t('subtitle'); ?></p>
      <a class="cta" href="order_type.php"><?php echo t('cta'); ?> &#8594;</a>
      <footer><?php echo t('footer_tagline'); ?></footer>
    </div>
  </main>
  <script src="assets/language.js"></script>
</body>
</html>
