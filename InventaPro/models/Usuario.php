<?php
class Usuario
{
    public function buscarPorUsuario(string $usuario): ?array
    {
        $stmt = getDB()->prepare(
            'SELECT u.*, r.nombre_rol
             FROM usuarios u
             JOIN roles r ON r.id_rol = u.id_rol
             WHERE u.usuario = ?'
        );
        $stmt->execute([$usuario]);
        return $stmt->fetch() ?: null;
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = getDB()->prepare(
            'SELECT u.*, r.nombre_rol
             FROM usuarios u
             JOIN roles r ON r.id_rol = u.id_rol
             WHERE u.id_usuario = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function todos(): array
    {
        return getDB()->query(
            'SELECT u.*, r.nombre_rol
             FROM usuarios u
             JOIN roles r ON r.id_rol = u.id_rol
             ORDER BY u.nombre_completo'
        )->fetchAll();
    }

    public function roles(): array
    {
        return getDB()->query('SELECT * FROM roles ORDER BY id_rol')->fetchAll();
    }
}
