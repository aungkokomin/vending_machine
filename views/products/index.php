<?php
$nextDirection = $direction === 'ASC' ? 'DESC' : 'ASC';
function sort_url(string $field, string $nextDirection): string
{
    return '/products?sort=' . urlencode($field) . '&direction=' . urlencode($nextDirection);
}
?>
<section>
  <div class="page-head">
    <div>
      <h1>Products</h1>
      <?php if (isset($_GET['purchase'])): ?><p class="success">Purchase completed.</p><?php endif; ?>
    </div>
    <?php if (($authUser['role'] ?? null) === 'Admin'): ?>
      <a class="button" href="/products/create">Add Product</a>
    <?php endif; ?>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th><a href="<?= sort_url('name', $nextDirection) ?>">Name</a></th>
        <th>Description</th>
        <th><a href="<?= sort_url('price', $nextDirection) ?>">Price</a></th>
        <th><a href="<?= sort_url('quantity', $nextDirection) ?>">Quantity</a></th>
        <?php if ($authUser): ?>
        <th>Actions</th>
        <?php endif; ?>
        
      </tr>
    </thead>
    <tbody>
      <?php foreach ($products as $product): ?>
        <tr>
          <td><?= htmlspecialchars($product['name']) ?></td>
          <td><?= htmlspecialchars((string) $product['description']) ?></td>
          <td>$<?= number_format((float) $product['price'], 2) ?></td>
          <td><?= (int) $product['quantity'] ?></td>
          <td class="actions">
            <?php if ($authUser): ?>
              <a href="/products/<?= (int) $product['id'] ?>/purchase">Purchase</a>
            <?php endif; ?>
            <?php if (($authUser['role'] ?? null) === 'Admin'): ?>
              <a href="/products/<?= (int) $product['id'] ?>/edit">Edit</a>
              <form action="/products/<?= (int) $product['id'] ?>/delete" method="post" class="inline-form">
                <button type="submit">Delete</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="pagination">
    <?php for ($i = 1; $i <= $pages; $i++): ?>
      <a class="<?= $i === $page ? 'active' : '' ?>" href="/products?page=<?= $i ?>&sort=<?= urlencode($sort) ?>&direction=<?= urlencode($direction) ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
</section>
