<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe Técnico de Investigación de Operaciones</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; margin: 20px; line-height: 1.4; }
        .header { border-bottom: 2px solid #d97706; padding-bottom: 8px; margin-bottom: 15px; }
        .title { font-size: 16px; font-weight: bold; color: #b45309; }
        .subtitle { font-size: 11px; color: #555; }
        h3 { color: #92400e; font-size: 12px; margin-top: 15px; margin-bottom: 6px; border-bottom: 1px solid #fde68a; padding-bottom: 3px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #ddd; padding: 5px 6px; text-align: left; }
        th { background-color: #fffbeb; color: #92400e; font-weight: bold; font-size: 9px; text-transform: uppercase; }
        .badge { padding: 2px 5px; border-radius: 3px; font-size: 8px; font-weight: bold; }
        .badge-a { background: #fee2e2; color: #b91c1c; }
        .badge-b { background: #fef3c7; color: #b45309; }
        .badge-c { background: #e0f2fe; color: #0369a1; }
        .formula-box { background: #f8fafc; border: 1px solid #cbd5e1; padding: 6px 10px; margin-bottom: 10px; font-family: monospace; font-size: 10px; }
        .footer { text-align: right; font-size: 9px; color: #888; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">INFORME TÉCNICO &mdash; INVESTIGACIÓN DE OPERACIONES (I.O.)</div>
        <div class="subtitle">Optimización de Inventarios y Logística Humanitaria en Refugios de Emergencia</div>
        <div style="font-size: 9px; color: #666; margin-top: 3px;">Fecha: {{ $fecha }} &bull; Cátedra: Investigación Operativa / IDO</div>
    </div>

    <h3>1. Marco Matemático y Modelos Aplicados</h3>
    <div class="formula-box">
        <strong>Modelo EOQ:</strong> Q* = &radic;((2 &times; D &times; S) / H) &nbsp;|&nbsp; <strong>Punto de Reorden:</strong> ROP = (d &times; L) + SS<br>
        <strong>Modelo Newsvendor:</strong> CR = Cu / (Cu + Co) &nbsp;|&nbsp; <strong>Lote Óptimo Estocástico:</strong> Q* = &mu; + Z &times; &sigma;
    </div>
    <p style="font-size:9.5px;">
        En emergencias masivas, los costos de escasez (<strong>Cu</strong>) para medicamentos vitales y raciones infantiles son exponencialmente mayores al costo de mantenimiento o desecho (<strong>Co</strong>), fijando niveles de servicio óptimos entre 85% y 98%.
    </p>

    <h3>2. Clasificación ABC Multicriterio (Principio de Pareto)</h3>
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Insumo</th>
                <th>Categoría</th>
                <th>Costo Unit. (C)</th>
                <th>Demanda Anual Est. (D)</th>
                <th>Valor Consumo Anual</th>
                <th>% Acumulado</th>
                <th>Clase I.O.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($abcData['items'] as $it)
            <tr>
                <td>{{ $it['supply']->codigo }}</td>
                <td><strong>{{ $it['supply']->nombre }}</strong></td>
                <td>{{ ucfirst($it['supply']->categoria) }}</td>
                <td>${{ number_format($it['supply']->costo_unitario, 2) }}</td>
                <td>{{ number_format($it['demanda_anual'], 0) }}</td>
                <td>${{ number_format($it['valor_consumo_anual'], 2) }}</td>
                <td>{{ $it['pct_acumulado'] }}%</td>
                <td>
                    <span class="badge {{ $it['clase_calculada'] === 'A' ? 'badge-a' : ($it['clase_calculada'] === 'B' ? 'badge-b' : 'badge-c') }}">
                        CLASE {{ $it['clase_calculada'] }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div style="font-size: 9px; color: #555; margin-bottom: 12px;">
        Resumen Pareto: Clase A ({{ $abcData['resumen']['A'] }} insumos críticos, 0-75% valor), Clase B ({{ $abcData['resumen']['B'] }} insumos moderados, 75-95% valor), Clase C ({{ $abcData['resumen']['C'] }} insumos de volumen, 95-100% valor).
    </div>

    <h3>3. Sugerencias de Rebalanceo Inter-Refugios (Balanceo de Recursos Escasos)</h3>
    <table>
        <thead>
            <tr>
                <th>Prioridad</th>
                <th>Insumo</th>
                <th>Refugio Donante (Superávit)</th>
                <th>Refugio Receptor (Déficit)</th>
                <th>Cantidad Transferible</th>
                <th>Justificación Operativa</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sugerencias as $sug)
            <tr>
                <td><strong>{{ strtoupper($sug['prioridad']) }}</strong></td>
                <td>{{ $sug['supply']->nombre }}</td>
                <td>{{ $sug['refugio_origen']->nombre }}</td>
                <td>{{ $sug['refugio_destino']->nombre }}</td>
                <td><strong>{{ $sug['cantidad_sugerida'] }} {{ $sug['unidad_medida'] }}</strong></td>
                <td style="font-size: 8.5px;">{{ $sug['motivo'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; color: #888;">No se requieren transferencias; el inventario se encuentra balanceado.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Informe Académico para Evaluación de Investigación de Operaciones &bull; NutriLnk
    </div>
</body>
</html>
