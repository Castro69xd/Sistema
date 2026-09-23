<?php
class Auditoria
{
    /** Returns the most recent audit log entries. */
    public function recientes(int $limite = 50): array
    {
        $stmt = getDB()->prepare(
            'SELECT a.*, u.nombre_completo AS usuario
             FROM auditoria a
             JOIN usuarios u ON u.id_usuario = a.id_usuario
             ORDER BY a.fecha_hora DESC
             LIMIT ?'
        );
        $stmt->bindValue(1, $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
