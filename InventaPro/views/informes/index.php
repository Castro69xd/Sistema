<?php
$titulo = 'Reports · InventaPro';
require __DIR__ . '/../layouts/base.php';
?>
  <div class="page-header">
    <div>
      <span class="page-eyebrow">Sales analytics</span>
      <h1>Reports</h1>
    </div>
  </div>

  <div class="kpi-grid">
    <div class="kpi-card">
      <span class="kpi-value"><?= number_format($resumen['unidades_vendidas']) ?></span>
      <span class="kpi-label">Units sold (all-time)</span>
    </div>
    <div class="kpi-card">
      <span class="kpi-value">$<?= number_format($resumen['ingresos_totales'], 2) ?></span>
      <span class="kpi-label">Total revenue</span>
    </div>
    <div class="kpi-card kpi-ganancia">
      <span class="kpi-value">$<?= number_format($resumen['ganancias_totales'], 2) ?></span>
      <span class="kpi-label">Total profit</span>
    </div>
  </div>

  <div class="panel">
    <h2 class="sub-h" style="margin-top:0">Sales over the last 14 days</h2>
    <?php if (empty($ventasPorDia)): ?>
      <p class="empty-inline">No sales registered in this period yet.</p>
    <?php else: ?>
      <?php require __DIR__ . '/../partials/grafica-barras.php'; ?>
    <?php endif; ?>
  </div>

  <div class="informes-grid">
    <div class="panel">
      <h2 class="sub-h" style="margin-top:0">🏆 Best-selling products</h2>
      <?php if (empty($masVendidos)): ?>
        <p class="empty-inline">No sales registered yet.</p>
      <?php else: ?>
        <table class="tbl">
          <thead><tr><th>#</th><th>Product</th><th>Units sold</th><th>Revenue</th><th>Profit</th></tr></thead>
          <tbody>
            <?php foreach ($masVendidos as $i => $p): ?>
              <tr>
                <td class="mono">#<?= $i + 1 ?></td>
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td><?= (int) $p['total_vendido'] ?></td>
                <td>$<?= number_format($p['ingresos'], 2) ?></td>
                <td class="texto-ganancia">$<?= number_format($p['ganancia'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <div class="panel">
      <h2 class="sub-h" style="margin-top:0">📉 Slowest-moving products</h2>
      <?php if (empty($menosVendidos)): ?>
        <p class="empty-inline">No products registered.</p>
      <?php else: ?>
        <table class="tbl">
          <thead><tr><th>Product</th><th>Units sold</th></tr></thead>
          <tbody>
            <?php foreach ($menosVendidos as $p): ?>
              <tr>
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td>
                  <?= (int) $p['total_vendido'] ?>
                  <?php if ((int) $p['total_vendido'] === 0): ?>
                    <span class="badge badge-warning">Never sold</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>

<?php require __DIR__ . '/../layouts/base-fin.php'; ?>
