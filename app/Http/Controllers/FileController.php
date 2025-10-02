<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Tag;
use App\Services\FileRecommendationService;
use App\Services\QuestService;
use Inertia\Inertia;
use ZipArchive;
use DOMDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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
                $search = $request->search;
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
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

        $files = $query->paginate(10)->withQueryString();

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
            $content = $this->extractText($uploadedFile);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['file' => 'Invalid file type or unable to process the file.']);
        }

        $file = File::create([
            'name' => $fileName,
            'description' => $request->input('description'),
            'path' => $path,
            'content' => $content,
            'file_hash' => $fileHash,
            'user_id' => auth()->id(),
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

            return redirect()->route('files.show', $file)
                ->with([
                    'success' => 'File uploaded successfully! You gained 20 XP!',
                    'content' => $content,
                    'file_id' => $file->id,
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


        return Inertia::render('Files/Show', [
            'file' => $file,
            'fileInfo' => [
                'extension' => $extension,
                'exists' => $fileExists,
                'url' => $fileExists ? route('files.serve', $file->id) : null,
                'size' => $fileExists ? Storage::size($file->path) : null,
                'lastModified' => $fileExists ? Storage::lastModified($file->path) : null,
            ],
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
            });

        $files = $query->withCount(['flashcards', 'quizzes'])
            ->orderBy('created_at', 'desc')
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

        $file->load('tags');
        $allTags = \App\Models\Tag::orderBy('name')->get();

        return Inertia::render('Files/Edit', [
            'file' => $file,
            'allTags' => $allTags,
        ]);
    }

    public function update(Request $request, File $file)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'tags' => 'nullable|array',
            'tags.*.id' => 'nullable|exists:tags,id',
            'tags.*.name' => 'nullable|string|max:50',
        ]);

        // Authorization is handled by middleware
        // $this->authorize('update', $file);

        $file->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        // Handle tags
        if ($request->has('tags')) {
            $tagIds = [];

            // Process existing tags
            foreach ($request->input('tags') as $tag) {
                if (isset($tag['id'])) {
                    $tagIds[] = $tag['id'];
                } else if (isset($tag['name']) && !empty($tag['name'])) {
                    // Create new tag if it doesn't exist
                    $newTag = \App\Models\Tag::firstOrCreate(['name' => $tag['name']]);
                    $tagIds[] = $newTag->id;
                }
            }

            $file->tags()->sync($tagIds);
        } else {
            $file->tags()->detach();
        }

        return redirect()->route('files.show', $file->id)
            ->with('success', 'File updated successfully');
    }

    public function destroy(File $file)
    {
        // Check if the user is authorized to delete the file
        $this->authorize('delete', $file);

        // Delete the file from storage
        if (Storage::exists($file->path)) {
            Storage::delete($file->path);
        }

        // Delete the file record from the database
        $file->delete();

        return redirect()->route('files.index')->with('success', 'File deleted successfully.');
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
            'Cache-Control' => 'private, max-age=3600',
        ]);
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

        try {
            $results = [];

            if ($request->boolean('generate_flashcards')) {
                $flashcardResult = $this->generateFlashcardsContent($file, $request->input('flashcards_count', 5));
                if (!$flashcardResult['success']) {
                    return back()->withErrors(['error' => $flashcardResult['error']]);
                }
                $results[] = $flashcardResult['message'];
            }

            if ($request->boolean('generate_multiple_choice_quizzes')) {
                $quizResult = $this->generateMultipleChoiceContent($file, $request->input('multiple_choice_count', 5));
                if (!$quizResult['success']) {
                    return back()->withErrors(['error' => $quizResult['error']]);
                }
                $results[] = $quizResult['message'];
            }

            if ($request->boolean('generate_enumeration_quizzes')) {
                $enumResult = $this->generateEnumerationContent($file, $request->input('enumeration_count', 3));
                if (!$enumResult['success']) {
                    return back()->withErrors(['error' => $enumResult['error']]);
                }
                $results[] = $enumResult['message'];
            }

            if ($request->boolean('generate_true_false_quizzes')) {
                $tfResult = $this->generateTrueFalseContent($file, $request->input('true_false_count', 3));
                if (!$tfResult['success']) {
                    return back()->withErrors(['error' => $tfResult['error']]);
                }
                $results[] = $tfResult['message'];
            }

            if (empty($results)) {
                return back()->withErrors(['error' => 'No content types selected for generation.']);
            }

            return back()->with('success', 'Content generated successfully: ' . implode(', ', $results));

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'An error occurred during generation: ' . $e->getMessage()]);
        }
    }

    private function generateContent(Request $request, File $file, string $type, array $schema)
    {
        if (!$file->verified) {
            return redirect()->back()->withErrors(['error' => 'File must be verified by a faculty or admin before generating content.']);
        }

        set_time_limit(0);
        $content = $file->content;

        if (empty($content)) {
            return response()->json([
                'error' => 'File content is empty or not available.'
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

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => "Generate {$type} using the following content:\n\nContent:\n" . $content
                        ]
                    ]
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

    private function generateFlashcardsContent(File $file, int $count)
    {
        try {
            set_time_limit(0);
            $content = $file->content;

            if (empty($content)) {
                return ['success' => false, 'error' => 'File content is empty or not available.'];
            }

            $apiKey = config('services.gemini.api_key');
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey;

            $generationConfig = [
                "response_mime_type" => "application/json",
                "response_schema" => [
                    "type" => "OBJECT",
                    "properties" => [
                        "flashcards" => [
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
                            "minItems" => $count,
                            "maxItems" => $count,
                            "nullable" => false,
                        ],
                    ],
                    "nullable" => false,
                    "required" => ["flashcards"],
                ],
            ];

            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => "Generate flashcards using the following content:\n\nContent:\n" . $content
                            ]
                        ]
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
                    $flashcards = $parsedData['flashcards'] ?? [];

                    foreach ($flashcards as $flashcard) {
                        \App\Models\Flashcard::create([
                            'question' => $flashcard['question'],
                            'answer' => $flashcard['answer'],
                            'file_id' => $file->id,
                        ]);
                    }

                    return ['success' => true, 'message' => count($flashcards) . ' flashcards generated'];
                } else {
                    return ['success' => false, 'error' => 'Failed to parse JSON from Gemini response.'];
                }
            }

            return ['success' => false, 'error' => 'Failed to call Gemini API.'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Error generating flashcards: ' . $e->getMessage()];
        }
    }

    private function generateMultipleChoiceContent(File $file, int $count)
    {
        try {
            set_time_limit(0);
            $content = $file->content;

            if (empty($content)) {
                return ['success' => false, 'error' => 'File content is empty or not available.'];
            }

            $apiKey = config('services.gemini.api_key');
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey;

            $generationConfig = [
                "response_mime_type" => "application/json",
                "response_schema" => [
                    "type" => "OBJECT",
                    "properties" => [
                        "multiple_choice_quizzes" => [
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
                            "minItems" => $count,
                            "maxItems" => $count,
                            "nullable" => false,
                        ],
                    ],
                    "nullable" => false,
                    "required" => ["multiple_choice_quizzes"],
                ],
            ];

            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => "Generate multiple choice quizzes using the following content:\n\nContent:\n" . $content
                            ]
                        ]
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
                    $quizzes = $parsedData['multiple_choice_quizzes'] ?? [];

                    foreach ($quizzes as $quiz) {
                        // Ensure the answer is included in the options
                        if (!in_array($quiz['answer'], $quiz['options'])) {
                            $quiz['options'][] = $quiz['answer'];
                        }

                        \App\Models\Quiz::create([
                            'question' => $quiz['question'],
                            'type' => 'multiple_choice',
                            'options' => $quiz['options'],
                            'answers' => [$quiz['answer']],
                            'file_id' => $file->id,
                        ]);
                    }

                    return ['success' => true, 'message' => count($quizzes) . ' multiple choice quizzes generated'];
                } else {
                    return ['success' => false, 'error' => 'Failed to parse JSON from Gemini response.'];
                }
            }

            return ['success' => false, 'error' => 'Failed to call Gemini API.'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Error generating multiple choice quizzes: ' . $e->getMessage()];
        }
    }

    private function generateEnumerationContent(File $file, int $count)
    {
        try {
            set_time_limit(0);
            $content = $file->content;

            if (empty($content)) {
                return ['success' => false, 'error' => 'File content is empty or not available.'];
            }

            $apiKey = config('services.gemini.api_key');
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey;

            $generationConfig = [
                "response_mime_type" => "application/json",
                "response_schema" => [
                    "type" => "OBJECT",
                    "properties" => [
                        "enumeration_quizzes" => [
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
                            "minItems" => $count,
                            "maxItems" => $count,
                            "nullable" => false,
                        ],
                    ],
                    "nullable" => false,
                    "required" => ["enumeration_quizzes"],
                ],
            ];

            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => "Generate enumeration quizzes using the following content:\n\nContent:\n" . $content
                            ]
                        ]
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
                    $quizzes = $parsedData['enumeration_quizzes'] ?? [];

                    foreach ($quizzes as $quiz) {
                        \App\Models\Quiz::create([
                            'question' => $quiz['question'],
                            'type' => 'enumeration',
                            'options' => null,
                            'answers' => $quiz['answers'],
                            'file_id' => $file->id,
                        ]);
                    }

                    return ['success' => true, 'message' => count($quizzes) . ' enumeration quizzes generated'];
                } else {
                    return ['success' => false, 'error' => 'Failed to parse JSON from Gemini response.'];
                }
            }

            return ['success' => false, 'error' => 'Failed to call Gemini API.'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Error generating enumeration quizzes: ' . $e->getMessage()];
        }
    }

    private function generateTrueFalseContent(File $file, int $count)
    {
        try {
            set_time_limit(0);
            $content = $file->content;

            if (empty($content)) {
                return ['success' => false, 'error' => 'File content is empty or not available.'];
            }

            $apiKey = config('services.gemini.api_key');
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey;

            $generationConfig = [
                "response_mime_type" => "application/json",
                "response_schema" => [
                    "type" => "OBJECT",
                    "properties" => [
                        "true_false_quizzes" => [
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
                            "minItems" => $count,
                            "maxItems" => $count,
                            "nullable" => false,
                        ],
                    ],
                    "nullable" => false,
                    "required" => ["true_false_quizzes"],
                ],
            ];

            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => "Generate true/false quizzes using the following content:\n\nContent:\n" . $content
                            ]
                        ]
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
                    $quizzes = $parsedData['true_false_quizzes'] ?? [];

                    foreach ($quizzes as $quiz) {
                        \App\Models\Quiz::create([
                            'question' => $quiz['question'],
                            'type' => 'true_false',
                            'options' => null,
                            'answers' => [$quiz['answer']],
                            'file_id' => $file->id,
                        ]);
                    }

                    return ['success' => true, 'message' => count($quizzes) . ' true/false quizzes generated'];
                } else {
                    return ['success' => false, 'error' => 'Failed to parse JSON from Gemini response.'];
                }
            }

            return ['success' => false, 'error' => 'Failed to call Gemini API.'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Error generating true/false quizzes: ' . $e->getMessage()];
        }
    }

    private function extractText($file)
    {
        $extension = $file->getClientOriginalExtension();
        $content = '';

        switch ($extension) {
            case 'txt':
                $content = file_get_contents($file->getRealPath());
                break;

            case 'pdf':
                // Use a library like `smalot/pdfparser` for PDF text extraction
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($file->getRealPath());
                $content = $pdf->getText();
                break;

            case 'docx':
                $content = $this->docx_to_text($file->getRealPath());
                break;

            case 'doc':
                $content = $this->doc_to_text($file->getRealPath());
                break;

            case 'xlsx':
                $content = $this->xlsx_to_text($file->getRealPath());
                break;

            case 'pptx':
                $content = $this->pptx_to_text($file->getRealPath());
                break;

            // Add cases for other file types as needed
            default:
                throw new \Exception('Unsupported file type');
        }

        // Ensure the content is UTF-8 encoded
        if (!mb_detect_encoding($content, 'UTF-8', true)) {
            $content = mb_convert_encoding($content, 'UTF-8');
        }

        return $content;
    }

    protected static function doc_to_text($path_to_file)
    {
        $fileHandle = fopen($path_to_file, 'r');
        $line = @fread($fileHandle, filesize($path_to_file));
        $lines = explode(chr(0x0D), $line);
        $response = '       ';
        foreach ($lines as $current_line) {
            $pos = strpos($current_line, chr(0x00));
            if (($pos !== FALSE) || (strlen($current_line) == 0)) {
                continue;
            }
            $response .= $current_line . '                   ';
        }

        $response = preg_replace('/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/', '', $response);
        return $response;
    }

    protected static function docx_to_text($path_to_file)
    {
        $response = '';
        $zip = new ZipArchive();

        if ($zip->open($path_to_file) !== true) {
            return false;
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

        return $response;
    }

    function xlsx_to_text($file)
    {
        $xml_filename = "xl/sharedStrings.xml"; //content file name
        $zip_handle = new ZipArchive;
        $output_text = "";
        if (true === $zip_handle->open($file)) {
            if (($xml_index = $zip_handle->locateName($xml_filename)) !== false) {

                $xml_datas = $zip_handle->getFromIndex($xml_index);
                $dom = new DOMDocument();
                $dom->loadXML($xml_datas, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
                $xml_handle = $dom;
                $output_text = strip_tags($xml_handle->saveXML());
            } else {
                $output_text .= "";
            }
            $zip_handle->close();
        } else {
            $output_text .= "";
        }
        return $output_text;
    }

    function pptx_to_text($input_file)
    {
        $zip_handle = new ZipArchive;
        $output_text = "";
        if (true === $zip_handle->open($input_file)) {
            $slide_number = 1; //loop through slide files
            while (($xml_index = $zip_handle->locateName("ppt/slides/slide" . $slide_number . ".xml")) !== false) {
                $xml_datas = $zip_handle->getFromIndex($xml_index);
                $xml_handle = new DOMDocument();
                $xml_handle->loadXML($xml_datas, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
                $output_text .= strip_tags($xml_handle->saveXML());
                $slide_number++;
            }
            if ($slide_number == 1) {
                $output_text .= "";
            }
            $zip_handle->close();
        } else {
            $output_text .= "";
        }
        return $output_text;
    }
}
