<?php
class EntradaController
{
    private Entrada $modelo;
    private Producto $productos;
    private Proveedor $proveedores;

    public function __construct()
    {
        $this->modelo      = new Entrada();
        $this->productos   = new Producto();
        $this->proveedores = new Proveedor();
    }

    public function index(): void
    {
        requireRole(['Administrator', 'Warehouse Clerk']);
        $entradas = $this->modelo->todas();
        require __DIR__ . '/../views/entradas/index.php';
    }

    public function crear(): void
    {
        requireRole(['Administrator', 'Warehouse Clerk']);
        $usuario = currentUser();
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idProveedor = (int) ($_POST['id_proveedor'] ?? 0);
            $items = $this->itemsDelFormulario('precio_compra');

            if ($idProveedor <= 0) {
                $error = 'Select a supplier.';
            } elseif (empty($items)) {
                $error = 'Add at least one product to this entry.';
            } else {
                try {
                    $idEntrada = $this->modelo->registrar($idProveedor, $usuario['id'], $items);
                    registrarAuditoria(
                        $usuario['id'],
                        'Registered stock-in #' . $idEntrada . ' with ' . count($items) . ' product(s)',
                        'entradas'
                    );
                    header('Location: index.php?accion=entradas&ok=1');
                    exit;
                } catch (Throwable $e) {
                    $error = 'Could not register the entry. Please try again.';
                }
            }
        }

        $listaProductos = $this->productos->todos();
        $listaProveedores = $this->proveedores->todos();
        require __DIR__ . '/../views/entradas/form.php';
    }

    private function itemsDelFormulario(string $campoPrecio): array
    {
        $productos = $_POST['id_producto'] ?? [];
        $cantidades = $_POST['cantidad'] ?? [];
        $precios = $_POST[$campoPrecio] ?? [];

        $items = [];
        foreach ($productos as $i => $idProducto) {
            $cantidad = (int) ($cantidades[$i] ?? 0);
            $precio = (float) ($precios[$i] ?? 0);
            if ((int) $idProducto > 0 && $cantidad > 0) {
                $items[] = [
                    'id_producto' => (int) $idProducto,
                    'cantidad'    => $cantidad,
                    $campoPrecio  => $precio,
                ];
            }
        }
        return $items;
    }
}
