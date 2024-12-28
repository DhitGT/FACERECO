<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

use App\Http\Controllers\ClassController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\DashboardController;

Route::middleware(['auth:admin'])->group(function () {
    // Route::resource('classes', ClassController::class);

    Route::resource('classes', ClassController::class)->except(['create', 'edit', 'show']);
    Route::delete('/classes/{class}', [ClassController::class, 'destroy'])->name('classes.destroy');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('classes/{class}/images', [ImageController::class, 'store'])->name('images.store');
    // Route::delete('images/{image}', [ImageController::class, 'destroy'])->name('images.destroy');
    Route::delete('/images/{class}/{image}', [ImageController::class, 'destroy'])->name('images.destroy');
});

Route::post('/storeAbsen', [DashboardController::class, 'store'])->name('dashboard.store');

Route::get('/images/{class}/{index}.jpg', function ($class, $index) {
    $path = "public/images/{$class}/{$index}.jpg";

    if (!Storage::exists($path)) {
        abort(404, 'Image not found.');
    }

    return response()->file(Storage::path($path));
});

// Admin login and registration routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login']);
    Route::get('register', [AdminAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('register', [AdminAuthController::class, 'register']);
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

});

Route::get('/', function () {
    return redirect()->route('classes.index'); // Redirect to /classes
})->name('classes');

Route::get('/api/classes', [ClassController::class, 'getAllClassNames']);


