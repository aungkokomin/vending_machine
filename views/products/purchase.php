<section class="panel narrow">
  <h1>Purchase <?= htmlspecialchars($product['name']) ?></h1>
  <dl class="details">
    <dt>Price</dt>
    <dd>$<?= number_format((float) $product['price'], 2) ?></dd>
    <dt>Available</dt>
    <dd><?= (int) $product['quantity'] ?></dd>
  </dl>
  <form action="/products/<?= (int) $product['id'] ?>/purchase" method="post" class="form" novalidate>
    <label>
      Quantity
      <input type="number" name="quantity" min="1" max="<?= (int) $product['quantity'] ?>" required>
      <?php if (isset($errors['quantity'])): ?><span class="field-error"><?= htmlspecialchars($errors['quantity']) ?></span><?php endif; ?>
    </label>
    <button type="submit">Purchase</button>
  </form>
</section>
