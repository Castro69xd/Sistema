<?php
/**
 * Base layout — header + sidebar.
 * Expected input variable: $titulo
 */
$usuarioActual = currentUser();
$rolActual = $usuarioActual['rol'] ?? null;
$accionActual = $_GET['accion'] ?? '';

function navActiva(string $accion, string $actual): string {
    return $accion === $actual ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($titulo ?? 'InventaPro') ?></title>
<link rel="stylesheet" href="public/css/styles.css">
</head>
<body class="app-body">
  <div class="app-shell">
    <aside class="sidebar">
      <div class="sidebar-brand">
        <img src="public/logo.png" alt="InventaPro" class="brand-icon-img">
        <span class="brand-name">Inventa<strong>Pro</strong></span>
      </div>

      <nav class="sidebar-nav">
        <a href="index.php?accion=dashboard" class="<?= navActiva('dashboard', $accionActual) ?>">
          <img src="public/dashboard.png" alt="" class="nav-ico-img"> Dashboard
        </a>

        <?php if (in_array($rolActual, ['Administrator', 'Warehouse Clerk', 'Cashier'], true)): ?>
        <a href="index.php?accion=productos" class="<?= navActiva('productos', $accionActual) ?>">
          <img src="public/productos.png" alt="" class="nav-ico-img"> Products
        </a>
        <?php endif; ?>

        <?php if (in_array($rolActual, ['Administrator', 'Warehouse Clerk'], true)): ?>
        <a href="index.php?accion=proveedores" class="<?= navActiva('proveedores', $accionActual) ?>">
          <img src="public/proveedores.png" alt="" class="nav-ico-img"> Suppliers
        </a>
        <a href="index.php?accion=entradas" class="<?= navActiva('entradas', $accionActual) ?>">
          <img src="public/entradas.png" alt="" class="nav-ico-img"> Stock In
        </a>
        <?php endif; ?>

        <?php if (in_array($rolActual, ['Administrator', 'Cashier'], true)): ?>
        <a href="index.php?accion=salidas" class="<?= navActiva('salidas', $accionActual) ?>">
          <img src="public/salidas.png" alt="" class="nav-ico-img"> Sales
        </a>
        <?php endif; ?>

        <?php if ($rolActual === 'Administrator'): ?>
        <a href="index.php?accion=informes" class="<?= navActiva('informes', $accionActual) ?>">
          <img src="public/informes.png" alt="" class="nav-ico-img"> Reports
        </a>
        <a href="index.php?accion=auditoria" class="<?= navActiva('auditoria', $accionActual) ?>">
          <img src="public/auditoria.png" alt="" class="nav-ico-img"> Audit Log
        </a>
        <?php endif; ?>
      </nav>

      <div class="sidebar-footer">
        <?php if ($usuarioActual): ?>
          <div class="user-chip">
            <span class="user-avatar"><?= strtoupper(substr($usuarioActual['nombre'], 0, 1)) ?></span>
            <div class="user-info">
              <span class="user-name"><?= htmlspecialchars($usuarioActual['nombre']) ?></span>
              <span class="user-role"><?= htmlspecialchars($usuarioActual['rol']) ?></span>
            </div>
          </div>
          <a href="index.php?accion=logout" class="logout-link">Log out</a>
        <?php endif; ?>
      </div>
    </aside>

    <div class="app-main">
      <main class="content">
