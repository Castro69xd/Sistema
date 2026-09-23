<?php
class DashboardController
{
    public function index(): void
    {
        requireLogin();
        $rol = currentUser()['rol'];

        $productoModelo = new Producto();
        $salidaModelo   = new Salida();
        $reporteModelo  = new Reporte();

        $totalProductos = $productoModelo->contarTotal();
        $alertas        = $productoModelo->conAlertaStock();
        $salidasHoy     = $salidaModelo->contarHoy();

        // The chart and top 5 list only make sense for roles that see Reports/Sales
        $ventasPorDia = in_array($rol, ['Administrator', 'Cashier'], true)
            ? $reporteModelo->ventasPorDia(7)
            : [];
        $top5 = $rol === 'Administrator'
            ? $reporteModelo->productosMasVendidos(5)
            : [];

        require __DIR__ . '/../views/dashboard/index.php';
    }
}
