<?php
session_start();
if (!isset($_SESSION['coach_id'])) {
    header('Location: coach_login.php');
    exit;
}

$coachNombre = isset($_SESSION['coach_nombre']) ? $_SESSION['coach_nombre'] : 'Coach';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Box Progressive | Dashboard Coach</title>
  <link rel="stylesheet" href="css/coach.css" />
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
        <a href="coach_dashboard.php" class="nav-item nav-item-active">📊 Dashboard</a>
        <a href="coach_alumnos.php" class="nav-item">👥 Alumnos</a>
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

     <!-- Main content -->
    <main class="coach-main">
      <header class="coach-main-header">
        <h1>Resumen general del gym</h1>
        <p>Bienvenido, <?= htmlspecialchars($coachNombre) ?> 👊</p>
      </header>

      <section class="coach-cards-grid">
        <article class="coach-card">
          <h2>Alumnos activos</h2>
          <p class="coach-card-number" id="card-alumnos-activos">–</p>
          <p class="coach-card-sub">Total de alumnos con membresía activa</p>
        </article>

        <article class="coach-card">
          <h2>Próximos torneos</h2>
          <p class="coach-card-number" id="card-torneos">–</p>
          <p class="coach-card-sub">Eventos próximos en el calendario</p>
        </article>

        <article class="coach-card">
          <h2>Retos activos</h2>
          <p class="coach-card-number" id="card-retos">–</p>
          <p class="coach-card-sub">Retos diarios/semanales vigentes</p>
        </article>

        <article class="coach-card">
          <h2>Insignias competitivas</h2>
          <p class="coach-card-number" id="card-insignias">–</p>
          <p class="coach-card-sub">Insignias exclusivas en juego</p>
        </article>
      </section>

      <!-- Aquí luego meteremos listas, tablas, etc. -->
    </main>
  </div>

  <script src="js/coach.js"></script>
</body>
</html>
