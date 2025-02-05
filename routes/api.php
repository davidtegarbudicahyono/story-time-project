<?php  
  
use App\Models\Story;  
use Illuminate\Http\Request;  
use Illuminate\Support\Facades\Route;  
use App\Http\Controllers\UserController;  
use App\Http\Controllers\StoryController;  
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\CategoryController;  
  
/*  
|--------------------------------------------------------------------------  
| API Routes  
|--------------------------------------------------------------------------  
|  
| Here is where you can register API routes for your application. These  
| routes are loaded by the RouteServiceProvider and all of them will  
| be assigned to the "api" middleware group. Make something great!  
|  
*/  
  

Route::post('login', [UserController::class, 'login']); 
Route::post('register', [UserController::class, 'register']);  
  
Route::middleware('auth:sanctum')->group(function () {  
    // Rute yang memerlukan autentikasi  
    
    Route::get('user/{id}', [UserController::class, 'getUserById']);   
    Route::post('edit-profile-image', [UserController::class, 'editProfileImage']);  
    Route::put('edit-profile', [UserController::class, 'editProfile']);  
    Route::post('logout', [UserController::class, 'logout']);
      
    // category  
    Route::apiResource('categories', CategoryController::class);  
  
    // bookmark  
    Route::apiResource('bookmarks', BookmarkController::class);
    // story  
    Route::apiResource('stories', StoryController::class)->except(['index','show']); 
    Route::get('stories/my-stories', [StoryController::class, 'myStories']);
});  

// story tanpa harus login  
Route::get('stories', [StoryController::class, 'index']);  
Route::get('stories/{id}', [StoryController::class, 'show']);
Route::get('latest-stories', [StoryController::class, 'getLatestStories']);
Route::get('newest-stories', [StoryController::class, 'getNewestStories']);
Route::get('stories-by-category/{categoryId}', [StoryController::class, 'storiesByCategory']);
Route::get('stories/PopularStories', [StoryController::class, 'getPopularStories']);
Route::get('stories/similar-stories/{storyId}', [StoryController::class, 'getSimilarStories']);
Route::get('stories/stories-a-z', [StoryController::class, 'getStoriesAZ']);
Route::get('stories/stories-z-a', [StoryController::class, 'getStoriesZA']);


