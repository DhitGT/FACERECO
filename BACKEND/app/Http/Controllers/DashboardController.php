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

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            // Add date and entry_hour automatically
            $validatedData['date'] = Carbon::now()->toDateString(); // Current date
            $validatedData['entry_hour'] = Carbon::now()->setTimezone('Asia/Jakarta')->toTimeString();
            // Current time

            Absen::create($validatedData);

            // Return success response as JSON
            return response()->json([
                'success' => true,
                'message' => 'Attendance record added successfully!',
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging
            // \Log::error('Error adding attendance record: ' . $e->getMessage());

            // Return error response as JSON
            return response()->json([
                'success' => false,
                'message' => 'Failed to add attendance record. Please try again.',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
