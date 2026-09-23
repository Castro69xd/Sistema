<?php
class Categoria
{
    public function todas(): array
    {
        return getDB()->query('SELECT * FROM categorias ORDER BY nombre')->fetchAll();
    }
}
