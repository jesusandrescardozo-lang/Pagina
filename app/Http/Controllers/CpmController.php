<?php

namespace App\Http\Controllers;

use App\Models\CpmProject;
use App\Models\CpmActivity;
use App\Services\CpmCalculatorService;
use App\Services\AuditService;
use Illuminate\Http\Request;

class CpmController extends Controller
{
    protected CpmCalculatorService $cpmService;

    public function __construct(CpmCalculatorService $cpmService)
    {
        $this->cpmService = $cpmService;
    }

    public function index()
    {
        $projects = CpmProject::withCount('activities')->latest()->get();
        return view('cpm.index', compact('projects'));
    }

    public function create()
    {
        return view('cpm.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|string|max:50',
        ]);

        $project = CpmProject::create([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'],
            'tipo' => $validated['tipo'],
            'user_id' => auth()->id() ?? 1,
        ]);

        AuditService::log('CPM', 'creado', "Proyecto CPM '{$project->nombre}' creado", $project);

        return redirect()->route('cpm.show', $project)->with('success', 'Proyecto CPM inicializado.');
    }

    public function show(CpmProject $cpm)
    {
        $project = $cpm;
        $resultado = $this->cpmService->calculate($project);
        $activities = $project->activities()->orderBy('codigo')->get();

        return view('cpm.show', [
            'project' => $project,
            'activities' => $activities,
            'duracionProyecto' => $resultado['duracion_proyecto'],
            'rutaCritica' => $resultado['ruta_critica'],
            'graphData' => $resultado['graph_data'],
        ]);
    }

    public function calculate(CpmProject $cpm)
    {
        $resultado = $this->cpmService->calculate($cpm);
        AuditService::log('CPM', 'calculo', "Ruta crítica calculada para el proyecto '{$cpm->nombre}'", $cpm);

        return response()->json($resultado);
    }

    public function storeActivity(Request $request, CpmProject $project)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:10',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'duracion' => 'required|integer|min:0',
            'predecesores' => 'nullable|array',
        ]);

        $activity = CpmActivity::create([
            'project_id' => $project->id,
            'codigo' => strtoupper($validated['codigo']),
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'],
            'duracion' => $validated['duracion'],
            'predecesores' => $validated['predecesores'] ?? [],
        ]);

        $this->cpmService->calculate($project);

        AuditService::log('CPM', 'actividad_agregada', "Actividad {$activity->codigo} agregada a {$project->nombre}", $activity);

        return back()->with('success', "Actividad {$activity->codigo} creada y ruta crítica recalculada.");
    }

    public function updateActivity(Request $request, CpmActivity $activity)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'duracion' => 'required|integer|min:0',
            'predecesores' => 'nullable|array',
        ]);

        $activity->update([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'],
            'duracion' => $validated['duracion'],
            'predecesores' => $validated['predecesores'] ?? [],
        ]);

        $this->cpmService->calculate($activity->project);

        return back()->with('success', "Actividad {$activity->codigo} actualizada.");
    }

    public function destroyActivity(CpmActivity $activity)
    {
        $project = $activity->project;
        $codigo = $activity->codigo;
        $activity->delete();

        $this->cpmService->calculate($project);

        AuditService::log('CPM', 'actividad_eliminada', "Actividad {$codigo} eliminada", $project);

        return back()->with('success', "Actividad {$codigo} eliminada.");
    }

    public function destroy(CpmProject $cpm)
    {
        $nombre = $cpm->nombre;
        $cpm->delete();

        AuditService::log('CPM', 'proyecto_eliminado', "Proyecto CPM '{$nombre}' eliminado");

        return redirect()->route('cpm.index')->with('success', "Proyecto CPM '{$nombre}' eliminado.");
    }
}
