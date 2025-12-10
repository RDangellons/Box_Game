<?php
session_start();

// Si ya hay coach logueado, mandarlo al dashboard
if (isset($_SESSION['coach_id'])) {
    header('Location: coach_dashboard.php');
    exit;
}

$mensajeError = '';
if (isset($_GET['error']) && $_GET['error'] === '1') {
    $mensajeError = 'Usuario o contraseña incorrectos.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Box Progressive | Panel Coach</title>
  <link rel="stylesheet" href="css/login.css" />
</head>
<body class="body-login">

  <main class="login-container">
    <section class="login-card">
      <h1 class="login-title">Panel del Coach</h1>
      <p class="login-subtitle">Acceso administrativo</p>

      <?php if ($mensajeError): ?>
        <div class="login-error">
          <?= htmlspecialchars($mensajeError) ?>
        </div>
      <?php endif; ?>

      <form action="procesar_login_coach.php" method="POST" class="login-form">
        <label>
          Usuario
          <input type="text" name="usuario" required autocomplete="username">
        </label>

        <label>
          Contraseña
          <input type="password" name="password" required autocomplete="current-password">
        </label>

        <button type="submit" class="btn-login">
          Ingresar al panel
        </button>
      </form>
    </section>
  </main>

</body>
</html>
