<?php $titulo = 'Access denied'; require __DIR__ . '/../layouts/base.php'; ?>
  <div class="empty-state">
    <span class="empty-icon">🔒</span>
    <h2>Access denied</h2>
    <p>Your role (<?= htmlspecialchars(currentUser()['rol'] ?? '') ?>) does not have permission to view this section.</p>
    <a href="index.php?accion=dashboard" class="btn btn-primary">Back to dashboard</a>
  </div>
<?php require __DIR__ . '/../layouts/base-fin.php'; ?>
