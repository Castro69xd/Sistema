<?php
$titulo = 'Stock In · InventaPro';
require __DIR__ . '/../layouts/base.php';
?>
  <div class="page-header">
    <div>
      <span class="page-eyebrow">Incoming stock</span>
      <h1>Stock In · <?= count($entradas) ?></h1>
    </div>
    <a href="index.php?accion=entrada-crear" class="btn btn-primary">+ Register entry</a>
  </div>

  <?php if (isset($_GET['ok'])): ?>
    <p class="msg-ok">Entry registered and stock updated successfully.</p>
  <?php endif; ?>

  <div class="panel">
    <table class="tbl">
      <thead>
        <tr><th>#</th><th>Date</th><th>Supplier</th><th>Registered by</th><th>Products</th></tr>
      </thead>
      <tbody>
      <?php if (empty($entradas)): ?>
        <tr><td colspan="5">No entries registered yet.</td></tr>
      <?php endif; ?>
      <?php foreach ($entradas as $e): ?>
        <tr>
          <td class="mono">#<?= $e['id_entrada'] ?></td>
          <td><?= date('d/m/Y H:i', strtotime($e['fecha'])) ?></td>
          <td><?= htmlspecialchars($e['proveedor']) ?></td>
          <td><?= htmlspecialchars($e['usuario']) ?></td>
          <td><?= $e['items'] ?> product(s)</td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php require __DIR__ . '/../layouts/base-fin.php'; ?>
