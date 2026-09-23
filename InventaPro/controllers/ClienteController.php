<?php
class ClienteController
{
    private Cliente $modelo;

    public function __construct()
    {
        $this->modelo = new Cliente();
    }

    /**
     * JSON endpoint queried live from the sale form (when leaving the
     * "Customer name" field), to warn if this is already a frequent
     * customer and a discount will be applied.
     */
    public function info(): void
    {
        requireRole(['Administrator', 'Cashier']);
        header('Content-Type: application/json; charset=utf-8');

        $nombre = trim($_GET['nombre'] ?? '');
        if ($nombre === '') {
            echo json_encode(['nombre' => '']);
            exit;
        }

        $cliente = $this->modelo->buscarPorNombre($nombre);

        if (!$cliente) {
            echo json_encode(['nombre' => $nombre, 'nuevo' => true, 'frecuente' => false]);
            exit;
        }

        echo json_encode([
            'nombre'    => $nombre,
            'nuevo'     => false,
            'compras'   => (int) $cliente['compras_totales'],
            'frecuente' => $this->modelo->esFrecuente($cliente),
            'descuento' => $this->modelo->esFrecuente($cliente) ? Cliente::DESCUENTO_FRECUENTE * 100 : 0,
        ]);
        exit;
    }
}
