<?php

namespace App\Http\Controllers;

use App\Models\DocumentFolder;
use Illuminate\Http\Request;

class DocumentFolderController extends Controller
{
    /**
     * List all folders for the current company.
     */
    public function index()
    {
        $folders = DocumentFolder::root()
            ->ordered()
            ->with(['children' => fn($q) => $q->ordered()])
            ->withCount('documents')
            ->get();

        return response()->json($folders);
    }

    /**
     * Create a new folder.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'parent_id' => ['nullable', 'uuid', 'exists:document_folders,id'],
            'color' => ['nullable', 'string', 'max:20'],
            'icon' => ['nullable', 'string', 'max:50'],
        ]);

        // Get max sort order
        $maxOrder = DocumentFolder::where('parent_id', $validated['parent_id'] ?? null)->max('sort_order') ?? 0;

        $folder = DocumentFolder::create([
            'company_id' => session('current_tenant_id'),
            'parent_id' => $validated['parent_id'] ?? null,
            'name' => $validated['name'],
            'color' => $validated['color'] ?? 'gray',
            'icon' => $validated['icon'] ?? 'folder',
            'sort_order' => $maxOrder + 1,
            'is_system' => false,
        ]);

        return response()->json([
            'success' => true,
            'folder' => $folder,
        ]);
    }

    /**
     * Update a folder.
     */
    public function update(Request $request, DocumentFolder $folder)
    {
        if ($folder->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'Les dossiers systeme ne peuvent pas etre modifies.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'parent_id' => ['nullable', 'uuid', 'exists:document_folders,id'],
            'color' => ['nullable', 'string', 'max:20'],
            'icon' => ['nullable', 'string', 'max:50'],
        ]);

        // Prevent making a folder its own parent or child
        if ($validated['parent_id'] === $folder->id) {
            return response()->json([
                'success' => false,
                'message' => 'Un dossier ne peut pas etre son propre parent.',
            ], 422);
        }

        $folder->update($validated);

        return response()->json([
            'success' => true,
            'folder' => $folder->fresh(),
        ]);
    }

    /**
     * Delete a folder.
     */
    public function destroy(DocumentFolder $folder)
    {
        if ($folder->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'Les dossiers systeme ne peuvent pas etre supprimes.',
            ], 403);
        }

        // Move documents to root (null folder_id)
        $folder->documents()->update(['folder_id' => null]);

        // Move children to parent
        $folder->children()->update(['parent_id' => $folder->parent_id]);

        $folder->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Reorder folders.
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'folders' => ['required', 'array'],
            'folders.*.id' => ['required', 'uuid', 'exists:document_folders,id'],
            'folders.*.sort_order' => ['required', 'integer', 'min:0'],
            'folders.*.parent_id' => ['nullable', 'uuid', 'exists:document_folders,id'],
        ]);

        foreach ($validated['folders'] as $item) {
            DocumentFolder::where('id', $item['id'])->update([
                'sort_order' => $item['sort_order'],
                'parent_id' => $item['parent_id'] ?? null,
            ]);
        }

        return response()->json(['success' => true]);
    }
}
