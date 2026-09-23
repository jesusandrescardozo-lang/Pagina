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

        return view('shelters.index', compact('shelters'));
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
        $supplies = Supply::all();
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

    public function show(Shelter $shelter)
    {
        $shelter->load([
            'inventories.supply',
            'inventories.activeBatchesFefo',
            'movements.supply',
            'movements.user',
            'outgoingTransfers.refugioDestino',
            'incomingTransfers.refugioOrigen'
        ]);

        $inventariosAlimentos = $shelter->inventories->filter(fn($i) => $i->supply && $i->supply->categoria === 'alimento');
        $inventariosMedicos = $shelter->inventories->filter(fn($i) => $i->supply && $i->supply->categoria === 'medico');

        $movimientosRecientes = $shelter->movements()->latest('fecha_movimiento')->take(10)->get();

        return view('shelters.show', compact(
            'shelter',
            'inventariosAlimentos',
            'inventariosMedicos',
            'movimientosRecientes'
        ));
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
