/**
 * Genera el documento "Documentación 3 — PDM y ADM — NutriLnk" (Word .docx)
 * a partir de red.json (cálculos CPM) y de los PNG renderizados.
 *
 * Uso: node build_doc.js   (ejecutar con Node disponible en pdfx)
 */
const fs = require('fs');
const path = require('path');
const {
  Document, Packer, Paragraph, TextRun, HeadingLevel, AlignmentType,
  Table, TableRow, TableCell, WidthType, BorderStyle, ImageRun,
  PageOrientation, Footer, PageNumber, VerticalAlign, TableLayoutType,
  ShadingType,
} = require('C:/Users/jesus/AppData/Local/Temp/opencode/pdfx/node_modules/docx');

const BASE = __dirname;
const OUT = path.join(BASE, 'Documentacion-3_PDM-ADM_NutriLnk.docx');
const red = JSON.parse(fs.readFileSync(path.join(BASE, 'red.json'), 'utf8'));

/* ---------------------------------------------------------------- helpers */
const BORDER = { style: BorderStyle.SINGLE, size: 4, color: '9E9E9E' };
const BORDERS = {
  top: BORDER, bottom: BORDER, left: BORDER, right: BORDER,
  insideHorizontal: BORDER, insideVertical: BORDER,
};

const run = (t, o = {}) => new TextRun({
  text: String(t),
  size: o.size || 22,
  bold: !!o.bold,
  italics: !!o.italics,
  color: o.color,
});

const para = (content, o = {}) => new Paragraph({
  children: Array.isArray(content) ? content : [typeof content === 'string' ? run(content, o) : content],
  alignment: o.align,
  spacing: { before: o.before || 0, after: o.after === undefined ? 120 : o.after, line: o.line },
  indent: o.indent,
  pageBreakBefore: !!o.pageBreak,
});

const h1 = (t, pageBreak) => new Paragraph({
  heading: HeadingLevel.HEADING_1,
  pageBreakBefore: !!pageBreak,
  spacing: { before: 240, after: 160 },
  children: [new TextRun({ text: t })],
});

const h2 = (t) => new Paragraph({
  heading: HeadingLevel.HEADING_2,
  spacing: { before: 200, after: 120 },
  children: [new TextRun({ text: t })],
});

const bullet = (t, level = 0) => new Paragraph({
  children: [typeof t === 'string' ? run(t) : t],
  bullet: { level },
  spacing: { after: 60 },
});

const numbered = (n, t) => new Paragraph({
  children: [run(n + '. ', { bold: true }), ...[].concat(t)],
  spacing: { after: 80 },
  indent: { left: 360, hanging: 360 },
});

function cell(t, w, o = {}) {
  return new TableCell({
    width: { size: w, type: WidthType.DXA },
    shading: o.shade ? { fill: o.shade, type: ShadingType.CLEAR, color: 'auto' } : undefined,
    verticalAlign: VerticalAlign.CENTER,
    margins: { top: 30, bottom: 30, left: 70, right: 70 },
    columnSpan: o.span,
    children: [new Paragraph({
      alignment: o.align || AlignmentType.LEFT,
      spacing: { after: 0 },
      children: [run(t, { size: o.size || 16, bold: o.bold, color: o.color })],
    })],
  });
}

const headerRow = (labels, widths, shade = 'D9E2F3') =>
  new TableRow({
    tableHeader: true,
    children: labels.map((l, i) => cell(l, widths[i], {
      bold: true, shade, size: 16,
      align: AlignmentType.CENTER,
    })),
  });

const mkTable = (widths, rows) => new Table({
  width: { size: widths.reduce((a, b) => a + b, 0), type: WidthType.DXA },
  columnWidths: widths,
  layout: TableLayoutType.FIXED,
  borders: BORDERS,
  rows,
});

const caption = (t) => para([run(t, { size: 17, italics: true })], {
  align: AlignmentType.CENTER, after: 200,
});

function pngSize(file) {
  const b = fs.readFileSync(file);
  return { w: b.readUInt32BE(16), h: b.readUInt32BE(20) };
}

/** Figura ajustada al ancho/ alto disponibles (px @96dpi). */
function figure(file, maxW, maxH) {
  const { w, h } = pngSize(file);
  let W = maxW;
  let H = Math.round((maxW * h) / w);
  if (H > maxH) { H = maxH; W = Math.round((maxH * w) / h); }
  return new Paragraph({
    alignment: AlignmentType.CENTER,
    spacing: { after: 60 },
    children: [new ImageRun({
      data: fs.readFileSync(file),
      type: 'png',
      transformation: { width: W, height: H },
    })],
  });
}

const sum = (a) => a.reduce((x, y) => x + y, 0);
const crit = (a) => (a.critica ? 'Sí' : 'No');
const lst = (arr) => (arr && arr.length ? arr.join(', ') : '—');

/* -------------------------------------------------- medidas de página (twips) */
const P_MARG = { top: 1134, right: 1134, bottom: 1134, left: 1134, header: 708, footer: 708 }; // 2 cm
const L_MARG = { top: 500, right: 500, bottom: 500, left: 500, header: 300, footer: 300 };
const PORTRAIT_W = 11906 - P_MARG.left - P_MARG.right;   // ~9638 twips (17 cm)
const LAND_W = 16838 - L_MARG.left - L_MARG.right;       // ~15138 twips (26,7 cm)
const FIG_W = 1040;   // px disponibles en horizontal
const FIG_H = 600;    // px de alto disponibles en horizontal (página sola)
const U12 = red.uniones12.join(', ');
const U23 = red.uniones23.join(', ');
const pageBreak = () => new Paragraph({ pageBreakBefore: true, spacing: { after: 0 }, children: [] });
const COL_W = 645;    // px disponibles en vertical

const newFooter = () => new Footer({
  children: [new Paragraph({
    alignment: AlignmentType.RIGHT,
    children: [
      new TextRun({ text: 'Documentación 3 · PDM y ADM · NutriLnk — ', size: 16, color: '808080' }),
      new TextRun({ children: [PageNumber.CURRENT], size: 16, color: '808080' }),
    ],
  })],
});

/* ================================================================ SECCIÓN 1 */
const s1 = [
  // --- portada ---
  para([], { after: 0 }),
  para([], { after: 0 }),
  para([run('DOCUMENTACIÓN 3', { size: 60, bold: true, color: '1F3864' })],
    { align: AlignmentType.CENTER, before: 2200, after: 200 }),
  para([run('DIAGRAMA DE MÉTODO DE PRECEDENCIAS (PDM)', { size: 32, bold: true })],
    { align: AlignmentType.CENTER, after: 80 }),
  para([run('DIAGRAMA DE MÉTODO DE FLECHAS (ADM)', { size: 32, bold: true })],
    { align: AlignmentType.CENTER, after: 500 }),
  para([run('Ciclo de vida completo del proyecto · Ruta crítica y holguras', { size: 24, italics: true })],
    { align: AlignmentType.CENTER, after: 1400 }),
  para([run('Proyecto: ' + red.proyecto, { size: 24 })],
    { align: AlignmentType.CENTER, after: 400 }),
  para([run('Unidad de tiempo: ' + red.unidad, { size: 24 })],
    { align: AlignmentType.CENTER, after: 2200 }),
  para([run('Asignatura / Materia: [pendiente]', { size: 24 })],
    { align: AlignmentType.CENTER, after: 200 }),
  para([run('Estudiante: [pendiente]', { size: 24 })],
    { align: AlignmentType.CENTER, after: 200 }),
  para([run('Docente: [pendiente]', { size: 24 })],
    { align: AlignmentType.CENTER, after: 200 }),
  para([run('Fecha: ' + new Date().toLocaleDateString('es-ES', { day: '2-digit', month: 'long', year: 'numeric' }), { size: 24 })],
    { align: AlignmentType.CENTER, after: 0 }),

  // --- 1. datos del proyecto ---
  h1('1. Datos generales del proyecto', true),
  para('La presente documentación desarrolla los dos diagramas de redes de proyecto exigidos para el sistema '
    + red.proyecto + ', abarcando el ciclo de vida completo del proyecto '
    + '(Inicio → Análisis → Diseño → Construcción → Pruebas → Implantación → Cierre).'),
  para('Tabla 1. Resumen de la red de proyecto.'),
  mkTable([3600, 6000], [
    headerRow(['Concepto', 'Valor'], [3600, 6000]),
    new TableRow({ children: [cell('Proyecto', 3600, { bold: true, shade: 'F2F2F2' }), cell(red.proyecto, 6000)] }),
    new TableRow({ children: [cell('Fases (ciclo de vida)', 3600, { bold: true, shade: 'F2F2F2' }), cell('7 (Inicio, Análisis, Diseño, Construcción, Pruebas, Implantación, Cierre)', 6000)] }),
    new TableRow({ children: [cell('Número de actividades', 3600, { bold: true, shade: 'F2F2F2' }), cell(String(red.actividades.length), 6000)] }),
    new TableRow({ children: [cell('Unidad de tiempo', 3600, { bold: true, shade: 'F2F2F2' }), cell(red.unidad, 6000)] }),
    new TableRow({ children: [cell('Duración total del proyecto', 3600, { bold: true, shade: 'F2F2F2' }), cell(red.duracion + ' días hábiles', 6000)] }),
    new TableRow({ children: [cell('Ruta crítica', 3600, { bold: true, shade: 'F2F2F2' }), cell(red.rutaCritica.join(' → ') + '  (' + red.sumaRutaCritica + ' días)', 6000)] }),
    new TableRow({ children: [cell('Actividades críticas (HT = 0)', 3600, { bold: true, shade: 'F2F2F2' }), cell(String(red.actividades.filter((a) => a.critica).length) + ' de ' + red.actividades.length, 6000)] }),
    new TableRow({ children: [cell('Eventos en el ADM', 3600, { bold: true, shade: 'F2F2F2' }), cell(String(red.eventos.length), 6000)] }),
    new TableRow({ children: [cell('Actividades ficticias (dummy)', 3600, { bold: true, shade: 'F2F2F2' }), cell(String(red.ficticias.length) + ' (' + red.ficticias.map((f) => f.id).join(', ') + ')', 6000)] }),
    new TableRow({ children: [cell('Eventos de unión entre partes (ADM)', 3600, { bold: true, shade: 'F2F2F2' }), cell(U12 + ' (partes 1–2) y ' + U23 + ' (partes 2–3)', 6000)] }),
  ]),

  // --- 2. pasos PDM ---
  h1('2. Pasos seguidos para elaborar el diagrama PDM'),
  para('El Método de Precedencias (PDM) representa cada actividad como un nodo y cada dependencia como una flecha. '
    + 'Se han seguido los pasos siguientes:'),
  numbered(1, 'Descomposición del proyecto en actividades (estructura de desglose): se dividió el ciclo de vida completo en 7 fases y ' + red.actividades.length + ' actividades identificadas con el código A1…G2 (A = Inicio, B = Análisis, C = Diseño, D = Construcción, E = Pruebas, F = Implantación, G = Cierre).'),
  numbered(2, 'Estimación de la duración de cada actividad (d) en días hábiles.'),
  numbered(3, 'Establecimiento de las relaciones de precedencia (predecesoras y sucesoras), es decir, qué actividad debe terminar antes de que empiece otra. Todas las relaciones son de tipo fin–inicio (FS).'),
  numbered(4, 'Pasada hacia adelante (PASO): se calculan el inicio temprano ES = máximo de los EF de las predecesoras (0 si no tiene predecesoras) y el fin temprano EF = ES + d.'),
  numbered(5, 'Pasada hacia atrás (PAST): se calculan el fin tardío LF = mínimo de los LS de las sucesoras (duración total si no tiene sucesoras) y el inicio tardío LS = LF − d.'),
  numbered(6, 'Cálculo de la holgura total (HT): HT = LS − ES = LF − EF.'),
  numbered(7, 'Identificación de la ruta crítica: secuencia de actividades con HT = 0; su suma es la duración mínima del proyecto.'),
  numbered(8, 'Comprobación de que el PDM no contiene ciclos y de que todas las actividades están conectadas al inicio y al final del proyecto.'),
  numbered(9, 'Conversión de la red a ADM (método de flechas) para obtener eventos, actividades ficticias y los tiempos de evento (TE/TL).'),

  // --- 3. normas PDM ---
  h1('3. Normas del método de precedencias (PDM)'),
];
s1.push(...[
  bullet('N1. Toda actividad del proyecto aparece una sola vez en la red, con un código identificativo único.'),
  bullet('N2. Toda actividad tiene una duración estimada (d) en la unidad de tiempo del proyecto.'),
  bullet('N3. Las flechas expresan exclusivamente relaciones de precedencia (no duración ni recursos); el tiempo reside en los nodos.'),
  bullet('N4. Cada actividad, salvo la inicial, tiene al menos una predecesora; y salvo la final, al menos una sucesora.'),
  bullet('N5. No existen ciclos (retrocesos) en la red: la red es un grafo dirigido acíclico.'),
  bullet('N6. Existe un único punto de inicio y un único punto de fin del proyecto.'),
  bullet('N7. Pasada hacia adelante (PASO): ES = máx(EF de predecesoras) y EF = ES + d.'),
  bullet('N8. Pasada hacia atrás (PAST): LF = mín(LS de sucesoras) y LS = LF − d.'),
  bullet('N9. Holgura total: HT = LS − ES = LF − EF. Si HT = 0, la actividad es crítica.'),
  bullet('N10. La duración del proyecto es el EF de la(s) actividad(es) final(es) y coincide con la suma de la ruta crítica.'),

  h2('3.1. Inventario de actividades'),
  para('Tabla 2. Inventario de actividades: duración, predecesoras y sucesoras.'),
]);

const wInv = [800, 1500, 3400, 600, 1670, 1668];
s1.push(mkTable(wInv, [
  headerRow(['ID', 'Fase', 'Actividad', 'd', 'Predecesoras', 'Sucesoras'], wInv),
  ...red.actividades.map((a) => new TableRow({
    children: [
      cell(a.id, wInv[0], { bold: true, align: AlignmentType.CENTER, size: 15 }),
      cell(a.fase, wInv[1], { size: 14 }),
      cell(a.nombre, wInv[2], { size: 14 }),
      cell(a.d, wInv[3], { align: AlignmentType.CENTER, size: 15 }),
      cell(lst(a.preds), wInv[4], { align: AlignmentType.CENTER, size: 15 }),
      cell(lst(a.sucesoras), wInv[5], { align: AlignmentType.CENTER, size: 15 }),
    ],
  })),
]));

/* ================================================================ SECCIÓN 2 (landscape): matriz */
const s2 = [
  h1('3.2. Matriz de dependencias (matriz de precedencias)'),
  para('Filas = actividad predecesora; columnas = actividad sucesora. La marca «X» indica que la actividad de la fila '
    + 'debe terminar antes de que empiece la de la columna.', { after: 160 }),
];
{
  const ids = red.actividades.map((a) => a.id);
  const wM = [900, ...ids.map(() => 568)];
  // celda compacta para la matriz (márgenes y fuente reducidos)
  const mc = (t, w, o = {}) => new TableCell({
    width: { size: w, type: WidthType.DXA },
    shading: o.shade ? { fill: o.shade, type: ShadingType.CLEAR, color: 'auto' } : undefined,
    verticalAlign: VerticalAlign.CENTER,
    margins: { top: 10, bottom: 10, left: 30, right: 30 },
    children: [new Paragraph({
      alignment: o.align || AlignmentType.LEFT,
      spacing: { after: 0 },
      children: [run(t, { size: o.size || 11, bold: o.bold })],
    })],
  });
  const rows = [
    new TableRow({
      tableHeader: true,
      children: [mc('', wM[0], { shade: 'D9E2F3', bold: true }),
        ...ids.map((id, i) => mc(id, wM[i + 1], { shade: 'D9E2F3', bold: true, align: AlignmentType.CENTER }))],
    }),
    ...red.actividades.map((a) => new TableRow({
      children: [
        mc(a.id, wM[0], { shade: 'F2F2F2', bold: true, align: AlignmentType.CENTER }),
        ...ids.map((id, i) => mc(a.sucesoras.includes(id) ? 'X' : '', wM[i + 1], { align: AlignmentType.CENTER })),
      ],
    })),
  ];
  s2.push(mkTable(wM, rows));
}

/* ================================================================ SECCIÓN 3: cálculo PDM */
const s3 = [
  h1('4. Cálculo PDM: pases, holguras y ruta crítica'),
  para('Tabla 3. Cálculo completo del método de precedencias (PASO = pasada hacia adelante, ES/EF = inicio/fin tempranos; PAST = pasada hacia atrás, LS/LF = inicio/fin tardíos; '
    + 'HT = holgura total). En rojo, las actividades críticas (HT = 0).'),
];
{
  const w = [750, 3300, 550, 950, 950, 950, 950, 700, 538];
  const rows = [
    headerRow(['ID', 'Actividad', 'd', 'ES', 'EF', 'LS', 'LF', 'HT', 'Crít.'], w),
    ...red.actividades.map((a) => new TableRow({
      children: [
        cell(a.id, w[0], { bold: true, align: AlignmentType.CENTER, size: 14, shade: a.critica ? 'FCE4E4' : undefined }),
        cell(a.corto, w[1], { size: 14, shade: a.critica ? 'FCE4E4' : undefined }),
        cell(a.d, w[2], { align: AlignmentType.CENTER, size: 14, shade: a.critica ? 'FCE4E4' : undefined }),
        cell(a.ES, w[3], { align: AlignmentType.CENTER, size: 14, shade: a.critica ? 'FCE4E4' : undefined }),
        cell(a.EF, w[4], { align: AlignmentType.CENTER, size: 14, shade: a.critica ? 'FCE4E4' : undefined }),
        cell(a.LS, w[5], { align: AlignmentType.CENTER, size: 14, shade: a.critica ? 'FCE4E4' : undefined }),
        cell(a.LF, w[6], { align: AlignmentType.CENTER, size: 14, shade: a.critica ? 'FCE4E4' : undefined }),
        cell(a.HT, w[7], { align: AlignmentType.CENTER, size: 14, bold: a.critica, shade: a.critica ? 'FCE4E4' : undefined }),
        cell(crit(a), w[8], { align: AlignmentType.CENTER, size: 14, bold: a.critica, shade: a.critica ? 'FCE4E4' : undefined }),
      ],
    })),
    new TableRow({
      children: [
        cell('', w[0], { shade: 'D9E2F3' }),
        cell('TOTAL / DURACIÓN DEL PROYECTO', w[1], { bold: true, size: 14, shade: 'D9E2F3' }),
        cell(sum(red.actividades.map((a) => a.d)), w[2], { bold: true, align: AlignmentType.CENTER, size: 14, shade: 'D9E2F3' }),
        cell('', w[3], { shade: 'D9E2F3' }),
        cell(red.duracion, w[4], { bold: true, align: AlignmentType.CENTER, size: 14, shade: 'D9E2F3' }),
        cell('', w[5], { shade: 'D9E2F3' }),
        cell('', w[6], { shade: 'D9E2F3' }),
        cell('', w[7], { shade: 'D9E2F3' }),
        cell('', w[8], { shade: 'D9E2F3' }),
      ],
    }),
  ];
  s3.push(mkTable(w, rows));
  s3.push(para([run('Suma de las duraciones de todas las actividades: ' + sum(red.actividades.map((a) => a.d))
    + ' días (incluye actividades en paralelo). Duración total del proyecto (ruta crítica): ' + red.duracion + ' días hábiles.',
  )], { before: 100 }));
}

/* ================================================================ SECCIÓN 4 (landscape): figuras PDM */
const s4 = [
  h1('5. Diagrama PDM (método de precedencias)'),
  para([run('La red completa no cabe con legibilidad en una página A4, por lo que se presenta dividida en tres partes. '
    + 'Al inicio de las partes 2 y 3 aparecen nodos fantasma (dibujados en discontinua) que repiten las actividades de la parte anterior '
    + 'de las que dependen: ' + red.fantasmasPDM.parte2.join(', ') + ' en la parte 2 y ' + red.fantasmasPDM.parte3.join(', ') + ' en la parte 3. '
    + 'Los nodos en rojo son actividades críticas (HT = 0).', {})],
    { after: 140 }),
  figure(path.join(BASE, 'pdm_parte1.png'), FIG_W, 520),
  caption('Figura 1. PDM — Parte 1: fases 1. Inicio, 2. Análisis y 3. Diseño (actividades A1…C4).'),
  pageBreak(),
  figure(path.join(BASE, 'pdm_parte2.png'), FIG_W, FIG_H),
  caption('Figura 2. PDM — Parte 2: fase 4. Construcción (actividades D1…D7). '
    + red.fantasmasPDM.parte2.join(', ') + ' en discontinua son los nodos de enlace con la parte 1.'),
  pageBreak(),
  figure(path.join(BASE, 'pdm_parte3.png'), FIG_W, FIG_H),
  caption('Figura 3. PDM — Parte 3: fases 5. Pruebas, 6. Implantación y 7. Cierre (actividades E1…G2). '
    + red.fantasmasPDM.parte3.join(', ') + ' en discontinua son los nodos de enlace con la parte 2.'),
];

/* ================================================================ SECCIÓN 5: ADM */
const s5 = [
  h1('6. Método de flechas (ADM): normas y eventos'),
  para('En el Método de Flechas (ADM) la actividad es la flecha y los eventos (nodos) son instantes de tiempo sin duración. '
    + 'Se han seguido los pasos y normas siguientes:'),
  numbered(1, 'Cada actividad del PDM se traduce en una flecha que va desde un evento de inicio (i) hasta un evento final (j).'),
  numbered(2, 'Se numeran los eventos de forma correlativa y de izquierda a derecha, reservando el 1 para el inicio y el ' + red.eventos.length + ' para el fin.'),
  numbered(3, 'Se crean eventos intermedios cuando dos actividades comparten origen y destino, para evitar flechas paralelas con los mismos eventos.'),
  numbered(4, 'Se añaden actividades ficticias (duración 0) cuando una sucesora depende de una predecesora que no termina en su evento de inicio (dependencias indirectas).'),
  numbered(5, 'Cálculo hacia adelante de los tiempos de evento (TE): TE del evento inicial = 0; TE(j) = máx[TE(i) + d(i→j)].'),
  numbered(6, 'Cálculo hacia atrás de los tiempos de evento (TL): TL del evento final = TE del evento final; TL(i) = mín[TL(j) − d(i→j)].'),
  numbered(7, 'Holgura de la actividad i→j: HT = TL(j) − TE(i) − d(i→j) = FT − LF. HT = 0 ⇒ actividad crítica.'),
  numbered(8, 'La duración del proyecto es TE del evento final y debe coincidir con la del PDM.'),

  h2('6.1. Normas del método de flechas'),
  bullet('N1. Un único evento inicial (evento 1) y un único evento final (evento ' + red.eventos.length + ').'),
  bullet('N2. Toda actividad i → j cumple que i ≠ j (no hay lazos ni flechas que empiecen y terminen en el mismo evento).'),
  bullet('N3. No existen dos actividades distintas con el mismo par (i, j); si se daría, se inserta un evento intermedio.'),
  bullet('N4. Toda actividad parte de un evento y llega a otro; ningún evento queda aislado.'),
  bullet('N5. Las actividades ficticias tienen duración 0 y solo expresan dependencia (flecha discontinua).'),
  bullet('N6. Numeración correlativa ascendente de izquierda a derecha: si i → j, entonces i < j.'),
  bullet('N7. FT = IT + d (fin temprano = inicio temprano + duración); LF = LT + d (fin tardío = inicio tardío + duración).'),
  bullet('N8. HT = FT − LF = LT − IT; si HT = 0 la actividad es crítica.'),
  bullet('N9. TE del evento inicial = 0 y TL del evento final = TE del evento final (duración del proyecto).'),
  bullet('N10. La duración total obtenida en el ADM coincide exactamente con la del PDM.'),

  h2('6.2. Actividades ficticias (dummy)'),
  para('Tabla 4. Actividades ficticias generadas en la conversión PDM → ADM.'),
];
{
  const w = [700, 1400, 1900, 1900, 3738];
  s5.push(mkTable(w, [
    headerRow(['ID', 'De → A', 'Predecesora', 'Sucesora', 'Justificación'], w),
    ...red.ficticias.map((f) => new TableRow({
      children: [
        cell(f.id, w[0], { bold: true, align: AlignmentType.CENTER, size: 15 }),
        cell(f.de + ' → ' + f.a, w[1], { align: AlignmentType.CENTER, size: 15 }),
        cell(f.pre, w[2], { align: AlignmentType.CENTER, size: 15 }),
        cell(f.post, w[3], { align: AlignmentType.CENTER, size: 15 }),
        cell('Dependencia indirecta: ' + f.post + ' es sucesora de ' + f.pre + ', pero ' + f.pre
          + ' no termina en el evento de inicio de ' + f.post + '; se expresa con una flecha de duración 0.',
        w[4], { size: 14 }),
      ],
    })),
  ]));
}

s5.push(h2('6.3. Eventos del ADM (tiempos TE y TL)'), para('Tabla 5. Eventos: actividades entrantes y salientes, tiempo temprano (TE), '
  + 'tiempo tardío (TL) y holgura del evento (TL − TE).'));
{
  const w = [800, 3000, 3000, 900, 900, 1038];
  s5.push(mkTable(w, [
    headerRow(['Evento', 'Actividades entrantes', 'Actividades salientes', 'TE', 'TL', 'TL − TE'], w),
    ...red.eventos.map((e) => new TableRow({
      children: [
        cell(e.evento + (e.clave === 'START' ? ' (inicio)' : e.clave === 'END' ? ' (fin)' : ''), w[0],
          { bold: true, align: AlignmentType.CENTER, size: 15, shade: e.critico ? 'FCE4E4' : undefined }),
        cell(lst(e.entrantes), w[1], { align: AlignmentType.CENTER, size: 15, shade: e.critico ? 'FCE4E4' : undefined }),
        cell(lst(e.salientes), w[2], { align: AlignmentType.CENTER, size: 15, shade: e.critico ? 'FCE4E4' : undefined }),
        cell(e.TE, w[3], { align: AlignmentType.CENTER, size: 15, shade: e.critico ? 'FCE4E4' : undefined }),
        cell(e.TL, w[4], { align: AlignmentType.CENTER, size: 15, shade: e.critico ? 'FCE4E4' : undefined }),
        cell(e.holgura, w[5], { align: AlignmentType.CENTER, size: 15, bold: e.critico, shade: e.critico ? 'FCE4E4' : undefined }),
      ],
    })),
  ]));
  s5.push(para([run('Eventos de unión: los eventos ' + U12 + ' se comparten entre la parte 1 y la parte 2 del diagrama ADM, y el evento '
    + U23 + ' entre la parte 2 y la parte 3. Esto permite dividir la red en tres figuras sin perder su continuidad.')], { before: 100 }));
}

s5.push(h2('6.4. Cálculo del ADM por actividad'), para('Tabla 6. Cálculo de tiempos tempranos y tardíos por actividad: '
  + 'IT = inicio temprano (TE del evento i), FT = fin temprano (IT + d), LT = inicio tardío (TL del evento i), LF = fin tardío (LT + d), '
  + 'HT = FT − LF.'));
{
  const w = [1500, 550, 550, 550, 900, 900, 900, 900, 800, 2088];
  s5.push(mkTable(w, [
    headerRow(['Actividad', 'd', 'i', 'j', 'IT', 'FT', 'LT', 'LF', 'HT', 'Crítica'], w),
    ...red.actividades.map((a) => new TableRow({
      children: [
        cell(a.id + ' ' + a.corto, w[0], { size: 14, bold: a.critica, shade: a.critica ? 'FCE4E4' : undefined }),
        cell(a.d, w[1], { align: AlignmentType.CENTER, size: 14, shade: a.critica ? 'FCE4E4' : undefined }),
        cell(a.adm.i, w[2], { align: AlignmentType.CENTER, size: 14, shade: a.critica ? 'FCE4E4' : undefined }),
        cell(a.adm.j, w[3], { align: AlignmentType.CENTER, size: 14, shade: a.critica ? 'FCE4E4' : undefined }),
        cell(a.adm.IT, w[4], { align: AlignmentType.CENTER, size: 14, shade: a.critica ? 'FCE4E4' : undefined }),
        cell(a.adm.FT, w[5], { align: AlignmentType.CENTER, size: 14, shade: a.critica ? 'FCE4E4' : undefined }),
        cell(a.adm.LT, w[6], { align: AlignmentType.CENTER, size: 14, shade: a.critica ? 'FCE4E4' : undefined }),
        cell(a.adm.LF, w[7], { align: AlignmentType.CENTER, size: 14, shade: a.critica ? 'FCE4E4' : undefined }),
        cell(a.adm.HT, w[8], { align: AlignmentType.CENTER, size: 14, bold: a.critica, shade: a.critica ? 'FCE4E4' : undefined }),
        cell(crit(a), w[9], { align: AlignmentType.CENTER, size: 14, bold: a.critica, shade: a.critica ? 'FCE4E4' : undefined }),
      ],
    })),
  ]));
}

/* ================================================================ SECCIÓN 6 (landscape): figuras ADM */
const s6 = [
  h1('7. Diagrama ADM (método de flechas)'),
  para('Al igual que el PDM, la red completa se presenta en tres partes. Las partes se enlazan mediante eventos de unión '
    + '(resaltados en azul), que aparecen en las dos partes que conectan: eventos ' + U12 + ' entre la parte 1 y la 2, y evento '
    + U23 + ' entre la parte 2 y la 3. Las flechas en rojo forman la ruta crítica y las punteadas son actividades ficticias (duración 0).',
  { after: 140 }),
  figure(path.join(BASE, 'adm_parte1.png'), FIG_W, 480),
  caption('Figura 4. ADM — Parte 1: eventos 1 a 12 (fases Inicio, Análisis y Diseño). Inicio = evento 1; '
    + 'uniones con la parte 2 = eventos ' + U12 + '.'),
  pageBreak(),
  figure(path.join(BASE, 'adm_parte2.png'), FIG_W, 340),
  caption('Figura 5. ADM — Parte 2: eventos 9 a 16 (fase Construcción). Uniones: eventos ' + U12
    + ' (con la parte 1) y evento ' + U23 + ' (con la parte 3).'),
  figure(path.join(BASE, 'adm_parte3.png'), FIG_W, 220),
  caption('Figura 6. ADM — Parte 3: eventos 16 a 24 (fases Pruebas, Implantación y Cierre). '
    + 'Fin del proyecto = evento 24 (TE = ' + red.duracion + ').'),
];

/* ================================================================ SECCIÓN 7: ruta crítica, checklist, conclusiones */
const s7 = [
  h1('8. Ruta crítica', true),
  para('La ruta crítica es la secuencia de actividades sin holgura (HT = 0) que conecta el inicio con el fin del proyecto. '
    + 'Determina la duración mínima del proyecto: cualquier retraso en una de estas actividades retrasa la fecha de entrega en la misma medida.'),
  para('Tabla 7. Ruta crítica del proyecto.'),
];
{
  const w = [700, 3400, 3000, 1000, 1538];
  let acc = 0;
  const rows = [
    headerRow(['Pos.', 'Actividad', 'Fase', 'Duración', 'Día acumulado'], w),
    ...red.rutaCritica.map((id, i) => {
      const a = red.actividades.find((x) => x.id === id);
      acc += a.d;
      return new TableRow({
        children: [
          cell(i + 1, w[0], { align: AlignmentType.CENTER, size: 15, shade: 'FCE4E4' }),
          cell(a.id + ' — ' + a.nombre, w[1], { size: 14, shade: 'FCE4E4' }),
          cell(a.fase, w[2], { size: 14, shade: 'FCE4E4' }),
          cell(a.d, w[3], { align: AlignmentType.CENTER, size: 15, shade: 'FCE4E4' }),
          cell(acc, w[4], { align: AlignmentType.CENTER, size: 15, shade: 'FCE4E4' }),
        ],
      });
    }),
    new TableRow({
      children: [
        cell('', w[0], { shade: 'D9E2F3' }),
        cell('TOTAL — duración del proyecto', w[1], { bold: true, size: 15, shade: 'D9E2F3' }),
        cell('', w[2], { shade: 'D9E2F3' }),
        cell(red.sumaRutaCritica, w[3], { bold: true, align: AlignmentType.CENTER, size: 15, shade: 'D9E2F3' }),
        cell(red.duracion, w[4], { bold: true, align: AlignmentType.CENTER, size: 15, shade: 'D9E2F3' }),
      ],
    }),
  ];
  s7.push(mkTable(w, rows));
  s7.push(para([run('Secuencia: ' + red.rutaCritica.join(' → '))], { before: 100 }));

  const conHol = red.actividades.filter((a) => !a.critica).sort((a, b) => b.HT - a.HT);
  s7.push(para([run('Actividades no críticas y su holgura (HT en días): '
    + conHol.map((a) => a.id + ' (' + a.HT + ')').join(', ') + '.')],
  { before: 60 }));
}

s7.push(h1('9. Checklist de cumplimiento de las normas'));
{
  const w = [800, 5500, 900, 2438];
  const rows = [
    headerRow(['Norma', 'Descripción', 'Se cumple', 'Comprobación'], w),
  ];
  const ok = (d) => [cell('Sí', d, { align: AlignmentType.CENTER, size: 15, bold: true, color: '2E7D32' })];
  const pdm = [
    ['P1', 'Se han identificado todas las actividades del ciclo de vida completo sin solapamientos ambiguos.', 'Inventario (Tabla 2): 25 actividades en 7 fases'],
    ['P2', 'Cada actividad tiene duración estimada en la unidad de tiempo.', 'Columna «d» de las Tablas 2, 3 y 6'],
    ['P3', 'Las flechas del PDM solo expresan precedencia (FS).', 'Matriz de dependencias (§3.2)'],
    ['P4', 'No existen ciclos en la red.', 'generar_red.js valida aciclicidad antes de calcular'],
    ['P5', 'PASO: ES = máx(EF de predecesoras); EF = ES + d.', 'Verificado fila a fila en la Tabla 3'],
    ['P6', 'PAST: LF = mín(LS de sucesoras); LS = LF − d.', 'Verificado fila a fila en la Tabla 3'],
    ['P7', 'HT = LS − ES y HT = 0 identifica la ruta crítica.', 'Columna HT de la Tabla 3 (17 actividades con HT = 0)'],
    ['P8', 'La duración total es constante entre PDM y ADM.', '91 días en ambos métodos (Tablas 3, 6 y 7)'],
    ['A1', 'Un solo evento inicial y uno final en el ADM.', 'Evento 1 y evento 24 (Tabla 5)'],
    ['A2', 'En toda actividad i → j se cumple i < j (sin lazos).', 'Columnas i/j de la Tabla 6'],
    ['A3', 'No hay dos actividades con el mismo par (i, j).', 'Eventos intermedios generados en la conversión'],
    ['A4', 'Dependencias indirectas resueltas con ficticias de duración 0.', 'Tabla 4: d1 … d7 (7 ficticias)'],
    ['A5', 'TE y TL calculados hacia adelante y hacia atrás.', 'Tabla 5: TE(j) = máx[TE(i)+d], TL(i) = mín[TL(j)−d]'],
    ['A6', 'HT de la actividad = FT − LF = LT − IT.', 'Columna HT de la Tabla 6 = columna HT de la Tabla 3'],
    ['A7', 'Las partes del diagrama se unen por eventos comunes.', 'Eventos ' + U12 + ' (P1↔P2) y ' + U23 + ' (P2↔P3), Figuras 4 a 6'],
  ];
  pdm.forEach(([n, d, c]) => rows.push(new TableRow({
    cantSplit: true,
    children: [cell(n, w[0], { bold: true, align: AlignmentType.CENTER, size: 15 }), cell(d, w[1], { size: 14 }), ...ok(w[2]), cell(c, w[3], { size: 14 })],
  })));
  s7.push(mkTable(w, rows));
}

s7.push(h1('10. Conclusiones'));
{
  const conHol = red.actividades.filter((a) => !a.critica).sort((a, b) => b.HT - a.HT);
  s7.push(...[
    bullet('La duración mínima del proyecto ' + red.proyecto + ' es de ' + red.duracion + ' días hábiles, resultado idéntico en el PDM y en el ADM, lo que valida la conversión de una red a otra.'),
    bullet('La ruta crítica está formada por ' + red.rutaCritica.length + ' actividades (' + red.rutaCritica.join(' → ') + ') que no tienen holgura: cualquier retraso en ellas atrasa la entrega en el mismo número de días.'),
    bullet('Solo ' + red.actividades.filter((a) => !a.critica).length + ' actividades disponen de holgura; la de mayor margen es '
      + conHol[0].id + ' (' + conHol[0].corto + ') con HT = ' + conHol[0].HT + ' días, lo que permite reasignar recursos desde ella hacia las actividades críticas sin afectar a la fecha final.'),
    bullet('El paralelismo entre fases es importante (25 actividades suman ' + sum(red.actividades.map((a) => a.d))
      + ' días de trabajo frente a ' + red.duracion + ' días de proyecto), por lo que la planificación debe cuidar especialmente la asignación de recursos en las fases de Diseño y Construcción, donde coinciden más actividades.'),
    bullet('La conversión al ADM ha requerido ' + red.ficticias.length + ' actividades ficticias y '
      + red.eventos.length + ' eventos; los eventos de unión ' + red.uniones.join(', ')
      + ' permiten presentar la red en tres partes manteniendo la coherencia del conjunto.'),
    bullet('Las redes quedan documentadas con sus cálculos completos (PASO, PAST y HT en el PDM; IT/FT/LT/LF en el ADM), de modo que pueden reutilizarse para actualizar el plan si cambian alcance, duraciones o dependencias.'),
  ]);
}

s7.push(h1('Anexo A. Glosario de términos'));
{
  const w = [2600, 7038];
  const defs = [
    ['PDM (método de precedencias)', 'Red en la que las actividades se representan como nodos (cajas) y las dependencias como flechas.'],
    ['ADM (método de flechas)', 'Red en la que las actividades se representan como flechas y los eventos como nodos circulares sin duración.'],
    ['PASO (pasada hacia adelante)', 'Cálculo de los tiempos tempranos, del inicio al fin de la red: ES = máx(EF de las predecesoras) y EF = ES + d. En los nodos del PDM aparece como «PASO: ES → EF».'],
    ['ES / EF', 'Inicio temprano / fin temprano: momento más temprano en que la actividad puede empezar / terminar.'],
    ['PAST (pasada hacia atrás)', 'Cálculo de los tiempos tardíos, del fin al inicio de la red: LF = mín(LS de las sucesoras) y LS = LF − d. En los nodos del PDM aparece como «PAST: LS → LF».'],
    ['LS (inicio tardío)', 'Inicio tardío: momento más tardío en que puede empezar sin retrasar el proyecto. LS = LF − d.'],
    ['LF (fin tardío)', 'Fin tardío: mínimo de los LS de las sucesoras.'],
    ['HT (holgura total)', 'HT = LS − ES = LF − EF. Tiempo disponible de retraso sin afectar a la duración total.'],
    ['Ruta crítica', 'Secuencia de actividades con HT = 0 desde el inicio hasta el fin; su duración es la duración del proyecto.'],
    ['TE (tiempo temprano de evento)', 'ADM: momento más temprano en que puede ocurrir un evento. TE(j) = máx[TE(i) + d(i→j)].'],
    ['TL (tiempo tardío de evento)', 'ADM: momento más tardío en que puede ocurrir un evento sin retrasar el proyecto. TL(i) = mín[TL(j) − d(i→j)].'],
    ['IT / FT (ADM)', 'Inicio temprano (IT = TE del evento i) y fin temprano (FT = IT + d) de la actividad.'],
    ['LT / LF (ADM)', 'Inicio tardío (LT = TL del evento i) y fin tardío (LF = LT + d) de la actividad.'],
    ['Actividad ficticia (dummy)', 'Flecha de duración 0 que solo expresa una relación de dependencia entre eventos.'],
    ['Evento de unión', 'Evento compartido por dos partes de un diagrama dividido; en este documento: ' + U12 + ' (partes 1 y 2) y ' + U23 + ' (partes 2 y 3).'],
    ['Duración total', 'Suma de duraciones de la ruta crítica = ' + red.duracion + ' días hábiles.'],
  ];
  s7.push(mkTable(w, [
    headerRow(['Término', 'Definición'], w),
    ...defs.map(([t, d]) => new TableRow({ children: [cell(t, w[0], { bold: true, size: 15, shade: 'F2F2F2' }), cell(d, w[1], { size: 15 })] })),
  ]));
  s7.push(para([run('Nota: este documento ha sido generado a partir de los cálculos CPM contenidos en red.json (script generar_red.js), '
    + 'de modo que todos los valores de tablas y figuras son coherentes entre sí y verificables.')],
  { before: 200, after: 0 }));
}

/* ================================================================ documento */
const doc = new Document({
  creator: 'Proyecto NutriLnk',
  title: 'Documentación 3 — PDM y ADM — NutriLnk',
  description: 'Diagramas PDM y ADM del ciclo de vida completo del proyecto NutriLnk',
  styles: {
    default: {
      document: { run: { font: 'Calibri', size: 21 }, paragraph: { spacing: { line: 260 } } },
      heading1: { run: { font: 'Calibri', size: 30, bold: true, color: '1F3864' } },
      heading2: { run: { font: 'Calibri', size: 25, bold: true, color: '2E5395' } },
    },
  },
  sections: [
    { properties: { page: { size: { width: 11906, height: 16838 }, margin: P_MARG } }, footers: { default: newFooter() }, children: s1 },
    { properties: { page: { size: { orientation: PageOrientation.LANDSCAPE }, margin: L_MARG } }, footers: { default: newFooter() }, children: s2 },
    { properties: { page: { size: { width: 11906, height: 16838 }, margin: P_MARG } }, footers: { default: newFooter() }, children: s3 },
    { properties: { page: { size: { orientation: PageOrientation.LANDSCAPE }, margin: L_MARG } }, footers: { default: newFooter() }, children: s4 },
    { properties: { page: { size: { width: 11906, height: 16838 }, margin: P_MARG } }, footers: { default: newFooter() }, children: s5 },
    { properties: { page: { size: { orientation: PageOrientation.LANDSCAPE }, margin: L_MARG } }, footers: { default: newFooter() }, children: s6 },
    { properties: { page: { size: { width: 11906, height: 16838 }, margin: P_MARG } }, footers: { default: newFooter() }, children: s7 },
  ],
});

Packer.toBuffer(doc).then((buf) => {
  fs.writeFileSync(OUT, buf);
  console.log('Documento generado:', OUT, buf.length, 'bytes');
}).catch((e) => { console.error(e); process.exit(1); });
