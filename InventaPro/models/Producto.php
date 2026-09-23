<?php
class ProductoConMovimientosException extends Exception {}

class Producto
{
    public function todos(): array
    {
        return getDB()->query(
            'SELECT p.*, c.nombre AS categoria, u.abreviatura AS unidad
             FROM productos p
             LEFT JOIN categorias c ON c.id_categoria = p.id_categoria
             JOIN unidades_medida u ON u.id_unidad = p.id_unidad
             ORDER BY p.nombre'
        )->fetchAll();
    }

    public function buscar(int $id): ?array
    {
        $stmt = getDB()->prepare(
            'SELECT p.*, c.nombre AS categoria, u.abreviatura AS unidad
             FROM productos p
             LEFT JOIN categorias c ON c.id_categoria = p.id_categoria
             JOIN unidades_medida u ON u.id_unidad = p.id_unidad
             WHERE p.id_producto = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function buscarPorCodigoOTexto(string $termino): array
    {
        $like = '%' . $termino . '%';
        $stmt = getDB()->prepare(
            'SELECT p.*, u.abreviatura AS unidad
             FROM productos p
             JOIN unidades_medida u ON u.id_unidad = p.id_unidad
             WHERE p.codigo_barra = ? OR p.nombre LIKE ?
             ORDER BY p.nombre
             LIMIT 30'
        );
        $stmt->execute([$termino, $like]);
        return $stmt->fetchAll();
    }

    public function crear(array $d): int
    {
        $stmt = getDB()->prepare(
            'INSERT INTO productos
             (codigo_barra, nombre, descripcion, imagen, id_categoria, id_unidad, precio_compra, precio_venta, stock_actual, stock_minimo)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $d['codigo_barra'] ?: null, $d['nombre'], $d['descripcion'], $d['imagen'] ?? null,
            $d['id_categoria'] ?: null, $d['id_unidad'],
            $d['precio_compra'], $d['precio_venta'],
            $d['stock_actual'], $d['stock_minimo'],
        ]);
        return (int) getDB()->lastInsertId();
    }

    public function actualizar(int $id, array $d): bool
    {
        // If no new image was uploaded, leave the "imagen" column untouched
        // (so we don't wipe out the image the product already had).
        if (array_key_exists('imagen', $d) && $d['imagen'] !== null) {
            $stmt = getDB()->prepare(
                'UPDATE productos SET
                    codigo_barra = ?, nombre = ?, descripcion = ?, imagen = ?, id_categoria = ?,
                    id_unidad = ?, precio_compra = ?, precio_venta = ?, stock_minimo = ?
                 WHERE id_producto = ?'
            );
            return $stmt->execute([
                $d['codigo_barra'] ?: null, $d['nombre'], $d['descripcion'], $d['imagen'], $d['id_categoria'] ?: null,
                $d['id_unidad'], $d['precio_compra'], $d['precio_venta'], $d['stock_minimo'],
                $id,
            ]);
        }

        $stmt = getDB()->prepare(
            'UPDATE productos SET
                codigo_barra = ?, nombre = ?, descripcion = ?, id_categoria = ?,
                id_unidad = ?, precio_compra = ?, precio_venta = ?, stock_minimo = ?
             WHERE id_producto = ?'
        );
        return $stmt->execute([
            $d['codigo_barra'] ?: null, $d['nombre'], $d['descripcion'],
            $d['id_categoria'] ?: null, $d['id_unidad'],
            $d['precio_compra'], $d['precio_venta'], $d['stock_minimo'],
            $id,
        ]);
    }

    public function eliminar(int $id): bool
    {
        try {
            return getDB()->prepare('DELETE FROM productos WHERE id_producto = ?')->execute([$id]);
        } catch (PDOException $e) {
            // Code 23000 = foreign key violation (the product already has
            // stock-in or stock-out records and cannot be deleted).
            if ($e->getCode() === '23000') {
                throw new ProductoConMovimientosException(
                    'Cannot delete: this product already has stock-in or stock-out records.'
                );
            }
            throw $e;
        }
    }

    public function conAlertaStock(): array
    {
        return getDB()->query(
            'SELECT * FROM productos WHERE stock_actual <= stock_minimo ORDER BY stock_actual ASC'
        )->fetchAll();
    }

    public function contarTotal(): int
    {
        return (int) getDB()->query('SELECT COUNT(*) FROM productos')->fetchColumn();
    }

    /** Increases stock (used when registering an incoming stock entry). */
    public function incrementarStock(int $id, int $cantidad): void
    {
        getDB()->prepare('UPDATE productos SET stock_actual = stock_actual + ? WHERE id_producto = ?')
                ->execute([$cantidad, $id]);
    }

    /** Decreases stock (used when confirming a sale). */
    public function decrementarStock(int $id, int $cantidad): void
    {
        getDB()->prepare('UPDATE productos SET stock_actual = stock_actual - ? WHERE id_producto = ?')
                ->execute([$cantidad, $id]);
    }
}
