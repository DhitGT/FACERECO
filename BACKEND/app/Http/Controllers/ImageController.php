<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function store(Request $request, ClassModel $class)
    {
        $request->validate([
            'images.*' => 'required|image|mimes:jpg', // Validate each file
        ]);

        $images = $request->file('images'); // Get the array of images

        foreach ($images as $image) {
            // Count existing images to determine the next index
            $imageCount = $class->images()->count() + 1;

            // Save the uploaded file with the index as its name
            $fileName = "{$imageCount}.jpg";
            $path = $image->storeAs(
                "public/images/{$class->name}",
                $fileName
            );

            // Save the image data to the database
            $class->images()->create([
                'file_path' => str_replace('public/', '', $path), // Store relative path
            ]);
        }

        return redirect()->back()->with('success', 'Images uploaded successfully!');
    }

    public function destroy(ClassModel $class, Image $image)
    {
        // Step 1: Delete the specified image from storage
        Storage::delete("public/{$image->file_path}");

        // Step 2: Remove the image record from the database
        $image->delete();

        // Step 3: Get all remaining images for the class, sorted by their current file_path
        $images = $class->images()->orderBy('file_path')->get();

        // Step 4: Rename files to maintain sequential numbering
        foreach ($images as $index => $img) {
            $newFileName = ($index + 1) . '.jpg'; // New sequential filename
            $oldPath = "public/{$img->file_path}";
            $newPath = "public/images/{$class->name}/{$newFileName}";

            // Rename the file in storage
            if (!Storage::exists($newPath)) { // Avoid overwriting
                Storage::move($oldPath, $newPath);
            }

            // Update the file path in the database
            $img->update(['file_path' => str_replace('public/', '', $newPath)]);
        }

        return redirect()->back()->with('success', 'Image deleted and numbering updated successfully!');
    }
}
