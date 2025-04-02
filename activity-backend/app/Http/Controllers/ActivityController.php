<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ActivityController extends Controller
{
    /**
     * Handle CSV file upload and store activity data in the database.
     */
    public function uploadActivityData(Request $request)
    {
        // Validate the uploaded file (must be a CSV or TXT file, max size 2MB)
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:csv,txt|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Ensure 'uploads' directory exists
        if (!Storage::exists('uploads')) {
            Storage::makeDirectory('uploads');
        }

        // Store the uploaded file in 'storage/app/uploads/activity_data.csv'
        $file = $request->file('file');
        $file_path = $file->storeAs('uploads', 'activity_data.csv', 'local');

        // Get the full storage path
        $full_path = Storage::path($file_path);

        // Parse the CSV file into an array of activity data
        $data = $this->parseCSV($full_path);

        // Save each activity record in the database with user_id
        foreach ($data as $entry) {
            if (!isset($entry['user_id'])) {
                continue; // Skip invalid records
            }

            // Check if the record already exists in the database
            $existingActivity = Activity::where('user_id', $entry['user_id'])
                ->where('date', $entry['date'])
                ->first();

            // If it exists, skip to avoid duplicates
            if ($existingActivity) {
                continue;
            }

            // Create a new record, ensuring timestamps are handled automatically by Eloquent
            Activity::create([
                'user_id' => $entry['user_id'],
                'date' => $entry['date'],
                'steps' => $entry['steps'],
                'distance_km' => $entry['distance_km'],
                'active_minutes' => $entry['active_minutes'],
            ]);
        }

        // Return response after all data is processed
        return response()->json(['message' => 'CSV file uploaded and data saved successfully!'], 200);
    }

    /**
     * Parses a CSV file and converts it into an array of activity data.
     */
    private function parseCSV($full_path)
    {
        // Check if the file exists
        if (!file_exists($full_path)) {
            return [];
        }

        // Open the CSV file for reading
        $file = fopen($full_path, 'r');
        $header = fgetcsv($file); // Read the header row

        $batch = [];
        $chunk_size = 1000; // Process 1000 rows at a time
        $parsed_data = []; // Changed from $totalInserted to $parsed_data

        while (($row = fgetcsv($file)) !== false) {
            if (count($row) < 5) continue; // Skip invalid rows

            $batch[] = [
                'user_id' => (int) $row[0],
                'date' => $row[1],
                'steps' => (int) $row[2],
                'distance_km' => (float) $row[3],
                'active_minutes' => (int) $row[4]
            ];

            // Insert when the batch reaches the chunk size
            if (count($batch) >= $chunk_size) {
                foreach ($batch as $entry) {
                    // Insert data and ensure timestamps are handled by create()
                    Activity::create([
                        'user_id' => $entry['user_id'],
                        'date' => $entry['date'],
                        'steps' => $entry['steps'],
                        'distance_km' => $entry['distance_km'],
                        'active_minutes' => $entry['active_minutes'],
                    ]);
                }

                // Collect inserted data and reset the batch
                $parsed_data = array_merge($parsed_data, $batch);
                $batch = [];
            }
        }

        // Insert any remaining records
        if (!empty($batch)) {
            foreach ($batch as $entry) {
                // Insert data and ensure timestamps are handled by create()
                Activity::create([
                    'user_id' => $entry['user_id'],
                    'date' => $entry['date'],
                    'steps' => $entry['steps'],
                    'distance_km' => $entry['distance_km'],
                    'active_minutes' => $entry['active_minutes'],
                ]);
            }
            $parsed_data = array_merge($parsed_data, $batch);
        }

        fclose($file);

        return $parsed_data; // Return the parsed data array
    }
}
