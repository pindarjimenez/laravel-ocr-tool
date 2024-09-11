<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function processFile(Request $request)
    {
        // Check if the daily limit has been reached
        if ($this->hasReachedDailyLimit($request->ip())) {
            return response()->json([
                'error' => 'You have reached your daily limit of 5 file processes.'
            ], 429);
        }

        // Validate the incoming file
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:jpg,png,jpeg',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 400);
        }

        // Prepare the file for Guzzle
        $file = $request->file('file');

        $client = new Client();
        $apiKey = env('OCR_API_KEY');

        try {
            $response = $client->request('POST', 'https://api.ocr.space/parse/image', [
                'multipart' => [
                    [
                        'name'     => 'apikey',
                        'contents' => $apiKey,
                    ],
                    [
                        'name'     => 'file',
                        'contents' => fopen($file->getPathname(), 'r'),
                        'filename' => $file->getClientOriginalName(),
                    ],
                    [
                        'name'     => 'filetype',
                        'contents' => 'auto',
                    ],
                    [
                        'name'     => 'language',
                        'contents' => 'eng',
                    ],
                ],
            ]);

            $body = $response->getBody();
            $data = json_decode($body, true);
            
            // Increment the file process count
            $this->incrementFileProcessCount($request->ip());

            // Process and format the result as needed
            return response()->json([
                'text' => $data['ParsedResults'][0]['ParsedText'] ?? 'No text found',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to process file: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Check if the daily file processing limit has been reached for the given IP address.
     *
     * @param string $ipAddress
     * @return bool
     */
    protected function hasReachedDailyLimit($ipAddress)
    {
        // Generate cache key based on the IP address and current date
        $cacheKey = 'ip_' . $ipAddress . '_file_processes_' . now()->toDateString();

        // Get the count of files processed today
        $processedCount = Cache::get($cacheKey, 0);

        return $processedCount >= 5;
    }

    /**
     * Increment the file processing count for the given IP address.
     *
     * @param string $ipAddress
     * @return void
     */
    protected function incrementFileProcessCount($ipAddress)
    {
        // Generate cache key based on the IP address and current date
        $cacheKey = 'ip_' . $ipAddress . '_file_processes_' . now()->toDateString();

        // Get the current count of files processed today
        $processedCount = Cache::get($cacheKey, 0);

        // Increment the count and store it back in the cache
        Cache::put($cacheKey, $processedCount + 1, now()->endOfDay());
    }
}
