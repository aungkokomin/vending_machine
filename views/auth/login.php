<section class="panel narrow">
  <h1>Login</h1>
  <?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>
  <form action="/login" method="post" class="form">
    <label>
      Email
      <input type="email" name="email" required>
    </label>
    <label>
      Password
      <input type="password" name="password" required>
    </label>
    <button type="submit">Login</button>
  </form>
</section>
