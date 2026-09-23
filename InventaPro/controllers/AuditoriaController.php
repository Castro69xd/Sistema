<?php
class AuditoriaController
{
    public function index(): void
    {
        requireRole(['Administrator']);
        $registros = (new Auditoria())->recientes(100);
        require __DIR__ . '/../views/auditoria/index.php';
    }
}
