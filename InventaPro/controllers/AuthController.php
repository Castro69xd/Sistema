<?php
class AuthController
{
    private Usuario $modelo;

    public function __construct()
    {
        $this->modelo = new Usuario();
    }

    public function login(): void
    {
        $error = null;
        $usuario = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = trim($_POST['usuario'] ?? '');
            $pwd     = $_POST['password'] ?? '';

            $user = $this->modelo->buscarPorUsuario($usuario);

            if ($user && password_verify($pwd, $user['contrasena'])) {
                $_SESSION['user_id'] = $user['id_usuario'];
                $_SESSION['nombre']  = $user['nombre_completo'];
                $_SESSION['rol']     = $user['nombre_rol'];

                registrarAuditoria($user['id_usuario'], 'Logged in', 'usuarios');

                header('Location: index.php?accion=dashboard');
                exit;
            }

            $error = 'Incorrect username or password.';
        }

        require __DIR__ . '/../views/auth/login.php';
    }

    public function logout(): void
    {
        $user = currentUser();
        if ($user) {
            registrarAuditoria($user['id'], 'Logged out', 'usuarios');
        }
        $_SESSION = [];
        session_destroy();
        header('Location: index.php?accion=login');
        exit;
    }
}
