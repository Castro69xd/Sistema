<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Log in · InventaPro</title>
<link rel="stylesheet" href="public/css/styles.css">
</head>
<body class="auth-body">
  <div class="auth-page">
    <div class="auth-card">
      <div class="auth-brand">
        <img src="public/logo.png" alt="InventaPro" class="brand-icon-img brand-icon-img-lg">
        <span class="brand-name">Inventa<strong>Pro</strong></span>
      </div>
      <p class="auth-sub">Inventory control system</p>

      <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="index.php?accion=login">
        <label>Username
          <input type="text" name="usuario" value="<?= htmlspecialchars($usuario) ?>" autofocus required>
        </label>
        <label>Password
          <input type="password" name="password" required>
        </label>
        <button type="submit" class="btn btn-primary btn-block">Log in</button>
      </form>

      <div class="auth-demo">
        <p>Demo users:</p>
        <ul>
          <li><strong>admin</strong> / Admin2026! <span class="tag-rol">Administrator</span></li>
          <li><strong>bodeguero</strong> / Bodega2026! <span class="tag-rol">Warehouse Clerk</span></li>
          <li><strong>cajero</strong> / Caja2026! <span class="tag-rol">Cashier</span></li>
        </ul>
      </div>
    </div>
  </div>
</body>
</html>
