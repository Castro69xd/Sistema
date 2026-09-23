<?php
$titulo = 'New sale · InventaPro';
require __DIR__ . '/../layouts/base.php';
?>
  <div class="page-header">
    <div>
      <span class="page-eyebrow">UC-02 · Register a sale</span>
      <h1>New sale</h1>
    </div>
    <a href="index.php?accion=salidas" class="btn btn-ghost">&larr; Back</a>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-error">
      <strong>The sale could not be completed:</strong>
      <p><?= htmlspecialchars($error) ?></p>
    </div>
  <?php endif; ?>

  <div class="alert alert-error" id="error-venta-js" hidden>
    <strong>The sale could not be completed:</strong>
    <p id="error-venta-js-texto"></p>
  </div>

  <div class="pos-layout">
    <div class="panel pos-buscador">
      <input type="search" id="buscador-pos" class="buscador" placeholder="Search product by name or barcode..." autocomplete="off" autofocus>
      <div class="pos-lista" id="pos-lista"></div>
    </div>

    <div class="panel pos-carrito">
      <h3 class="sub-h">Cart</h3>
      <div class="carrito-items" id="carrito-items">
        <p class="empty-inline" id="carrito-vacio">Add products from the search above.</p>
      </div>
      <div class="carrito-total">
        <span>Total</span>
        <span id="carrito-total-valor">$0.00</span>
      </div>

      <form method="POST" action="index.php?accion=salida-nueva" id="form-salida">
        <label class="cliente-label">Customer name <span class="hint">(optional, for the invoice)</span>
          <input type="text" name="cliente_nombre" id="input-cliente-nombre" placeholder="Walk-in customer" autocomplete="off">
        </label>
        <p class="cliente-hint" id="cliente-hint" hidden></p>

        <label class="cliente-label">Payment method</label>
        <div class="metodo-pago-grupo">
          <label class="metodo-pago-opcion">
            <input type="radio" name="metodo_pago" value="cash" checked> 💵 Cash
          </label>
          <label class="metodo-pago-opcion">
            <input type="radio" name="metodo_pago" value="card"> 💳 Card
          </label>
        </div>

        <div id="inputs-carrito"></div>
        <button type="submit" class="btn btn-primary btn-block" id="btn-confirmar-venta" disabled>
          Confirm sale
        </button>
      </form>
    </div>
  </div>

  <script id="datos-productos" type="application/json">
    <?= json_encode(array_map(fn($p) => [
        'id' => (int) $p['id_producto'],
        'nombre' => $p['nombre'],
        'codigo' => $p['codigo_barra'],
        'precio' => (float) $p['precio_venta'],
        'stock' => (int) $p['stock_actual'],
        'unidad' => $p['unidad'],
    ], $listaProductos)) ?>
  </script>
<?php require __DIR__ . '/../layouts/base-fin.php'; ?>
<script src="public/js/app.js"></script>
