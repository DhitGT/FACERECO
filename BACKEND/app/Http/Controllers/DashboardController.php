<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Absen;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //
    public function index(Request $request)
    {
        $dateFilter = $request->query('date', now()->toDateString());
        $absens = Absen::where('date', $dateFilter)->get();

        return view('dashboard.index', compact('absens', 'dateFilter'));
    }

    public function getAttendanceByCurrentTime()
    {
        // Get the current date and time
        $now = Carbon::now();
        $currentDate = $now->toDateString(); // Format YYYY-MM-DD
        $currentTime = $now->toTimeString(); // Format HH:MM:SS

        // Query the database
        $attendanceRecords = DB::table('absens')
            ->select('name')
            ->whereDate('date', $currentDate) // Match the current date
            // ->whereTime('entry_hour', '<=', $currentTime) // Entry hours before or equal to now
            ->get();

        // Return the response
        return response()->json(

            $attendanceRecords,
        );
    }
    public function getExitAttendanceByCurrentTime()
    {
        // Get the current date and time
        $now = Carbon::now();
        $currentDate = $now->toDateString(); // Format YYYY-MM-DD
        $currentTime = $now->toTimeString(); // Format HH:MM:SS

        // Query the database
        $attendanceRecords = DB::table('absens')
            ->select('name')
            ->whereNotNull('exit_hour') // Match the current date
            ->whereDate('date', $currentDate)  // Entry hours before or equal to now
            ->get();

        // Return the response
        return response()->json(

            $attendanceRecords,
        );
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'flag' => 'required|in:masuk,keluar', // Validate the flag to ensure it is either 'masuk' or 'keluar'
            ]);

            $currentDate = Carbon::now()->toDateString(); // Current date
            $currentTime = Carbon::now()->setTimezone('Asia/Jakarta')->toTimeString(); // Current time

            if ($validatedData['flag'] === 'masuk') {
                // Handle 'masuk'
                $validatedData['date'] = $currentDate;
                $validatedData['entry_hour'] = $currentTime;

                Absen::create($validatedData); // Create a new record

                return response()->json([
                    'success' => true,
                    'message' => 'Attendance record for "Masuk" added successfully!',
                ]);
            } elseif ($validatedData['flag'] === 'keluar') {
                // Handle 'keluar'
                $absen = Absen::where('name', $validatedData['name'])
                    ->where('date', $currentDate)
                    ->first();

                if ($absen) {
                    $absen->update(['exit_hour' => $currentTime]); // Update exit_hour
                    return response()->json([
                        'success' => true,
                        'message' => 'Attendance record for "Keluar" updated successfully!',
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'No "Masuk" record found for today to update "Keluar".',
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Log the error for debugging
            // \Log::error('Error handling attendance record: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to handle attendance record. Please try again.',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
