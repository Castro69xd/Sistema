<?php
class ProveedorConMovimientosException extends Exception {}

class Proveedor
{
    public function todos(): array
    {
        return getDB()->query('SELECT * FROM proveedores ORDER BY nombre')->fetchAll();
    }

    public function buscar(int $id): ?array
    {
        $stmt = getDB()->prepare('SELECT * FROM proveedores WHERE id_proveedor = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function crear(array $d): int
    {
        $stmt = getDB()->prepare(
            'INSERT INTO proveedores (nombre, telefono, direccion, condicion_pago) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$d['nombre'], $d['telefono'], $d['direccion'], $d['condicion_pago']]);
        return (int) getDB()->lastInsertId();
    }

    public function actualizar(int $id, array $d): bool
    {
        $stmt = getDB()->prepare(
            'UPDATE proveedores SET nombre=?, telefono=?, direccion=?, condicion_pago=? WHERE id_proveedor=?'
        );
        return $stmt->execute([$d['nombre'], $d['telefono'], $d['direccion'], $d['condicion_pago'], $id]);
    }

    public function eliminar(int $id): bool
    {
        try {
            return getDB()->prepare('DELETE FROM proveedores WHERE id_proveedor = ?')->execute([$id]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                throw new ProveedorConMovimientosException(
                    'Cannot delete: this supplier already has stock-in records or linked products.'
                );
            }
            throw $e;
        }
    }
}
