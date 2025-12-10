<?php
// procesar_login.php
session_start();
require_once __DIR__ . '/api/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if ($usuario === '' || $password === '') {
    header('Location: login.php?error=1');
    exit;
}

$pdo = getPDO();

// Buscar alumno por usuario
$stmt = $pdo->prepare("
    SELECT id, usuario, password_hash
    FROM alumnos
    WHERE usuario = :usuario
    LIMIT 1
");
$stmt->execute([':usuario' => $usuario]);
$alumno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$alumno) {
    // Usuario no existe
    header('Location: login.php?error=1');
    exit;
}

// Verificar contraseña
if (!password_verify($password, $alumno['password_hash'])) {
    // Contraseña incorrecta
    header('Location: login.php?error=1');
    exit;
}

// Todo bien, guardamos la sesión
$_SESSION['alumno_id'] = (int)$alumno['id'];

// Redirigimos al dashboard del alumno
header('Location: alumnos.php');
exit;
