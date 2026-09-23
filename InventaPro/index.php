<?php
// index.php — single entry point (router)
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/auth.php';
require_once __DIR__ . '/models/Usuario.php';
require_once __DIR__ . '/models/Producto.php';
require_once __DIR__ . '/models/Categoria.php';
require_once __DIR__ . '/models/UnidadMedida.php';
require_once __DIR__ . '/models/Proveedor.php';
require_once __DIR__ . '/models/Entrada.php';
require_once __DIR__ . '/models/Salida.php';
require_once __DIR__ . '/models/Auditoria.php';
require_once __DIR__ . '/models/Cliente.php';
require_once __DIR__ . '/models/Reporte.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/DashboardController.php';
require_once __DIR__ . '/controllers/ProductoController.php';
require_once __DIR__ . '/controllers/ProveedorController.php';
require_once __DIR__ . '/controllers/EntradaController.php';
require_once __DIR__ . '/controllers/SalidaController.php';
require_once __DIR__ . '/controllers/AuditoriaController.php';
require_once __DIR__ . '/controllers/ClienteController.php';
require_once __DIR__ . '/controllers/InformeController.php';

$accion = $_GET['accion'] ?? (currentUser() ? 'dashboard' : 'login');

$auth        = new AuthController();
$dashboard   = new DashboardController();
$productos   = new ProductoController();
$proveedores = new ProveedorController();
$entradas    = new EntradaController();
$salidas     = new SalidaController();
$auditoria   = new AuditoriaController();
$clientes    = new ClienteController();
$informes    = new InformeController();

match ($accion) {
    'login'             => $auth->login(),
    'logout'            => $auth->logout(),

    'dashboard'         => $dashboard->index(),

    'productos'         => $productos->index(),
    'producto-crear'    => $productos->crear(),
    'producto-editar'   => $productos->editar($_GET['id'] ?? null),
    'producto-eliminar' => $productos->eliminar($_GET['id'] ?? null),

    'proveedores'         => $proveedores->index(),
    'proveedor-crear'     => $proveedores->crear(),
    'proveedor-editar'    => $proveedores->editar($_GET['id'] ?? null),
    'proveedor-eliminar'  => $proveedores->eliminar($_GET['id'] ?? null),

    'entradas'          => $entradas->index(),
    'entrada-crear'     => $entradas->crear(),

    'salidas'           => $salidas->index(),
    'salida-nueva'      => $salidas->nueva(),
    'factura'           => $salidas->factura($_GET['id'] ?? null),

    'auditoria'         => $auditoria->index(),

    'cliente-info'      => $clientes->info(),
    'informes'          => $informes->index(),

    default             => http_response_code(404),
};
