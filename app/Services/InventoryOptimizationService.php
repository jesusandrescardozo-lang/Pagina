<?php

namespace App\Services;

use App\Models\Shelter;
use App\Models\ShelterInventory;
use App\Models\Supply;

class InventoryOptimizationService
{
    /**
     * MODELO EOQ (Economic Order Quantity) / Cantidad Económica de Pedido (Wilson)
     *
     * @param float $demandaAnual (D)
     * @param float $costoPedido (S)
     * @param float $costoMantenimiento (H)
     * @param float $costoUnitario (C)
     * @param float $leadTimeDias (L)
     * @param float $stockSeguridad (SS)
     * @return array
     */
    public function calcularEoq(
        float $demandaAnual,
        float $costoPedido,
        float $costoMantenimiento,
        float $costoUnitario = 0.0,
        float $leadTimeDias = 3.0,
        float $stockSeguridad = 0.0
    ): array {
        $demandaAnual = max(1.0, $demandaAnual);
        $costoPedido = max(0.01, $costoPedido);
        $costoMantenimiento = max(0.01, $costoMantenimiento);

        // Q* = sqrt((2 * D * S) / H)
        $qOptimo = sqrt((2 * $demandaAnual * $costoPedido) / $costoMantenimiento);

        // Número de órdenes al año: N = D / Q*
        $numOrdenesAno = $demandaAnual / $qOptimo;

        // Tiempo entre pedidos (días): T = (Q* / D) * 365
        $tiempoEntrePedidosDias = ($qOptimo / $demandaAnual) * 365;

        // Demanda diaria promedio: d = D / 365
        $demandaDiaria = $demandaAnual / 365;

        // Punto de Reorden: ROP = (d * L) + SS
        $rop = ($demandaDiaria * $leadTimeDias) + $stockSeguridad;

        // Costos anuales
        $costoOrdenar = ($demandaAnual / $qOptimo) * $costoPedido;
        $costoMantener = ($qOptimo / 2) * $costoMantenimiento;
        $costoCompra = $demandaAnual * $costoUnitario;
        $costoTotalInventario = $costoOrdenar + $costoMantener;
        $costoTotalConCompra = $costoTotalInventario + $costoCompra;

        // Generar puntos para curva gráfica interactiva de costos
        $curvaCostos = $this->generarCurvaCostosEoq($demandaAnual, $costoPedido, $costoMantenimiento, $qOptimo);

        return [
            'q_optimo' => round($qOptimo, 2),
            'q_optimo_entero' => (int) round($qOptimo),
            'rop' => round($rop, 2),
            'stock_seguridad' => round($stockSeguridad, 2),
            'num_ordenes_ano' => round($numOrdenesAno, 2),
            'tiempo_entre_pedidos_dias' => round($tiempoEntrePedidosDias, 1),
            'demanda_diaria' => round($demandaDiaria, 2),
            'costo_ordenar' => round($costoOrdenar, 2),
            'costo_mantener' => round($costoMantener, 2),
            'costo_total_inventario' => round($costoTotalInventario, 2),
            'costo_compra' => round($costoCompra, 2),
            'costo_total_con_compra' => round($costoTotalConCompra, 2),
            'curva_costos' => $curvaCostos,
        ];
    }

    /**
     * Genera datos para la gráfica de Costo de Ordenar, Costo de Mantener y Costo Total
     */
    private function generarCurvaCostosEoq(float $D, float $S, float $H, float $Qopt): array
    {
        $points = [];
        $minQ = max(1, (int)($Qopt * 0.25));
        $maxQ = (int)($Qopt * 2.2);
        $step = max(1, (int)(($maxQ - $minQ) / 20));

        for ($q = $minQ; $q <= $maxQ; $q += $step) {
            $costoOrd = ($D / $q) * $S;
            $costoMant = ($q / 2) * $H;
            $costoTot = $costoOrd + $costoMant;
            $points[] = [
                'q' => $q,
                'costo_ordenar' => round($costoOrd, 2),
                'costo_mantener' => round($costoMant, 2),
                'costo_total' => round($costoTot, 2),
            ];
        }

        return $points;
    }

    /**
     * MODELO NEWSVENDOR (Período Único con Demanda Probabilística)
     * Especial para insumos perecederos y kits de emergencia en refugios.
     *
     * @param float $mediaDemanda (μ)
     * @param float $desviacionDemanda (σ)
     * @param float $costoEscasez (Cu) Costo de subestimación
     * @param float $costoDesecho (Co) Costo de sobreestimación
     * @return array
     */
    public function calcularNewsvendor(
        float $mediaDemanda,
        float $desviacionDemanda,
        float $costoEscasez,
        float $costoDesecho
    ): array {
        $costoEscasez = max(0.01, $costoEscasez);
        $costoDesecho = max(0.01, $costoDesecho);

        // Razón Crítica: CR = Cu / (Cu + Co)
        $razonCritica = $costoEscasez / ($costoEscasez + $costoDesecho);

        // Inversa de la distribución normal estándar para CR (Aproximación de Beasley-Springer-Moro / Hastings)
        $z = $this->inversaNormalEstandar($razonCritica);

        // Q* = μ + Z * σ
        $qOptimo = $mediaDemanda + ($z * $desviacionDemanda);
        $qOptimo = max(0.0, $qOptimo);

        // Probabilidad de no quedar desabastecido (Nivel de servicio óptimo)
        $nivelServicio = $razonCritica * 100;

        return [
            'razon_critica' => round($razonCritica, 4),
            'nivel_servicio_pct' => round($nivelServicio, 2),
            'z_score' => round($z, 3),
            'q_optimo' => round($qOptimo, 2),
            'q_optimo_entero' => (int) round($qOptimo),
            'stock_seguridad_newsvendor' => round($z * $desviacionDemanda, 2),
            'probabilidad_escasez_pct' => round((1 - $razonCritica) * 100, 2),
        ];
    }

    /**
     * Aproximación de Abramowitz & Stegun para la inversa de la normal estándar
     */
    public function inversaNormalEstandar(float $p): float
    {
        if ($p <= 0.0) return -4.0;
        if ($p >= 1.0) return 4.0;
        if (abs($p - 0.5) < 1e-7) return 0.0;

        $pTemp = $p < 0.5 ? $p : 1.0 - $p;
        $t = sqrt(-2.0 * log($pTemp));

        // Coeficientes racionales
        $c0 = 2.515517;
        $c1 = 0.802853;
        $c2 = 0.010328;
        $d1 = 1.432788;
        $d2 = 0.189269;
        $d3 = 0.001308;

        $xp = $t - (($c0 + $t * ($c1 + $t * $c2)) / (1.0 + $t * ($d1 + $t * ($d2 + $t * $d3))));

        return $p < 0.5 ? -$xp : $xp;
    }

    /**
     * Duración de la caché de los cálculos analíticos (segundos).
     * Son lecturas costosas que cambian poco entre peticiones.
     */
    public const CACHE_TTL = 60;

    /**
     * Clave que guarda la "versión" vigente de la caché analítica.
     *
     * Toda clave analítica incorpora esta versión ("matriz_autonomia:3:12").
     * Invalidar consiste en incrementarla: las entradas anteriores quedan
     * huérfanas y expiran solas en CACHE_TTL. Así NO hace falta un
     * Cache::flush() global, que destruiría también claves ajenas
     * (rate-limiter de login, sesiones, tokens...).
     */
    public const CACHE_VERSION_KEY = 'io:cache_version';

    /**
     * Versión vigente de la caché analítica (1 si nadie ha invalidado aún).
     */
    public static function cacheVersion(): int
    {
        return (int) cache()->remember(self::CACHE_VERSION_KEY, 7 * 24 * 3600, fn () => 1);
    }

    /**
     * Invalida TODA la caché analítica sin tocar el resto del caché de la app.
     * Llamado por AuditService en cada escritura de inventario.
     */
    public static function invalidarCacheAnalitica(): void
    {
        // forever: la versión no caduca; las entradas con versión anterior
        // expiran por CACHE_TTL de forma natural.
        cache()->forever(self::CACHE_VERSION_KEY, self::cacheVersion() + 1);
    }

    /**
     * Construye una clave de caché analítica con la versión vigente.
     */
    private function cacheKeyConVersion(string $prefijo, ?int $shelterId): string
    {
        return "{$prefijo}:" . self::cacheVersion() . ':' . ($shelterId ?? 'global');
    }

    /**
     * Snapshot compartido de refugios + inventarios + insumos.
     *
     * Antes, CADA cálculo (matriz de autonomía, ABC, rebalanceo) y el
     * dashboard cargaban por su cuenta los mismos datos: 13 consultas de
     * ida y vuelta (≈2,6 s con Neon) solo para leer las mismas tablas.
     * Aquí se cargan UNA vez (3 consultas) y todos comparten el resultado.
     *
     * Vive en el contenedor de la aplicación: Laravel construye un
     * contenedor nuevo en CADA petición, así que el snapshot nunca cruza
     * peticiones (nunca sirve datos viejos entre requests).
     *
     * El orden de los inventarios NO es indiferente: los algoritmos son
     * codiciosos y las sumas son en coma flotante, así que el orden en que
     * se recorren cambia las asignaciones. Se respeta el orden físico de la
     * tabla, que es exactamente el que devolvían las consultas originales
     * (verificado: 20/20 insumos y 6/6 refugios con idéntico orden).
     *
     * @return array{shelters: \Illuminate\Support\Collection, supplies: \Illuminate\Support\Collection, inventarios_por_insumo: array<int, array>}
     */
    private function snapshot(): array
    {
        if (! app()->bound('io.snapshot')) {
            // withTrashed: el ABC contabiliza el stock de refugios dados de
            // baja (con población 0), igual que hacía antes el eager
            // "inventories.shelter" (que devolvía shelter = null).
            $shelters = Shelter::withTrashed()->get();
            $supplies = Supply::all();
            $inventarios = ShelterInventory::query()->get();

            $sheltersPorId = $shelters->keyBy('id');
            $suppliesPorId = $supplies->keyBy('id');

            $inventariosPorInsumo = [];
            $inventariosPorRefugio = [];

            foreach ($inventarios as $inv) {
                $shelter = $sheltersPorId->get($inv->shelter_id);

                // Refugio padre en memoria: evita una consulta por inventario
                // (N+1). Con refugio dado de baja el eager anterior devolvía
                // null y así lo dejamos (población 0 en el ABC).
                $baja = $shelter === null || $shelter->trashed();
                $inv->setRelation('shelter', $baja ? null : $shelter);
                // El insumo también va en memoria: los accessors
                // consumo_diario_estimado / dias_autonomia lo necesitan.
                $inv->setRelation('supply', $suppliesPorId->get($inv->supply_id));

                $inventariosPorInsumo[$inv->supply_id][] = $inv;
                $inventariosPorRefugio[$inv->shelter_id][] = $inv;
            }

            // Cada refugio queda con sus inventarios ya cargados (mismo orden
            // que devolvía el eager "inventories" original).
            foreach ($shelters as $shelter) {
                $shelter->setRelation('inventories', collect($inventariosPorRefugio[$shelter->id] ?? []));
            }

            app()->instance('io.snapshot', [
                'shelters' => $shelters,
                'supplies' => $supplies,
                'inventarios_por_insumo' => $inventariosPorInsumo,
            ]);
        }

        return app('io.snapshot');
    }

    /**
     * Refugios sin dar de baja, con inventarios e insumos cargados y
     * ordenados por código (lo que pide el dashboard). Reutiliza el
     * snapshot: cero consultas extra cuando ya se cargó.
     */
    public function refugiosOperativos()
    {
        return $this->snapshot()['shelters']
            ->reject(fn ($s) => $s->trashed())
            ->sortBy('codigo')
            ->values();
    }

    /**
     * ANÁLISIS ABC DE INVENTARIOS (Pareto)
     * Clasifica los insumos según valor total de consumo anual o criticidad.
     */
    public function calcularClasificacionAbc(?int $shelterId = null): array
    {
        $cacheKey = $this->cacheKeyConVersion('clasificacion_abc', $shelterId);

        return cache()->remember($cacheKey, self::CACHE_TTL, function () use ($shelterId) {
            return $this->calcularAbc($shelterId);
        });
    }

    private function calcularAbc(?int $shelterId = null): array
    {
        $snapshot = $this->snapshot();
        $supplies = $snapshot['supplies'];
        if ($supplies->isEmpty()) {
            return ['items' => [], 'resumen' => ['A' => 0, 'B' => 0, 'C' => 0]];
        }

        $items = [];
        $totalValorConsumo = 0.0;

        foreach ($supplies as $s) {
            $consumoTotalDiario = 0.0;
            $stockActualTotal = 0.0;

            // Mismos inventarios que antes devolvía Supply::with('inventories.shelter')
            foreach ($snapshot['inventarios_por_insumo'][$s->id] ?? [] as $inv) {
                if ($shelterId !== null && $inv->shelter_id !== $shelterId) {
                    continue;
                }
                $pob = $inv->shelter ? $inv->shelter->poblacion_total : 0;
                $consumoTotalDiario += $pob * $s->consumo_diario_referencia;
                $stockActualTotal += $inv->stock_actual;
            }

            $demandaAnual = max(5.0, $consumoTotalDiario * 365);
            $valorConsumoAnual = $demandaAnual * max(0.1, $s->costo_unitario);

            $totalValorConsumo += $valorConsumoAnual;
            $items[] = [
                'supply' => $s,
                'demanda_anual' => round($demandaAnual, 1),
                'valor_consumo_anual' => $valorConsumoAnual,
                'stock_actual_total' => $stockActualTotal,
            ];
        }

        // Ordenar descendentemente por valor de consumo
        usort($items, fn($a, $b) => $b['valor_consumo_anual'] <=> $a['valor_consumo_anual']);

        // Calcular porcentajes acumulados y asignar clase ABC
        $acumulado = 0.0;
        $conteoA = 0;
        $conteoB = 0;
        $conteoC = 0;

        foreach ($items as &$item) {
            $item['pct_valor'] = $totalValorConsumo > 0 ? ($item['valor_consumo_anual'] / $totalValorConsumo) * 100 : 0;
            $acumulado += $item['pct_valor'];
            $item['pct_acumulado'] = round($acumulado, 2);

            if ($item['pct_acumulado'] <= 75.0) {
                $clase = 'A';
                $conteoA++;
            } elseif ($item['pct_acumulado'] <= 95.0) {
                $clase = 'B';
                $conteoB++;
            } else {
                $clase = 'C';
                $conteoC++;
            }

            $item['clase_calculada'] = $clase;
        }

        return [
            'items' => $items,
            'total_valor_anual' => round($totalValorConsumo, 2),
            'resumen' => [
                'A' => $conteoA,
                'B' => $conteoB,
                'C' => $conteoC,
            ]
        ];
    }

    /**
     * MATRIZ DE AUTONOMÍA Y RIESGO DE DESABASTECIMIENTO (RUNWAY)
     * Retorna el estado de todos los refugios y sus insumos clasificados por días de cobertura.
     */
    public function getMatrizAutonomia(?int $shelterId = null): array
    {
        $cacheKey = $this->cacheKeyConVersion('matriz_autonomia', $shelterId);

        return cache()->remember($cacheKey, self::CACHE_TTL, function () use ($shelterId) {
            return $this->calcularMatrizAutonomia($shelterId);
        });
    }

    private function calcularMatrizAutonomia(?int $shelterId = null): array
    {
        // Mismos refugios que antes devolvía
        // Shelter::with(['inventories.supply'])->where('estado', '!=', 'cerrado')
        $shelters = $this->snapshot()['shelters']
            ->reject(fn ($s) => $s->trashed() || $s->estado === 'cerrado')
            ->filter(fn ($s) => $shelterId === null || (int) $s->id === $shelterId)
            ->values();

        $resultado = [];
        $alertasCriticas = [];
        $alertasPrecaucion = [];

        foreach ($shelters as $shelter) {
            $insumosStatus = [];
            $minDiasAlimentos = 999.0;
            $minDiasMedicos = 999.0;

            foreach ($shelter->inventories as $inv) {
                // El refugio padre ya está en memoria: vincularlo evita que cada
                // inventario lance su propia consulta (N+1).
                $inv->setRelation('shelter', $shelter);

                $supply = $inv->supply;
                if (!$supply) continue;

                $dias = $inv->dias_autonomia;
                if ($supply->categoria === 'alimento') {
                    $minDiasAlimentos = min($minDiasAlimentos, $dias);
                } else {
                    $minDiasMedicos = min($minDiasMedicos, $dias);
                }

                $statusItem = [
                    'inventory_id' => $inv->id,
                    'supply_id' => $supply->id,
                    'codigo' => $supply->codigo,
                    'nombre' => $supply->nombre,
                    'categoria' => $supply->categoria,
                    'clasificacion_abc' => $supply->clasificacion_abc,
                    'stock_actual' => $inv->stock_actual,
                    'unidad_medida' => $supply->unidad_medida,
                    'consumo_diario' => $inv->consumo_diario_estimado,
                    'dias_autonomia' => $dias,
                    'alerta' => $inv->nivel_alerta,
                ];

                $insumosStatus[] = $statusItem;

                if ($dias < 2.0) {
                    $alertasCriticas[] = [
                        'shelter' => $shelter,
                        'supply' => $supply,
                        'dias_autonomia' => $dias,
                        'stock_actual' => $inv->stock_actual,
                        'unidad_medida' => $supply->unidad_medida,
                    ];
                } elseif ($dias <= 5.0) {
                    $alertasPrecaucion[] = [
                        'shelter' => $shelter,
                        'supply' => $supply,
                        'dias_autonomia' => $dias,
                        'stock_actual' => $inv->stock_actual,
                        'unidad_medida' => $supply->unidad_medida,
                    ];
                }
            }

            $resultado[] = [
                'shelter' => $shelter,
                'min_dias_alimentos' => $minDiasAlimentos === 999.0 ? 0.0 : $minDiasAlimentos,
                'min_dias_medicos' => $minDiasMedicos === 999.0 ? 0.0 : $minDiasMedicos,
                'insumos' => $insumosStatus,
            ];
        }

        return [
            'refugios' => $resultado,
            'alertas_criticas' => $alertasCriticas,
            'alertas_precaucion' => $alertasPrecaucion,
            'total_criticos' => count($alertasCriticas),
            'total_precaucion' => count($alertasPrecaucion),
        ];
    }

    /**
     * ALGORITMO DE REBALANCEO Y REDISTRIBUCIÓN INTER-REFUGIOS (I.O.)
     * Encuentra desbalances entre refugios con déficit inminente y refugios con superávit.
     */
    public function sugerirTransferenciasRebalanceo(?int $shelterId = null): array
    {
        $cacheKey = $this->cacheKeyConVersion('sugerencias_rebalanceo', $shelterId);

        return cache()->remember($cacheKey, self::CACHE_TTL, function () use ($shelterId) {
            return $this->calcularSugerenciasRebalanceo($shelterId);
        });
    }

    private function calcularSugerenciasRebalanceo(?int $shelterId = null): array
    {
        $snapshot = $this->snapshot();
        $supplies = $snapshot['supplies'];
        $sugerencias = [];

        // Mismos inventarios que antes devolvía la consulta
        // ShelterInventory::with(['shelter','supply'])->whereHas('shelter', activo)
        $inventariosPorSuministro = collect();
        foreach ($snapshot['inventarios_por_insumo'] as $supplyId => $inventarios) {
            $activos = array_values(array_filter(
                $inventarios,
                fn ($inv) => $inv->shelter !== null && $inv->shelter->estado === 'activo'
            ));

            if ($activos !== []) {
                $inventariosPorSuministro->put($supplyId, collect($activos));
            }
        }

        foreach ($supplies as $supply) {
            $inventarios = $inventariosPorSuministro->get($supply->id, collect());

            $deficitarios = []; // Refugios con < 2.5 días
            $superavitarios = []; // Refugios con > 6.0 días

            foreach ($inventarios as $inv) {
                $dias = $inv->dias_autonomia;
                $consumoDiario = max(0.1, $inv->consumo_diario_estimado);

                if ($dias < 2.5) {
                    // Déficit: cantidad requerida para llegar a 4 días de autonomía
                    $metaDias = 4.0;
                    $cantidadNecesaria = max(1.0, ($metaDias - $dias) * $consumoDiario);
                    $deficitarios[] = [
                        'shelter' => $inv->shelter,
                        'dias_actuales' => $dias,
                        'consumo_diario' => $consumoDiario,
                        'cantidad_necesaria' => round($cantidadNecesaria, 1),
                    ];
                } elseif ($dias > 6.0) {
                    // Superávit: cantidad que puede ceder dejando un colchón de 4 días
                    $metaReservaDias = 4.0;
                    $cantidadCedible = max(0.0, ($dias - $metaReservaDias) * $consumoDiario);
                    if ($cantidadCedible > 5.0) {
                        $superavitarios[] = [
                            'shelter' => $inv->shelter,
                            'dias_actuales' => $dias,
                            'consumo_diario' => $consumoDiario,
                            'cantidad_cedible' => round($cantidadCedible, 1),
                        ];
                    }
                }
            }

            // Cruzar deficitarios con superavitarios (Asignación Greedy / Heurística de balanceo)
            foreach ($deficitarios as $def) {
                foreach ($superavitarios as &$sup) {
                    if ($sup['cantidad_cedible'] <= 0) continue;

                    $transferible = min($def['cantidad_necesaria'], $sup['cantidad_cedible']);
                    if ($transferible >= 1.0) {
                        $sugerencias[] = [
                            'supply' => $supply,
                            'refugio_origen' => $sup['shelter'],
                            'refugio_destino' => $def['shelter'],
                            'cantidad_sugerida' => round($transferible, 1),
                            'unidad_medida' => $supply->unidad_medida,
                            'prioridad' => $def['dias_actuales'] < 1.0 ? 'urgente' : 'alta',
                            'motivo' => "El refugio destino tiene solo {$def['dias_actuales']} días de autonomía. El origen dispone de {$sup['dias_actuales']} días y puede ceder recursos sin desproteger a su población.",
                        ];

                        $def['cantidad_necesaria'] -= $transferible;
                        $sup['cantidad_cedible'] -= $transferible;

                        if ($def['cantidad_necesaria'] <= 0) {
                            break;
                        }
                    }
                }
            }
        }

        if ($shelterId !== null) {
            $sugerencias = array_values(array_filter($sugerencias, function($item) use ($shelterId) {
                return $item['refugio_origen']->id == $shelterId || $item['refugio_destino']->id == $shelterId;
            }));
        }

        return $sugerencias;
    }
}
