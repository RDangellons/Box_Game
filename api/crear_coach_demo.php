<?php
require_once __DIR__ . '/config.php';

$pdo = getPDO();

// Datos del coach de prueba
$nombreCompleto = 'Juan Coach';
$usuario        = 'coach';
$passwordPlano  = 'admin123';
$email          = 'coach@example.com';

$passwordHash = password_hash($passwordPlano, PASSWORD_DEFAULT);

// Insertar solo si no existe ese usuario
$stmt = $pdo->prepare("SELECT id FROM coaches WHERE usuario = :usuario LIMIT 1");
$stmt->execute([':usuario' => $usuario]);
$existe = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existe) {
    echo "Ya existe un coach con usuario '{$usuario}'.";
    exit;
}

$stmt = $pdo->prepare("
  INSERT INTO coaches (
    nombre_completo, usuario, password_hash, email
  ) VALUES (
    :nombre_completo, :usuario, :password_hash, :email
  )
");

$stmt->execute([
  ':nombre_completo' => $nombreCompleto,
  ':usuario'         => $usuario,
  ':password_hash'   => $passwordHash,
  ':email'           => $email
]);

echo "Coach creado. Usuario: {$usuario} / Password: {$passwordPlano}";
