<?php
class Entrada
{
    public function todas(): array
    {
        return getDB()->query(
            'SELECT e.*, pr.nombre AS proveedor, u.nombre_completo AS usuario,
                    (SELECT COUNT(*) FROM detalle_entradas d WHERE d.id_entrada = e.id_entrada) AS items
             FROM entradas e
             JOIN proveedores pr ON pr.id_proveedor = e.id_proveedor
             JOIN usuarios u ON u.id_usuario = e.id_usuario
             ORDER BY e.fecha DESC'
        )->fetchAll();
    }

    public function detalle(int $idEntrada): array
    {
        $stmt = getDB()->prepare(
            'SELECT d.*, p.nombre AS producto
             FROM detalle_entradas d
             JOIN productos p ON p.id_producto = d.id_producto
             WHERE d.id_entrada = ?'
        );
        $stmt->execute([$idEntrada]);
        return $stmt->fetchAll();
    }

    /**
     * Registers a complete stock-in entry (header + line items) inside a
     * transaction, and increases the stock of every received product.
     *
     * $items = [['id_producto' => 1, 'cantidad' => 10, 'precio_compra' => 5.00], ...]
     */
    public function registrar(int $idProveedor, int $idUsuario, array $items): int
    {
        $db = getDB();
        $db->beginTransaction();

        try {
            $stmt = $db->prepare('INSERT INTO entradas (id_proveedor, id_usuario) VALUES (?, ?)');
            $stmt->execute([$idProveedor, $idUsuario]);
            $idEntrada = (int) $db->lastInsertId();

            $stmtDetalle = $db->prepare(
                'INSERT INTO detalle_entradas (id_entrada, id_producto, cantidad, precio_compra)
                 VALUES (?, ?, ?, ?)'
            );
            $stmtStock = $db->prepare(
                'UPDATE productos SET stock_actual = stock_actual + ? WHERE id_producto = ?'
            );

            foreach ($items as $item) {
                $stmtDetalle->execute([
                    $idEntrada, $item['id_producto'], $item['cantidad'], $item['precio_compra'],
                ]);
                $stmtStock->execute([$item['cantidad'], $item['id_producto']]);
            }

            $db->commit();
            return $idEntrada;
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
