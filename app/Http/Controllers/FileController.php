<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\File;
use Inertia\Inertia;
use ZipArchive;
use DOMDocument;

class FileController extends Controller
{
    public function index()
    {
        $files = File::paginate(10);
        return Inertia::render('Files/Index', [
            'files' => $files,
        ]);
    }

    public function create()
    {
        return Inertia::render('Files/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:txt,xlsx,pdf,pptx,doc,docx|max:2048',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        $uploadedFile = $request->file('file');
        $path = $uploadedFile->store('uploads', 'public');

        try {
            // Extract text content based on file type
            $content = $this->extractText($uploadedFile);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['file' => 'Invalid file type or unable to process the file.']);
        }

        $file = File::create([
            'name' => $uploadedFile->getClientOriginalName(),
            'path' => $path,
            'content' => $content,
        ]);

        if ($request->has('tags')) {
            $file->tags()->sync($request->input('tags'));
        }

        return redirect()->back()->with([
            'success' => 'File uploaded successfully!',
            'content' => $content,
            'file_id' => $file->id,
        ]);
    }
    public function show($id)
    {
        $file = File::findOrFail($id);

        // Get file extension
        $extension = pathinfo($file->name, PATHINFO_EXTENSION);

        // Check if file exists
        $filePath = storage_path('app/public/' . $file->path);
        $fileExists = file_exists($filePath);

        return Inertia::render('Files/Show', [
            'file' => $file,
            'fileInfo' => [
                'extension' => $extension,
                'exists' => $fileExists,
                'size' => $fileExists ? $this->formatFileSize(filesize($filePath)) : null,
                'lastModified' => $fileExists ? date('Y-m-d H:i:s', filemtime($filePath)) : null,
            ],
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
    public function edit($id)
    {
        $file = File::findOrFail($id);
        return Inertia::render('Files/Edit', [
            'file' => $file,
        ]);
    }

    public function update(Request $request, $id)
    {
        // Logic to update a specific file
    }

    public function destroy($id)
    {
        // Logic to delete a specific file
    }

    public function download($id)
    {
        // Logic to download a specific file
    }

    public function upload(Request $request)
    {
        // Logic to upload a file
    }

    public function search(Request $request)
    {
        // Logic to search for files
    }

    public function share($id)
    {
        // Logic to share a file
    }

    public function unshare($id)
    {
        // Logic to unshare a file
    }

    public function favorite($id)
    {
        // Logic to mark a file as favorite
    }

    public function unfavorite($id)
    {
        // Logic to unmark a file as favorite
    }

    public function tag($id, Request $request)
    {
        // Logic to tag a file
    }

    public function untag($id, Request $request)
    {
        // Logic to untag a file
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
