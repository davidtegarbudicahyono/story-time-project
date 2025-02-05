<?php    
    
namespace App\Http\Controllers;    
    
use App\Models\Story;    
use App\Models\StoryImage;    
use Illuminate\Http\Request;    
use Illuminate\Support\Facades\Storage;    
use Illuminate\Support\Facades\Log;    
use Exception;    
    
class StoryController extends Controller    
{    
    /**    
     * Menampilkan daftar semua cerita.    
     */    
    public function index()    
    {    
        try {  
            $stories = Story::with(['users', 'categories', 'images'])->get();    
    
            return response()->json([    
                'message' => 'Cerita berhasil diambil',    
                'stories' => $stories,    
            ], 200);    
        } catch (Exception $e) {  
            Log::error('Error fetching stories: ' . $e->getMessage());  
            return response()->json(['message' => 'Failed to retrieve stories'], 500);  
        }  
    }    
    
    /**          
     * Menyimpan cerita baru ke dalam database.          
     */          
    public function store(Request $request)              
    {              
        try {    
            $validatedData = $request->validate([              
                'category_id' => 'required|exists:categories,id',              
                'title' => 'required|string|max:255',              
                'content' => 'required|string',              
                'content_images.*' => 'required|image|mimes:jpg,jpeg,png|max:2048',              
            ]);              
                        
            // Cek jumlah gambar yang diunggah  
            if ($request->file('content_images') && count($request->file('content_images')) > 5) {  
                return response()->json([  
                    'message' => 'Maksimal 5 gambar dapat diunggah.'  
                ], 400);  
            }  
                            
            $validatedData['user_id'] = auth()->id();              
                            
            $story = Story::create($validatedData);              
                            
            // Menyimpan gambar ke tabel story_images              
            $images = []; // Array untuk menyimpan informasi gambar        
            if ($request->hasFile('content_images')) {              
                foreach ($request->file('content_images') as $image) {              
                    // Simpan dengan nama file asli      
                    $originalName = $image->getClientOriginalName(); // Ambil nama file asli      
                    $path = $image->storeAs('content_images', $originalName, 'public'); // Simpan dengan nama asli      
                        
                    $storyImage = StoryImage::create([              
                        'story_id' => $story->id,              
                        'image_path' => $path,              
                    ]);        
                    $images[] = $storyImage; // Tambahkan gambar ke array        
                }              
            }              
                            
            return response()->json([              
                'message' => 'Cerita berhasil dibuat',              
                'story' => $story,              
                'images' => $images, // Sertakan gambar dalam respons        
            ], 201);              
        } catch (Exception $e) {    
            Log::error('Error creating story: ' . $e->getMessage());    
            return response()->json(['message' => 'Failed to create story'], 500);    
        }    
    } 
    
    /**      
     * Memperbarui cerita berdasarkan ID.      
     */      
    public function update(Request $request, $id)      
    {      
        try {    
            // Validasi data yang diterima  
            $validatedData = $request->validate([              
                'category_id' => 'sometimes|exists:categories,id',              
                'title' => 'sometimes|string|max:255',              
                'content' => 'sometimes|string',              
                'content_images.*' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
            ]);              
            
            // Cek apakah cerita ada  
            $story = Story::find($id);      
            if (!$story) {      
                return response()->json([      
                    'message' => 'Cerita tidak ditemukan',      
                ], 404);      
            }      
    
            // Update data cerita  
            $story->update($validatedData);      
    
            // Menyimpan gambar baru jika ada  
            if ($request->hasFile('content_images')) {  
                // Cek jumlah gambar yang diunggah  
                if (count($request->file('content_images')) > 5) {  
                    return response()->json([  
                        'message' => 'Maksimal 5 gambar dapat diunggah.'  
                    ], 400);  
                }  
    
                // Hapus gambar lama jika ada  
                $oldImages = StoryImage::where('story_id', $story->id)->get();  
                foreach ($oldImages as $oldImage) {  
                    Storage::disk('public')->delete($oldImage->image_path);  
                    $oldImage->delete();  
                }  
    
                // Simpan gambar baru  
                $images = []; // Array untuk menyimpan informasi gambar        
                foreach ($request->file('content_images') as $image) {              
                    // Simpan dengan nama file asli      
                    $originalName = $image->getClientOriginalName();      
                    $path = $image->storeAs('content_images', $originalName, 'public');     
                        
                    $storyImage = StoryImage::create([              
                        'story_id' => $story->id,              
                        'image_path' => $path,              
                    ]);        
                    $images[] = $storyImage; // Tambahkan gambar ke array        
                }              
            }  
    
            return response()->json([              
                'message' => 'Cerita berhasil diperbarui',              
                'story' => $story,              
                'images' => isset($images) ? $images : [], // Sertakan gambar baru dalam respons        
            ], 200);              
        } catch (Exception $e) {    
            Log::error('Error updating story: ' . $e->getMessage());    
            return response()->json(['message' => 'Failed to update story'], 500);    
        }    
    }  

    
    /**    
     * Menampilkan cerita berdasarkan ID.    
     */    
    public function show($id)      
    {      
        try {  
            $story = Story::with(['users', 'categories', 'images'])->find($id);      
          
            if (!$story) {      
                return response()->json([      
                    'message' => 'Cerita tidak ditemukan brooo1111',      
                ], 404);      
            }      
          
            return response()->json([      
                'message' => 'Cerita berhasil diambil',      
                'story' => $story,      
            ], 200);      
        } catch (Exception $e) {  
            Log::error('Error fetching story: ' . $e->getMessage());  
            return response()->json(['message' => 'Failed to retrieve story'], 500);  
        }  
    }    
      
    /**    
     * Menghapus cerita berdasarkan ID.    
     */    
    public function destroy($id)    
    {    
        try {  
            $story = Story::find($id);    
    
            if (!$story) {    
                return response()->json([    
                    'message' => 'Cerita tidak ditemukan',    
                ], 404);    
            }    
    
            if ($story->user_id !== auth()->id()) {    
                return response()->json([    
                    'message' => 'Tidak memiliki izin',    
                ], 403);    
            }    
    
            // Menghapus semua gambar terkait    
            $images = StoryImage::where('story_id', $story->id)->get();    
            foreach ($images as $image) {    
                Storage::disk('public')->delete($image->image_path);    
                $image->delete();    
            }    
    
            $story->delete();    
    
            return response()->json([    
                'message' => 'Cerita berhasil dihapus',    
            ], 200);    
        } catch (Exception $e) {  
            Log::error('Error deleting story: ' . $e->getMessage());  
            return response()->json(['message' => 'Failed to delete story'], 500);  
        }  
    } 
    
    /**      
     * Menampilkan cerita terbaru untuk indeks.      
     */      
    public function getLatestStories()      
    {      
        try {    
            $stories = Story::with(['images', 'categories', 'users'])    
                ->orderBy('created_at', 'desc')    
                ->paginate(6);    
    
            if ($stories->isEmpty()) {    
                return response()->json(['message' => 'No stories found'], 404);    
            }    
    
            return response()->json([    
                'status' => true,    
                'data' => $stories,    
            ], 200);    
        } catch (Exception $e) {    
            Log::error('Error fetching newest story index: ' . $e->getMessage());    
            return response()->json(['message' => 'Failed to retrieve stories'], 500);    
        }    
    }
  
    /**    
     * Menampilkan cerita terbaru.    
     */    
    public function getNewestStories()    
    {    
        try {  
            $stories = Story::with(['images', 'categories', 'users'])  
                ->orderBy('created_at', 'desc')  
                ->paginate(12);  
      
            if ($stories->isEmpty()) {  
                return response()->json([  
                    'message' => 'No stories found'  
                ], 404);  
            }  
      
            return response()->json([  
                'status' => true,  
                'data' => $stories,  
            ], 200);  
        } catch (Exception $e) {  
            Log::error('Error fetching newest stories: ' . $e->getMessage());  
            return response()->json(['message' => 'Failed to retrieve stories'], 500);  
        }  
    }  
  
    /**    
     * Menampilkan cerita berdasarkan kategori.    
     */    
    public function storiesByCategory($categoryId)    
    {    
        try {  
            $stories = Story::with(['images', 'categories', 'users'])  
                ->where('category_id', $categoryId)  
                ->orderBy('created_at', 'desc')  
                ->paginate(12);  
      
            if ($stories->isEmpty()) {  
                return response()->json([  
                    'message' => 'No stories found in this category'  
                ], 404);  
            }  
      
            return response()->json([  
                'status' => true,  
                'data' => $stories,  
            ], 200);  
        } catch (Exception $e) {  
            Log::error('Error fetching stories by category: ' . $e->getMessage());  
            return response()->json(['message' => 'Failed to retrieve stories'], 500);  
        }  
    }  
  
    /**    
     * Menampilkan cerita milik pengguna yang sedang login.    
     */    
    public function myStories()    
    {    
        try {  
            $user = auth()->user();  
      
            if (!$user) {  
                return response()->json(['message' => 'User not authenticated'], 401);  
            }  
      
            $stories = Story::with(['images', 'categories', 'users'])  
                ->where('user_id', $user->id)  
                ->paginate(4);  
      
            if ($stories->isEmpty()) {  
                return response()->json(['message' => 'No stories found'], 404);  
            }  
      
            return response()->json([  
                'status' => true,  
                'data' => $stories,  
            ], 200);  
        } catch (Exception $e) {  
            Log::error('Error fetching my stories: ' . $e->getMessage());  
            return response()->json(['message' => 'Failed to retrieve stories'], 500);  
        }  
    }  
  
    /**
     * Menampilkan cerita populer berdasarkan jumlah bookmark.
     */
    public function getPopularStories()
    {
        try {
            $stories = Story::with(['users', 'categories', 'images'])
                ->orderBy('bookmarks_count', 'desc')
                ->paginate(12);

            if ($stories->isEmpty()) {
                return response()->json(['message' => 'No popular stories available'], 404);
            }

            return response()->json([
                'message' => 'Popular stories retrieved successfully',
                'stories' => $stories,
            ], 200);
        } catch (Exception $e) {
            Log::error('Error fetching popular stories: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to retrieve popular stories'], 500);
        }
    }

    /**
     * Menampilkan cerita serupa berdasarkan kategori.
     */
    public function getSimilarStories($storyId)
    {
        try {
            $story = Story::find($storyId);

            if (!$story) {
                return response()->json(['message' => 'Story not found'], 404);
            }

            $similarStories = Story::with(['users', 'categories', 'images'])
                ->where('category_id', $story->category_id)
                ->where('id', '!=', $storyId)
                ->orderBy('created_at', 'desc')
                ->paginate(3);

            if ($similarStories->isEmpty()) {
                return response()->json(['message' => 'No similar stories available'], 404);
            }

            return response()->json([
                'message' => 'Similar stories retrieved successfully',
                'stories' => $similarStories,
            ], 200);
        } catch (Exception $e) {
            Log::error('Error fetching similar stories: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to retrieve similar stories'], 500);
        }
    }

    /**
     * Menampilkan cerita diurutkan dari A-Z berdasarkan judul.
     */
    public function getStoriesAZ(Request $request)
    {
        try {
            $stories = Story::with(['users', 'categories', 'images'])
                ->orderBy('title', 'asc')
                ->paginate(12);

            if ($stories->isEmpty()) {
                return response()->json(['message' => 'No stories found'], 404);
            }

            return response()->json([
                'message' => 'Stories retrieved successfully',
                'stories' => $stories,
            ], 200);
        } catch (Exception $e) {
            Log::error('Error fetching stories A-Z: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to retrieve stories'], 500);
        }
    }

    /**
     * Menampilkan cerita diurutkan dari Z-A berdasarkan judul.
     */
    public function getStoriesZA(Request $request)
    {
        try {
            $stories = Story::with(['users', 'categories', 'images'])
                ->orderBy('title', 'desc')
                ->paginate(12);

            if ($stories->isEmpty()) {
                return response()->json(['message' => 'No stories found'], 404);
            }

            return response()->json([
                'message' => 'Stories retrieved successfully',
                'stories' => $stories,
            ], 200);
        } catch (Exception $e) {
            Log::error('Error fetching stories Z-A: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to retrieve stories'], 500);
        }
    }
  
}  
