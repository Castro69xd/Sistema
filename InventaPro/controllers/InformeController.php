<?php
class InformeController
{
    public function index(): void
    {
        requireRole(['Administrator']);

        $reporte = new Reporte();
        $resumen = $reporte->resumenGeneral();
        $masVendidos = $reporte->productosMasVendidos(10);
        $menosVendidos = $reporte->productosMenosVendidos(10);
        $ventasPorDia = $reporte->ventasPorDia(14);

        require __DIR__ . '/../views/informes/index.php';
    }
}
