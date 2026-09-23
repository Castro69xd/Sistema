<?php
$titulo = $modo === 'crear' ? 'New supplier' : 'Edit supplier';
require __DIR__ . '/../layouts/base.php';

$accionUrl = $modo === 'crear'
    ? 'index.php?accion=proveedor-crear'
    : 'index.php?accion=proveedor-editar&id=' . $datos['id_proveedor'];
?>
  <div class="page-header">
    <div>
      <span class="page-eyebrow"><?= $modo === 'crear' ? 'New' : 'Edit' ?></span>
      <h1><?= $titulo ?></h1>
    </div>
    <a href="index.php?accion=proveedores" class="btn btn-ghost">&larr; Back</a>
  </div>

  <?php if (!empty($errores)): ?>
    <div class="alert alert-error">
      <?php foreach ($errores as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="<?= $accionUrl ?>" class="panel form-grid">
    <label class="span-2">Name / Company
      <input type="text" name="nombre" value="<?= htmlspecialchars($datos['nombre']) ?>" required>
    </label>
    <label>Phone
      <input type="text" name="telefono" value="<?= htmlspecialchars($datos['telefono'] ?? '') ?>">
    </label>
    <label>Payment terms
      <select name="condicion_pago">
        <option value="Cash" <?= ($datos['condicion_pago'] ?? '') === 'Cash' ? 'selected' : '' ?>>Cash</option>
        <option value="Credit" <?= ($datos['condicion_pago'] ?? '') === 'Credit' ? 'selected' : '' ?>>Credit</option>
      </select>
    </label>
    <label class="span-2">Address
      <input type="text" name="direccion" value="<?= htmlspecialchars($datos['direccion'] ?? '') ?>">
    </label>
    <div class="span-2">
      <button type="submit" class="btn btn-primary"><?= $modo === 'crear' ? 'Save supplier' : 'Save changes' ?></button>
    </div>
  </form>
<?php require __DIR__ . '/../layouts/base-fin.php'; ?>
