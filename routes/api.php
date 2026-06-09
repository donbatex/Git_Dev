<?php
use App\Http\Controllers\Api\V1\PostController as V1PostController;
use App\Http\Controllers\Api\V1\PromptGenerationController;
use App\Http\Controllers\Api\V2\PostController as V2PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::prefix('v1')->group(function () {
        Route::apiResource('posts', V1PostController::class);
        Route::apiResource('prompt-generations', PromptGenerationController::class)->only(['index', 'store']);
    });
    

    Route::prefix('v2')->group(function () {
        Route::apiResource('posts', V2PostController::class);
    });
});

// Route::get('/hello', function () {
//     return response()->json(['message' => 'Hello Laravel API']);
// }); 


require __DIR__.'/auth.php';

// Route::apiResource('posts', PostController::class);

// Route::get('/posts', [PostController::class, 'index'])->name('posts.index');

// Route::post('/posts', [PostController::class, 'store'])->name('posts.store');

// Route::get('/posts/{id}', [PostController::class, 'show'])->name('posts.show');

// Route::get('/hello', function () {
//     return response()->json(['message' => 'Hello Laravel API']);
// }); 