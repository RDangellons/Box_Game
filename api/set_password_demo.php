<?php
require_once __DIR__ . '/config.php';

$pdo = getPDO();

// Cambia estos valores:
$idAlumno = 3;
$usuario   = 'luis';      // lo que usará para iniciar sesión
$passwordPlano = '1234';  // contraseña simple de prueba

$passwordHash = password_hash($passwordPlano, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("
  UPDATE alumnos
  SET usuario = :usuario,
      password_hash = :password_hash
  WHERE id = :id
");
$stmt->execute([
  ':usuario' => $usuario,
  ':password_hash' => $passwordHash,
  ':id' => $idAlumno
]);

echo "Listo. Usuario: {$usuario} / Password: {$passwordPlano}";
