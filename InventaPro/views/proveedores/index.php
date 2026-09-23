<?php
$titulo = 'Suppliers · InventaPro';
require __DIR__ . '/../layouts/base.php';
?>
  <div class="page-header">
    <div>
      <span class="page-eyebrow">Suppliers</span>
      <h1>Suppliers · <?= count($proveedores) ?></h1>
    </div>
    <a href="index.php?accion=proveedor-crear" class="btn btn-primary">+ New supplier</a>
  </div>

  <?php if (isset($_GET['ok'])): ?>
    <p class="msg-ok">Operation completed successfully.</p>
  <?php endif; ?>

  <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error"><p><?= htmlspecialchars($_GET['error']) ?></p></div>
  <?php endif; ?>

  <div class="panel">
    <table class="tbl">
      <thead>
        <tr><th>Name</th><th>Phone</th><th>Address</th><th>Payment terms</th><th></th></tr>
      </thead>
      <tbody>
      <?php if (empty($proveedores)): ?>
        <tr><td colspan="5">No suppliers registered yet.</td></tr>
      <?php endif; ?>
      <?php foreach ($proveedores as $p): ?>
        <tr>
          <td><?= htmlspecialchars($p['nombre']) ?></td>
          <td><?= htmlspecialchars($p['telefono'] ?: '—') ?></td>
          <td><?= htmlspecialchars($p['direccion'] ?: '—') ?></td>
          <td><span class="badge badge-neutral"><?= htmlspecialchars($p['condicion_pago']) ?></span></td>
          <td class="acciones">
            <a href="index.php?accion=proveedor-editar&id=<?= $p['id_proveedor'] ?>" class="btn btn-primary btn-sm">Edit</a>
            <?php if ($rolActual === 'Administrator'): ?>
              <button type="button" class="btn btn-primary btn-sm btn-eliminar-proveedor" data-id="<?= $p['id_proveedor'] ?>" data-nombre="<?= htmlspecialchars($p['nombre']) ?>">Delete</button>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="modal-overlay" id="modal-eliminar-proveedor" hidden>
    <div class="modal-box">
      <h3>Delete this supplier?</h3>
      <p id="modal-nombre-proveedor"></p>
      <form method="POST" id="form-eliminar-proveedor" action="index.php?accion=proveedor-eliminar">
        <div class="modal-actions">
          <button type="button" class="btn btn-ghost" id="modal-cancelar-proveedor">Cancel</button>
          <button type="submit" class="btn btn-danger">Yes, delete</button>
        </div>
      </form>
    </div>
  </div>
<?php require __DIR__ . '/../layouts/base-fin.php'; ?>
<script src="public/js/app.js"></script>
