<?php
// procesar_login_coach.php
session_start();
require_once __DIR__ . '/api/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: coach_login.php');
    exit;
}

$usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if ($usuario === '' || $password === '') {
    header('Location: coach_login.php?error=1');
    exit;
}

$pdo = getPDO();

// Buscar coach por usuario
$stmt = $pdo->prepare("
    SELECT id, usuario, password_hash, nombre_completo
    FROM coaches
    WHERE usuario = :usuario AND activo = 1
    LIMIT 1
");
$stmt->execute([':usuario' => $usuario]);
$coach = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$coach) {
    // Usuario no existe o inactivo
    header('Location: coach_login.php?error=1');
    exit;
}

// Verificar contraseña
if (!password_verify($password, $coach['password_hash'])) {
    header('Location: coach_login.php?error=1');
    exit;
}

// Guardar sesión del coach
$_SESSION['coach_id'] = (int)$coach['id'];
$_SESSION['coach_nombre'] = $coach['nombre_completo'];

// (Opcional) limpiar sesión de alumno para no mezclar roles
unset($_SESSION['alumno_id']);

// Ir al dashboard
header('Location: coach_dashboard.php');
exit;
