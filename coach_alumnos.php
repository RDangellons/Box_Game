<?php
session_start();
if (!isset($_SESSION['coach_id'])) {
    header('Location: coach_login.php');
    exit;
}

require_once __DIR__ . '/api/config.php';
$pdo = getPDO();

// Traer alumnos
$stmt = $pdo->query("
    SELECT
      id,
      nombre_completo,
      alias,
      categoria,
      nivel,
      tipo_membresia,
      membresia_inicio,
      membresia_fin,
      activo
    FROM alumnos
    ORDER BY nombre_completo ASC
");
$alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

function calcularDiasRestantes($fechaFin) {
    if (!$fechaFin) return null;
    $hoy = new DateTime('today');
    $fin = DateTime::createFromFormat('Y-m-d', $fechaFin);
    if (!$fin) return null;
    $diff = $hoy->diff($fin);
    $dias = (int)$diff->format('%r%a');
    return $dias < 0 ? 0 : $dias;
}

$coachNombre = isset($_SESSION['coach_nombre']) ? $_SESSION['coach_nombre'] : 'Coach';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Box Progressive | Alumnos</title>
  <link rel="stylesheet" href="css/coach_alumnos.css" />
</head>
<body class="body-coach">

  <!-- Topbar móvil -->
  <div class="coach-mobile-topbar">
    <button class="coach-mobile-menu-btn" id="btnToggleSidebar">
      ☰
    </button>
    <span class="coach-mobile-topbar-title">Box Progressive · Coach</span>
  </div>

  <!-- Overlay para fondo oscuro en móvil -->
  <div class="coach-overlay" id="coachOverlay"></div>

  <div class="coach-layout">
    <!-- Sidebar -->
    <aside class="coach-sidebar" id="coachSidebar">
      <div class="coach-brand">
        <h2>Box Progressive</h2>
        <p>Panel del Coach</p>
      </div>

      <nav class="coach-nav">
        <a href="coach_dashboard.php" class="nav-item">📊 Dashboard</a>
        <a href="coach_alumnos.php" class="nav-item nav-item-active">👥 Alumnos</a>
        <a href="#" class="nav-item">💬 Mensajes</a>
        <a href="#" class="nav-item">📰 Noticias</a>
        <a href="#" class="nav-item">🥊 Torneos</a>
        <a href="#" class="nav-item">🎯 Retos</a>
        <a href="#" class="nav-item">🏅 Insignias</a>
        <a href="#" class="nav-item">🧩 Patrocinadores</a>
      </nav>

      <div class="coach-footer">
        <p><?= htmlspecialchars($coachNombre) ?></p>
        <a href="logout.php" class="btn-logout-coach">Cerrar sesión</a>
      </div>
    </aside>

    <!-- Main -->
    <main class="coach-main">
      <header class="coach-main-header">
        <h1>Alumnos</h1>
        <p>Gestiona los perfiles, membresías y estadísticas.</p>
      </header>

      <!-- Barra de acciones -->
      <section class="coach-toolbar">
        <div class="coach-toolbar-left">
          <span class="coach-toolbar-label">
            Total de alumnos: <?= count($alumnos) ?>
          </span>
        </div>
        <div class="coach-toolbar-right">
          <!-- luego: buscador, filtros, botón "Nuevo alumno" -->
        </div>
      </section>

      <!-- Tabla de alumnos -->
      <section class="coach-table-wrapper">
        <table class="coach-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Nombre</th>
              <th>Alias</th>
              <th>Categoría</th>
              <th>Nivel</th>
              <th>Membresía</th>
              <th>Días restantes</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($alumnos)): ?>
            <tr>
              <td colspan="9" class="coach-table-empty">
                No hay alumnos registrados todavía.
              </td>
            </tr>
          <?php else: ?>
            <?php
            $i = 1;
            foreach ($alumnos as $alumno):
              $diasRestantes = calcularDiasRestantes($alumno['membresia_fin']);
              $tipoMembresia = $alumno['tipo_membresia'] ?: '-';
              $categoria = $alumno['categoria'] ?: '-';

              if ($alumno['membresia_fin'] === null) {
                  $estadoMembresia = 'Sin registro';
                  $estadoClase = 'badge-gray';
              } elseif ($diasRestantes === 0) {
                  $estadoMembresia = 'Vencida';
                  $estadoClase = 'badge-red';
              } elseif ($diasRestantes <= 2) {
                  $estadoMembresia = 'Por vencer';
                  $estadoClase = 'badge-red';
              } elseif ($diasRestantes <= 7) {
                  $estadoMembresia = 'Por vencer pronto';
                  $estadoClase = 'badge-yellow';
              } else {
                  $estadoMembresia = 'Activa';
                  $estadoClase = 'badge-green';
              }

              $estadoAlumnoClase = $alumno['activo'] ? 'badge-green-soft' : 'badge-gray-soft';
              $estadoAlumnoTexto = $alumno['activo'] ? 'Activo' : 'Inactivo';
            ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($alumno['nombre_completo']) ?></td>
              <td><?= htmlspecialchars($alumno['alias']) ?></td>
              <td><?= htmlspecialchars($categoria) ?></td>
              <td><?= (int)$alumno['nivel'] ?></td>
              <td><?= htmlspecialchars($tipoMembresia) ?></td>
              <td>
                <?php if ($diasRestantes === null): ?>
                  <span class="coach-table-muted">-</span>
                <?php else: ?>
                  <?= $diasRestantes ?> día<?= $diasRestantes === 1 ? '' : 's' ?>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge <?= $estadoClase ?>">
                  <?= $estadoMembresia ?>
                </span>
              </td>
              <td>
                <a href="coach_alumnos_detalle.php?id=<?= (int)$alumno['id'] ?>" class="btn-table">
                  Ver
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </section>
    </main>
  </div>

  <script src="js/coach.js"></script>
</body>
</html>
