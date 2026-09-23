<?php
$titulo = $modo === 'crear' ? 'New product' : 'Edit product';
require __DIR__ . '/../layouts/base.php';

$accionUrl = $modo === 'crear'
    ? 'index.php?accion=producto-crear'
    : 'index.php?accion=producto-editar&id=' . $datos['id_producto'];
?>
  <div class="page-header">
    <div>
      <span class="page-eyebrow"><?= $modo === 'crear' ? 'New' : 'Edit' ?></span>
      <h1><?= $titulo ?></h1>
    </div>
    <a href="index.php?accion=productos" class="btn btn-ghost">&larr; Back</a>
  </div>

  <?php if (!empty($errores)): ?>
    <div class="alert alert-error">
      <?php foreach ($errores as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="<?= $accionUrl ?>" class="panel form-grid" enctype="multipart/form-data">
    <div class="span-2 imagen-actual-wrap">
      <?php if (!empty($datos['imagen'])): ?>
        <img src="<?= htmlspecialchars($datos['imagen']) ?>" alt="Current image" class="imagen-actual">
      <?php else: ?>
        <div class="imagen-actual imagen-actual-vacia">No image</div>
      <?php endif; ?>
      <label class="imagen-input-label">
        <?= !empty($datos['imagen']) ? 'Change image (optional)' : 'Product image (optional)' ?>
        <input type="file" name="imagen" accept="image/png, image/jpeg, image/webp">
        <span class="hint">JPG, PNG or WEBP · max 2 MB</span>
      </label>
    </div>

    <label>Barcode
      <input type="text" name="codigo_barra" value="<?= htmlspecialchars($datos['codigo_barra'] ?? '') ?>" placeholder="Optional">
    </label>

    <label class="span-2">Name
      <input type="text" name="nombre" value="<?= htmlspecialchars($datos['nombre']) ?>" required>
    </label>

    <label class="span-2">Description
      <textarea name="descripcion" rows="2"><?= htmlspecialchars($datos['descripcion'] ?? '') ?></textarea>
    </label>

    <label>Category
      <select name="id_categoria">
        <option value="">-- No category --</option>
        <?php foreach ($listaCategorias as $c): ?>
          <option value="<?= $c['id_categoria'] ?>" <?= ($datos['id_categoria'] ?? '') == $c['id_categoria'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>Unit of measure
      <select name="id_unidad" required>
        <option value="">-- Select --</option>
        <?php foreach ($listaUnidades as $u): ?>
          <option value="<?= $u['id_unidad'] ?>" <?= ($datos['id_unidad'] ?? '') == $u['id_unidad'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($u['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>Purchase price
      <input type="number" step="0.01" min="0" name="precio_compra" value="<?= htmlspecialchars($datos['precio_compra']) ?>" required>
    </label>

    <label>Sale price
      <input type="number" step="0.01" min="0" name="precio_venta" value="<?= htmlspecialchars($datos['precio_venta']) ?>" required>
    </label>

    <?php if ($modo === 'crear'): ?>
    <label>Initial stock
      <input type="number" step="1" min="0" name="stock_actual" value="<?= htmlspecialchars($datos['stock_actual']) ?>" required>
    </label>
    <?php endif; ?>

    <label>Minimum stock (alert)
      <input type="number" step="1" min="0" name="stock_minimo" value="<?= htmlspecialchars($datos['stock_minimo']) ?>" required>
    </label>

    <div class="span-2">
      <button type="submit" class="btn btn-primary"><?= $modo === 'crear' ? 'Save product' : 'Save changes' ?></button>
    </div>
  </form>
<?php require __DIR__ . '/../layouts/base-fin.php'; ?>
