<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShelterRequest;
use App\Http\Requests\UpdateShelterRequest;
use App\Models\Shelter;
use App\Models\Supply;
use App\Models\ShelterInventory;
use App\Services\AuditService;
use Illuminate\Http\Request;

class ShelterController extends Controller
{
    public function index(Request $request)
    {
        $datos = $this->cacheaPagina($request, function () use ($request) {
            $query = Shelter::with(['inventories.supply']);

            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }

            if ($request->filled('q')) {
                $term = '%' . $request->q . '%';
                $query->where(function($q) use ($term) {
                    $q->where('nombre', 'like', $term)
                      ->orWhere('codigo', 'like', $term)
                      ->orWhere('municipio', 'like', $term);
                });
            }

            $shelters = $query->orderBy('nombre')->paginate(12)->withQueryString();

            return compact('shelters');
        });

        return view('shelters.index', $datos);
    }

    public function create()
    {
        return view('shelters.create');
    }

    public function store(StoreShelterRequest $request)
    {
        $validated = $request->validated();

        $adultos = $validated['poblacion_adultos'] ?? 0;
        $ninos = $validated['poblacion_ninos'] ?? 0;
        $mayores = $validated['poblacion_adultos_mayores'] ?? 0;
        $validated['poblacion_total'] = $adultos + $ninos + $mayores;

        $shelter = Shelter::create($validated);

        // Inicializar registros de inventario para todos los insumos existentes
        // (lista cacheada: una consulta en vez de una por petición de escritura)
        $supplies = Supply::listaParaMenu();
        foreach ($supplies as $supply) {
            ShelterInventory::create([
                'shelter_id' => $shelter->id,
                'supply_id' => $supply->id,
                'stock_actual' => 0.00,
                'stock_minimo' => $supply->stock_seguridad_sugerido,
                'stock_maximo' => $supply->stock_seguridad_sugerido * 5,
                'punto_reorden' => $supply->stock_seguridad_sugerido * 1.5,
            ]);
        }

        AuditService::log('refugios', 'creado', "Nuevo refugio registrado: {$shelter->nombre} ({$shelter->codigo})", $shelter, [], $shelter->toArray());

        return redirect()->route('shelters.show', $shelter)
            ->with('success', "Refugio '{$shelter->nombre}' creado exitosamente.");
    }

    public function show(Request $request, Shelter $shelter)
    {
        // Caché de datos (60 s, invalidada por versión en cada escritura).
        // Sin esto la página hacía 9 consultas de ~366 ms = 3,5 s CADA vez,
        // incluso en visitas repetidas.
        $datos = $this->cacheaPagina($request, function () use ($shelter) {
            $shelter->load([
                'inventories.supply',
                // 'inventories.shelter' NO: el refugio ya viene del route binding.
                // Asignarlo a mano (ver abajo) ahorra 1 consulta de ida y vuelta
                // a Neon (~460 ms) que devolvía exactamente la misma fila.
                'inventories.activeBatchesFefo',
                // movements/outgoing/incoming NO se cargan aquí: la vista solo usa
                // los 10 movimientos recientes, que se cargan con eager loading
                // justo debajo. Cargarlos enteros eran 5 consultas de ida y vuelta
                // a Neon (~1.750 ms) sin que ninguna vista las consumiera.
            ]);

            // Cada inventario pertenece AL refugio que ya está en memoria: se le
            // asigna sin consultar. Los accessors consumo_diario_estimado /
            // dias_autonomia / nivel_alerta (que la vista llama en CADA fila)
            // necesitan relationLoaded('shelter') para no hacer N+1.
            foreach ($shelter->inventories as $inv) {
                $inv->setRelation('shelter', $shelter);
            }

            $inventariosAlimentos = $shelter->inventories->filter(fn($i) => $i->supply && $i->supply->categoria === 'alimento');
            $inventariosMedicos = $shelter->inventories->filter(fn($i) => $i->supply && $i->supply->categoria === 'medico');

            // 'supply' debe venir eager-load-eado: la vista accede $m->supply->nombre
            // en cada fila. Sin él era N+1 (10 consultas de ~350 ms = 3.500 ms).
            $movimientosRecientes = $shelter->movements()
                ->with('supply')
                ->latest('fecha_movimiento')
                ->take(10)
                ->get();

            return compact('shelter', 'inventariosAlimentos', 'inventariosMedicos', 'movimientosRecientes');
        });

        return view('shelters.show', $datos);
    }

    public function edit(Shelter $shelter)
    {
        return view('shelters.edit', compact('shelter'));
    }

    public function update(UpdateShelterRequest $request, Shelter $shelter)
    {
        $validated = $request->validated();

        $validated['poblacion_total'] = $validated['poblacion_adultos'] + $validated['poblacion_ninos'] + $validated['poblacion_adultos_mayores'];

        $antes = $shelter->toArray();
        $shelter->update($validated);

        AuditService::log('refugios', 'actualizado', "Refugio actualizado: {$shelter->nombre}", $shelter, $antes, $shelter->toArray());

        return redirect()->route('shelters.show', $shelter)
            ->with('success', "Información del refugio '{$shelter->nombre}' actualizada.");
    }

    public function destroy(Shelter $shelter)
    {
        $nombre = $shelter->nombre;
        $antes = $shelter->toArray();
        $shelter->delete();

        AuditService::log('refugios', 'eliminado', "Refugio dado de baja (borrado lógico): {$nombre}", null, $antes, []);

        return redirect()->route('shelters.index')
            ->with('success', "Refugio '{$nombre}' dado de baja (registro conservado para trazabilidad).");
    }
}
