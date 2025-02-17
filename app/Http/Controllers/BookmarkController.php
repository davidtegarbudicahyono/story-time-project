<?php

namespace App\Http\Controllers;

use App\Models\Story;
use App\Models\Bookmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class BookmarkController extends Controller
{
    /**
     * Menampilkan semua bookmark pengguna yang sedang login.
     */
    public function index()
    {
        try {
            $user = auth()->user();
    
            if (!$user) {
                return response()->json(['message' => 'User not authenticated'], 401);
            }
    
            $stories = Bookmark::where('user_id', $user->id)
                ->with('story.images', 'story.categories', 'story.users')
                ->paginate(4);
    
            if ($stories->isEmpty()) {
                return response()->json(['message' => 'No stories found'], 404);
            }
    
            return response()->json([
                'status' => true,
                'data' => $stories,
            ], 200);
        } catch (Exception $e) {
            Log::error('Error retrieving bookmarks: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to retrieve stories'], 500);
        }
    }
    

    /**
     * Menyimpan bookmark baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'story_id' => 'required|exists:stories,id',
        ]);

        try {
            $bookmark = Bookmark::create([
                'user_id' => Auth::id(),
                'story_id' => $request->story_id,
            ]);

            return response()->json([
                'message' => 'Bookmark berhasil ditambahkan.',
                'data' => $bookmark,
            ], 201);
        } catch (Exception $e) {
            Log::error('Error adding bookmark: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menambahkan bookmark'], 500);
        }
    }

    /**
     * Menghapus bookmark tertentu oleh pengguna yang sedang login.
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'User not authenticated'], 401);
            }

            $bookmark = Bookmark::where('id', $id)->where('user_id', $user->id)->first();

            if (!$bookmark) {
                return response()->json(['message' => 'Bookmark not found'], 404);
            }

            $bookmark->delete();

            return response()->json([
                'message' => 'Bookmark berhasil dihapus.',
            ], 200);
        } catch (Exception $e) {
            Log::error('Error deleting bookmark: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus bookmark'], 500);
        }
    }
}
