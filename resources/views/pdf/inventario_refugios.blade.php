<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Inventario de Refugios</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; margin: 20px; }
        .header { border-bottom: 2px solid #c88340; padding-bottom: 10px; margin-bottom: 15px; }
        .title { font-size: 18px; font-weight: bold; color: #c88340; }
        .subtitle { font-size: 11px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background-color: #fdf8f3; color: #9c6023; font-weight: bold; font-size: 10px; text-transform: uppercase; }
        .badge { padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; }
        .badge-critico { background: #fee2e2; color: #b91c1c; }
        .badge-precaucion { background: #fef3c7; color: #b45309; }
        .badge-optimo { background: #dcfce7; color: #15803d; }
        .shelter-header { background-color: #fcf4ec; padding: 6px 10px; font-weight: bold; font-size: 12px; margin-top: 15px; border-left: 4px solid #c88340; }
        .footer { text-align: right; font-size: 9px; color: #888; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">SISTEMA DE GESTIÓN DE INVENTARIOS EN REFUGIOS</div>
        <div class="subtitle">Reporte Oficial de Existencias, Demanda Per Cápita y Días de Cobertura Humanitaria</div>
        <div style="font-size: 10px; color: #555; margin-top: 5px;">Fecha de emisión: {{ $fecha }}</div>
    </div>

    @foreach($shelters as $shelter)
        <div class="shelter-header">
            {{ $shelter->nombre }} ({{ $shelter->codigo }}) &mdash; Población: {{ $shelter->poblacion_total }} albergados ({{ $shelter->poblacion_ninos }} niños, {{ $shelter->poblacion_atencion_medica }} médicos)
        </div>
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Insumo</th>
                    <th>Categoría</th>
                    <th>Clase ABC</th>
                    <th>Stock Actual</th>
                    <th>Consumo Diario</th>
                    <th>Días Autonomía</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($shelter->inventories as $inv)
                <tr>
                    <td>{{ $inv->supply->codigo }}</td>
                    <td><strong>{{ $inv->supply->nombre }}</strong></td>
                    <td>{{ ucfirst($inv->supply->categoria) }}</td>
                    <td>Clase {{ $inv->supply->clasificacion_abc }}</td>
                    <td>{{ $inv->stock_actual }} {{ $inv->supply->unidad_medida }}</td>
                    <td>{{ $inv->consumo_diario_estimado }} / día</td>
                    <td><strong>{{ $inv->dias_autonomia > 100 ? '> 90' : $inv->dias_autonomia }} días</strong></td>
                    <td>
                        @if($inv->dias_autonomia < 2)
                            <span class="badge badge-critico">CRÍTICO (&lt;2d)</span>
                        @elseif($inv->dias_autonomia <= 5)
                            <span class="badge badge-precaucion">PRECAUCIÓN</span>
                        @else
                            <span class="badge badge-optimo">ÓPTIMO</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    <div class="footer">
        Generado por Sistema NutriLnk &bull; Modelos de Investigación de Operaciones Humanitarias
    </div>
</body>
</html>
