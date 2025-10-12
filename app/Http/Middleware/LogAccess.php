<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AccessLog;
use Illuminate\Support\Facades\Auth;

class LogAccess
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Only log important routes to avoid cluttering
        $importantRoutes = [
            'files', 'quiz', 'flashcard', 'statistics', 'myfiles'
        ];

        $path = $request->path();
        $shouldLog = false;
        
        // Check if the path contains any of our important keywords
        foreach ($importantRoutes as $keyword) {
            if (str_contains($path, $keyword)) {
                $shouldLog = true;
                break;
            }
        }

        // Skip assets, API calls, and non-essential requests
        if ($shouldLog && 
            !str_contains($path, 'assets') && 
            !str_contains($path, '.css') && 
            !str_contains($path, '.js') && 
            !str_contains($path, '.png') && 
            !str_contains($path, '.jpg') &&
            !str_contains($path, 'api/') &&
            $request->method() !== 'HEAD' &&
            $request->method() !== 'OPTIONS') {
            
            try {
                AccessLog::create([
                    'user_id' => Auth::id(),
                    'route' => $request->path(),
                    'method' => $request->method(),
                    'accessed_at' => now(),
                ]);
            } catch (\Exception $e) {
                // Fail silently to not break the application
            }
        }
        
        return $response;
    }

    /**
     * Determine the action type based on the request
     */
    private function determineActionType(Request $request): string
    {
        $path = $request->path();
        $method = $request->method();

        if (str_contains($path, 'files')) {
            if ($method === 'POST' && !str_contains($path, 'check-duplicate')) return 'File Upload';
            if ($method === 'PUT') return 'File Update';
            if ($method === 'DELETE') return 'File Delete';
            if (str_contains($path, 'create')) return 'File Create Page';
            if (str_contains($path, 'myfiles')) return 'My Files View';
            if (preg_match('/files\/\d+$/', $path)) return 'File View';
            return 'Files Browse';
        }

        if (str_contains($path, 'quiz')) {
            if ($method === 'POST') return 'Quiz Created'; 
            return 'Quiz Access';
        }

        if (str_contains($path, 'flashcard')) {
            if ($method === 'POST') return 'Flashcard Created';
            return 'Flashcard Access';
        }

        if (str_contains($path, 'statistics')) {
            return 'Statistics View';
        }

        if (str_contains($path, 'login')) {
            return 'User Login';
        }

        if (str_contains($path, 'register')) {
            return 'User Registration';
        }

        return ucfirst(strtolower($method)) . ' Request';
    }

    /**
     * Truncate user agent to avoid very long strings
     */
    private function truncateUserAgent(?string $userAgent): ?string
    {
        if (!$userAgent) return null;
        return strlen($userAgent) > 100 ? substr($userAgent, 0, 100) . '...' : $userAgent;
    }
}

