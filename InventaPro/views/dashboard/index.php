<?php
$titulo = 'Dashboard · InventaPro';
require __DIR__ . '/../layouts/base.php';
?>
  <div class="page-header">
    <div>
      <span class="page-eyebrow">Overview</span>
      <h1>Hi, <?= htmlspecialchars($usuarioActual['nombre']) ?></h1>
    </div>
  </div>

  <?php if (isset($_GET['ok'])): ?>
    <p class="msg-ok">Operation completed successfully.</p>
  <?php endif; ?>

  <div class="kpi-grid">
    <a href="index.php?accion=productos" class="kpi-card kpi-card-link">
      <img src="public/productos.png" alt="" class="kpi-ico-img">
      <span class="kpi-value"><?= $totalProductos ?></span>
      <span class="kpi-label">Registered products</span>
    </a>
    <a href="index.php?accion=productos" class="kpi-card kpi-card-link <?= count($alertas) > 0 ? 'kpi-alert' : '' ?>">
      <img src="public/alerta.png" alt="" class="kpi-ico-img">
      <span class="kpi-value"><?= count($alertas) ?></span>
      <span class="kpi-label">Active stock alerts</span>
    </a>
    <?php if (in_array($rolActual, ['Administrator', 'Cashier'], true)): ?>
    <a href="index.php?accion=salidas" class="kpi-card kpi-card-link">
      <img src="public/salidas.png" alt="" class="kpi-ico-img">
      <span class="kpi-value"><?= $salidasHoy ?></span>
      <span class="kpi-label">Sales registered today</span>
    </a>
    <?php else: ?>
    <div class="kpi-card">
      <img src="public/salidas.png" alt="" class="kpi-ico-img">
      <span class="kpi-value"><?= $salidasHoy ?></span>
      <span class="kpi-label">Sales registered today</span>
    </div>
    <?php endif; ?>
  </div>

  <?php if (!empty($ventasPorDia) || !empty($top5)): ?>
  <div class="dash-grid">
    <?php if (!empty($ventasPorDia)): ?>
    <div class="panel">
      <h2 class="sub-h" style="margin-top:0">Sales over the last 7 days</h2>
      <?php require __DIR__ . '/../partials/grafica-barras.php'; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($top5)): ?>
    <div class="panel">
      <div class="panel-head">
        <h2>🏆 Top 5 products</h2>
        <a href="index.php?accion=informes" class="btn btn-ghost btn-sm">View reports</a>
      </div>
      <div class="top5-lista">
        <?php foreach ($top5 as $i => $p): ?>
          <div class="top5-fila">
            <span class="top5-rank">#<?= $i + 1 ?></span>
            <span class="top5-nombre"><?= htmlspecialchars($p['nombre']) ?></span>
            <span class="top5-cantidad"><?= (int) $p['total_vendido'] ?> sold</span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <div class="panel">
    <div class="panel-head">
      <h2>Products at or below minimum stock</h2>
      <?php if (in_array($rolActual, ['Administrator', 'Warehouse Clerk'], true)): ?>
        <a href="index.php?accion=entradas" class="btn btn-ghost btn-sm">Register stock-in</a>
      <?php endif; ?>
    </div>

    <?php if (empty($alertas)): ?>
      <p class="empty-inline">No products with a stock alert right now. 🎉</p>
    <?php else: ?>
      <table class="tbl">
        <thead>
          <tr><th>Product</th><th>Current stock</th><th>Minimum stock</th><th>Status</th></tr>
        </thead>
        <tbody>
          <?php foreach ($alertas as $a): ?>
            <tr>
              <td><?= htmlspecialchars($a['nombre']) ?></td>
              <td><?= $a['stock_actual'] ?></td>
              <td><?= $a['stock_minimo'] ?></td>
              <td>
                <?php if ($a['stock_actual'] <= 0): ?>
                  <span class="badge badge-danger">Out of stock</span>
                <?php else: ?>
                  <span class="badge badge-warning">Low stock</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
<?php require __DIR__ . '/../layouts/base-fin.php'; ?>
