<?php

namespace App\Http\Controllers;

use App\Models\DocumentTag;
use Illuminate\Http\Request;

class DocumentTagController extends Controller
{
    /**
     * List all tags for the current company.
     */
    public function index()
    {
        $tags = DocumentTag::ordered()
            ->withCount('documents')
            ->get();

        return response()->json($tags);
    }

    /**
     * Create a new tag.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'in:' . implode(',', DocumentTag::COLORS)],
        ]);

        $tag = DocumentTag::create([
            'company_id' => session('current_tenant_id'),
            'name' => $validated['name'],
            'color' => $validated['color'] ?? 'gray',
        ]);

        return response()->json([
            'success' => true,
            'tag' => $tag,
        ]);
    }

    /**
     * Update a tag.
     */
    public function update(Request $request, DocumentTag $tag)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'in:' . implode(',', DocumentTag::COLORS)],
        ]);

        $tag->update($validated);

        return response()->json([
            'success' => true,
            'tag' => $tag->fresh(),
        ]);
    }

    /**
     * Delete a tag.
     */
    public function destroy(DocumentTag $tag)
    {
        // Detach from all documents
        $tag->documents()->detach();

        $tag->delete();

        return response()->json(['success' => true]);
    }
}
