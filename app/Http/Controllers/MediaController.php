<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Schema(
 *     schema="Media",
 *     type="object",
 *     required={"file_path"},
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="file_path", type="string", example="public/images/example.png"),
 *     @OA\Property(property="type", type="string", example="image") 
 * )
 */
class MediaController extends Controller
{
    /**
     * @OA\Post(
     *     path="/media",
     *     summary="Upload a media file",
     *     tags={"Media"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"file"},
     *             @OA\Property(property="file", type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Media file uploaded successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Media")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:2048', 
        ]);

        try {
            $path = $request->file('file')->store('images', 'public');
    
            // ایجاد مدیا و نوع آن به صورت خودکار در مدل
            $media = Media::create(['file_path' => $path]);
    
            return response()->json([
                'id' => $media->id,
                'file_path' => $media->file_path,
                'type' => $media->type,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }
}
    