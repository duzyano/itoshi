<?php
// order_type.php
// Let user choose whether they will eat here or take away. Store choice in session and redirect to menu.
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $choice = $_POST['order_type'] ?? '';
    if ($choice === 'here' || $choice === 'takeaway') {
        $_SESSION['order_type'] = $choice;
    }
    header('Location: menu.php');
    exit;
}
?>
<!doctype html>
<html lang="nl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Kies Besteltype - Happy Herbivore</title>
  <link rel="stylesheet" href="assets/menu.css">
  <style>
    .type-wrap { max-width:720px; margin:40px auto; padding:20px; text-align:center }
    .type-grid { display:flex; gap:20px; justify-content:center; margin-top:24px }
    .type-card { flex:1; min-width:200px; padding:28px 20px; border-radius:12px; background:#f6f6f6; cursor:pointer }
    .type-card h3 { margin:0 0 8px }
    .btn-choose { margin-top:16px; display:inline-block; padding:10px 18px; border-radius:8px; font-weight:700; border:none }
    .here { background:#8cd003; color:#fff }
    .takeaway { background:#053631; color:#fff }
  </style>
</head>
<body class="menu-page">
  <?php include 'includes/header.php'; ?>
  <main class="menu-main">
    <div class="type-wrap">
      <h2>Kies je besteltype</h2>
      <p>Wil je hier eten of meenemen?</p>

      <div class="type-grid">
        <form method="post" style="flex:1">
          <div class="type-card">
            <h3>Hier eten</h3>
            <p>Geniet van je maaltijd bij ons in het restaurant.</p>
            <input type="hidden" name="order_type" value="here">
            <button class="btn-choose here" type="submit">Hier eten</button>
          </div>
        </form>

        <form method="post" style="flex:1">
          <div class="type-card">
            <h3>Meenemen</h3>
            <p>Neem het mee en geniet thuis of onderweg.</p>
            <input type="hidden" name="order_type" value="takeaway">
            <button class="btn-choose takeaway" type="submit">Meenemen</button>
          </div>
        </form>
      </div>
    </div>
  </main>
  <?php include 'includes/footer.php'; ?>
</body>
</html>
