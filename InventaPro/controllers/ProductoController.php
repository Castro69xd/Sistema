<?php
class ProductoController
{
    private Producto $modelo;
    private Categoria $categorias;
    private UnidadMedida $unidades;

    public function __construct()
    {
        $this->modelo     = new Producto();
        $this->categorias = new Categoria();
        $this->unidades   = new UnidadMedida();
    }

    public function index(): void
    {
        requireRole(['Administrator', 'Warehouse Clerk', 'Cashier']);
        $productos = $this->modelo->todos();
        require __DIR__ . '/../views/productos/index.php';
    }

    public function crear(): void
    {
        requireRole(['Administrator', 'Warehouse Clerk']);
        $usuario = currentUser();

        $errores = [];
        $datos = [
            'codigo_barra' => '', 'nombre' => '', 'descripcion' => '',
            'id_categoria' => '', 'id_unidad' => '',
            'precio_compra' => '', 'precio_venta' => '',
            'stock_actual' => '0', 'stock_minimo' => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = $this->datosDelFormulario(true);
            $errores = $this->validar($datos, true);

            $rutaImagen = null;
            if (empty($errores)) {
                [$rutaImagen, $errorImagen] = $this->procesarImagenSubida();
                if ($errorImagen) $errores[] = $errorImagen;
            }

            if (empty($errores)) {
                $datos['imagen'] = $rutaImagen;
                $this->modelo->crear($datos);
                registrarAuditoria($usuario['id'], 'Created product "' . $datos['nombre'] . '"', 'productos');
                header('Location: index.php?accion=productos&ok=1');
                exit;
            }
        }

        $modo = 'crear';
        $listaCategorias = $this->categorias->todas();
        $listaUnidades = $this->unidades->todas();
        require __DIR__ . '/../views/productos/form.php';
    }

    public function editar(?string $id): void
    {
        requireRole(['Administrator', 'Warehouse Clerk']);
        $usuario = currentUser();
        $producto = $this->modelo->buscar((int) $id);

        if (!$producto) {
            header('Location: index.php?accion=productos');
            exit;
        }

        $errores = [];
        $datos = $producto;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = $this->datosDelFormulario(false);
            $errores = $this->validar($datos, false);

            $rutaImagen = null;
            if (empty($errores)) {
                [$rutaImagen, $errorImagen] = $this->procesarImagenSubida();
                if ($errorImagen) $errores[] = $errorImagen;
            }

            if (empty($errores)) {
                $datos['imagen'] = $rutaImagen; // null = keep the current image
                $this->modelo->actualizar((int) $id, $datos);
                registrarAuditoria($usuario['id'], 'Updated product "' . $datos['nombre'] . '"', 'productos');
                header('Location: index.php?accion=productos&ok=1');
                exit;
            }
            $datos['id_producto'] = $id;
            $datos['imagen'] = $producto['imagen'];
        }

        $modo = 'editar';
        $listaCategorias = $this->categorias->todas();
        $listaUnidades = $this->unidades->todas();
        require __DIR__ . '/../views/productos/form.php';
    }

    public function eliminar(?string $id): void
    {
        requireRole(['Administrator']);
        $usuario = currentUser();
        $producto = $this->modelo->buscar((int) $id);

        if ($producto && $_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->modelo->eliminar((int) $id);
                registrarAuditoria($usuario['id'], 'Deleted product "' . $producto['nombre'] . '"', 'productos');
                header('Location: index.php?accion=productos&ok=1');
                exit;
            } catch (ProductoConMovimientosException $e) {
                header('Location: index.php?accion=productos&error=' . urlencode($e->getMessage()));
                exit;
            }
        }

        header('Location: index.php?accion=productos');
        exit;
    }

    /**
     * Validates and stores the uploaded image (if the user picked one).
     * Returns [relativePath, error]. relativePath is null if no image
     * was uploaded (not an error, the field is optional).
     */
    private function procesarImagenSubida(): array
    {
        if (empty($_FILES['imagen']['name'])) {
            return [null, null];
        }

        $archivo = $_FILES['imagen'];

        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            return [null, 'There was an error uploading the image. Please try again.'];
        }

        $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $tipoReal = mime_content_type($archivo['tmp_name']);

        if (!isset($permitidos[$tipoReal])) {
            return [null, 'The image must be JPG, PNG or WEBP.'];
        }

        if ($archivo['size'] > 2 * 1024 * 1024) {
            return [null, 'The image must not be larger than 2 MB.'];
        }

        $carpetaDestino = __DIR__ . '/../public/uploads/productos/';
        if (!is_dir($carpetaDestino)) {
            mkdir($carpetaDestino, 0755, true);
        }

        $nombreArchivo = uniqid('prod_', true) . '.' . $permitidos[$tipoReal];
        $rutaCompleta = $carpetaDestino . $nombreArchivo;

        if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            return [null, 'The image could not be saved on the server.'];
        }

        return ['public/uploads/productos/' . $nombreArchivo, null];
    }

    private function datosDelFormulario(bool $incluyeStockInicial): array
    {
        $datos = [
            'codigo_barra'  => trim($_POST['codigo_barra'] ?? ''),
            'nombre'        => trim($_POST['nombre'] ?? ''),
            'descripcion'   => trim($_POST['descripcion'] ?? ''),
            'id_categoria'  => $_POST['id_categoria'] ?? '',
            'id_unidad'     => $_POST['id_unidad'] ?? '',
            'precio_compra' => $_POST['precio_compra'] ?? '',
            'precio_venta'  => $_POST['precio_venta'] ?? '',
            'stock_minimo'  => $_POST['stock_minimo'] ?? '',
        ];
        if ($incluyeStockInicial) {
            $datos['stock_actual'] = $_POST['stock_actual'] ?? '0';
        }
        return $datos;
    }

    private function validar(array $d, bool $esNuevo): array
    {
        $errores = [];
        if ($d['nombre'] === '') $errores[] = 'Name is required.';
        if (!is_numeric($d['precio_compra']) || $d['precio_compra'] < 0) $errores[] = 'Purchase price is not valid.';
        if (!is_numeric($d['precio_venta']) || $d['precio_venta'] < 0) $errores[] = 'Sale price is not valid.';
        if (!is_numeric($d['stock_minimo']) || $d['stock_minimo'] < 0) $errores[] = 'Minimum stock is not valid.';
        if ($esNuevo && (!is_numeric($d['stock_actual']) || $d['stock_actual'] < 0)) $errores[] = 'Initial stock is not valid.';
        if ($d['id_unidad'] === '') $errores[] = 'Select a unit of measure.';
        return $errores;
    }
}
