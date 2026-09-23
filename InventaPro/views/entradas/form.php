<?php
$titulo = 'Register entry · InventaPro';
require __DIR__ . '/../layouts/base.php';
?>
  <div class="page-header">
    <div>
      <span class="page-eyebrow">New entry</span>
      <h1>Register incoming stock</h1>
    </div>
    <a href="index.php?accion=entradas" class="btn btn-ghost">&larr; Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error"><p><?= htmlspecialchars($error) ?></p></div>
  <?php endif; ?>

  <form method="POST" action="index.php?accion=entrada-crear" class="panel" id="form-entrada">
    <div class="form-grid">
      <label class="span-2">Supplier
        <select name="id_proveedor" required>
          <option value="">-- Select a supplier --</option>
          <?php foreach ($listaProveedores as $p): ?>
            <option value="<?= $p['id_proveedor'] ?>"><?= htmlspecialchars($p['nombre']) ?> (<?= htmlspecialchars($p['condicion_pago']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </label>
    </div>

    <h3 class="sub-h">Products received</h3>
    <table class="tbl" id="tabla-items-entrada">
      <thead>
        <tr><th>Product</th><th>Quantity</th><th>Purchase price</th><th></th></tr>
      </thead>
      <tbody>
        <tr class="fila-item">
          <td>
            <select name="id_producto[]" class="select-producto" required>
              <option value="">-- Product --</option>
              <?php foreach ($listaProductos as $p): ?>
                <option value="<?= $p['id_producto'] ?>" data-precio="<?= $p['precio_compra'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td><input type="number" name="cantidad[]" min="1" step="1" required></td>
          <td><input type="number" name="precio_compra[]" min="0" step="0.01" required></td>
          <td><button type="button" class="btn-quitar-fila">✕</button></td>
        </tr>
      </tbody>
    </table>

    <button type="button" class="btn btn-ghost btn-sm" id="btn-agregar-fila">+ Add product</button>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Register entry</button>
    </div>
  </form>

  <template id="plantilla-fila-entrada">
    <tr class="fila-item">
      <td>
        <select name="id_producto[]" class="select-producto" required>
          <option value="">-- Product --</option>
          <?php foreach ($listaProductos as $p): ?>
            <option value="<?= $p['id_producto'] ?>" data-precio="<?= $p['precio_compra'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </td>
      <td><input type="number" name="cantidad[]" min="1" step="1" required></td>
      <td><input type="number" name="precio_compra[]" min="0" step="0.01" required></td>
      <td><button type="button" class="btn-quitar-fila">✕</button></td>
    </tr>
  </template>
<?php require __DIR__ . '/../layouts/base-fin.php'; ?>
<script src="public/js/app.js"></script>
