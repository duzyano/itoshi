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

    <div id="bg-carousel">
    <div class="bg-slide active"></div>
    <div class="bg-slide"></div>
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
  <script src="assets/background-carousel.js"></script>
</body>

</html>