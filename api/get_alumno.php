<?php
// api/get_alumno.php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode([
        "ok" => false,
        "error" => "ID de alumno no válido"
    ]);
    exit;
}

$pdo = getPDO();

$stmt = $pdo->prepare("
    SELECT
      id,
      nombre_completo,
      alias,
      rango,
      categoria,
      nivel,
      xp_actual,
      xp_max,
      fuerza,
      velocidad,
      defensa,
      resistencia,
      tipo_membresia,
      membresia_inicio,
      membresia_fin,
      foto_url,
      activo
    FROM alumnos
    WHERE id = :id
    LIMIT 1
");

$stmt->execute([':id' => $id]);
$alumno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$alumno) {
    http_response_code(404);
    echo json_encode([
        "ok" => false,
        "error" => "Alumno no encontrado"
    ]);
    exit;
}

echo json_encode([
    "ok" => true,
    "data" => $alumno
]);
