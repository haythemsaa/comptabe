<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\DocumentTag;
use App\Models\Partner;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
// use Intervention\Image\Laravel\Facades\Image; // Package not installed

class DocumentController extends Controller
{
    /**
     * Display the document archive.
     */
    public function index(Request $request)
    {
        $query = Document::with(['folder', 'uploader', 'tags'])
            ->active()
            ->latest('created_at');

        // Filter by folder
        if ($request->filled('folder')) {
            $query->where('folder_id', $request->folder);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by fiscal year
        if ($request->filled('year')) {
            $query->where('fiscal_year', $request->year);
        }

        // Filter starred
        if ($request->boolean('starred')) {
            $query->starred();
        }

        // Filter by recent (last 30 days)
        if ($request->boolean('recent')) {
            $query->where('created_at', '>=', now()->subDays(30));
        }

        // Filter by tag
        if ($request->filled('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('document_tags.id', $request->tag);
            });
        }

        // Filter by multiple tags
        if ($request->filled('tags') && is_array($request->tags)) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->whereIn('document_tags.id', $request->tags);
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('document_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('document_date', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $documents = $query->paginate(24)->withQueryString();

        // Get folders for sidebar
        $folders = DocumentFolder::root()->ordered()->with('children')->get();

        // Get tags
        $tags = DocumentTag::ordered()->get();

        // Stats
        $stats = [
            'total' => Document::active()->count(),
            'starred' => Document::active()->starred()->count(),
            'this_month' => Document::active()->whereMonth('created_at', now()->month)->count(),
            'storage_used' => Document::sum('file_size'),
        ];

        // Available types
        $types = Document::TYPES;

        // Available fiscal years
        $fiscalYears = Document::distinct()->pluck('fiscal_year')->filter()->sort()->reverse()->values();

        return view('documents.index', compact(
            'documents', 'folders', 'tags', 'stats', 'types', 'fiscalYears'
        ));
    }

    /**
     * Show archived documents.
     */
    public function archived(Request $request)
    {
        $documents = Document::with(['folder', 'uploader'])
            ->archived()
            ->latest('archived_at')
            ->paginate(24);

        return view('documents.archived', compact('documents'));
    }

    /**
     * Show upload form.
     */
    public function create()
    {
        $folders = DocumentFolder::with(['parent', 'children'])->root()->ordered()->get();
        $tags = DocumentTag::ordered()->get();
        $partners = Partner::orderBy('name')->get(['id', 'name']);
        $types = Document::TYPES;

        return view('documents.create', compact('folders', 'tags', 'partners', 'types'));
    }

    /**
     * Store uploaded documents.
     */
    public function store(Request $request)
    {
        $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['required', 'file', 'max:20480', 'mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx,xls,xlsx,csv,txt'],
            'folder_id' => ['nullable', 'uuid', 'exists:document_folders,id'],
            'type' => ['nullable', 'string', 'in:' . implode(',', array_keys(Document::TYPES))],
            'document_date' => ['nullable', 'date'],
            'fiscal_year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'partner_id' => ['nullable', 'uuid', 'exists:partners,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['uuid', 'exists:document_tags,id'],
        ]);

        $uploadedDocuments = [];

        DB::transaction(function () use ($request, &$uploadedDocuments) {
            foreach ($request->file('files') as $file) {
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $size = $file->getSize();

                // SECURITY: Validate magic bytes (real file type, not just extension)
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $realMimeType = finfo_file($finfo, $file->getRealPath());
                finfo_close($finfo);

                // Whitelist of allowed MIME types
                $allowedMimeTypes = [
                    'application/pdf',
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                    'image/webp',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'text/csv',
                    'text/plain',
                ];

                if (!in_array($realMimeType, $allowedMimeTypes)) {
                    throw new \Exception("Type de fichier non autorisé: {$realMimeType}. Fichier: {$originalName}");
                }

                // Block dangerous file types (even if they pass MIME check)
                $dangerousExtensions = ['php', 'exe', 'sh', 'bat', 'cmd', 'com', 'js', 'html', 'htm', 'phtml', 'phar'];
                if (in_array(strtolower($extension), $dangerousExtensions)) {
                    throw new \Exception("Extension de fichier interdite: {$extension}");
                }

                // Use real MIME type (validated by magic bytes)
                $mimeType = $realMimeType;

                // Generate unique filename
                $filename = Str::uuid() . '.' . $extension;
                $path = 'documents/' . now()->format('Y/m') . '/' . $filename;

                // SECURITY: Store file on private disk (not publicly accessible)
                $file->storeAs(dirname($path), basename($path), 'private');

                // Create thumbnail for images
                $thumbnailPath = null;
                if (str_starts_with($mimeType, 'image/')) {
                    $thumbnailPath = $this->createThumbnail($file, $filename);
                }

                // Create document record
                $document = Document::create([
                    'company_id' => session('current_tenant_id'),
                    'folder_id' => $request->folder_id,
                    'uploaded_by' => auth()->id(),
                    'name' => pathinfo($originalName, PATHINFO_FILENAME),
                    'original_filename' => $originalName,
                    'file_path' => $path,
                    'disk' => 'private', // SECURITY: Use private disk
                    'mime_type' => $mimeType,
                    'file_size' => $size,
                    'extension' => strtolower($extension),
                    'type' => $request->type ?? 'other',
                    'document_date' => $request->document_date,
                    'fiscal_year' => $request->fiscal_year ?? now()->year,
                    'description' => $request->description,
                    'partner_id' => $request->partner_id,
                    'thumbnail_path' => $thumbnailPath,
                    'shared_with_accountant' => true,
                ]);

                // Attach tags
                if ($request->filled('tags')) {
                    $document->tags()->sync($request->tags);
                }

                // SECURITY: Log document upload for audit trail
                AuditLog::log('document_uploaded', Document::class, $document->id, [
                    'filename' => $originalName,
                    'mime_type' => $mimeType,
                    'validated_mime' => $realMimeType,
                    'size' => $size,
                    'extension' => $extension,
                ]);

                $uploadedDocuments[] = $document;
            }
        });

        $count = count($uploadedDocuments);
        $message = $count === 1
            ? 'Document televerse avec succes.'
            : "{$count} documents televerses avec succes.";

        return redirect()->route('documents.index')
            ->with('success', $message);
    }

    /**
     * Display a document.
     */
    public function show(Document $document)
    {
        $document->load(['folder', 'uploader', 'tags', 'invoice', 'partner']);

        return view('documents.show', compact('document'));
    }

    /**
     * Show edit form.
     */
    public function edit(Document $document)
    {
        $folders = DocumentFolder::with(['parent', 'children'])->root()->ordered()->get();
        $tags = DocumentTag::ordered()->get();
        $partners = Partner::orderBy('name')->get(['id', 'name']);
        $types = Document::TYPES;

        return view('documents.edit', compact('document', 'folders', 'tags', 'partners', 'types'));
    }

    /**
     * Update document metadata.
     */
    public function update(Request $request, Document $document)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'folder_id' => ['nullable', 'uuid', 'exists:document_folders,id'],
            'type' => ['required', 'string', 'in:' . implode(',', array_keys(Document::TYPES))],
            'document_date' => ['nullable', 'date'],
            'fiscal_year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'reference' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'partner_id' => ['nullable', 'uuid', 'exists:partners,id'],
            'shared_with_accountant' => ['boolean'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['uuid', 'exists:document_tags,id'],
        ]);

        $document->update($validated);
        $document->tags()->sync($request->tags ?? []);

        return redirect()->route('documents.show', $document)
            ->with('success', 'Document mis a jour.');
    }

    /**
     * Delete a document.
     */
    public function destroy(Document $document)
    {
        // Delete file from storage
        Storage::disk($document->disk)->delete($document->file_path);

        if ($document->thumbnail_path) {
            Storage::disk($document->disk)->delete($document->thumbnail_path);
        }

        $document->delete();

        return redirect()->route('documents.index')
            ->with('success', 'Document supprime.');
    }

    /**
     * Download a document.
     */
    public function download(Document $document)
    {
        // SECURITY: Verify user has access to this document (multi-tenant check)
        if ($document->company_id !== session('current_tenant_id')) {
            abort(403, 'Accès refusé à ce document.');
        }

        // SECURITY: Check if user is authenticated
        if (!auth()->check()) {
            abort(401, 'Authentification requise.');
        }

        // SECURITY: Log document download for audit trail
        AuditLog::log('document_downloaded', Document::class, $document->id, [
            'filename' => $document->original_filename,
            'file_path' => $document->file_path,
        ]);

        // Use Storage facade for secure file streaming (works with both public and private disks)
        return Storage::disk($document->disk)->download($document->file_path, $document->original_filename);
    }

    /**
     * Preview a document (inline display).
     */
    public function preview(Document $document)
    {
        // SECURITY: Verify user has access to this document (multi-tenant check)
        if ($document->company_id !== session('current_tenant_id')) {
            abort(403, 'Accès refusé à ce document.');
        }

        // SECURITY: Check if user is authenticated
        if (!auth()->check()) {
            abort(401, 'Authentification requise.');
        }

        if (!$document->hasPreview()) {
            abort(404, 'Apercu non disponible pour ce type de document.');
        }

        // Use Storage facade for secure file streaming
        return response()->file(
            Storage::disk($document->disk)->path($document->file_path),
            [
                'Content-Type' => $document->mime_type,
                'Content-Disposition' => 'inline',
            ]
        );
    }

    /**
     * Toggle star status.
     */
    public function toggleStar(Document $document)
    {
        $document->toggleStar();

        return response()->json([
            'success' => true,
            'is_starred' => $document->is_starred,
        ]);
    }

    /**
     * Archive a document.
     */
    public function archive(Document $document)
    {
        $document->archive();

        return back()->with('success', 'Document archive.');
    }

    /**
     * Unarchive a document.
     */
    public function unarchive(Document $document)
    {
        $document->unarchive();

        return back()->with('success', 'Document restaure.');
    }

    /**
     * Move document to folder.
     */
    public function move(Request $request, Document $document)
    {
        $request->validate([
            'folder_id' => ['nullable', 'uuid', 'exists:document_folders,id'],
        ]);

        $document->update(['folder_id' => $request->folder_id]);

        return response()->json(['success' => true]);
    }

    /**
     * Bulk actions.
     */
    public function bulk(Request $request)
    {
        $request->validate([
            'action' => ['required', 'in:delete,archive,unarchive,move'],
            'documents' => ['required', 'array', 'min:1'],
            'documents.*' => ['uuid', 'exists:documents,id'],
            'folder_id' => ['required_if:action,move', 'nullable', 'uuid'],
        ]);

        $documents = Document::whereIn('id', $request->documents)->get();

        switch ($request->action) {
            case 'delete':
                foreach ($documents as $doc) {
                    Storage::disk($doc->disk)->delete($doc->file_path);
                    if ($doc->thumbnail_path) {
                        Storage::disk($doc->disk)->delete($doc->thumbnail_path);
                    }
                    $doc->delete();
                }
                $message = count($documents) . ' document(s) supprime(s).';
                break;

            case 'archive':
                Document::whereIn('id', $request->documents)->update([
                    'is_archived' => true,
                    'archived_at' => now(),
                ]);
                $message = count($documents) . ' document(s) archive(s).';
                break;

            case 'unarchive':
                Document::whereIn('id', $request->documents)->update([
                    'is_archived' => false,
                    'archived_at' => null,
                ]);
                $message = count($documents) . ' document(s) restaure(s).';
                break;

            case 'move':
                Document::whereIn('id', $request->documents)->update([
                    'folder_id' => $request->folder_id,
                ]);
                $message = count($documents) . ' document(s) deplace(s).';
                break;
        }

        return back()->with('success', $message);
    }

    /**
     * Create thumbnail for image.
     * Note: Requires intervention/image package to be installed.
     */
    protected function createThumbnail($file, string $filename): ?string
    {
        // Intervention Image package not installed - skip thumbnail creation
        // To enable thumbnails, run: composer require intervention/image
        return null;
    }
}
