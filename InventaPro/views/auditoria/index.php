<?php
$titulo = 'Audit Log · InventaPro';
require __DIR__ . '/../layouts/base.php';
?>
  <div class="page-header">
    <div>
      <span class="page-eyebrow">Traceability</span>
      <h1>Audit log</h1>
    </div>
  </div>

  <div class="panel">
    <table class="tbl">
      <thead>
        <tr><th>Date and time</th><th>User</th><th>Action</th><th>Affected table</th></tr>
      </thead>
      <tbody>
      <?php if (empty($registros)): ?>
        <tr><td colspan="4">No actions logged yet.</td></tr>
      <?php endif; ?>
      <?php foreach ($registros as $r): ?>
        <tr>
          <td class="mono"><?= date('d/m/Y H:i:s', strtotime($r['fecha_hora'])) ?></td>
          <td><?= htmlspecialchars($r['usuario']) ?></td>
          <td><?= htmlspecialchars($r['accion']) ?></td>
          <td><?= $r['tabla_afectada'] ? '<span class="badge badge-neutral">' . htmlspecialchars($r['tabla_afectada']) . '</span>' : '—' ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php require __DIR__ . '/../layouts/base-fin.php'; ?>
