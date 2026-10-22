<?php

namespace App\Http\Controllers;

use App\Models\Shelter;
use App\Models\Supply;
use App\Models\ShelterInventory;
use App\Models\InventoryMovement;
use App\Models\SupplyTransfer;
use App\Services\InventoryOptimizationService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected InventoryOptimizationService $inventoryService;

    public function __construct(InventoryOptimizationService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function switchShelter(Request $request)
    {
        $request->validate([
            'shelter_id' => 'nullable|exists:shelters,id',
        ]);

        $shelterId = $request->input('shelter_id');
        if ($shelterId) {
            $shelter = Shelter::find($shelterId);
            if ($shelter) {
                session([
                    'shelter_id' => $shelter->id,
                    'shelter_nombre' => $shelter->nombre,
                    'shelter_codigo' => $shelter->codigo,
                ]);
                return back()->with('success', "Vista operativa cambiada a: [{$shelter->codigo}] {$shelter->nombre}");
            }
        }
        session()->forget(['shelter_id', 'shelter_nombre', 'shelter_codigo']);
        return back()->with('success', 'Vista cambiada a: Red Centralizada (Todos los Refugios)');
    }

    public function index(Request $request)
    {
        // Si se pasa parámetro por URL (ej. ?shelter_id=1 o ?shelter_id=all)
        if ($request->has('shelter_id')) {
            $queryShelterId = $request->query('shelter_id');
            if ($queryShelterId && $queryShelterId !== 'all') {
                $found = Shelter::find($queryShelterId);
                if ($found) {
                    session([
                        'shelter_id' => $found->id,
                        'shelter_nombre' => $found->nombre,
                        'shelter_codigo' => $found->codigo,
                    ]);
                }
            } else {
                session()->forget(['shelter_id', 'shelter_nombre', 'shelter_codigo']);
            }
        }

        $activeShelterId = session('shelter_id');
        $activeShelter = $activeShelterId ? Shelter::with(['inventories.supply'])->find($activeShelterId) : null;
        if ($activeShelterId && !$activeShelter) {
            session()->forget(['shelter_id', 'shelter_nombre', 'shelter_codigo']);
            $activeShelterId = null;
        }

        $refugios = Shelter::with(['inventories.supply'])->orderBy('codigo')->get();
        $totalRefugios = $refugios->count();
        $refugiosActivos = $refugios->where('estado', 'activo')->count();

        if ($activeShelter) {
            // Métricas específicas del refugio activo
            $poblacionTotal = $activeShelter->poblacion_total;
            $capacidadMaxima = $activeShelter->capacidad_maxima;
            $ocupacionPct = $activeShelter->ocupacion_porcentaje;
            $poblacionNinos = $activeShelter->poblacion_ninos;
            $poblacionMayores = $activeShelter->poblacion_adultos_mayores;
            $poblacionMedica = $activeShelter->poblacion_atencion_medica;

            $matrizAutonomia = $this->inventoryService->getMatrizAutonomia($activeShelter->id);
            $sugerenciasTransferencia = $this->inventoryService->sugerirTransferenciasRebalanceo($activeShelter->id);

            $movimientosRecientes = InventoryMovement::with(['shelter', 'supply', 'user'])
                ->where('shelter_id', $activeShelter->id)
                ->latest('fecha_movimiento')
                ->take(8)
                ->get();

            $transferenciasActivas = SupplyTransfer::with(['refugioOrigen', 'refugioDestino', 'supply'])
                ->where(function($q) use ($activeShelter) {
                    $q->where('refugio_origen_id', $activeShelter->id)
                      ->orWhere('refugio_destino_id', $activeShelter->id);
                })
                ->whereIn('estado', ['solicitada', 'aprobada', 'en_transito'])
                ->latest()
                ->take(5)
                ->get();

            $abcData = $this->inventoryService->calcularClasificacionAbc($activeShelter->id);
        } else {
            // Métricas agregadas centralizadas (Todos los refugios)
            $poblacionTotal = $refugios->sum('poblacion_total');
            $capacidadMaxima = $refugios->sum('capacidad_maxima');
            $ocupacionPct = $capacidadMaxima > 0 ? round(($poblacionTotal / $capacidadMaxima) * 100, 1) : 0;
            $poblacionNinos = $refugios->sum('poblacion_ninos');
            $poblacionMayores = $refugios->sum('poblacion_adultos_mayores');
            $poblacionMedica = $refugios->sum('poblacion_atencion_medica');

            $matrizAutonomia = $this->inventoryService->getMatrizAutonomia();
            $sugerenciasTransferencia = $this->inventoryService->sugerirTransferenciasRebalanceo();

            $movimientosRecientes = InventoryMovement::with(['shelter', 'supply', 'user'])
                ->latest('fecha_movimiento')
                ->take(8)
                ->get();

            $transferenciasActivas = SupplyTransfer::with(['refugioOrigen', 'refugioDestino', 'supply'])
                ->whereIn('estado', ['solicitada', 'aprobada', 'en_transito'])
                ->latest()
                ->take(5)
                ->get();

            $abcData = $this->inventoryService->calcularClasificacionAbc();
        }

        $alertasCriticas = $matrizAutonomia['alertas_criticas'];
        $alertasPrecaucion = $matrizAutonomia['alertas_precaucion'];
        $criticosAlimentos = collect($alertasCriticas)->where('supply.categoria', 'alimento')->count();
        $criticosMedicos = collect($alertasCriticas)->where('supply.categoria', 'medico')->count();

        // Datos para Gráfica de Ocupación por Refugio
        $chartRefugiosLabels = $refugios->pluck('nombre')->toArray();
        $chartRefugiosPoblacion = $refugios->pluck('poblacion_total')->toArray();
        $chartRefugiosCapacidad = $refugios->pluck('capacidad_maxima')->toArray();

        return view('dashboard.index', compact(
            'refugios',
            'activeShelter',
            'totalRefugios',
            'refugiosActivos',
            'poblacionTotal',
            'capacidadMaxima',
            'ocupacionPct',
            'poblacionNinos',
            'poblacionMayores',
            'poblacionMedica',
            'alertasCriticas',
            'alertasPrecaucion',
            'criticosAlimentos',
            'criticosMedicos',
            'sugerenciasTransferencia',
            'movimientosRecientes',
            'transferenciasActivas',
            'chartRefugiosLabels',
            'chartRefugiosPoblacion',
            'chartRefugiosCapacidad',
            'abcData'
        ));
    }
}