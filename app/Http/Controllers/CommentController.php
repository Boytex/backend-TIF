<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Incident;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request, int $id)
    {
        $incident = Incident::findOrFail($id);
        $this->ensureCanView($request, $incident);

        $comments = Comment::with('user:id,name,role')
            ->where('incident_id', $incident->id)
            ->orderBy('created_at')
            ->get();

        return response()->json(['data' => $comments]);
    }

    public function store(Request $request, int $id)
    {
        $incident = Incident::findOrFail($id);
        $this->ensureCanView($request, $incident);

        $validated = $request->validate([
            'comment' => ['required', 'string', 'min:1', 'max:2000'],
        ]);

        $comment = Comment::create([
            'incident_id' => $incident->id,
            'user_id' => $request->user()->id,
            'comment' => trim($validated['comment']),
        ])->load('user:id,name,role');

        return response()->json([
            'message' => 'Comentario agregado correctamente.',
            'data' => $comment,
        ], 201);
    }

    private function ensureCanView(Request $request, Incident $incident): void
    {
        if (! $request->user()->isTechnician() && $incident->user_id !== $request->user()->id) {
            abort(404, 'No se encontró la incidencia solicitada.');
        }
    }
}
