<?php

namespace App\Http\Controllers;

use App\Models\story;
use App\Models\bookmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;    
use Exception;

class BookmarkController extends Controller
{
    /**
     * Menambahkan bookmark untuk cerita tertentu oleh pengguna yang sedang login.
     */
    public function addBookmark(Request $request, $storyId)
    {
        try {
            // Pastikan pengguna sudah login
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'User not authenticated'], 401);
            }

            // Cek apakah cerita ada
            $story = story::find($storyId);
            if (!$story) {
                return response()->json(['message' => 'Story not found'], 404);
            }

            // Cek apakah pengguna sudah bookmark cerita ini
            $existingBookmark = bookmark::where('user_id', $user->id)->where('story_id', $storyId)->first();
            if ($existingBookmark) {
                return response()->json(['message' => 'Story already bookmarked'], 400);
            }

            // Tambahkan bookmark
            $bookmark = bookmark::create([
                'user_id' => $user->id,
                'story_id' => $storyId,
            ]);

            return response()->json([
                'message' => 'Bookmark added successfully',
                'bookmark' => $bookmark,
            ], 201);
        } catch (Exception $e) {
            Log::error('Error adding bookmark: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to add bookmark'], 500);
        }
    }

    /**
     * Menghapus bookmark untuk cerita tertentu oleh pengguna yang sedang login.
     */
    public function deleteBookmark(Request $request, $storyId)
    {
        try {
            // Pastikan pengguna sudah login
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'User not authenticated'], 401);
            }

            // Cek apakah cerita ada
            $story = Story::find($storyId);
            if (!$story) {
                return response()->json(['message' => 'Story not found'], 404);
            }

            // Cek apakah pengguna memiliki bookmark untuk cerita ini
            $bookmark = Bookmark::where('user_id', $user->id)->where('story_id', $storyId)->first();
            if (!$bookmark) {
                return response()->json(['message' => 'Bookmark not found'], 404);
            }

            // Hapus bookmark
            $bookmark->delete();

            return response()->json([
                'message' => 'Bookmark deleted successfully',
            ], 200);
        } catch (Exception $e) {
            Log::error('Error deleting bookmark: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to delete bookmark'], 500);
        }
    }
}
