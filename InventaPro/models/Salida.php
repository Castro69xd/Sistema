<?php
/**
 * Specific exception for when there isn't enough stock.
 * Lets the controller tell this case apart from a real database error
 * and show the warning message required by the sequence diagram (UC-02).
 */
class StockInsuficienteException extends Exception {}

class Salida
{
    public function todas(): array
    {
        return getDB()->query(
            'SELECT s.*, u.nombre_completo AS usuario,
                    (SELECT COUNT(*) FROM detalle_salidas d WHERE d.id_salida = s.id_salida) AS items,
                    (SELECT SUM(d.cantidad * d.precio_venta) FROM detalle_salidas d WHERE d.id_salida = s.id_salida) AS total
             FROM salidas s
             JOIN usuarios u ON u.id_usuario = s.id_usuario
             ORDER BY s.fecha DESC
             LIMIT 100'
        )->fetchAll();
    }

    public function buscar(int $idSalida): ?array
    {
        $stmt = getDB()->prepare(
            'SELECT s.*, u.nombre_completo AS usuario
             FROM salidas s
             JOIN usuarios u ON u.id_usuario = s.id_usuario
             WHERE s.id_salida = ?'
        );
        $stmt->execute([$idSalida]);
        return $stmt->fetch() ?: null;
    }

    public function detalle(int $idSalida): array
    {
        $stmt = getDB()->prepare(
            'SELECT d.*, p.nombre AS producto
             FROM detalle_salidas d
             JOIN productos p ON p.id_producto = d.id_producto
             WHERE d.id_salida = ?'
        );
        $stmt->execute([$idSalida]);
        return $stmt->fetchAll();
    }

    public function contarHoy(): int
    {
        return (int) getDB()->query(
            'SELECT COUNT(*) FROM salidas WHERE DATE(fecha) = CURDATE()'
        )->fetchColumn();
    }

    /**
     * Registers a complete sale (UC-02: Register a sale / stock-out).
     *
     * Mirrors the sequence diagram: for every product in the cart, it
     * locks the row with SELECT ... FOR UPDATE, checks that the current
     * stock covers the requested quantity, and only if the WHOLE cart is
     * valid does it confirm the sale, insert the line items, deduct the
     * stock and log it in the audit trail. If any product doesn't have
     * enough stock, the whole flow is stopped (rollback) and the
     * customer is told which product caused it — exactly like the diagram.
     *
     * $items = [['id_producto' => 1, 'cantidad' => 2, 'precio_venta' => 12.50], ...]
     */
    public function registrar(
        int $idUsuario,
        array $items,
        ?int $idCliente = null,
        ?string $clienteNombre = null,
        string $metodoPago = 'cash',
        float $descuentoPorcentaje = 0
    ): int {
        // Sum quantities per product BEFORE validating. If the same
        // id_producto shows up in more than one line (for example, a
        // hand-built POST request that skips the browser cart), the
        // real sum must be validated against stock, not each line on
        // its own — otherwise two lines of 3 units each could each pass
        // validation individually even if real stock is only 5.
        $cantidadesPorProducto = [];
        $precioPorProducto = [];
        foreach ($items as $item) {
            $id = $item['id_producto'];
            $cantidadesPorProducto[$id] = ($cantidadesPorProducto[$id] ?? 0) + $item['cantidad'];
            $precioPorProducto[$id] = $item['precio_venta'];
        }

        $db = getDB();
        $db->beginTransaction();

        try {
            $stmtStockActual = $db->prepare(
                'SELECT nombre, stock_actual, precio_compra FROM productos WHERE id_producto = ? FOR UPDATE'
            );

            $costoPorProducto = [];
            foreach ($cantidadesPorProducto as $idProducto => $cantidadTotal) {
                $stmtStockActual->execute([$idProducto]);
                $producto = $stmtStockActual->fetch();

                if (!$producto || $producto['stock_actual'] < $cantidadTotal) {
                    throw new StockInsuficienteException(
                        'Not enough stock for "' . ($producto['nombre'] ?? 'product') . '". '
                        . 'Available: ' . ($producto['stock_actual'] ?? 0) . ', requested: ' . $cantidadTotal . '.'
                    );
                }

                // Store TODAY's purchase cost, at the exact moment of the
                // sale — this way "Total profit" stays exact forever,
                // even if the product's purchase price is edited later.
                $costoPorProducto[$idProducto] = (float) $producto['precio_compra'];
            }

            $subtotal = 0;
            foreach ($cantidadesPorProducto as $idProducto => $cantidadTotal) {
                $subtotal += $cantidadTotal * $precioPorProducto[$idProducto];
            }
            $descuentoMonto = round($subtotal * $descuentoPorcentaje, 2);

            $stmt = $db->prepare(
                'INSERT INTO salidas (id_usuario, id_cliente, cliente_nombre, metodo_pago, descuento)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$idUsuario, $idCliente, $clienteNombre ?: null, $metodoPago, $descuentoMonto]);
            $idSalida = (int) $db->lastInsertId();

            $stmtDetalle = $db->prepare(
                'INSERT INTO detalle_salidas (id_salida, id_producto, cantidad, precio_venta, costo_unitario)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmtStock = $db->prepare(
                'UPDATE productos SET stock_actual = stock_actual - ? WHERE id_producto = ?'
            );

            foreach ($cantidadesPorProducto as $idProducto => $cantidadTotal) {
                $stmtDetalle->execute([
                    $idSalida, $idProducto, $cantidadTotal,
                    $precioPorProducto[$idProducto], $costoPorProducto[$idProducto],
                ]);
                $stmtStock->execute([$cantidadTotal, $idProducto]);
            }

            $db->commit();
            return $idSalida;
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
