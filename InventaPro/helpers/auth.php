<?php
/**
 * Central authentication helper and role-based access control.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Requires an active session; redirects to login otherwise. */
function requireLogin(): void {
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?accion=login');
        exit;
    }
}

/**
 * Requires an active session AND one of the allowed roles.
 * Usage: requireRole(['Administrator', 'Warehouse Clerk']);
 */
function requireRole(array $allowedRoles): void {
    requireLogin();
    if (!in_array($_SESSION['rol'], $allowedRoles, true)) {
        http_response_code(403);
        require __DIR__ . '/../views/errores/403.php';
        exit;
    }
}

/** Returns the logged-in user's basic data, or null if not logged in. */
function currentUser(): ?array {
    return isset($_SESSION['user_id'])
        ? [
            'id'     => $_SESSION['user_id'],
            'nombre' => $_SESSION['nombre'],
            'rol'    => $_SESSION['rol'],
          ]
        : null;
}

/** Logs an action into the audit table (auditoria). */
function registrarAuditoria(int $idUsuario, string $accion, ?string $tabla = null): void {
    getDB()->prepare(
        'INSERT INTO auditoria (id_usuario, accion, tabla_afectada) VALUES (?, ?, ?)'
    )->execute([$idUsuario, $accion, $tabla]);
}
