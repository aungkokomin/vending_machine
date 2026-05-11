<section class="panel">
  <h1>Edit Product</h1>
  <form action="/products/<?= (int) $product['id'] ?>/update" method="post" class="form" novalidate>
    <?php require __DIR__ . '/form.php'; ?>
    <button type="submit">Save</button>
  </form>
</section>
