<?php
$titulo = 'Sales · InventaPro';
require __DIR__ . '/../layouts/base.php';
?>
  <div class="page-header">
    <div>
      <span class="page-eyebrow">Sales</span>
      <h1>Sales · <?= count($salidas) ?></h1>
    </div>
    <a href="index.php?accion=salida-nueva" class="btn btn-primary">+ New sale</a>
  </div>

  <?php if (isset($_GET['ok'])): ?>
    <p class="msg-ok">Sale registered and stock updated successfully.</p>
  <?php endif; ?>

  <div class="panel">
    <table class="tbl">
      <thead>
        <tr><th>#</th><th>Date</th><th>Customer</th><th>Registered by</th><th>Products</th><th>Total</th><th></th></tr>
      </thead>
      <tbody>
      <?php if (empty($salidas)): ?>
        <tr><td colspan="7">No sales registered yet.</td></tr>
      <?php endif; ?>
      <?php foreach ($salidas as $s): ?>
        <tr>
          <td class="mono">#<?= $s['id_salida'] ?></td>
          <td><?= date('d/m/Y H:i', strtotime($s['fecha'])) ?></td>
          <td><?= htmlspecialchars($s['cliente_nombre'] ?: 'Walk-in customer') ?></td>
          <td><?= htmlspecialchars($s['usuario']) ?></td>
          <td><?= $s['items'] ?> product(s)</td>
          <td>$<?= number_format($s['total'], 2) ?></td>
          <td><a href="index.php?accion=factura&id=<?= $s['id_salida'] ?>" class="btn btn-primary btn-sm" target="_blank">View invoice</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php require __DIR__ . '/../layouts/base-fin.php'; ?>
