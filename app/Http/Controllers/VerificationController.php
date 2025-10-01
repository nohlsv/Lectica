<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class VerificationController extends Controller
{
    /**
     * Show the document upload page
     */
    public function showUpload()
    {
        $user = Auth::user();
        
        // Redirect if user doesn't need to upload document
        if (!$user->needsDocumentUpload() && !$user->isVerificationRejected()) {
            return redirect()->route('home');
        }

        return Inertia::render('Verification/Upload', [
            'user' => $user,
            'documentRequired' => $user->user_role === 'student' ? 'COR (Certificate of Registration)' : 'Valid ID'
        ]);
    }

    /**
     * Handle document upload
     */
    public function uploadDocument(Request $request)
    {
        $user = Auth::user();

        try {
            // Debug: Log the request details
            \Log::info('Upload attempt', [
                'user_id' => $user->id,
                'has_file' => $request->hasFile('document'),
                'files' => $request->allFiles(),
                'max_file_size' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size')
            ]);

            // Check if file was actually uploaded first
            if (!$request->hasFile('document')) {
                // Check if there's a file in the files array but hasFile returns false
                // This usually indicates the file exceeded upload limits
                if (!empty($request->allFiles())) {
                    return redirect()->back()->withErrors(['document' => 'File upload failed. The file may be too large. Server limit is ' . ini_get('upload_max_filesize') . '. Please choose a smaller file.']);
                }
                return redirect()->back()->withErrors(['document' => 'No file was selected. Please choose a document to upload.']);
            }

            $file = $request->file('document');
            
            // Check if file upload was successful
            if (!$file->isValid()) {
                $error = $file->getError();
                $errorMessage = match($error) {
                    UPLOAD_ERR_INI_SIZE => 'File is too large (exceeds server limit)',
                    UPLOAD_ERR_FORM_SIZE => 'File is too large (exceeds form limit)', 
                    UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Server error: No temporary directory',
                    UPLOAD_ERR_CANT_WRITE => 'Server error: Cannot write to disk',
                    UPLOAD_ERR_EXTENSION => 'Server error: File upload stopped by extension',
                    default => 'Unknown upload error (code: ' . $error . ')'
                };
                return redirect()->back()->withErrors(['document' => $errorMessage]);
            }

            // Validate the file
            $request->validate([
                'document' => [
                    'required',
                    'file',
                    'mimes:jpg,jpeg,png,pdf',
                    'max:5120' // 5MB max
                ]
            ], [
                'document.required' => 'Please select a document to upload.',
                'document.file' => 'The uploaded file is not valid.',
                'document.mimes' => 'Only JPG, PNG, and PDF files are allowed.',
                'document.max' => 'The file size must not exceed 5MB.',
            ]);

            // Delete old document if exists
            if ($user->verification_document_path) {
                try {
                    Storage::disk('public')->delete($user->verification_document_path);
                } catch (\Exception $e) {
                    \Log::warning('Failed to delete old document: ' . $e->getMessage());
                }
            }

            // Store new document with a unique filename
            $filename = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
            
            // Check if directory exists and is writable
            $storagePath = storage_path('app/public/verification-documents');
            if (!is_dir($storagePath)) {
                mkdir($storagePath, 0755, true);
            }
            
            if (!is_writable($storagePath)) {
                return redirect()->back()->withErrors(['document' => 'Server error: Storage directory is not writable.']);
            }

            $path = $file->storeAs('verification-documents', $filename, 'public');

            if (!$path) {
                return redirect()->back()->withErrors(['document' => 'Failed to save document to server. Please try again.']);
            }

            // Verify the file was actually saved
            if (!Storage::disk('public')->exists($path)) {
                return redirect()->back()->withErrors(['document' => 'File upload verification failed. Please try again.']);
            }

            // Update user record
            $user->update([
                'verification_document_path' => $path,
                'verification_status' => 'pending',
                'document_uploaded_at' => now(),
                'verification_notes' => null, // Clear previous notes
                'verified_at' => null,
                'verified_by' => null
            ]);

            \Log::info('Document uploaded successfully', [
                'user_id' => $user->id,
                'path' => $path,
                'filename' => $filename
            ]);

            return redirect()->route('verification.status')->with('success', 'Document uploaded successfully! Your document is now pending admin verification.');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        } catch (\Exception $e) {
            \Log::error('Document upload failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withErrors(['document' => 'Upload failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Show verification status page
     */
    public function showStatus()
    {
        $user = Auth::user();
        
        return Inertia::render('Verification/Status', [
            'user' => $user->load('verifiedBy')
        ]);
    }
}
