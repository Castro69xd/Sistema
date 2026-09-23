<?php
class Cliente
{
    /** From the 4th purchase onward (3 previous purchases), the customer is "frequent". */
    const COMPRAS_PARA_FRECUENTE = 3;

    /** Automatic discount for frequent customers: 5%. */
    const DESCUENTO_FRECUENTE = 0.05;

    public function buscarPorNombre(string $nombre): ?array
    {
        $stmt = getDB()->prepare('SELECT * FROM clientes WHERE nombre = ?');
        $stmt->execute([$nombre]);
        return $stmt->fetch() ?: null;
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = getDB()->prepare('SELECT * FROM clientes WHERE id_cliente = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function todos(): array
    {
        return getDB()->query(
            'SELECT * FROM clientes ORDER BY compras_totales DESC, nombre'
        )->fetchAll();
    }

    /** Returns the existing customer by name, or creates one if it doesn't exist. */
    public function obtenerOCrear(string $nombre): array
    {
        $existente = $this->buscarPorNombre($nombre);
        if ($existente) {
            return $existente;
        }

        $stmt = getDB()->prepare('INSERT INTO clientes (nombre) VALUES (?)');
        $stmt->execute([$nombre]);
        return $this->buscarPorId((int) getDB()->lastInsertId());
    }

    /** "Frequent" = already had COMPRAS_PARA_FRECUENTE or more purchases BEFORE this sale. */
    public function esFrecuente(array $cliente): bool
    {
        return $cliente['compras_totales'] >= self::COMPRAS_PARA_FRECUENTE;
    }

    public function incrementarCompras(int $idCliente): void
    {
        getDB()->prepare('UPDATE clientes SET compras_totales = compras_totales + 1 WHERE id_cliente = ?')
                ->execute([$idCliente]);
    }
}
