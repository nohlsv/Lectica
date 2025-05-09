<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Tag;
use App\Services\FileRecommendationService;
use Inertia\Inertia;
use ZipArchive;
use DOMDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class FileController extends Controller
{
    public function index(Request $request)
    {
        $files = File::with('user')
            ->latest()
            ->paginate(10);

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

        if ($request->has('tags')) {
            $tagIds = collect($request->input('tags'))->pluck('id')->all();
            $file->tags()->sync($tagIds);
        }

        return redirect()->route('files.show', $file->id)->with('success', 'File uploaded successfully!');
        //        return redirect()->back()->with([
//            'success' => 'File uploaded successfully!',
//            'content' => $content,
//            'file_id' => $file->id,
//        ]);
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
                'url' => $fileExists ? Storage::url($file->path) : null,
                'size' => $fileExists ? Storage::size($file->path) : null,
                'lastModified' => $fileExists ? Storage::lastModified($file->path) : null,
            ],
        ]);
    }

    public function indexPersonal(Request $request)
    {
        $query = File::with(['tags', 'user'])
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

    public function destroy($id)
    {
        // Logic to delete a specific file
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

    public function upload(Request $request)
    {
        // Logic to upload a file
    }

    public function search(Request $request)
    {
        // Logic to search for files
    }

    public function tag($id, Request $request)
    {
        // Logic to tag a file
    }

    public function untag($id, Request $request)
    {
        // Logic to untag a file
    }

    public function generateFlashcardsAndQuizzes(Request $request, File $file)
    {
        set_time_limit(0);
        $content = $file->content;

        if (empty($content)) {
            return response()->json([
                'error' => 'File content is empty or not available.'
            ], 400);
        }

        $apiKey = env('GEMINI_API_KEY'); // Ensure your API key is in the .env file
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey;

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' =>
                                "Generate flashcards and quizzes in using the following content:

                            Content:
                            " . $content
                        ]
                    ]
                ]
            ],
            "generationConfig" => [
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
                                "nullable" => False,
                                "required" => ["question", "answer"],
                                "propertyOrdering" => ["question", "answer"]
                            ],
                            "minItems" => 3,
                            "maxItems" => 30,
                            "nullable" => False
                        ],
                        "quizzes" => [
                            "type" => "ARRAY",
                            "items" => [
                                "type" => "OBJECT",
                                "properties" => [
                                    "question" => ["type" => "STRING"],
                                    "options" => [
                                        "type" => "ARRAY",
                                        "items" => ["type" => "STRING"]
                                    ],
                                    "answer" => ["type" => "STRING"]
                                ],
                                "nullable" => False,
                                "required" => ["question", "options", "correct_option"],
                                "propertyOrdering" => ["question", "options", "correct_option"]
                            ],
                            "minItems" => 3,
                            "maxItems" => 30,
                            "nullable" => False
                        ]
                    ],
                    "nullable" => False,
                    "required" => ["flashcards", "quizzes"],
                    "propertyOrdering" => ["flashcards", "quizzes"],
                ]
            ]
        ];

        $response = Http::post($url, $payload);

        if ($response->successful()) {
            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            logger()->info('Gemini response: ' . $text);

            $parsedData = json_decode($text, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $flashcards = collect($parsedData['flashcards'] ?? [])->map(function ($flashcard) use ($file) {
                    return \App\Models\Flashcard::create([
                        'question' => $flashcard['question'],
                        'answer' => $flashcard['answer'],
                        'file_id' => $file->id,
                    ]);
                });

                $quizzes = collect($parsedData['quizzes'] ?? [])->map(function ($quiz) use ($file) {
                    return \App\Models\Quiz::create([
                        'question' => $quiz['question'],
                        'type' => 'multiple_choice',
                        'options' => json_encode($quiz['options']),
                        'answer' => $quiz['answer'],
                        'file_id' => $file->id,
                    ]);
                });

                return response()->json([
                    'flashcards' => $flashcards,
                    'quizzes' => $quizzes,
                ]);
            } else {
                return response()->json([
                    'error' => 'Failed to parse JSON from Gemini response.',
                    'raw_response' => $text
                ], 500);
            }
        }

        return response()->json([
            'error' => 'Failed to call Gemini API.',
            'details' => $response->body()
        ], $response->status());
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
