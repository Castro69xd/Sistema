<?php
$fecha = new DateTime($salida['fecha']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invoice-<?= str_pad($salida['id_salida'], 6, '0', STR_PAD_LEFT) ?>-InventaPro</title>
<link rel="stylesheet" href="public/css/styles.css">
</head>
<body class="factura-body">
  <div class="factura-toolbar no-print">
    <a href="index.php?accion=salidas" class="btn btn-ghost">&larr; Back to sales</a>
    <div class="factura-toolbar-derecha">
      <a href="index.php?accion=salida-nueva" class="btn btn-ghost">+ New sale</a>
      <button type="button" class="btn btn-primary" onclick="window.print()">🖨️ Print / Save as PDF</button>
    </div>
  </div>

  <div class="factura-hoja">
    <div class="factura-header">
      <div>
        <h1 class="factura-h1-con-logo"><img src="public/logo.png" alt="" class="factura-logo-img"> InventaPro</h1>
        <p class="factura-empresa">Golden Nail Hardware Store</p>
      </div>
      <div class="factura-numero">
        <span class="factura-numero-label">INVOICE</span>
        <span class="factura-numero-valor">#<?= str_pad($salida['id_salida'], 6, '0', STR_PAD_LEFT) ?></span>
        <span class="factura-badge-pagado">✓ PAID</span>
      </div>
    </div>

    <div class="factura-body-inner">
      <div class="factura-meta">
        <div>
          <span class="factura-meta-label">Customer</span>
          <span class="factura-meta-valor">
            <?= htmlspecialchars($salida['cliente_nombre'] ?: 'Walk-in customer') ?>
            <?php if ($descuento > 0): ?>
              <span class="badge badge-success">Frequent customer</span>
            <?php endif; ?>
          </span>
        </div>
        <div>
          <span class="factura-meta-label">Date</span>
          <span class="factura-meta-valor"><?= $fecha->format('d/m/Y h:i A') ?></span>
        </div>
        <div>
          <span class="factura-meta-label">Served by</span>
          <span class="factura-meta-valor"><?= htmlspecialchars($salida['usuario']) ?></span>
        </div>
        <div>
          <span class="factura-meta-label">Payment method</span>
          <span class="factura-meta-valor">
            <?= $salida['metodo_pago'] === 'card' ? '💳 Card' : '💵 Cash' ?>
          </span>
        </div>
      </div>

      <table class="factura-tabla">
        <thead>
          <tr><th>Product</th><th>Quantity</th><th>Unit price</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
          <?php foreach ($detalle as $d): ?>
            <tr>
              <td><?= htmlspecialchars($d['producto']) ?></td>
              <td><?= $d['cantidad'] ?></td>
              <td>$<?= number_format($d['precio_venta'], 2) ?></td>
              <td>$<?= number_format($d['cantidad'] * $d['precio_venta'], 2) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <?php if ($descuento > 0): ?>
        <div class="factura-subtotal-row">
          <span>Subtotal</span>
          <span>$<?= number_format($subtotal, 2) ?></span>
        </div>
        <div class="factura-subtotal-row factura-descuento-row">
          <span>Frequent customer discount</span>
          <span>-$<?= number_format($descuento, 2) ?></span>
        </div>
      <?php endif; ?>

      <div class="factura-total-row">
        <span>Total</span>
        <span>$<?= number_format($total, 2) ?></span>
      </div>

      <p class="factura-footer">Thank you for your purchase · InventaPro — Inventory control system</p>
    </div>
  </div>
</body>
</html>
