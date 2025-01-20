<?php  
  
use App\Models\Story;  
use Illuminate\Http\Request;  
use Illuminate\Support\Facades\Route;  
use App\Http\Controllers\UserController;  
use App\Http\Controllers\StoryController;  
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
  

Route::post('/login', [UserController::class, 'login']); 
Route::post('/register', [UserController::class, 'register']);  
  
Route::middleware('auth:sanctum')->group(function () {  
    // Rute yang memerlukan autentikasi  
    
    Route::get('/user/{id}', [UserController::class, 'getUserById']);   
    Route::post('/edit-profile-image', [UserController::class, 'editProfileImage']);  
    Route::put('/edit-profile', [UserController::class, 'editProfile']);  
    Route::post('/logout', [UserController::class, 'logout']);
      
    // category  
    Route::apiResource('/categories', CategoryController::class);  
  
    // story  
    Route::get('/stories/newest', [StoryController::class, 'getNewestStory']);
    Route::get('/stories/category/{id}', [StoryController::class, 'storiesByCategory']);
    Route::get('/stories/my-stories', [StoryController::class, 'myStories']);
    Route::get('/stories/newest-index', [StoryController::class, 'newestStoryIndex']);
    Route::resource('/stories', StoryController::class); 
});  
