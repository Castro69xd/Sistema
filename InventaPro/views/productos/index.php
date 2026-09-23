<?php
$titulo = 'Products · InventaPro';
require __DIR__ . '/../layouts/base.php';
?>
  <div class="page-header">
    <div>
      <span class="page-eyebrow">Catalogue</span>
      <h1>Products · <?= count($productos) ?></h1>
    </div>
    <?php if (in_array($rolActual, ['Administrator', 'Warehouse Clerk'], true)): ?>
      <a href="index.php?accion=producto-crear" class="btn btn-primary">+ New product</a>
    <?php endif; ?>
  </div>

  <?php if (isset($_GET['ok'])): ?>
    <p class="msg-ok">Operation completed successfully.</p>
  <?php endif; ?>

  <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error"><p><?= htmlspecialchars($_GET['error']) ?></p></div>
  <?php endif; ?>

  <input type="search" id="buscador" class="buscador" placeholder="Search by name or barcode..." autocomplete="off">

  <div class="panel">
    <table class="tbl" id="tabla-productos">
      <thead>
        <tr>
          <th></th><th>Code</th><th>Name</th><th>Category</th><th>Sale price</th><th>Stock</th><th>Status</th>
          <?php if (in_array($rolActual, ['Administrator', 'Warehouse Clerk'], true)): ?><th></th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($productos)): ?>
        <tr><td colspan="8">No products registered yet.</td></tr>
      <?php endif; ?>
      <?php foreach ($productos as $p): ?>
        <tr data-texto="<?= htmlspecialchars(mb_strtolower($p['nombre'] . ' ' . $p['codigo_barra'])) ?>">
          <td class="col-thumb">
            <?php if (!empty($p['imagen'])): ?>
              <button type="button" class="thumb-btn" data-imagen="<?= htmlspecialchars($p['imagen']) ?>" data-nombre="<?= htmlspecialchars($p['nombre']) ?>" title="View full image">
                <img src="<?= htmlspecialchars($p['imagen']) ?>" alt="" class="thumb">
              </button>
            <?php else: ?>
              <span class="thumb thumb-vacia">📦</span>
            <?php endif; ?>
          </td>
          <td class="mono"><?= htmlspecialchars($p['codigo_barra'] ?: '—') ?></td>
          <td><?= htmlspecialchars($p['nombre']) ?></td>
          <td><?= htmlspecialchars($p['categoria'] ?? 'No category') ?></td>
          <td>$<?= number_format($p['precio_venta'], 2) ?></td>
          <td><?= $p['stock_actual'] ?> <span class="unidad"><?= htmlspecialchars($p['unidad']) ?></span></td>
          <td>
            <?php if ($p['stock_actual'] <= 0): ?>
              <span class="badge badge-danger">Out of stock</span>
            <?php elseif ($p['stock_actual'] <= $p['stock_minimo']): ?>
              <span class="badge badge-warning">Low stock</span>
            <?php else: ?>
              <span class="badge badge-success">OK</span>
            <?php endif; ?>
          </td>
          <?php if (in_array($rolActual, ['Administrator', 'Warehouse Clerk'], true)): ?>
          <td class="acciones">
            <a href="index.php?accion=producto-editar&id=<?= $p['id_producto'] ?>" class="btn btn-primary btn-sm">Edit</a>
            <?php if ($rolActual === 'Administrator'): ?>
              <button type="button" class="btn btn-primary btn-sm btn-eliminar" data-id="<?= $p['id_producto'] ?>" data-nombre="<?= htmlspecialchars($p['nombre']) ?>">Delete</button>
            <?php endif; ?>
          </td>
          <?php endif; ?>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="modal-overlay" id="modal-imagen" hidden>
    <div class="modal-imagen-box">
      <button type="button" class="modal-imagen-cerrar" id="modal-imagen-cerrar" title="Close">✕</button>
      <img src="" alt="" id="modal-imagen-img">
      <p id="modal-imagen-nombre"></p>
    </div>
  </div>

  <div class="modal-overlay" id="modal-eliminar" hidden>
    <div class="modal-box">
      <h3>Delete this product?</h3>
      <p id="modal-nombre"></p>
      <form method="POST" id="form-eliminar" action="index.php?accion=producto-eliminar">
        <div class="modal-actions">
          <button type="button" class="btn btn-ghost" id="modal-cancelar">Cancel</button>
          <button type="submit" class="btn btn-danger">Yes, delete</button>
        </div>
      </form>
    </div>
  </div>
<?php require __DIR__ . '/../layouts/base-fin.php'; ?>
<script src="public/js/app.js"></script>
