<?php
// alumno.php
session_start();
if (!isset($_SESSION['alumno_id'])) {
    header('Location: login.php');
    exit;
}
$alumnoId = (int)$_SESSION['alumno_id'];
?>



<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Box Progressive | Alumno</title>
  <link rel="stylesheet" href="css/alumnos.css" />
</head>
<body>

  <main class="screen-alumno">
    <!-- ENCABEZADO -->
    <header class="header-alumno">
  <div class="header-left">
    <h1 class="gym-name">Box Progressive Gym</h1>
    <p class="saludo">Hola, <span id="alumno-alias">Alumno</span> 🥊</p>
  </div>

  <a href="logout.php" class="btn-logout">
    Cerrar sesión
  </a>
</header>


    <!-- TARJETA PRINCIPAL DEL ALUMNO -->
    <section class="card-alumno">
      <!-- Parte superior: avatar + datos -->
      <div class="card-alumno-top">
        <div class="avatar" id="avatar">
          <!-- Si no hay foto, se ponen iniciales vía JS -->
          <span id="avatar-iniciales">AA</span>
        </div>
        <div class="info-principal">
          <h2 class="nombre-alumno" id="alumno-nombre">Nombre del alumno</h2>
          <p class="rango" id="alumno-rango">Rango: -</p>
          <p class="categoria" id="alumno-categoria">Categoría: -</p>
          <p class="nivel" id="alumno-nivel">Nivel: LVL 0</p>
        </div>
      </div>

      <!-- Bloque XP -->
      <div class="bloque-xp">
        <div class="xp-text">
          <span id="texto-xp">EXP: 0 / 100</span>
        </div>
        <div class="xp-bar">
          <div class="xp-bar-fill" id="xp-bar-fill"></div>
        </div>
      </div>

      <!-- Bloque Membresía -->
      <div class="bloque-membresia">
        <h3>⏳ Mi membresía</h3>
        <p>Tipo: <span id="membresia-tipo">-</span></p>
        <p>Vence: <span id="membresia-fin">-</span></p>
        <p>Días restantes: <span class="dias-restantes" id="membresia-dias">0</span></p>

        <div class="membresia-bar">
          <div class="membresia-bar-fill" id="membresia-bar-fill"></div>
        </div>

        <p class="membresia-estado">
          Estado: <span class="badge-estado" id="membresia-estado">-</span>
        </p>
      </div>
    </section>

    <!-- LUEGO ABAJO IRÁN: STATS, MENSAJES, RETOS, ETC. -->

  </main>

  <script>const ALUMNO_ID = <?= $alumnoId ?></script>

  <script src="js/alumnos.js"></script>
</body>
</html>
