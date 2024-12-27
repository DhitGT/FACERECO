<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClassController extends Controller
{
    public function index()
    {
        $classes = ClassModel::with('images')->get();
        return view('classes.index', compact('classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:classes,name',
        ]);

        ClassModel::create(['name' => $request->name]);

        return redirect()->back()->with('success', 'Class created successfully.');
    }
    public function getAllClassNames()
    {
        // Fetch all class names with their associated class_id
        $classes = ClassModel::all(); // Fetch all classes

        // Prepare an array to hold the class names and their image counts
        $classData = [];

        foreach ($classes as $class) {
            // Count the number of images associated with the current class_id
            $imageCount = \DB::table('images')
                ->where('class_id', $class->id) // Count images where class_id matches
                ->count();

            // Add the class name, class_id, and the image count to the result array
            $classData[] = [
                'class_name' => $class->name,
                'class_id' => $class->id,
                'image_count' => $imageCount,
            ];
        }

        // Return the result as JSON
        return response()->json($classData);
    }


    public function destroy(ClassModel $class)
    {
        // Step 1: Get all images related to the class
        $images = $class->images;

        // Step 2: Delete all images from the storage
        foreach ($images as $image) {
            Storage::delete("public/{$image->file_path}");
        }

        // Step 3: Remove all images from the database
        $class->images()->delete();

        // Step 4: Delete the class from the database
        $class->delete();

        return redirect()->route('classes.index')->with('success', 'Class and associated images deleted successfully!');
    }
}

