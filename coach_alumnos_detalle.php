<?php
session_start();
if (!isset($_SESSION['coach_id'])) {
    header('Location: coach_login.php');
    exit;
}

require_once __DIR__ . '/api/config.php';
$pdo = getPDO();

// Validar ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: coach_alumnos.php');
    exit;
}

// 1) PRIMERO: TRAER AL ALUMNO DE LA BD
$stmt = $pdo->prepare("
    SELECT
      id,
      nombre_completo,
      alias,
      categoria,
      rango,
      nivel,
      xp_actual,
      xp_max,
      fuerza,
      velocidad,
      defensa,
      resistencia,
      fecha_nacimiento,
      peso_kg,
      estatura_cm,
      tipo_sangre,
      alergias,
      padecimientos,
      notas_medicas,
      tipo_membresia,
      membresia_inicio,
      membresia_fin,
      foto_url,
      contacto_emergencia_nombre,
      contacto_emergencia_parentesco,
      contacto_emergencia_telefono,
      contacto_emergencia_telefono2,
      activo
    FROM alumnos
    WHERE id = :id
    LIMIT 1
");
$stmt->execute([':id' => $id]);
$alumno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$alumno) {
    header('Location: coach_alumnos.php');
    exit;
}


// Procesar formulario (POST) para actualizar datos
$mensajeOk = '';
$mensajeError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir y sanear datos
    $nombre_completo   = trim($_POST['nombre_completo'] ?? '');
    $alias             = trim($_POST['alias'] ?? '');
    $categoria         = trim($_POST['categoria'] ?? '');
    $rango             = trim($_POST['rango'] ?? '');
    $nivel             = (int)($_POST['nivel'] ?? 1);
    $xp_actual         = (int)($_POST['xp_actual'] ?? 0);
    $xp_max            = (int)($_POST['xp_max'] ?? 100);
    $fuerza            = (int)($_POST['fuerza'] ?? 50);
    $velocidad         = (int)($_POST['velocidad'] ?? 50);
    $defensa           = (int)($_POST['defensa'] ?? 50);
    $resistencia       = (int)($_POST['resistencia'] ?? 50);
    $tipo_membresia    = $_POST['tipo_membresia'] ?? 'Mensual';
    $membresia_inicio  = $_POST['membresia_inicio'] ?: null;
    $membresia_fin     = $_POST['membresia_fin'] ?: null;
    $activo            = isset($_POST['activo']) ? 1 : 0;
        // Ficha médica
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?: null;
    $peso_kg          = $_POST['peso_kg'] !== '' ? (float)$_POST['peso_kg'] : null;
    $estatura_cm      = $_POST['estatura_cm'] !== '' ? (float)$_POST['estatura_cm'] : null;
    $tipo_sangre      = trim($_POST['tipo_sangre'] ?? '');
    $alergias         = trim($_POST['alergias'] ?? '');
    $padecimientos    = trim($_POST['padecimientos'] ?? '');
    $notas_medicas    = trim($_POST['notas_medicas'] ?? '');

    // Contactos de emergencia
    $contacto_nombre      = trim($_POST['contacto_emergencia_nombre'] ?? '');
    $contacto_parentesco  = trim($_POST['contacto_emergencia_parentesco'] ?? '');
    $contacto_tel1        = trim($_POST['contacto_emergencia_telefono'] ?? '');
    $contacto_tel2        = trim($_POST['contacto_emergencia_telefono2'] ?? '');


        // Procesar foto (opcional)
    $nuevaFotoUrl = $alumno['foto_url']; // por defecto dejamos la que ya tiene

    if (isset($_FILES['foto_alumno']) && $_FILES['foto_alumno']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['foto_alumno'];

        if ($file['error'] === UPLOAD_ERR_OK) {
            $maxSize = 500 * 1024; // 500 KB
            if ($file['size'] > $maxSize) {
                $mensajeError = 'La foto es demasiado pesada. Máximo 500 KB.';
            } else {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                $ext = '';
                if ($mimeType === 'image/jpeg') {
                    $ext = '.jpg';
                } elseif ($mimeType === 'image/png') {
                    $ext = '.png';
                } else {
                    $mensajeError = 'Formato de imagen no permitido. Solo JPG o PNG.';
                }

                if ($ext !== '' && $mensajeError === '') {
                    // Generar nombre único para la imagen
                    $fileName = 'alumno_' . $id . '_' . time() . $ext;
                    $destino = __DIR__ . '/img/alumnos/' . $fileName;
                    $rutaRelativa = 'img/alumnos/' . $fileName;

                    if (move_uploaded_file($file['tmp_name'], $destino)) {
                        $nuevaFotoUrl = $rutaRelativa;
                    } else {
                        $mensajeError = 'No se pudo guardar la imagen en el servidor.';
                    }
                }
            }
        } else {
            $mensajeError = 'Error al subir la imagen (código ' . $file['error'] . ').';
        }
    }


    // Reglas básicas
    if ($nombre_completo === '' || $alias === '') {
        $mensajeError = 'El nombre y alias son obligatorios.';
    } else {
        try {
                         $stmtUpdate = $pdo->prepare("
                UPDATE alumnos
                SET
                  nombre_completo   = :nombre_completo,
                  alias             = :alias,
                  categoria         = :categoria,
                  rango             = :rango,
                  nivel             = :nivel,
                  xp_actual         = :xp_actual,
                  xp_max            = :xp_max,
                  fuerza            = :fuerza,
                  velocidad         = :velocidad,
                  defensa           = :defensa,
                  resistencia       = :resistencia,
                  fecha_nacimiento  = :fecha_nacimiento,
                  peso_kg           = :peso_kg,
                  estatura_cm       = :estatura_cm,
                  tipo_sangre       = :tipo_sangre,
                  alergias          = :alergias,
                  padecimientos     = :padecimientos,
                  notas_medicas     = :notas_medicas,
                  tipo_membresia    = :tipo_membresia,
                  membresia_inicio  = :membresia_inicio,
                  membresia_fin     = :membresia_fin,
                  foto_url          = :foto_url,
                  contacto_emergencia_nombre      = :contacto_nombre,
                  contacto_emergencia_parentesco  = :contacto_parentesco,
                  contacto_emergencia_telefono    = :contacto_tel1,
                  contacto_emergencia_telefono2   = :contacto_tel2,
                  activo            = :activo
                WHERE id = :id
                LIMIT 1
            ");
                $stmtUpdate->execute([
                ':nombre_completo'  => $nombre_completo,
                ':alias'            => $alias,
                ':categoria'        => $categoria,
                ':rango'            => $rango,
                ':nivel'            => $nivel,
                ':xp_actual'        => $xp_actual,
                ':xp_max'           => $xp_max,
                ':fuerza'           => max(0, min(100, $fuerza)),
                ':velocidad'        => max(0, min(100, $velocidad)),
                ':defensa'          => max(0, min(100, $defensa)),
                ':resistencia'      => max(0, min(100, $resistencia)),
                ':fecha_nacimiento' => $fecha_nacimiento,
                ':peso_kg'          => $peso_kg,
                ':estatura_cm'      => $estatura_cm,
                ':tipo_sangre'      => $tipo_sangre,
                ':alergias'         => $alergias,
                ':padecimientos'    => $padecimientos,
                ':notas_medicas'    => $notas_medicas,
                ':tipo_membresia'   => $tipo_membresia,
                ':membresia_inicio' => $membresia_inicio,
                ':membresia_fin'    => $membresia_fin,
                ':foto_url'         => $nuevaFotoUrl,
                ':contacto_nombre'     => $contacto_nombre,
                ':contacto_parentesco' => $contacto_parentesco,
                ':contacto_tel1'       => $contacto_tel1,
                ':contacto_tel2'       => $contacto_tel2,
                ':activo'           => $activo,
                ':id'               => $id,
            ]);
           
            $mensajeOk = 'Datos del alumno actualizados correctamente.';
        } catch (PDOException $e) {
            $mensajeError = 'Error al guardar los cambios.';
        }
    }


    // Traer datos del alumno
      $stmt = $pdo->prepare("
        SELECT
          id,
          nombre_completo,
          alias,
          categoria,
          rango,
          nivel,
          xp_actual,
          xp_max,
          fuerza,
          velocidad,
          defensa,
          resistencia,
          fecha_nacimiento,
          peso_kg,
          estatura_cm,
          tipo_sangre,
          alergias,
          padecimientos,
          notas_medicas,
          tipo_membresia,
          membresia_inicio,
          membresia_fin,
          foto_url,
          contacto_emergencia_nombre,
          contacto_emergencia_parentesco,
          contacto_emergencia_telefono,
          contacto_emergencia_telefono2,
          activo
        FROM alumnos
        WHERE id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    $alumno = $stmt->fetch(PDO::FETCH_ASSOC);
}





if (!$alumno) {
    header('Location: coach_alumnos.php');
    exit;
}

// Helper para días restantes
function calcularDiasRestantes($fechaFin) {
    if (!$fechaFin) return null;
    $hoy = new DateTime('today');
    $fin = DateTime::createFromFormat('Y-m-d', $fechaFin);
    if (!$fin) return null;
    $diff = $hoy->diff($fin);
    $dias = (int)$diff->format('%r%a');
    return $dias < 0 ? 0 : $dias;
}

$diasRestantes = calcularDiasRestantes($alumno['membresia_fin']);
$coachNombre = isset($_SESSION['coach_nombre']) ? $_SESSION['coach_nombre'] : 'Coach';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Box Progressive | Alumno: <?= htmlspecialchars($alumno['alias']) ?></title>
  <link rel="stylesheet" href="css/coach_alumnos_detalle.css" />
</head>
<body class="body-coach">

  <!-- Topbar móvil -->
  <div class="coach-mobile-topbar">
    <button class="coach-mobile-menu-btn" id="btnToggleSidebar">
      ☰
    </button>
    <span class="coach-mobile-topbar-title">Box Progressive · Coach</span>
  </div>

  <!-- Overlay -->
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
        <h1>Alumno: <?= htmlspecialchars($alumno['nombre_completo']) ?></h1>
        <p>Edita datos, stats y membresía.</p>
        <a href="coach_alumnos.php" class="btn-volver-lista">← Volver a alumnos</a>
      </header>

      <?php if ($mensajeOk): ?>
        <div class="alert alert-ok"><?= htmlspecialchars($mensajeOk) ?></div>
      <?php endif; ?>

      <?php if ($mensajeError): ?>
        <div class="alert alert-error"><?= htmlspecialchars($mensajeError) ?></div>
      <?php endif; ?>

     <form action="coach_alumnos_detalle.php?id=<?= (int)$alumno['id'] ?>" method="POST" class="alumno-form" enctype="multipart/form-data">
        <section class="alumno-grid">

         <!-- Avatar / foto -->
  <div class="alumno-card">
    <h2>Foto del alumno</h2>

    <div class="alumno-avatar-preview">
      <?php if (!empty($alumno['foto_url'])): ?>
        <img src="<?= htmlspecialchars($alumno['foto_url']) ?>" alt="Foto de <?= htmlspecialchars($alumno['nombre_completo']) ?>">
      <?php else: ?>
        <div class="alumno-avatar-placeholder">
          <?php
          // iniciales simples
          $nombreParts = explode(' ', preg_replace('/"/', '', $alumno['nombre_completo']));
          $ini1 = strtoupper(substr($nombreParts[0] ?? '', 0, 1));
          $ini2 = strtoupper(substr($nombreParts[count($nombreParts)-1] ?? '', 0, 1));
          echo htmlspecialchars($ini1 . $ini2);
          ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="field-group">
      <label>Subir nueva foto (JPG/PNG, máx. ~500 KB)
        <input type="file" name="foto_alumno" accept="image/jpeg,image/png">
      </label>
    </div>

    

    <?php if (!empty($alumno['foto_url'])): ?>
      <p class="membresia-info">
        Ruta actual: <code><?= htmlspecialchars($alumno['foto_url']) ?></code>
      </p>
    <?php endif; ?>
  </div>


          <!-- Datos generales -->
          <div class="alumno-card">
            <h2>Datos generales</h2>

            <div class="field-group">
              <label>Nombre completo
                <input type="text" name="nombre_completo" value="<?= htmlspecialchars($alumno['nombre_completo']) ?>" required>
              </label>
            </div>

            <div class="field-group">
              <label>Alias
                <input type="text" name="alias" value="<?= htmlspecialchars($alumno['alias']) ?>" required>
              </label>
            </div>

            <div class="field-row">
              <label>Categoría
                <input type="text" name="categoria" value="<?= htmlspecialchars($alumno['categoria']) ?>">
              </label>
              <label>Rango
                <input type="text" name="rango" value="<?= htmlspecialchars($alumno['rango']) ?>">
              </label>
            </div>

            <div class="field-row">
              <label>Nivel
                <input type="number" name="nivel" min="1" max="1000" value="<?= (int)$alumno['nivel'] ?>">
              </label>
              <label>Activo
                <input type="checkbox" name="activo" <?= $alumno['activo'] ? 'checked' : '' ?>>
              </label>
            </div>
          </div>

          <!-- XP y stats -->
          <div class="alumno-card">
            <h2>XP y estadísticas</h2>

            <div class="field-row">
              <label>XP actual
                <input type="number" name="xp_actual" min="0" value="<?= (int)$alumno['xp_actual'] ?>">
              </label>
              <label>XP máximo
                <input type="number" name="xp_max" min="1" value="<?= (int)$alumno['xp_max'] ?>">
              </label>
            </div>

            <div class="field-row">
              <label>Fuerza (0–100)
                <input type="number" name="fuerza" min="0" max="100" value="<?= (int)$alumno['fuerza'] ?>">
              </label>
              <label>Velocidad (0–100)
                <input type="number" name="velocidad" min="0" max="100" value="<?= (int)$alumno['velocidad'] ?>">
              </label>
            </div>

            <div class="field-row">
              <label>Defensa (0–100)
                <input type="number" name="defensa" min="0" max="100" value="<?= (int)$alumno['defensa'] ?>">
              </label>
              <label>Resistencia (0–100)
                <input type="number" name="resistencia" min="0" max="100" value="<?= (int)$alumno['resistencia'] ?>">
              </label>
            </div>
          </div>
          

          <!-- Membresía -->
          <div class="alumno-card">
            <h2>Membresía</h2>

            <div class="field-row">
              <label>Tipo de membresía
                <select name="tipo_membresia">
                  <option value="Mensual" <?= $alumno['tipo_membresia'] === 'Mensual' ? 'selected' : '' ?>>Mensual</option>
                  <option value="Semanal" <?= $alumno['tipo_membresia'] === 'Semanal' ? 'selected' : '' ?>>Semanal</option>
                  <option value="Otro" <?= $alumno['tipo_membresia'] === 'Otro' ? 'selected' : '' ?>>Otro</option>
                </select>
              </label>
            </div>

            <div class="field-row">
              <label>Inicio
                <input type="date" name="membresia_inicio" value="<?= htmlspecialchars($alumno['membresia_inicio'] ?? '') ?>">
              </label>
              <label>Fin
                <input type="date" name="membresia_fin" value="<?= htmlspecialchars($alumno['membresia_fin'] ?? '') ?>">
              </label>
            </div>

            <p class="membresia-info">
              Días restantes: 
              <?php if ($diasRestantes === null): ?>
                <span class="coach-table-muted">-</span>
              <?php else: ?>
                <strong><?= $diasRestantes ?></strong> día<?= $diasRestantes === 1 ? '' : 's' ?>
              <?php endif; ?>
            </p>
          </div>

            <!-- Ficha médica y contactos de emergencia -->
  <div class="alumno-card">
    <h2>Ficha médica & emergencia</h2>

    <div class="field-row">
      <label>Fecha de nacimiento
        <input type="date" name="fecha_nacimiento"
               value="<?= htmlspecialchars($alumno['fecha_nacimiento'] ?? '') ?>">
      </label>
    </div>

    <div class="field-row">
      <label>Peso (kg)
        <input type="number" step="0.1" name="peso_kg"
               value="<?= htmlspecialchars($alumno['peso_kg'] ?? '') ?>">
      </label>
      <label>Estatura (cm)
        <input type="number" step="0.1" name="estatura_cm"
               value="<?= htmlspecialchars($alumno['estatura_cm'] ?? '') ?>">
      </label>
    </div>

    <div class="field-row">
      <label>Tipo de sangre
        <select name="tipo_sangre">
          <?php
            $tipos = ['', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
            foreach ($tipos as $tipo) {
                $sel = ($alumno['tipo_sangre'] === $tipo) ? 'selected' : '';
                $label = $tipo === '' ? 'No especificado' : $tipo;
                echo "<option value=\"".htmlspecialchars($tipo)."\" $sel>$label</option>";
            }
          ?>
        </select>
      </label>
    </div>

    <div class="field-group">
      <label>Alergias
        <textarea name="alergias" rows="2"><?= htmlspecialchars($alergias = $alumno['alergias'] ?? '') ?></textarea>
      </label>
    </div>

    <div class="field-group">
      <label>Padecimientos / antecedentes
        <textarea name="padecimientos" rows="2"><?= htmlspecialchars($alumno['padecimientos'] ?? '') ?></textarea>
      </label>
    </div>

    <div class="field-group">
      <label>Notas médicas (ej. restricciones, recomendaciones)
        <textarea name="notas_medicas" rows="2"><?= htmlspecialchars($alumno['notas_medicas'] ?? '') ?></textarea>
      </label>
    </div>

    <hr style="border-color: rgba(31,41,55,0.8); margin: 0.5rem 0;">

    <h3 style="margin:0.3rem 0 0.4rem; font-size:0.9rem;">Contacto de emergencia</h3>

    <div class="field-group">
      <label>Nombre
        <input type="text" name="contacto_emergencia_nombre"
               value="<?= htmlspecialchars($alumno['contacto_emergencia_nombre'] ?? '') ?>">
      </label>
    </div>

    <div class="field-group">
      <label>Parentesco
        <input type="text" name="contacto_emergencia_parentesco"
               value="<?= htmlspecialchars($alumno['contacto_emergencia_parentesco'] ?? '') ?>">
      </label>
    </div>

    <div class="field-row">
      <label>Teléfono 1
        <input type="text" name="contacto_emergencia_telefono"
               value="<?= htmlspecialchars($alumno['contacto_emergencia_telefono'] ?? '') ?>">
      </label>
      <label>Teléfono 2
        <input type="text" name="contacto_emergencia_telefono2"
               value="<?= htmlspecialchars($alumno['contacto_emergencia_telefono2'] ?? '') ?>">
      </label>
    </div>
  </div>


        </section>

        <div class="alumno-form-actions">
          <button type="submit" class="btn-guardar">
            Guardar cambios
          </button>
        </div>
      </form>
    </main>
  </div>

  <script src="js/coach.js"></script>
</body>
</html>
