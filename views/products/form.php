<?php
$errors ??= [];
$product ??= [];
?>
<div class="form-grid">
  <label>
    Name
    <input name="name" value="<?= htmlspecialchars((string) ($product['name'] ?? '')) ?>" required>
    <?php if (isset($errors['name'])): ?><span class="field-error"><?= htmlspecialchars($errors['name']) ?></span><?php endif; ?>
  </label>
  <label>
    Price
    <input type="number" name="price" min="0.01" step="0.01" value="<?= htmlspecialchars((string) ($product['price'] ?? '')) ?>" required>
    <?php if (isset($errors['price'])): ?><span class="field-error"><?= htmlspecialchars($errors['price']) ?></span><?php endif; ?>
  </label>
  <label>
    Quantity
    <input type="number" name="quantity" min="0" step="1" value="<?= htmlspecialchars((string) ($product['quantity'] ?? '')) ?>" required>
    <?php if (isset($errors['quantity'])): ?><span class="field-error"><?= htmlspecialchars($errors['quantity']) ?></span><?php endif; ?>
  </label>
  <label class="full">
    Description
    <textarea name="description" rows="4"><?= htmlspecialchars((string) ($product['description'] ?? '')) ?></textarea>
  </label>
</div>
