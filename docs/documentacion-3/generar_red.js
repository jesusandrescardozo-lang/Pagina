/**
 * Documentación 3 — Red de proyecto NUTRILINK
 * Genera el cálculo CPM (método de precedencias PDM) y su conversión al
 * método de flechas (ADM), exportando:
 *   - red.json   (actividades, cálculos, eventos ADM, ficticias)
 *   - pdm.mmd    (diagrama Mermaid PDM / actividad sobre nodo)
 *   - adm.mmd    (diagrama Mermaid ADM / actividad sobre flecha)
 *
 * Uso:  node generar_red.js
 */
const fs = require('fs');
const path = require('path');

/* ------------------------------------------------------------------ *
 * 1. Inventario de actividades (ciclo de vida completo del sistema)
 *    d = duración en días hábiles; preds = actividades predecesoras
 * ------------------------------------------------------------------ */
const ACTS = [
  // Fase 1 — Inicio
  { id: 'A1', fase: '1. Inicio',       nombre: 'Levantamiento de requerimientos',        corto: 'Levantamiento de req.',        d: 10, preds: [] },
  { id: 'A2', fase: '1. Inicio',       nombre: 'Estudio de factibilidad y alcance',      corto: 'Factibilidad y alcance',       d: 5,  preds: ['A1'] },
  { id: 'A3', fase: '1. Inicio',       nombre: 'Acta de inicio del proyecto',            corto: 'Acta de inicio',               d: 2,  preds: ['A2'] },

  // Fase 2 — Análisis
  { id: 'B1', fase: '2. Análisis',     nombre: 'Requisitos funcionales y no funcionales', corto: 'Requisitos',                   d: 8,  preds: ['A3'] },
  { id: 'B2', fase: '2. Análisis',     nombre: 'Modelamiento de procesos (casos de uso / DFD)', corto: 'Modelo de procesos',      d: 6,  preds: ['B1'] },
  { id: 'B3', fase: '2. Análisis',     nombre: 'Diseño conceptual de datos (modelo ER)',  corto: 'Modelo ER de datos',           d: 5,  preds: ['B1'] },

  // Fase 3 — Diseño
  { id: 'C1', fase: '3. Diseño',       nombre: 'Diseño arquitectónico y técnico (Laravel 9 + SQLite)', corto: 'Diseño arquitectónico', d: 6, preds: ['B2', 'B3'] },
  { id: 'C2', fase: '3. Diseño',       nombre: 'Diseño de base de datos (esquema físico)', corto: 'Diseño de BD',               d: 5,  preds: ['B3'] },
  { id: 'C3', fase: '3. Diseño',       nombre: 'Diseño de interfaces (mockups)',         corto: 'Mockups de interfaz',          d: 7,  preds: ['B2'] },
  { id: 'C4', fase: '3. Diseño',       nombre: 'Diseño de seguridad, roles y auditoría',  corto: 'Diseño de seguridad',          d: 4,  preds: ['B1'] },

  // Fase 4 — Construcción
  { id: 'D1', fase: '4. Construcción', nombre: 'Entorno base, dependencias y migraciones', corto: 'Entorno y migraciones',      d: 4,  preds: ['C1', 'C2'] },
  { id: 'D2', fase: '4. Construcción', nombre: 'Módulo de refugios e insumos (CRUD + ABC)', corto: 'Módulo refugios/insumos',    d: 8,  preds: ['D1', 'C3'] },
  { id: 'D3', fase: '4. Construcción', nombre: 'Módulo de kardex y lotes FEFO',           corto: 'Kardex y lotes FEFO',          d: 7,  preds: ['D2'] },
  { id: 'D4', fase: '4. Construcción', nombre: 'Módulo de transferencias inter-refugios',  corto: 'Transferencias',               d: 8,  preds: ['D3'] },
  { id: 'D5', fase: '4. Construcción', nombre: 'Modelos de inventario (EOQ, reorden, Newsvendor)', corto: 'Modelos de inventario',  d: 6, preds: ['D2'] },
  { id: 'D6', fase: '4. Construcción', nombre: 'Permisos, CSRF, throttling y bitácora',   corto: 'Seguridad y auditoría',        d: 6,  preds: ['C4', 'D1'] },
  { id: 'D7', fase: '4. Construcción', nombre: 'Reportes PDF (dompdf) y panel Dashboard', corto: 'Reportes PDF y dashboard',     d: 5,  preds: ['D2'] },

  // Fase 5 — Pruebas
  { id: 'E1', fase: '5. Pruebas',      nombre: 'Pruebas unitarias e de integración',      corto: 'Pruebas unitarias',            d: 6,  preds: ['D4', 'D5', 'D6', 'D7'] },
  { id: 'E2', fase: '5. Pruebas',      nombre: 'Pruebas E2E/HTTP y matriz de permisos',   corto: 'Pruebas E2E',                  d: 5,  preds: ['E1'] },
  { id: 'E3', fase: '5. Pruebas',      nombre: 'Pruebas de usabilidad (UAT) con roles demo', corto: 'Usabilidad (UAT)',          d: 4,  preds: ['E2'] },

  // Fase 6 — Implantación
  { id: 'F1', fase: '6. Implantación', nombre: 'Despliegue (Docker/Render) y datos semilla', corto: 'Despliegue',                 d: 4,  preds: ['E3'] },
  { id: 'F2', fase: '6. Implantación', nombre: 'Capacitación de usuarios',                corto: 'Capacitación',                 d: 4,  preds: ['F1'] },
  { id: 'F3', fase: '6. Implantación', nombre: 'Puesta en producción (corte operativo)',  corto: 'Puesta en producción',         d: 2,  preds: ['F2', 'G1'] },

  // Fase 7 — Cierre
  { id: 'G1', fase: '7. Cierre',       nombre: 'Documentación y manual de usuario',        corto: 'Manual de usuario',            d: 4,  preds: ['E3'] },
  { id: 'G2', fase: '7. Cierre',       nombre: 'Cierre administrativo y lecciones aprendidas', corto: 'Cierre y lecciones',       d: 2,  preds: ['F3', 'G1'] },
];

/* ------------------------------------------------------------------ *
 * 2. Validaciones básicas de la lista de actividades
 * ------------------------------------------------------------------ */
const byId = {};
ACTS.forEach((a) => { if (byId[a.id]) throw new Error('ID duplicado: ' + a.id); byId[a.id] = a; });
ACTS.forEach((a) => a.preds.forEach((p) => { if (!byId[p]) throw new Error(a.id + ' referencia inexistente ' + p); }));

const succ = {};
ACTS.forEach((a) => { succ[a.id] = []; });
ACTS.forEach((a) => a.preds.forEach((p) => succ[p].push(a.id)));

// Orden topológico (Kahn) + detección de ciclos
const indeg = {};
ACTS.forEach((a) => { indeg[a.id] = a.preds.length; });
let cola = ACTS.filter((a) => indeg[a.id] === 0).map((a) => a.id);
const order = [];
while (cola.length) {
  const id = cola.shift();
  order.push(id);
  succ[id].forEach((s) => { if (--indeg[s] === 0) cola.push(s); });
}
if (order.length !== ACTS.length) throw new Error('La red contiene ciclos o actividades inalcanzables.');

/* ------------------------------------------------------------------ *
 * 3. PASO — cálculo hacia adelante / PAST — cálculo hacia atrás (PDM)
 * ------------------------------------------------------------------ */
const ES = {}, EF = {}, LS = {}, LF = {}, HT = {}, crit = {};
order.forEach((id) => {
  const a = byId[id];
  const es = a.preds.length ? Math.max(...a.preds.map((p) => EF[p])) : 0;
  ES[id] = es;
  EF[id] = es + a.d;
});
const DURACION = Math.max(...ACTS.map((a) => EF[a.id]));
[...order].reverse().forEach((id) => {
  const s = succ[id];
  const lf = s.length ? Math.min(...s.map((x) => LS[x])) : DURACION;
  LF[id] = lf;
  LS[id] = lf - byId[id].d;
  HT[id] = LS[id] - ES[id];
  crit[id] = HT[id] === 0;
});
const rutaCritica = order.filter((id) => crit[id]);
const sumaRutaCritica = rutaCritica.reduce((t, id) => t + byId[id].d, 0);
if (sumaRutaCritica !== DURACION) throw new Error('La ruta crítica no cuadra con la duración del proyecto.');

/* ------------------------------------------------------------------ *
 * 4. ADM — conversión a método de flechas (actividad sobre flecha)
 *    Eventos (nodos) = instantes sin duración.
 *    Reglas aplicadas:
 *      S(a) = evento de inicio (predecesoras completadas)
 *      E(a) = evento de fin; si todas las sucesoras comparten el mismo
 *             conjunto de predecesoras, la actividad termina en ese evento;
 *             si no, termina en su propio evento "recién terminada" y se
 *             conectan actividades ficticias (dummy).
 * ------------------------------------------------------------------ */
const claves = (set) => 'S:{' + [...set].sort().join(',') + '}';
function Ekey(a) {
  const s = succ[a.id];
  if (s.length === 0) return 'END';
  const keys = new Set(s.map((x) => claves(byId[x].preds)));
  if (keys.size === 1) return [...keys][0];
  return 'S:{' + a.id + '}';
}
function Skey(a) {
  return a.preds.length === 0 ? 'START' : claves(a.preds);
}

let S = {}, E = {};
const overrides = {}; // actividad -> evento de fin impuesto (resuelve flechas paralelas)
let intentos = 0;
function construirEventos() {
  if (++intentos > 100) throw new Error('No fue posible resolver las flechas paralelas del ADM.');
  S = {}; E = {};
  ACTS.forEach((a) => { S[a.id] = Skey(a); E[a.id] = overrides[a.id] || Ekey(a); });
  // Detección de flechas paralelas (2 actividades con mismo origen y destino)
  const vistos = {};
  for (const a of ACTS) {
    const k = S[a.id] + '>' + E[a.id];
    if (vistos[k]) {
      // Norma ADM: no se permiten dos flechas iguales -> se separa una de
      // ellas con su propio evento de fin unido por una ficticia.
      const otro = vistos[k];
      overrides[otro] = 'S:{' + otro + '}';
      return construirEventos(); // revalidar todo
    }
    vistos[k] = a.id;
  }
}
construirEventos();

// Actividades ficticias: cuando E(predecesora) != S(sucesora)
const dummies = [];
const vistosDummy = new Set();
ACTS.forEach((a) => succ[a.id].forEach((s) => {
  if (E[a.id] !== S[s]) {
    const k = E[a.id] + '>' + S[s];
    if (!vistosDummy.has(k)) { vistosDummy.add(k); dummies.push({ from: E[a.id], to: S[s], pre: a.id, post: s }); }
  }
}));

// Grafo de eventos
const nodos = new Set(['START', 'END']);
ACTS.forEach((a) => { nodos.add(S[a.id]); nodos.add(E[a.id]); });
const aristas = [];
ACTS.forEach((a) => aristas.push({ from: S[a.id], to: E[a.id], tipo: 'real', act: a.id, d: a.d }));
dummies.forEach((dd, i) => aristas.push({ from: dd.from, to: dd.to, tipo: 'dummy', act: 'd' + (i + 1), d: 0 }));

// Orden topológico de eventos para numerarlos (número de origen < destino)
const adj = {};
[...nodos].forEach((n) => { adj[n] = []; });
aristas.forEach((e) => adj[e.from].push(e.to));
const indegN = {};
[...nodos].forEach((n) => { indegN[n] = 0; });
aristas.forEach((e) => { indegN[e.to] += 1; });
let cq = [...nodos].filter((n) => indegN[n] === 0);
if (cq.length !== 1 || cq[0] !== 'START') throw new Error('El ADM debe tener un único evento inicial. Encontrados: ' + cq.join(','));
const topoN = [];
const indegWork = { ...indegN };
const work = new Set([...nodos]);
while (cq.length) {
  const n = cq.shift();
  topoN.push(n);
  adj[n].forEach((m) => { if (--indegWork[m] === 0) cq.push(m); });
}
if (topoN.length !== nodos.size) throw new Error('El ADM tiene ciclos.');
const num = {};
topoN.forEach((n, i) => { num[n] = i + 1; });
const numEvento = (clave) => num[clave];
if (num[topoN[topoN.length - 1]] !== nodos.size || topoN[topoN.length - 1] !== 'END')
  throw new Error('El evento final debe ser el último numerado.');

// PASO sobre eventos: TE (tiempo más temprano)
const TE = {}; TE.START = 0;
topoN.forEach((n) => {
  if (n === 'START') return;
  const ent = aristas.filter((e) => e.to === n);
  TE[n] = Math.max(...ent.map((e) => TE[e.from] + e.d));
});
// PAST sobre eventos: TL (tiempo más tardío)
const TL = {}; TL.END = DURACION;
[...topoN].reverse().forEach((n) => {
  if (n === 'END') return;
  const sal = aristas.filter((e) => e.from === n);
  TL[n] = Math.min(...sal.map((e) => TL[e.to] - e.d));
});

// Verificación: los tiempos del ADM coinciden con los del PDM
// (en ADM el fin más temprano es TE(origen)+d; el evento de destino puede
//  recibir otras actividades, por eso TE(destino) >= fin más temprano)
ACTS.forEach((a) => {
  if (TE[S[a.id]] !== ES[a.id]) throw new Error(`ADM/PDM desalineado en ${a.id}: TE inicio ${TE[S[a.id]]} != PASO ${ES[a.id]}`);
  if (TE[S[a.id]] + a.d !== EF[a.id]) throw new Error(`ADM/PDM desalineado en ${a.id}: TE fin ${TE[S[a.id]] + a.d} != PASO fin ${EF[a.id]}`);
  if (TL[E[a.id]] !== LF[a.id]) throw new Error(`ADM/PDM desalineado en ${a.id}: TL fin ${TL[E[a.id]]} != PAST fin ${LF[a.id]}`);
  if (TL[E[a.id]] - a.d !== LS[a.id]) throw new Error(`ADM/PDM desalineado en ${a.id}: LS ${TL[E[a.id]] - a.d} != PAST inicio ${LS[a.id]}`);
});

/* ------------------------------------------------------------------ *
 * 5. Salidas
 * ------------------------------------------------------------------ */
const OUT = path.resolve(__dirname);
const datos = {
  proyecto: 'NUTRILINK — Sistema de Gestión de Inventarios Médicos y Alimentarios en Refugios',
  unidad: 'día hábil',
  duracion: DURACION,
  rutaCritica,
  sumaRutaCritica,
  actividades: ACTS.map((a) => ({
    ...a,
    sucesoras: succ[a.id],
    ES: ES[a.id], EF: EF[a.id], LS: LS[a.id], LF: LF[a.id],
    HT: HT[a.id], critica: crit[a.id],
    adm: {
      i: numEvento(S[a.id]),
      j: numEvento(E[a.id]),
      IT: TE[S[a.id]],          // inicio más temprano (evento de origen)
      FT: TE[S[a.id]] + a.d,    // fin más temprano
      LT: LS[a.id],             // inicio más tardío
      LF: LF[a.id],             // fin más tardío (evento de destino)
      HT: HT[a.id],
    },
  })),
  eventos: topoN.map((n, idx) => ({
    evento: idx + 1,
    clave: n,
    etiqueta: n === 'START' ? 'Inicio' : (n === 'END' ? 'Fin' : ''),
    entrantes: aristas.filter((e) => e.to === n).map((e) => (e.tipo === 'real' ? e.act : e.act + '*')),
    salientes: aristas.filter((e) => e.from === n).map((e) => (e.tipo === 'real' ? e.act : e.act + '*')),
    TE: TE[n], TL: TL[n],
    holgura: TL[n] - TE[n],
    critico: TL[n] - TE[n] === 0,
  })),
  ficticias: dummies.map((d, i) => ({ id: 'd' + (i + 1), de: numEvento(d.from), a: numEvento(d.to), pre: d.pre, post: d.post })),
  aristas: aristas.map((e) => ({ ...e, from: numEvento(e.from), to: numEvento(e.to) })),
};
fs.writeFileSync(path.join(OUT, 'red.json'), JSON.stringify(datos, null, 2), 'utf8');

/* --- Funciones de dibujo de los diagramas ---------------------------- */
const etiquetaPDM = (a, fantasma, parteOrigen) => {
  const est = crit[a.id] ? 'CRÍTICA' : `HT ${HT[a.id]}`;
  const extra = fantasma ? `<br/><i>enlace con la parte ${parteOrigen}</i>` : '';
  return `<b>${a.id}</b> ${a.corto}<br/>D: ${a.d} d | ${est}<br/>PASO: ${ES[a.id]} → ${EF[a.id]}<br/>PAST: ${LS[a.id]} → ${LF[a.id]}${extra}`;
};

const aristasNum = aristas.map((e) => ({ ...e, from: numEvento(e.from), to: numEvento(e.to) }));

/** Dibuja un diagrama PDM con las actividades indicadas (y sus fantasmas). */
function renderPDM(fichero, lista, fantasmas, parteOrigen = 1) {
  const todo = [...lista, ...fantasmas];
  const idSet = new Set(todo.map((a) => a.id));
  let txt = 'flowchart LR\n';
  [...new Set(todo.map((a) => a.fase))].forEach((f, i) => {
    txt += `  subgraph fase${i + 1}["${f}"]\n    direction TB\n`;
    todo.filter((a) => a.fase === f).forEach((a) => { txt += `    ${a.id}["${etiquetaPDM(a, fantasmas.includes(a), parteOrigen)}"]\n`; });
    txt += '  end\n';
  });
  const enlaces = [];
  todo.forEach((a) => a.preds.forEach((pr) => { if (idSet.has(pr)) enlaces.push([pr, a.id]); }));
  enlaces.forEach(([f, t]) => { txt += `  ${f} --> ${t}\n`; });
  txt += '  classDef critica fill:#ffd6d6,stroke:#c00000,stroke-width:3px,color:#000;\n';
  txt += '  classDef fantasma fill:#f7f7f7,stroke:#888,stroke-width:1px,stroke-dasharray:6 4,color:#444;\n';
  txt += '  classDef fantasmaCrit fill:#fff0f0,stroke:#c00000,stroke-width:2px,stroke-dasharray:6 4,color:#000;\n';
  lista.filter((a) => crit[a.id]).forEach((a) => { txt += `  class ${a.id} critica;\n`; });
  fantasmas.forEach((a) => { txt += `  class ${a.id} ${crit[a.id] ? 'fantasmaCrit' : 'fantasma'};\n`; });
  fs.writeFileSync(path.join(OUT, fichero), txt, 'utf8');
}

/** Dibuja un diagrama ADM con las actividades indicadas (actividad sobre flecha). */
function renderADM(fichero, acts, uniones) {
  const local = new Set();
  acts.forEach((a) => { local.add(numEvento(S[a.id])); local.add(numEvento(E[a.id])); });
  const lista = aristasNum.filter((e) => (e.tipo === 'real' ? acts.some((a) => a.id === e.act) : (local.has(e.from) && local.has(e.to))));
  const ids = new Set(lista.map((e) => e.act));
  let txt = 'flowchart LR\n';
  [...local].sort((x, y) => x - y).forEach((n) => {
    txt += n === 1 ? '  START((1))\n' : `  N${n}((${n}))\n`;
  });
  lista.forEach((e, i) => {
    const f = e.from === 1 ? 'START' : 'N' + e.from;
    const t = 'N' + e.to;
    if (e.tipo === 'real') {
      const a = byId[e.act];
      txt += `  ${f} -- "${a.id} (${a.d})" --> ${t}\n`;
    } else {
      txt += `  ${f} -. "${e.act} (0)" .-> ${t}\n`;
    }
  });
  txt += '  classDef union fill:#dce9ff,stroke:#1c4fd8,stroke-width:3px,color:#000;\n';
  uniones.forEach((n) => { if (local.has(n)) txt += `  class ${n === 1 ? 'START' : 'N' + n} union;\n`; });
  lista.forEach((e, i) => { if (e.tipo === 'real' && crit[e.act]) txt += `  linkStyle ${i} stroke:#c00000,stroke-width:3px;\n`; });
  if (!ids.size) throw new Error('Diagrama ADM vacío: ' + fichero);
  fs.writeFileSync(path.join(OUT, fichero), txt, 'utf8');
}

/* --- 1) PDM completo (referencia) --- */
renderPDM('pdm.mmd', ACTS, []);

/* --- 2) ADM completo (referencia) --- */
renderADM('adm.mmd', ACTS, []);

/* --- 3) Red dividida en tres partes (legible en hoja A4 horizontal) ---
 *   Parte 1: fases 1-3 (Inicio, Análisis, Diseño)
 *   Parte 2: fase 4 (Construcción)
 *   Parte 3: fases 5-7 (Pruebas, Implantación, Cierre)                    */
const faseNum = (a) => Number(a.fase.charAt(0));
const parte1 = ACTS.filter((a) => faseNum(a) <= 3);
const parte2 = ACTS.filter((a) => faseNum(a) === 4);
const parte3 = ACTS.filter((a) => faseNum(a) >= 5);
const fantasmasDe = (previa, actual) =>
  previa.filter((a) => succ[a.id].some((s) => actual.some((x) => x.id === s)));
const fantasmas2 = fantasmasDe(parte1, parte2);
const fantasmas3 = fantasmasDe(parte2, parte3);
if (fantasmasDe(parte1, parte3).length) throw new Error('Hay dependencias directas parte 1 → parte 3');
renderPDM('pdm_parte1.mmd', parte1, []);
renderPDM('pdm_parte2.mmd', parte2, fantasmas2, 1);
renderPDM('pdm_parte3.mmd', parte3, fantasmas3, 2);

const admP1 = ACTS.filter((a) => /^[ABC]/.test(a.id));
const admP2 = ACTS.filter((a) => /^D/.test(a.id));
const admP3 = ACTS.filter((a) => /^[EFG]/.test(a.id));
const eventosDe = (acts) => {
  const s = new Set();
  acts.forEach((a) => { s.add(numEvento(S[a.id])); s.add(numEvento(E[a.id])); });
  return s;
};
const ev1 = eventosDe(admP1);
const ev2 = eventosDe(admP2);
const ev3 = eventosDe(admP3);
const comunes = (x, y) => [...x].filter((n) => y.has(n)).sort((p, q) => p - q);
const uniones12 = comunes(ev1, ev2);
const uniones23 = comunes(ev2, ev3);
if (comunes(ev1, ev3).length) throw new Error('Eventos compartidos entre parte 1 y parte 3');
const uniones = [...uniones12, ...uniones23];
renderADM('adm_parte1.mmd', admP1, uniones);
renderADM('adm_parte2.mmd', admP2, uniones);
renderADM('adm_parte3.mmd', admP3, uniones);
datos.uniones = uniones;
datos.uniones12 = uniones12;
datos.uniones23 = uniones23;
datos.partesADM = [ev1, ev2, ev3].map((s) => [...s].sort((p, q) => p - q));
datos.fantasmasPDM = { parte2: fantasmas2.map((a) => a.id), parte3: fantasmas3.map((a) => a.id) };

fs.writeFileSync(path.join(OUT, 'red.json'), JSON.stringify(datos, null, 2), 'utf8');

/* --- Resumen por consola --- */
console.log('Actividades:', ACTS.length);
console.log('Duración total:', DURACION, 'días hábiles');
console.log('Ruta crítica  :', rutaCritica.join(' → '), '=', sumaRutaCritica);
console.log('Eventos ADM   :', nodos.size, '| Actividades ficticias:', dummies.length);
console.log('PDM  parte 1:', parte1.length, '| parte 2:', parte2.length, '+', fantasmas2.length, 'fantasmas | parte 3:', parte3.length, '+', fantasmas3.length, 'fantasmas');
console.log('ADM  eventos por parte:', datos.partesADM.map((p) => p[0] + '–' + p[p.length - 1]).join(' | '));
console.log('Eventos de unión: P1↔P2 =', uniones12.join(', '), '| P2↔P3 =', uniones23.join(', '));
console.log('Archivos: red.json, pdm*.mmd, adm*.mmd');
