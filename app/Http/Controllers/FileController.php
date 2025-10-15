<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateFileContent;
use App\Models\File;
use App\Models\Tag;
use App\Models\Collection;
use App\Models\User;
use App\Services\FileRecommendationService;
use App\Services\QuestService;
use Inertia\Inertia;
use ZipArchive;
use DOMDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIOFactory;
use PhpOffice\PhpPresentation\IOFactory as PresentationIOFactory;

class FileController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private QuestService $questService
    ) {}

    public function index(Request $request)
    {
        $query = File::verified()->with(['user', 'tags'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = strtolower($request->search);
                return $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%'])
                      ->orWhereRaw('LOWER(description) LIKE ?', ['%' . $search . '%'])
                      ->orWhereRaw('LOWER(content) LIKE ?', ['%' . $search . '%']);
                });
            })
            ->when($request->filled('tags') && is_array($request->tags), function ($query) use ($request) {
                return $query->whereHas('tags', function ($q) use ($request) {
                    $q->whereIn('tags.id', $request->tags);
                }, '=', count($request->tags));
            })
            ->when($request->boolean('starred'), function ($query) {
                $query->whereHas('starredBy', function ($q) {
                    $q->where('user_id', auth()->id());
                });
            })
            ->when($request->boolean('sameProgram'), function ($query) {
                $userProgramId = auth()->user()->program_id;
                if ($userProgramId) {
                    $query->whereHas('user', function ($q) use ($userProgramId) {
                        $q->where('program_id', $userProgramId);
                    });
                }
            });

        // Handle sorting
        if ($request->filled('sort') && in_array($request->input('sort'), ['name', 'created_at', 'star_count'])) {
            $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->input('sort'), $direction);
        }

        $files = $query->paginate(12)->withQueryString();

        return Inertia::render('Files/Index', [
            'files' => $files,
        ]);
    }

    public function create()
    {
        $allTags = Tag::orderBy('name')->get();
        return Inertia::render('Files/Create', [
            'allTags' => $allTags,
        ]);
    }

    /**
     * Calculate file hash for duplicate detection
     */
    private function calculateFileHash($file)
    {
        return hash_file('sha256', $file->getRealPath());
    }

    public function store(Request $request)
    {
        set_time_limit(0);
        $request->validate([
            'file' => 'required|file|mimes:txt,xlsx,pdf,pptx,doc,docx|max:25600',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'tags' => 'nullable|array',
            'tags.*.id' => 'integer|exists:tags,id',
            'tags.*.name' => 'string|max:50',
            'collections' => 'nullable|array',
            'collections.*' => 'integer|exists:collections,id',
        ]);

        $uploadedFile = $request->file('file');
        $fileHash = $this->calculateFileHash($uploadedFile);

        // Use the provided name or fallback to original filename if empty
        $fileName = $request->input('name');
        if (empty($fileName)) {
            $fileName = $uploadedFile->getClientOriginalName();
        }

        $userID = auth()->id();
        $user = auth()->user();

        // Check for duplicate by name and hash
        $existingFile = File::where(function ($query) use ($userID, $fileHash) {
            $query->where('user_id', $userID)
                ->where('file_hash', $fileHash);
        })->first();

        if ($existingFile) {
            return redirect()->back()->withErrors(
                [
                    'file' => 'This file already exists in the system.',
                    'duplicate_file_id' => $existingFile->id // Optional: to link to the existing file
                ]
            );
        }

        $path = $uploadedFile->store('uploads');

        try {
            // Convert document to PDF and get both text content and PDF path
            $conversionResult = $this->convertToPdfAndExtractText($uploadedFile);
            $content = $conversionResult['content'];
            $pdfPath = $conversionResult['pdf_path'];
            
            // Upload PDF to Gemini File API and get URI
            $geminiFileUri = $this->uploadToGeminiFileApi($pdfPath);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['file' => 'Invalid file type or unable to process the file: ' . $e->getMessage()]);
        }

        // Auto-verify files uploaded by faculty or admin
        $isAutoVerified = in_array($user->user_role, ['faculty', 'admin']);
        
        $file = File::create([
            'name' => $fileName,
            'description' => $request->input('description'),
            'path' => $path,
            'pdf_path' => $pdfPath,
            'gemini_file_uri' => $geminiFileUri,
            'content' => $content,
            'file_hash' => $fileHash,
            'user_id' => auth()->id(),
            'verified' => $isAutoVerified,
            'verified_at' => $isAutoVerified ? now() : null,
            'verified_by' => $isAutoVerified ? auth()->id() : null,
        ]);

            // Handle tags
            if ($request->has('tags')) {
                $tagIds = [];
                foreach ($request->input('tags') as $tagData) {
                    if (isset($tagData['id'])) {
                        $tagIds[] = $tagData['id'];
                    } elseif (isset($tagData['name'])) {
                        $tag = Tag::firstOrCreate(['name' => $tagData['name']]);
                        $tagIds[] = $tag->id;
                    }
                }
                $file->tags()->sync($tagIds);
            }

            // Handle collections
            if ($request->has('collections') && is_array($request->input('collections'))) {
                $collectionIds = $request->input('collections');
                
                // Verify that the user has access to these collections (owns them or they are public)
                $validCollections = Collection::whereIn('id', $collectionIds)
                    ->where(function ($query) {
                        $query->where('user_id', auth()->id())
                              ->orWhere('is_public', true);
                    })
                    ->pluck('id')
                    ->toArray();
                
                if (!empty($validCollections)) {
                    $file->collections()->attach($validCollections);
                    
                    // Update collection counts
                    Collection::whereIn('id', $validCollections)->each(function ($collection) {
                        $collection->updateCounts();
                    });
                }
            }

            // Award XP for file upload (20 base XP)
            $user->addExperience(20);

            // Update quest progress for file upload
            $this->questService->checkQuestCompletion($user, 'file_upload');
            // Update quest progress for activity streak
            $this->questService->updateQuestProgress($user, 'activity_streak');

            // Check for first file upload achievement
            $userFileCount = File::where('user_id', $user->id)->count();
            if ($userFileCount == 1) {
                $user->notify(new \App\Notifications\AchievementUnlockedNotification(
                    'First Upload',
                    'Upload your first file',
                    '📁'
                ));
            }

            // For multi-file uploads, return to create page with success data instead of redirecting
            if ($request->header('X-Inertia') && $request->header('X-Multi-File-Upload')) {
                return back()->with([
                    'file_upload_success' => true,
                    'uploaded_file_id' => $file->id,
                    'message' => 'File uploaded successfully! You gained 20 XP!'
                ]);
            }

            return redirect()->route('files.show', $file)
                ->with([
                    'success' => 'File uploaded successfully! You gained 20 XP!',
                    'content' => $content,
                    'file_id' => $file->id,
                    'uploaded_file_id' => $file->id,
                ]);
    }

    public function show(Request $request, $id)
    {
        $file = File::with(['tags', 'user'])
            ->findOrFail($id);

        // Get file extension
        $extension = pathinfo($file->path, PATHINFO_EXTENSION);

        // Check if file exists
        $fileExists = Storage::exists($file->path);

        // Get collections that contain this file (public collections or owned by current user)
        $collections = collect();
        if (auth()->check()) {
            $collections = $file->collections()
                ->with(['user'])
                ->withCount('files')
                ->where(function ($query) {
                    $query->where('is_public', true)
                          ->orWhere('user_id', auth()->id());
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return Inertia::render('Files/Show', [
            'file' => $file,
            'fileInfo' => [
                'extension' => $extension,
                'exists' => $fileExists,
                'url' => $fileExists ? route('files.serve', $file->id) : null,
                'size' => $fileExists ? Storage::size($file->path) : null,
                'lastModified' => $fileExists ? Storage::lastModified($file->path) : null,
            ],
            'collections' => $collections,
        ]);
    }

    public function indexPersonal(Request $request)
    {
        $query = File::with(['tags', 'user', 'verifier'])
            ->where('user_id', auth()->id())
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->filled('tags') && is_array($request->tags), function ($query) use ($request) {
                return $query->whereHas('tags', function ($q) use ($request) {
                    $q->whereIn('tags.id', $request->tags);
                }, '=', count($request->tags));
            })
            ->when($request->boolean('starred'), function ($query) use ($request) {
                return $query->whereHas('starredBy', function ($q) {
                    $q->where('user_id', auth()->id());
                });
            })
            ->when($request->boolean('verified'), function ($query) use ($request) {
                return $query->where('verified', true);
            })
            ->when($request->boolean('pending'), function ($query) use ($request) {
                return $query->where('verified', false)->where('is_denied', false);
            })
            ->when($request->boolean('denied'), function ($query) use ($request) {
                return $query->where('is_denied', true);
            });

        // Handle sorting
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        
        $validSorts = ['name', 'created_at', 'star_count', 'updated_at'];
        if (!in_array($sort, $validSorts)) {
            $sort = 'created_at';
        }
        
        if ($sort === 'star_count') {
            $query->withCount('starredBy as star_count')->orderBy('star_count', $direction);
        } else {
            $query->orderBy($sort, $direction);
        }

        $files = $query->withCount(['flashcards', 'quizzes'])
            ->paginate(9)
            ->withQueryString();

        // Get starred file IDs for the current user
        $starredFiles = auth()->user()->starredFiles->pluck('id')->toArray();

        // Add is_starred flag to each file
        $files->getCollection()->transform(function ($file) use ($starredFiles) {
            $file->is_starred = in_array($file->id, $starredFiles);
            return $file;
        });

        $tags = Tag::orderBy('name')->get();

        return Inertia::render('MyFiles', [
            'files' => $files,
            'tags' => $tags,
            'selectedTags' => $request->tags ?? [],
        ]);
    }

    /**
     * Format file size to human-readable format
     */
    private function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
    public function edit(File $file)
    {
        // Authorization is handled by middleware
        // $this->authorize('update', $file);

        $file->load(['tags', 'user']);
        $allTags = \App\Models\Tag::orderBy('name')->get();

        return Inertia::render('Files/Edit', [
            'file' => $file,
            'allTags' => $allTags,
        ]);
    }

    public function update(Request $request, File $file)
    {
        // Log the incoming request for debugging
        \Log::info('File update request', [
            'file_id' => $file->id,
            'request_data' => $request->all()
        ]);

        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255', // Make name optional since it's not being sent
                'description' => 'nullable|string|max:1000',
                'tags' => 'nullable|array',
                'collections' => 'nullable|array',
                'collections.*' => 'integer|exists:collections,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed for file update', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            throw $e;
        }

        // Authorization check - ensure user owns the file or is admin
        if ($file->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized to update this file');
        }

        // Only update fields that are provided
        $updateData = [];
        if (isset($validated['name'])) {
            $updateData['name'] = $validated['name'];
        }
        if (isset($validated['description'])) {
            $updateData['description'] = $validated['description'];
        }
        
        if (!empty($updateData)) {
            $file->update($updateData);
        }

        // Handle tags
        if ($request->has('tags')) {
            $tagIds = [];
            $tags = $request->input('tags', []);

            \Log::info('Processing tags for file update', [
                'file_id' => $file->id,
                'tags_raw' => $tags
            ]);

            if (is_array($tags)) {
                foreach ($tags as $tag) {
                    if (is_string($tag)) {
                        // Handle simple string format (tag name)
                        if (!empty($tag)) {
                            $newTag = \App\Models\Tag::firstOrCreate(['name' => $tag]);
                            $tagIds[] = $newTag->id;
                        }
                    } elseif (is_array($tag)) {
                        // Handle object format with id/name properties
                        if (isset($tag['id']) && is_numeric($tag['id'])) {
                            $tagIds[] = (int)$tag['id'];
                        } elseif (isset($tag['name']) && !empty($tag['name'])) {
                            $newTag = \App\Models\Tag::firstOrCreate(['name' => $tag['name']]);
                            $tagIds[] = $newTag->id;
                        }
                    }
                }
            }

            \Log::info('Final tag IDs for sync', [
                'file_id' => $file->id,
                'tag_ids' => $tagIds
            ]);

            $file->tags()->sync($tagIds);
        } else {
            $file->tags()->detach();
        }

        // Handle collections if provided
        if ($request->has('collections')) {
            $collectionIds = $request->input('collections', []);
            
            if (is_array($collectionIds) && !empty($collectionIds)) {
                // Verify that the user has access to these collections (owns them or they are public)
                $validCollections = Collection::whereIn('id', $collectionIds)
                    ->where(function ($query) {
                        $query->where('user_id', auth()->id())
                              ->orWhere('is_public', true);
                    })
                    ->pluck('id')
                    ->toArray();
                
                // Sync collections (this will replace existing ones)
                $file->collections()->sync($validCollections);
                
                // Update collection counts for new collections
                if (!empty($validCollections)) {
                    try {
                        Collection::whereIn('id', $validCollections)->each(function ($collection) {
                            $collection->updateCounts();
                        });
                    } catch (\Exception $e) {
                        \Log::error('Failed to update collection counts', [
                            'error' => $e->getMessage(),
                            'collections' => $validCollections
                        ]);
                    }
                }
            } else {
                // If collections is empty array, remove all collections
                $file->collections()->sync([]);
            }
        }

        // Always return an Inertia response
        return redirect()->route('files.show', $file->id)
            ->with('success', 'File updated successfully');
    }

    public function destroy(Request $request, File $file)
    {
        // Check if the user is authorized to delete the file
        $this->authorize('delete', $file);

        $forceDelete = $request->query('force_delete') === 'true';

        // Check if current user is admin and wants to force delete
        if (Auth::user()->user_role === 'admin' && $forceDelete) {
            // Admin permanent deletion
            if (Storage::exists($file->path)) {
                Storage::delete($file->path);
            }
            // Also delete the generated PDF
            if ($file->pdf_path && Storage::exists($file->pdf_path)) {
                Storage::delete($file->pdf_path);
            }
            $file->delete();
            return redirect()->route('files.index')->with('success', 'File permanently deleted from storage.');
        } else {
            // Archive mode: Transfer ownership to admin instead of deleting
            $adminUser = User::where('user_role', 'admin')->first();
            
            if (!$adminUser) {
                return back()->withErrors(['error' => 'Cannot archive file: No admin user found.']);
            }

            // Transfer ownership to admin
            $originalOwner = Auth::user()->first_name . " " . Auth::user()->last_name;
            $archiveInfo = "\n\n[Originally owned by: " . $originalOwner . " - Archived on " . now()->format('Y-m-d H:i:s') . "]";
            
            $file->update([
                'user_id' => $adminUser->id,
                'name' => '[ARCHIVED] ' . $file->name,
                'description' => ($file->description ?? '') . $archiveInfo
            ]);

            return redirect()->route('files.index')->with('success', 'File archived and transferred to admin for backup.');
        }
    }

    public function download(File $file)
    {
        $filePath = $file->path; // Use the relative path stored in the database

        if (!Storage::exists($filePath)) {
            return back()->withErrors(['file' => 'File not found on server.']);
        }

        // Ensure the file name includes the correct extension
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $fileName = $file->name;
        if (!str_ends_with($fileName, ".{$extension}")) {
            $fileName .= ".{$extension}";
        }

        return Storage::download($filePath, $fileName); // Use Storage::download for proper handling
    }

    public function serve(File $file)
    {
        $filePath = $file->path;

        if (!Storage::exists($filePath)) {
            abort(404, 'File not found');
        }

        // Get file info
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeType = Storage::mimeType($filePath);
        
        // Return file response with proper headers for inline viewing
        return response(Storage::get($filePath), 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $file->name . '.' . $extension . '"',
            'Cache-Control' => 'public, max-age=3600',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET',
        ]);
    }

    public function checkDuplicate($hash)
    {
        $existingFile = File::where('file_hash', $hash)
            ->where('user_id', auth()->id())
            ->with(['tags'])
            ->first();

        if ($existingFile) {
            return response()->json([
                'exists' => true,
                'file' => [
                    'id' => $existingFile->id,
                    'name' => $existingFile->name,
                    'description' => $existingFile->description,
                    'tags' => $existingFile->tags,
                    'url' => route('files.show', $existingFile->id)
                ]
            ]);
        }

        return response()->json(['exists' => false]);
    }

    public function checkDuplicateByFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
            'name' => 'required|string'
        ]);

        $uploadedFile = $request->file('file');
        $fileName = $request->input('name');
        
        // Calculate the same hash the backend would calculate
        $fileHash = $this->calculateFileHash($uploadedFile);
        
        // Check for exact hash match (same file content)
        $existingFile = File::where('file_hash', $fileHash)
            ->where('user_id', auth()->id())
            ->with(['tags'])
            ->first();

        if ($existingFile) {
            return response()->json([
                'exists' => true,
                'file' => [
                    'id' => $existingFile->id,
                    'name' => $existingFile->name,
                    'description' => $existingFile->description,
                    'tags' => $existingFile->tags,
                    'url' => route('files.show', $existingFile->id)
                ]
            ]);
        }

        return response()->json(['exists' => false]);
    }

    public function generateFlashcards(Request $request, File $file)
    {
        return $this->generateContent($request, $file, 'flashcards', [
            "type" => "ARRAY",
            "items" => [
                "type" => "OBJECT",
                "properties" => [
                    "question" => ["type" => "STRING"],
                    "answer" => ["type" => "STRING"]
                ],
                "nullable" => false,
                "required" => ["question", "answer"],
            ],
        ]);
    }

    public function generateMultipleChoiceQuizzes(Request $request, File $file)
    {
        return $this->generateContent($request, $file, 'multiple_choice_quizzes', [
            "type" => "ARRAY",
            "items" => [
                "type" => "OBJECT",
                "properties" => [
                    "question" => ["type" => "STRING"],
                    "options" => [
                        "type" => "ARRAY",
                        "items" => ["type" => "STRING"],
                        "minItems" => 2,
                        "maxItems" => 4,
                        "nullable" => false,
                    ],
                    "answer" => ["type" => "STRING"]
                ],
                "nullable" => false,
                "required" => ["question", "options", "answer"],
            ],
        ]);
    }

    public function generateEnumerationQuizzes(Request $request, File $file)
    {
        return $this->generateContent($request, $file, 'enumeration_quizzes', [
            "type" => "ARRAY",
            "items" => [
                "type" => "OBJECT",
                "properties" => [
                    "question" => ["type" => "STRING"],
                    "answers" => [
                        "type" => "ARRAY",
                        "items" => ["type" => "STRING"],
                        "minItems" => 1,
                        "maxItems" => 10,
                        "nullable" => false,
                    ]
                ],
                "nullable" => false,
                "required" => ["question", "answers"],
            ],
        ]);
    }

    public function generateTrueFalseQuizzes(Request $request, File $file)
    {
        return $this->generateContent($request, $file, 'true_false_quizzes', [
            "type" => "ARRAY",
            "items" => [
                "type" => "OBJECT",
                "properties" => [
                    "question" => ["type" => "STRING"],
                    "answer" => ["type" => "STRING"]
                ],
                "nullable" => false,
                "required" => ["question", "answer"],
            ],
        ]);
    }

    public function generateFlashcardsAndQuizzes(Request $request, File $file)
    {
        if (!$file->verified) {
            return back()->withErrors(['error' => 'File must be verified by a faculty or admin before generating flashcards and quizzes.']);
        }

        $request->validate([
            'generate_flashcards' => 'boolean',
            'generate_multiple_choice_quizzes' => 'boolean',
            'generate_enumeration_quizzes' => 'boolean',
            'generate_true_false_quizzes' => 'boolean',
            'flashcards_count' => 'nullable|integer|min:1|max:15',
            'multiple_choice_count' => 'nullable|integer|min:1|max:15',
            'enumeration_count' => 'nullable|integer|min:1|max:15',
            'true_false_count' => 'nullable|integer|min:1|max:15',
        ]);

        $types = [];
        $counts = [];

        if ($request->boolean('generate_flashcards')) {
            $types[] = 'flashcards';
            $counts['flashcards'] = $request->input('flashcards_count', 5);
        }

        if ($request->boolean('generate_multiple_choice_quizzes')) {
            $types[] = 'multiple_choice';
            $counts['multiple_choice'] = $request->input('multiple_choice_count', 5);
        }

        if ($request->boolean('generate_enumeration_quizzes')) {
            $types[] = 'enumeration';
            $counts['enumeration'] = $request->input('enumeration_count', 5);
        }

        if ($request->boolean('generate_true_false_quizzes')) {
            $types[] = 'true_false';
            $counts['true_false'] = $request->input('true_false_count', 5);
        }

        if (empty($types)) {
            return back()->withErrors(['error' => 'Please select at least one type of content to generate.']);
        }

        // Update status to pending and dispatch job
        $file->update(['generation_status' => 'pending']);
        GenerateFileContent::dispatch($file, $types, $counts);

        return back()->with('success', 'Content generation started. You will be notified when it\'s complete.');
    }

    private function generateContent(Request $request, File $file, string $type, array $schema)
    {
        if (!$file->verified) {
            return redirect()->back()->withErrors(['error' => 'File must be verified by a faculty or admin before generating content.']);
        }

        set_time_limit(0);

        // Check if we have a Gemini file URI, otherwise fall back to text content
        if (empty($file->gemini_file_uri) && empty($file->content)) {
            return response()->json([
                'error' => 'File content and Gemini file URI are both empty or not available.'
            ], 400);
        }

        $request->validate([
            'count' => 'required|integer|min:1|max:15',
        ]);

        $count = $request->input('count');

        $apiKey = config('services.gemini.api_key');
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey;

        $generationConfig = [
            "response_mime_type" => "application/json",
            "response_schema" => [
                "type" => "OBJECT",
                "properties" => [
                    $type => array_merge($schema, [
                        "minItems" => $count,
                        "maxItems" => $count,
                        "nullable" => false,
                    ]),
                ],
                "nullable" => false,
                "required" => [$type],
            ],
        ];

        // Prepare the payload - use file URI if available, otherwise fall back to text
        $parts = [];
        if (!empty($file->gemini_file_uri)) {
            $parts[] = [
                'text' => "Generate {$count} {$type} from the following document:"
            ];
            $parts[] = [
                'file_data' => [
                    'mime_type' => 'application/pdf',
                    'file_uri' => $file->gemini_file_uri
                ]
            ];
        } else {
            $parts[] = [
                'text' => "Generate {$count} {$type} using the following content:\n\nContent:\n" . $file->content
            ];
        }

        $payload = [
            'contents' => [
                [
                    'parts' => $parts
                ]
            ],
            "generationConfig" => $generationConfig,
        ];

        $response = Http::timeout(300)->post($url, $payload);

        if ($response->successful()) {
            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            logger()->info('Gemini response: ' . $text);

            $parsedData = json_decode($text, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $items = $parsedData[$type] ?? [];

                collect($items)->each(function ($item) use ($file, $type) {
                    if ($type === 'multiple_choice_quizzes') {
                        // Ensure the answer is included in the options
                        if (!in_array($item['answer'], $item['options'])) {
                            $item['options'][] = $item['answer'];
                        }
                    }

                    match ($type) {
                        'flashcards' => \App\Models\Flashcard::create([
                            'question' => $item['question'],
                            'answer' => $item['answer'],
                            'file_id' => $file->id,
                        ]),
                        'multiple_choice_quizzes' => \App\Models\Quiz::create([
                            'question' => $item['question'],
                            'type' => 'multiple_choice',
                            'options' => $item['options'],
                            'answers' => [$item['answer']],
                            'file_id' => $file->id,
                        ]),
                        'enumeration_quizzes' => \App\Models\Quiz::create([
                            'question' => $item['question'],
                            'type' => 'enumeration',
                            'options' => null,
                            'answers' => $item['answers'],
                            'file_id' => $file->id,
                        ]),
                        'true_false_quizzes' => \App\Models\Quiz::create([
                            'question' => $item['question'],
                            'type' => 'true_false',
                            'options' => null,
                            'answers' => [$item['answer']],
                            'file_id' => $file->id,
                        ]),
                    };
                });

                // Update quest progress for quiz generation
                $user = auth()->user();
                $this->questService->updateQuestProgress($user, 'quiz_generate');

                return redirect()->route('files.show', $file->id)
                    ->with('success', ucfirst(str_replace('_', ' ', $type)) . ' generated successfully!');
            } else {
                return redirect()->back()->withErrors([
                    'error' => 'Failed to parse JSON from Gemini response.',
                    'raw_response' => $text,
                ]);
            }
        }

        return redirect()->back()->withErrors([
            'error' => 'Failed to call Gemini API.',
            'details' => $response->body(),
        ]);
    }

    public function generateContentInternal(File $file, int $count, string $type, array $schema)
    {
        try {
            set_time_limit(0);

            // Check if we have a Gemini file URI, otherwise fall back to text content
            if (empty($file->gemini_file_uri) && empty($file->content)) {
                return ['success' => false, 'error' => 'File content and Gemini file URI are both empty or not available.'];
            }

            $apiKey = config('services.gemini.api_key');
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey;

            $generationConfig = [
                "response_mime_type" => "application/json",
                "response_schema" => [
                    "type" => "OBJECT",
                    "properties" => [
                        $type => array_merge($schema, [
                            "minItems" => $count,
                            "maxItems" => $count,
                            "nullable" => false,
                        ]),
                    ],
                    "nullable" => false,
                    "required" => [$type],
                ],
            ];

            // Prepare the payload - use file URI if available, otherwise fall back to text
            $parts = [];
            // Attempt to verify file URI if it exists
            $shouldUseFileUri = false;
            if (!empty($file->gemini_file_uri)) {
                try {
                    // Attempt to verify the file URI still exists/is accessible
                    $verifyUrl = str_replace('/generateContent', '/files/' . basename($file->gemini_file_uri), $url);
                    $verifyResponse = Http::timeout(10)->get($verifyUrl);
                    $shouldUseFileUri = $verifyResponse->successful();
                } catch (\Exception $e) {
                    logger()->warning('Failed to verify Gemini file URI', [
                        'file_id' => $file->id,
                        'uri' => $file->gemini_file_uri,
                        'error' => $e->getMessage()
                    ]);
                    $shouldUseFileUri = false;
                }

                if (!$shouldUseFileUri && $file->pdf_path && Storage::exists($file->pdf_path)) {
                    // Re-upload the PDF if URI is invalid
                    try {
                        logger()->info('Re-uploading PDF to Gemini', ['file_id' => $file->id]);
                        $newUri = $this->uploadToGeminiFileApi($file->pdf_path);
                        $file->update(['gemini_file_uri' => $newUri]);
                        $shouldUseFileUri = true;
                        $file->gemini_file_uri = $newUri;
                    } catch (\Exception $e) {
                        logger()->error('Failed to re-upload PDF to Gemini', [
                            'file_id' => $file->id,
                            'error' => $e->getMessage()
                        ]);
                        $shouldUseFileUri = false;
                    }
                }
            }

            if ($shouldUseFileUri) {
                $parts[] = [
                    'text' => "Generate {$count} {$type} from the following document:"
                ];
                $parts[] = [
                    'file_data' => [
                        'mime_type' => 'application/pdf',
                        'file_uri' => $file->gemini_file_uri
                    ]
                ];
            } else {
                // Fall back to using text content if we can't use the file URI
                logger()->info('Falling back to text content for generation', ['file_id' => $file->id]);
                $parts[] = [
                    'text' => "Generate {$count} {$type} using the following content:\n\nContent:\n" . $file->content
                ];
            }

            $payload = [
                'contents' => [
                    [
                        'parts' => $parts
                    ]
                ],
                "generationConfig" => $generationConfig,
            ];

            $response = Http::timeout(300)->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

                $parsedData = json_decode($text, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $items = $parsedData[$type] ?? [];

                    foreach ($items as $item) {
                        if ($type === 'multiple_choice_quizzes') {
                            // Ensure the answer is included in the options
                            if (!in_array($item['answer'], $item['options'])) {
                                $item['options'][] = $item['answer'];
                            }
                        }

                        match ($type) {
                            'flashcards' => \App\Models\Flashcard::create([
                                'question' => $item['question'],
                                'answer' => $item['answer'],
                                'file_id' => $file->id,
                            ]),
                            'multiple_choice_quizzes' => \App\Models\Quiz::create([
                                'question' => $item['question'],
                                'type' => 'multiple_choice',
                                'options' => $item['options'],
                                'answers' => [$item['answer']],
                                'file_id' => $file->id,
                            ]),
                            'enumeration_quizzes' => \App\Models\Quiz::create([
                                'question' => $item['question'],
                                'type' => 'enumeration',
                                'options' => null,
                                'answers' => $item['answers'],
                                'file_id' => $file->id,
                            ]),
                            'true_false_quizzes' => \App\Models\Quiz::create([
                                'question' => $item['question'],
                                'type' => 'true_false',
                                'options' => null,
                                'answers' => [$item['answer']],
                                'file_id' => $file->id,
                            ]),
                        };
                    }

                    return ['success' => true, 'message' => count($items) . ' ' . str_replace('_', ' ', $type) . ' generated'];
                } else {
                    return ['success' => false, 'error' => 'Failed to parse JSON from Gemini response.'];
                }
            }

            logger()->error('Gemini API call failed', [
                'file_id' => $file->id,
                'type' => $type,
                'status_code' => $response->status(),
                'response_body' => $response->body(),
                'request_url' => $url,
                'request_payload' => $payload
            ]);
            return ['success' => false, 'error' => 'Failed to call Gemini API.'];
        } catch (\Exception $e) {
            logger()->error('Error generating content', [
                'file_id' => $file->id,
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['success' => false, 'error' => 'Error generating ' . str_replace('_', ' ', $type) . ': ' . $e->getMessage()];
        }
    }

    public function generateFlashcardsContent(File $file, int $count)
    {
        return $this->generateContentInternal($file, $count, 'flashcards', [
            "type" => "ARRAY",
            "items" => [
                "type" => "OBJECT",
                "properties" => [
                    "question" => ["type" => "STRING"],
                    "answer" => ["type" => "STRING"]
                ],
                "nullable" => false,
                "required" => ["question", "answer"],
            ],
        ]);
    }

    public function generateMultipleChoiceContent(File $file, int $count)
    {
        return $this->generateContentInternal($file, $count, 'multiple_choice_quizzes', [
            "type" => "ARRAY",
            "items" => [
                "type" => "OBJECT",
                "properties" => [
                    "question" => ["type" => "STRING"],
                    "options" => [
                        "type" => "ARRAY",
                        "items" => ["type" => "STRING"],
                        "minItems" => 2,
                        "maxItems" => 4,
                        "nullable" => false,
                    ],
                    "answer" => ["type" => "STRING"]
                ],
                "nullable" => false,
                "required" => ["question", "options", "answer"],
            ],
        ]);
    }

    public function generateEnumerationContent(File $file, int $count)
    {
        return $this->generateContentInternal($file, $count, 'enumeration_quizzes', [
            "type" => "ARRAY",
            "items" => [
                "type" => "OBJECT",
                "properties" => [
                    "question" => ["type" => "STRING"],
                    "answers" => [
                        "type" => "ARRAY",
                        "items" => ["type" => "STRING"],
                        "minItems" => 1,
                        "maxItems" => 10,
                        "nullable" => false,
                    ]
                ],
                "nullable" => false,
                "required" => ["question", "answers"],
            ],
        ]);
    }

    public function generateTrueFalseContent(File $file, int $count)
    {
        return $this->generateContentInternal($file, $count, 'true_false_quizzes', [
            "type" => "ARRAY",
            "items" => [
                "type" => "OBJECT",
                "properties" => [
                    "question" => ["type" => "STRING"],
                    "answer" => ["type" => "STRING"]
                ],
                "nullable" => false,
                "required" => ["question", "answer"],
            ],
        ]);
    }

    private function convertToPdfAndExtractText($file)
    {
        $extension = $file->getClientOriginalExtension();
        $content = '';
        $pdfPath = null;

        switch ($extension) {
            case 'txt':
                $content = file_get_contents($file->getRealPath());
                $pdfPath = $this->convertTextToPdf($content, $file->getClientOriginalName());
                break;

            case 'pdf':
                // Already a PDF, just extract text and store path
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($file->getRealPath());
                $content = $pdf->getText();
                // Store the PDF directly
                $pdfPath = 'pdfs/' . uniqid() . '_' . $file->getClientOriginalName();
                Storage::put($pdfPath, file_get_contents($file->getRealPath()));
                break;

            case 'docx':
                $result = $this->convertDocxToPdfDirect($file->getRealPath(), $file->getClientOriginalName());
                $content = $result['content'];
                $pdfPath = $result['pdf_path'];
                break;

            case 'doc':
                // For older DOC files, fallback to text extraction
                $content = $this->doc_to_text($file->getRealPath());
                $pdfPath = $this->convertTextToPdf($content, $file->getClientOriginalName());
                break;

            case 'xlsx':
                $result = $this->convertXlsxToPdfDirect($file->getRealPath(), $file->getClientOriginalName());
                $content = $result['content'];
                $pdfPath = $result['pdf_path'];
                break;

            case 'pptx':
                $result = $this->convertPptxToPdfDirect($file->getRealPath(), $file->getClientOriginalName());
                $content = $result['content'];
                $pdfPath = $result['pdf_path'];
                break;

            default:
                throw new \Exception('Unsupported file type');
        }

        // Ensure the content is UTF-8 encoded
        if (!mb_detect_encoding($content, 'UTF-8', true)) {
            $content = mb_convert_encoding($content, 'UTF-8');
        }

        return [
            'content' => $content,
            'pdf_path' => $pdfPath
        ];
    }

    private function convertTextToPdf($content, $originalName)
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        $dompdf = new Dompdf($options);
        
        $html = '<html><head><meta charset="UTF-8"></head><body>';
        $html .= '<h1>' . htmlspecialchars(pathinfo($originalName, PATHINFO_FILENAME)) . '</h1>';
        $html .= '<div style="font-family: Arial, sans-serif; font-size: 12px; line-height: 1.6;">';
        $html .= nl2br(htmlspecialchars($content));
        $html .= '</div></body></html>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfContent = $dompdf->output();
        $pdfPath = 'pdfs/' . uniqid() . '_' . pathinfo($originalName, PATHINFO_FILENAME) . '.pdf';
        
        Storage::put($pdfPath, $pdfContent);
        
        return $pdfPath;
    }

    private function convertDocxToPdfDirect($docxPath, $originalName)
    {
        try {
            // Configure TCPDF for PhpWord
            Settings::setPdfRendererName(Settings::PDF_RENDERER_TCPDF);
            Settings::setPdfRendererPath(base_path('vendor/tecnickcom/tcpdf'));

            // Load the DOCX file
            $phpWord = WordIOFactory::load($docxPath);
            
            // Create PDF writer
            $pdfWriter = WordIOFactory::createWriter($phpWord, 'PDF');
            
            // Generate PDF path
            $pdfPath = 'pdfs/' . uniqid() . '_' . pathinfo($originalName, PATHINFO_FILENAME) . '.pdf';
            $fullPdfPath = storage_path('app/' . $pdfPath);
            
            // Ensure directory exists
            $directory = dirname($fullPdfPath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // Save PDF
            $pdfWriter->save($fullPdfPath);
            
            // Extract text content for database storage
            $content = $this->docx_to_text($docxPath);
            
            return [
                'content' => $content,
                'pdf_path' => $pdfPath
            ];
        } catch (\Exception $e) {
            logger()->warning('Failed to convert DOCX to PDF directly, falling back to text conversion', [
                'error' => $e->getMessage(),
                'file' => $originalName
            ]);
            
            // Fallback to text extraction and conversion
            $content = $this->docx_to_text($docxPath);
            $pdfPath = $this->convertTextToPdf($content, $originalName);
            
            return [
                'content' => $content,
                'pdf_path' => $pdfPath
            ];
        }
    }

    private function convertXlsxToPdfDirect($xlsxPath, $originalName)
    {
        try {
            // Load the Excel file
            $spreadsheet = SpreadsheetIOFactory::load($xlsxPath);
            
            // Generate PDF path
            $pdfPath = 'pdfs/' . uniqid() . '_' . pathinfo($originalName, PATHINFO_FILENAME) . '.pdf';
            $fullPdfPath = storage_path('app/' . $pdfPath);
            
            // Ensure directory exists
            $directory = dirname($fullPdfPath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // Configure PDF writer
            $writer = SpreadsheetIOFactory::createWriter($spreadsheet, 'Tcpdf');
            $writer->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
            $writer->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
            
            // Save PDF
            $writer->save($fullPdfPath);
            
            // Extract text content for database storage
            $content = $this->xlsx_to_text($xlsxPath);
            
            return [
                'content' => $content,
                'pdf_path' => $pdfPath
            ];
        } catch (\Exception $e) {
            logger()->warning('Failed to convert XLSX to PDF directly, falling back to text conversion', [
                'error' => $e->getMessage(),
                'file' => $originalName
            ]);
            
            // Fallback to text extraction and conversion
            $content = $this->xlsx_to_text($xlsxPath);
            $pdfPath = $this->convertTextToPdf($content, $originalName);
            
            return [
                'content' => $content,
                'pdf_path' => $pdfPath
            ];
        }
    }

    private function convertPptxToPdfDirect($pptxPath, $originalName)
    {
        try {
            // Load the PowerPoint file
            $presentation = PresentationIOFactory::load($pptxPath);
            
            // Generate PDF path
            $pdfPath = 'pdfs/' . uniqid() . '_' . pathinfo($originalName, PATHINFO_FILENAME) . '.pdf';
            $fullPdfPath = storage_path('app/' . $pdfPath);
            
            // Ensure directory exists
            $directory = dirname($fullPdfPath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // Create PDF writer
            $writer = PresentationIOFactory::createWriter($presentation, 'PDF');
            
            // Save PDF
            $writer->save($fullPdfPath);
            
            // Extract text content for database storage
            $content = $this->pptx_to_text($pptxPath);
            
            return [
                'content' => $content,
                'pdf_path' => $pdfPath
            ];
        } catch (\Exception $e) {
            logger()->warning('Failed to convert PPTX to PDF directly, falling back to text conversion', [
                'error' => $e->getMessage(),
                'file' => $originalName
            ]);
            
            // Fallback to text extraction and conversion
            $content = $this->pptx_to_text($pptxPath);
            $pdfPath = $this->convertTextToPdf($content, $originalName);
            
            return [
                'content' => $content,
                'pdf_path' => $pdfPath
            ];
        }
    }

    private function uploadToGeminiFileApi($pdfPath)
    {
        try {
            $apiKey = config('services.gemini.api_key');
            
            // Get file content and metadata
            $fileContent = Storage::get($pdfPath);
            $filename = basename($pdfPath);
            $mimeType = 'application/pdf';
            $numBytes = strlen($fileContent);
            
            $baseUrl = 'https://generativelanguage.googleapis.com';
            
            // Step 1: Initial resumable request to get upload URL
            $initialResponse = Http::withHeaders([
                'x-goog-api-key' => $apiKey,
                'X-Goog-Upload-Protocol' => 'resumable',
                'X-Goog-Upload-Command' => 'start',
                'X-Goog-Upload-Header-Content-Length' => $numBytes,
                'X-Goog-Upload-Header-Content-Type' => $mimeType,
                'Content-Type' => 'application/json'
            ])->post($baseUrl . '/upload/v1beta/files', [
                'file' => [
                    'display_name' => $filename
                ]
            ]);

            if (!$initialResponse->successful()) {
                logger()->error('Failed to initiate Gemini file upload', [
                    'response' => $initialResponse->body(),
                    'status' => $initialResponse->status()
                ]);
                return null;
            }

            // Extract upload URL from response headers
            $uploadUrl = $initialResponse->header('x-goog-upload-url');
            if (!$uploadUrl) {
                logger()->error('No upload URL returned from Gemini API', [
                    'headers' => $initialResponse->headers()
                ]);
                return null;
            }

            // Step 2: Upload the actual file bytes
            $uploadResponse = Http::withHeaders([
                'Content-Length' => $numBytes,
                'X-Goog-Upload-Offset' => '0',
                'X-Goog-Upload-Command' => 'upload, finalize'
            ])->withBody($fileContent, $mimeType)
            ->post($uploadUrl);

            if ($uploadResponse->successful()) {
                $data = $uploadResponse->json();
                $fileUri = $data['file']['uri'] ?? null;
                
                if ($fileUri) {
                    logger()->info('Successfully uploaded file to Gemini API', [
                        'file_uri' => $fileUri,
                        'filename' => $filename
                    ]);
                    return $fileUri;
                } else {
                    logger()->error('No file URI returned from Gemini upload', [
                        'response' => $uploadResponse->body()
                    ]);
                    return null;
                }
            } else {
                logger()->error('Failed to upload file bytes to Gemini API', [
                    'response' => $uploadResponse->body(),
                    'status' => $uploadResponse->status()
                ]);
                return null;
            }

        } catch (\Exception $e) {
            logger()->error('Exception during Gemini file upload', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'pdf_path' => $pdfPath
            ]);
            return null;
        }
    }

    protected function doc_to_text($path_to_file)
    {
        $fileHandle = fopen($path_to_file, 'r');
        $line = @fread($fileHandle, filesize($path_to_file));
        $lines = explode(chr(0x0D), $line);
        $response = '';
        foreach ($lines as $current_line) {
            $pos = strpos($current_line, chr(0x00));
            if (($pos !== FALSE) || (strlen($current_line) == 0)) {
                continue;
            }
            $response .= $current_line . ' ';
        }

        $response = preg_replace('/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/', '', $response);
        return trim($response);
    }

    protected function docx_to_text($path_to_file)
    {
        $response = '';
        $zip = new ZipArchive();

        if ($zip->open($path_to_file) !== true) {
            return '';
        }

        // Read the document.xml file content
        $content = $zip->getFromName('word/document.xml');
        if ($content !== false) {
            $response = $content;
        }

        $zip->close();

        // Clean up the XML content
        $response = str_replace('</w:r></w:p></w:tc><w:tc>', ' ', $response);
        $response = str_replace('</w:r></w:p>', "\r\n", $response);
        $response = strip_tags($response);

        return trim($response);
    }

    protected function xlsx_to_text($file)
    {
        try {
            // Try using PhpSpreadsheet for better text extraction
            $spreadsheet = SpreadsheetIOFactory::load($file);
            $output_text = "";
            
            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $output_text .= "Sheet: " . $worksheet->getTitle() . "\n";
                
                foreach ($worksheet->getRowIterator() as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                    
                    $rowText = "";
                    foreach ($cellIterator as $cell) {
                        $value = $cell->getFormattedValue();
                        if (!empty(trim($value))) {
                            $rowText .= $value . " | ";
                        }
                    }
                    
                    if (!empty(trim($rowText))) {
                        $output_text .= rtrim($rowText, " | ") . "\n";
                    }
                }
                $output_text .= "\n";
            }
            
            return trim($output_text);
        } catch (\Exception $e) {
            // Fallback to original method
            $xml_filename = "xl/sharedStrings.xml";
            $zip_handle = new ZipArchive;
            $output_text = "";
            
            if (true === $zip_handle->open($file)) {
                if (($xml_index = $zip_handle->locateName($xml_filename)) !== false) {
                    $xml_datas = $zip_handle->getFromIndex($xml_index);
                    $dom = new DOMDocument();
                    $dom->loadXML($xml_datas, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
                    $xml_handle = $dom;
                    $output_text = strip_tags($xml_handle->saveXML());
                }
                $zip_handle->close();
            }
            
            return trim($output_text);
        }
    }

    protected function pptx_to_text($input_file)
    {
        try {
            // Try using PhpPresentation for better text extraction
            $presentation = PresentationIOFactory::load($input_file);
            $output_text = "";
            
            foreach ($presentation->getAllSlides() as $slideIndex => $slide) {
                $output_text .= "Slide " . ($slideIndex + 1) . ":\n";
                
                foreach ($slide->getShapeCollection() as $shape) {
                    if ($shape instanceof \PhpOffice\PhpPresentation\Shape\RichText) {
                        foreach ($shape->getParagraphs() as $paragraph) {
                            foreach ($paragraph->getRichTextElements() as $element) {
                                if ($element instanceof \PhpOffice\PhpPresentation\Shape\RichText\TextElement) {
                                    $output_text .= $element->getText() . " ";
                                }
                            }
                            $output_text .= "\n";
                        }
                    }
                }
                $output_text .= "\n";
            }
            
            return trim($output_text);
        } catch (\Exception $e) {
            // Fallback to original method
            $zip_handle = new ZipArchive;
            $output_text = "";
            
            if (true === $zip_handle->open($input_file)) {
                $slide_number = 1;
                while (($xml_index = $zip_handle->locateName("ppt/slides/slide" . $slide_number . ".xml")) !== false) {
                    $xml_datas = $zip_handle->getFromIndex($xml_index);
                    $xml_handle = new DOMDocument();
                    $xml_handle->loadXML($xml_datas, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
                    $output_text .= "Slide " . $slide_number . ":\n";
                    $output_text .= strip_tags($xml_handle->saveXML()) . "\n\n";
                    $slide_number++;
                }
                $zip_handle->close();
            }
            
            return trim($output_text);
        }
    }

    /**
     * Get the most recent file uploaded by the current user
     */
    public function getRecent(Request $request)
    {
        $file = File::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->select('id', 'name')
            ->first();

        if (!$file) {
            return response()->json(['error' => 'No files found'], 404);
        }

        return response()->json($file);
    }
}
