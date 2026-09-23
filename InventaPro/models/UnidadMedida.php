<?php
class UnidadMedida
{
    public function todas(): array
    {
        return getDB()->query('SELECT * FROM unidades_medida ORDER BY nombre')->fetchAll();
    }
}
