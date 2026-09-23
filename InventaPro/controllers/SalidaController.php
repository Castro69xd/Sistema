<?php
class SalidaController
{
    private Salida $modelo;
    private Producto $productos;
    private Cliente $clientes;

    public function __construct()
    {
        $this->modelo = new Salida();
        $this->productos = new Producto();
        $this->clientes = new Cliente();
    }

    public function index(): void
    {
        requireRole(['Administrator', 'Cashier']);
        $salidas = $this->modelo->todas();
        require __DIR__ . '/../views/salidas/index.php';
    }

    /**
     * Cashier screen: search products, build the cart and confirm.
     * Implements UC-02 exactly as it appears in the sequence diagram.
     */
    public function nueva(): void
    {
        requireRole(['Administrator', 'Cashier']);
        $usuario = currentUser();
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $items = $this->itemsDelFormulario();
            $clienteNombre = trim($_POST['cliente_nombre'] ?? '');
            $metodoPago = in_array($_POST['metodo_pago'] ?? '', ['cash', 'card'], true)
                ? $_POST['metodo_pago']
                : 'cash';

            if (empty($items)) {
                $error = 'Add at least one product to the sale.';
            } else {
                try {
                    $idCliente = null;
                    $descuentoPct = 0;

                    if ($clienteNombre !== '') {
                        $cliente = $this->clientes->obtenerOCrear($clienteNombre);
                        $idCliente = $cliente['id_cliente'];
                        if ($this->clientes->esFrecuente($cliente)) {
                            $descuentoPct = Cliente::DESCUENTO_FRECUENTE;
                        }
                    }

                    $idSalida = $this->modelo->registrar(
                        $usuario['id'],
                        $items,
                        $idCliente,
                        $clienteNombre ?: null,
                        $metodoPago,
                        $descuentoPct
                    );

                    if ($idCliente) {
                        $this->clientes->incrementarCompras($idCliente);
                    }

                    registrarAuditoria(
                        $usuario['id'],
                        'Registered sale #' . $idSalida . ' with ' . count($items) . ' product(s)',
                        'salidas'
                    );
                    header('Location: index.php?accion=factura&id=' . $idSalida);
                    exit;
                } catch (StockInsuficienteException $e) {
                    // Same as the sequence diagram: the sale is blocked
                    // and the warning is shown, nothing gets registered.
                    $error = $e->getMessage();
                } catch (Throwable $e) {
                    $error = 'Could not register the sale. Please try again.';
                }
            }
        }

        // Full catalogue for the client-side live search
        $listaProductos = $this->productos->todos();
        require __DIR__ . '/../views/salidas/form.php';
    }

    /** Shows the invoice for an already registered sale, ready to print / save as PDF. */
    public function factura(?string $id): void
    {
        requireRole(['Administrator', 'Cashier']);
        $salida = $this->modelo->buscar((int) $id);

        if (!$salida) {
            header('Location: index.php?accion=salidas');
            exit;
        }

        $detalle = $this->modelo->detalle((int) $id);
        $subtotal = array_sum(array_map(fn($d) => $d['cantidad'] * $d['precio_venta'], $detalle));
        $descuento = (float) $salida['descuento'];
        $total = $subtotal - $descuento;

        require __DIR__ . '/../views/salidas/factura.php';
    }

    private function itemsDelFormulario(): array
    {
        $productosIds = $_POST['id_producto'] ?? [];
        $cantidades   = $_POST['cantidad'] ?? [];
        $precios      = $_POST['precio_venta'] ?? [];

        $items = [];
        foreach ($productosIds as $i => $idProducto) {
            $cantidad = (int) ($cantidades[$i] ?? 0);
            $precio = (float) ($precios[$i] ?? 0);
            if ((int) $idProducto > 0 && $cantidad > 0) {
                $items[] = [
                    'id_producto'  => (int) $idProducto,
                    'cantidad'     => $cantidad,
                    'precio_venta' => $precio,
                ];
            }
        }
        return $items;
    }
}
