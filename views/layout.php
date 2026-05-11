<?php
$authUser ??= null;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Vending Machine</title>
  <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
  <header class="topbar">
    <a class="brand" href="/products">Vending Machine</a>
    <nav>
      <a href="/products">Products</a>
      <?php if (($authUser['role'] ?? null) === 'Admin'): ?>
        <a href="/products/create">Add Product</a>
      <?php endif; ?>
      <?php if ($authUser): ?>
        <span><?= htmlspecialchars($authUser['name']) ?> (<?= htmlspecialchars($authUser['role']) ?>)</span>
        <form action="/logout" method="post" class="inline-form">
          <button type="submit">Logout</button>
        </form>
      <?php else: ?>
        <a href="/login">Login</a>
      <?php endif; ?>
    </nav>
  </header>
  <main class="container">
    <?= $content ?>
  </main>
</body>
</html>
