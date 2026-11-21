<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class IncidentController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in([
                Incident::STATUS_OPEN,
                Incident::STATUS_IN_PROGRESS,
                Incident::STATUS_RESOLVED,
            ])],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        $query = Incident::query()
            ->with(['category:id,name', 'creator:id,name,email', 'technician:id,name,email'])
            ->latest();

        if (! $request->user()->isTechnician()) {
            $query->where('user_id', $request->user()->id);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        return response()->json(['data' => $query->get()]);
    }

    public function store(Request $request)
    {
        if ($request->user()->isTechnician()) {
            abort(403, 'Los técnicos no pueden registrar incidencias desde este sistema.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
        ]);

        $incident = Incident::create([
            ...$validated,
            'status' => Incident::STATUS_OPEN,
            'user_id' => $request->user()->id,
        ]);

        $incident->load(['category:id,name', 'creator:id,name,email', 'technician:id,name,email']);

        return response()->json([
            'message' => 'La incidencia se registró correctamente.',
            'data' => $incident,
        ], 201);
    }

    public function show(Request $request, int $id)
    {
        $incident = Incident::with([
            'category:id,name',
            'creator:id,name,email',
            'technician:id,name,email',
            'comments.user:id,name,role',
        ])->findOrFail($id);

        $this->ensureCanView($request, $incident);

        return response()->json(['data' => $incident]);
    }

    public function tomar(Request $request, int $id)
    {
        if (! $request->user()->isTechnician()) {
            abort(403, 'Solo un técnico puede tomar una incidencia.');
        }

        $incident = DB::transaction(function () use ($id, $request) {
            $incident = Incident::lockForUpdate()->findOrFail($id);

            if ($incident->status !== Incident::STATUS_OPEN) {
                abort(409, 'Solo se pueden tomar incidencias que estén ABIERTAS.');
            }

            $incident->update([
                'technician_id' => $request->user()->id,
                'status' => Incident::STATUS_IN_PROGRESS,
            ]);

            return $incident;
        });

        $incident->load(['category:id,name', 'creator:id,name,email', 'technician:id,name,email']);

        return response()->json([
            'message' => 'La incidencia quedó asignada a tu usuario.',
            'data' => $incident,
        ]);
    }

    public function resolver(Request $request, int $id)
    {
        $validated = $request->validate([
            'resolution' => ['required', 'string', 'min:5', 'max:5000'],
        ]);

        if (! $request->user()->isTechnician()) {
            abort(403, 'Solo un técnico puede resolver una incidencia.');
        }

        $incident = Incident::findOrFail($id);

        if ($incident->technician_id !== $request->user()->id) {
            abort(403, 'Solo el técnico asignado puede resolver esta incidencia.');
        }
        if ($incident->status !== Incident::STATUS_IN_PROGRESS) {
            abort(409, 'La incidencia debe estar EN PROCESO para poder resolverse.');
        }

        $incident->update([
            'resolution' => $validated['resolution'],
            'status' => Incident::STATUS_RESOLVED,
        ]);

        $incident->load(['category:id,name', 'creator:id,name,email', 'technician:id,name,email']);

        return response()->json([
            'message' => 'La incidencia se marcó como resuelta.',
            'data' => $incident,
        ]);
    }

    private function ensureCanView(Request $request, Incident $incident): void
    {
        if (! $request->user()->isTechnician() && $incident->user_id !== $request->user()->id) {
            abort(404, 'No se encontró la incidencia solicitada.');
        }
    }
}
