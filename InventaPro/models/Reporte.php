<?php
class Reporte
{
    /**
     * Overall totals of everything sold: units, revenue (what customers
     * paid) and profit (revenue minus the real purchase cost stored at
     * the moment of each sale — exact, not an approximation using the
     * current purchase price).
     */
    public function resumenGeneral(): array
    {
        $fila = getDB()->query(
            'SELECT
                COALESCE(SUM(cantidad), 0) AS unidades_vendidas,
                COALESCE(SUM(cantidad * precio_venta), 0) AS ingresos_totales,
                COALESCE(SUM(cantidad * (precio_venta - costo_unitario)), 0) AS ganancias_totales
             FROM detalle_salidas'
        )->fetch();

        return [
            'unidades_vendidas' => (int) $fila['unidades_vendidas'],
            'ingresos_totales'  => (float) $fila['ingresos_totales'],
            'ganancias_totales' => (float) $fila['ganancias_totales'],
        ];
    }

    public function productosMasVendidos(int $limite = 10): array
    {
        $stmt = getDB()->prepare(
            'SELECT p.id_producto, p.nombre, p.imagen,
                    SUM(d.cantidad) AS total_vendido,
                    SUM(d.cantidad * d.precio_venta) AS ingresos,
                    SUM(d.cantidad * (d.precio_venta - d.costo_unitario)) AS ganancia
             FROM detalle_salidas d
             JOIN productos p ON p.id_producto = d.id_producto
             GROUP BY p.id_producto, p.nombre, p.imagen
             ORDER BY total_vendido DESC
             LIMIT ?'
        );
        $stmt->bindValue(1, $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Includes products with 0 sales (never sold). */
    public function productosMenosVendidos(int $limite = 10): array
    {
        $stmt = getDB()->prepare(
            'SELECT p.id_producto, p.nombre, p.imagen,
                    COALESCE(SUM(d.cantidad), 0) AS total_vendido
             FROM productos p
             LEFT JOIN detalle_salidas d ON d.id_producto = p.id_producto
             GROUP BY p.id_producto, p.nombre, p.imagen
             ORDER BY total_vendido ASC
             LIMIT ?'
        );
        $stmt->bindValue(1, $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Confirmed sales grouped by day, for the dashboard chart. */
    public function ventasPorDia(int $dias = 7): array
    {
        $stmt = getDB()->prepare(
            "SELECT DATE(s.fecha) AS dia,
                    COUNT(DISTINCT s.id_salida) AS ventas,
                    COALESCE(SUM(d.cantidad * d.precio_venta), 0) AS ingresos
             FROM salidas s
             LEFT JOIN detalle_salidas d ON d.id_salida = s.id_salida
             WHERE s.fecha >= (CURDATE() - INTERVAL ? DAY)
             GROUP BY DATE(s.fecha)
             ORDER BY dia"
        );
        $stmt->bindValue(1, $dias, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
