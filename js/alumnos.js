
// ALUMNO_ID viene desde alumno.php
const API_BASE_URL = 'http://localhost/Box_Game/api';
const alumnoId = typeof ALUMNO_ID !== 'undefined' ? ALUMNO_ID : 0;
const API_URL = `${API_BASE_URL}/get_alumno.php?id=${alumnoId}`;

// Función para calcular diferencia de días entre hoy y una fecha final
function calcularDiasRestantes(fechaFinStr) {
  if (!fechaFinStr) return 0;
  const hoy = new Date();
  const fechaFin = new Date(fechaFinStr + 'T23:59:59');
  const diffMs = fechaFin - hoy;
  const msPorDia = 1000 * 60 * 60 * 24;
  const dias = Math.ceil(diffMs / msPorDia);
  return dias < 0 ? 0 : dias;
}

// Función para calcular % de membresía usada
function calcularPorcentajeMembresia(inicioStr, finStr) {
  if (!inicioStr || !finStr) return 0;

  const inicio = new Date(inicioStr + 'T00:00:00');
  const fin = new Date(finStr + 'T23:59:59');
  const hoy = new Date();

  const totalMs = fin - inicio;
  if (totalMs <= 0) return 100;

  const usadoMs = Math.min(Math.max(hoy - inicio, 0), totalMs);
  const porcentajeUsado = (usadoMs / totalMs) * 100;
  return Math.round(porcentajeUsado);
}

// Obtener iniciales de un nombre
function obtenerIniciales(nombreCompleto) {
  if (!nombreCompleto) return '?';
  const partes = nombreCompleto
    .replace(/"/g, '')
    .split(' ')
    .filter(p => p.length > 0);

  if (partes.length === 1) {
    return partes[0].charAt(0).toUpperCase();
  }

  const primera = partes[0].charAt(0).toUpperCase();
  const ultima = partes[partes.length - 1].charAt(0).toUpperCase();
  return primera + ultima;
}

// Rellenar la tarjeta con datos del alumno
function renderAlumno(alumno) {


  // Saludo
  const alias = alumno.alias || alumno.nombre_completo;
  document.getElementById('alumno-alias').textContent = alias;

  // Avatar
  const avatarDiv = document.getElementById('avatar');
  const avatarInicialesEl = document.getElementById('avatar-iniciales');
  const iniciales = obtenerIniciales(alumno.nombre_completo);

  if (alumno.foto_url && alumno.foto_url.trim() !== '') {
    // Si hay foto, limpiamos el contenido y ponemos la imagen
    avatarDiv.innerHTML = '';
    const img = document.createElement('img');
    img.src = alumno.foto_url;
    img.alt = alumno.nombre_completo;
    avatarDiv.appendChild(img);
  } else {
    // Si no hay foto, mostramos las iniciales
    avatarInicialesEl.textContent = iniciales;
  }


  // Datos principales
  document.getElementById('alumno-nombre').textContent = alumno.nombre_completo;
  document.getElementById('alumno-rango').textContent = 'Rango: ' + (alumno.rango || '-');
  document.getElementById('alumno-categoria').textContent = 'Categoría: ' + (alumno.categoria || '-');
  document.getElementById('alumno-nivel').textContent = 'Nivel: LVL ' + (alumno.nivel || 0);

  // XP
  const xpActual = Number(alumno.xp_actual) || 0;
  const xpMax = Number(alumno.xp_max) || 100;
  const xpPorcentaje = Math.max(0, Math.min(100, Math.round((xpActual / xpMax) * 100)));

  document.getElementById('texto-xp').textContent =
    `EXP: ${xpActual} / ${xpMax}`;

  const xpBarFill = document.getElementById('xp-bar-fill');
  xpBarFill.style.width = xpPorcentaje + '%';



//Stats
function setStat(idBar, idTxt, value) {
  const v = Math.max(0, Math.min(100, Number(value) || 0));
  const elBar = document.getElementById(idBar);
  const elTxt = document.getElementById(idTxt);

  if (elTxt) elTxt.textContent = v;
  if (elBar) elBar.style.width = `${v}%`;
}

// Dentro de tu renderAlumno(data):
setStat('stat-fuerza', 'stat-fuerza-txt', alumno.fuerza);
setStat('stat-velocidad', 'stat-velocidad-txt', alumno.velocidad);
setStat('stat-defensa', 'stat-defensa-txt', alumno.defensa);
setStat('stat-resistencia', 'stat-resistencia-txt', alumno.resistencia);


  // Membresía
  const tipoMembresia = alumno.tipo_membresia || '-';
  const fechaFin = alumno.membresia_fin || null;
  const fechaInicio = alumno.membresia_inicio || null;

  document.getElementById('membresia-tipo').textContent = tipoMembresia;

  if (fechaFin) {
    const partes = fechaFin.split('-'); // YYYY-MM-DD
    document.getElementById('membresia-fin').textContent =
      `${partes[2]}/${partes[1]}/${partes[0]}`;
  } else {
    document.getElementById('membresia-fin').textContent = '-';
  }

  const diasRestantes = calcularDiasRestantes(fechaFin);
  document.getElementById('membresia-dias').textContent = diasRestantes;

  const porcentajeUsado = calcularPorcentajeMembresia(fechaInicio, fechaFin);
  const porcentajeRestante = Math.max(0, 100 - porcentajeUsado);

  const membresiaBarFill = document.getElementById('membresia-bar-fill');
  membresiaBarFill.style.width = porcentajeRestante + '%';

  // Estado
  const estadoEl = document.getElementById('membresia-estado');
  if (!fechaFin) {
    estadoEl.textContent = 'Sin registro';
    estadoEl.style.color = '#9ca3af';
  } else if (diasRestantes == 0) {
    estadoEl.textContent = '🔴 VENCIDA';
    estadoEl.style.color = '#f87171';
  } else if (diasRestantes <= 4) {
    estadoEl.textContent = '🟡 Por vencer pronto';
    estadoEl.style.color = '#facc15';
  } else {
    estadoEl.textContent = '🟢 Activa';
    estadoEl.style.color = '#4ade80';
  }
}

// Llamar a la API al cargar la página
document.addEventListener('DOMContentLoaded', () => {
  fetch(API_URL)
    .then(res => res.json())
    .then(json => {
      if (!json.ok) {
        console.error('Error desde API:', json.error);
        alert('No se pudo cargar la información del alumno.');
        return;
      }
      renderAlumno(json.data);
    })
    .catch(err => {
      console.error('Error de red:', err);
      alert('Error de conexión con el servidor.');
    });
});
