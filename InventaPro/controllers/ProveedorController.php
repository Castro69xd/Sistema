<?php
class ProveedorController
{
    private Proveedor $modelo;

    public function __construct()
    {
        $this->modelo = new Proveedor();
    }

    public function index(): void
    {
        requireRole(['Administrator', 'Warehouse Clerk']);
        $proveedores = $this->modelo->todos();
        require __DIR__ . '/../views/proveedores/index.php';
    }

    public function crear(): void
    {
        requireRole(['Administrator', 'Warehouse Clerk']);
        $usuario = currentUser();
        $errores = [];
        $datos = ['nombre' => '', 'telefono' => '', 'direccion' => '', 'condicion_pago' => 'Cash'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = $this->datosDelFormulario();
            $errores = $this->validar($datos);

            if (empty($errores)) {
                $this->modelo->crear($datos);
                registrarAuditoria($usuario['id'], 'Created supplier "' . $datos['nombre'] . '"', 'proveedores');
                header('Location: index.php?accion=proveedores&ok=1');
                exit;
            }
        }

        $modo = 'crear';
        require __DIR__ . '/../views/proveedores/form.php';
    }

    public function editar(?string $id): void
    {
        requireRole(['Administrator', 'Warehouse Clerk']);
        $usuario = currentUser();
        $proveedor = $this->modelo->buscar((int) $id);

        if (!$proveedor) {
            header('Location: index.php?accion=proveedores');
            exit;
        }

        $errores = [];
        $datos = $proveedor;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = $this->datosDelFormulario();
            $errores = $this->validar($datos);

            if (empty($errores)) {
                $this->modelo->actualizar((int) $id, $datos);
                registrarAuditoria($usuario['id'], 'Updated supplier "' . $datos['nombre'] . '"', 'proveedores');
                header('Location: index.php?accion=proveedores&ok=1');
                exit;
            }
            $datos['id_proveedor'] = $id;
        }

        $modo = 'editar';
        require __DIR__ . '/../views/proveedores/form.php';
    }

    public function eliminar(?string $id): void
    {
        requireRole(['Administrator']);
        $usuario = currentUser();
        $proveedor = $this->modelo->buscar((int) $id);

        if ($proveedor && $_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->modelo->eliminar((int) $id);
                registrarAuditoria($usuario['id'], 'Deleted supplier "' . $proveedor['nombre'] . '"', 'proveedores');
                header('Location: index.php?accion=proveedores&ok=1');
                exit;
            } catch (ProveedorConMovimientosException $e) {
                header('Location: index.php?accion=proveedores&error=' . urlencode($e->getMessage()));
                exit;
            }
        }

        header('Location: index.php?accion=proveedores');
        exit;
    }

    private function datosDelFormulario(): array
    {
        return [
            'nombre'         => trim($_POST['nombre'] ?? ''),
            'telefono'       => trim($_POST['telefono'] ?? ''),
            'direccion'      => trim($_POST['direccion'] ?? ''),
            'condicion_pago' => $_POST['condicion_pago'] ?? 'Cash',
        ];
    }

    private function validar(array $d): array
    {
        $errores = [];
        if ($d['nombre'] === '') $errores[] = 'Supplier name is required.';
        return $errores;
    }
}
