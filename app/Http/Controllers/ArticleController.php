<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *     schema="Article",
 *     type="object",
 *     required={"title", "content", "category_id"},
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="title", type="string", example="Article Title"),
 *     @OA\Property(property="summary", type="string", example="A brief summary of the article"),
 *     @OA\Property(property="content", type="string", example="Full article content goes here"),
 *     @OA\Property(property="category_id", type="integer", format="int64", example=1),
 *     @OA\Property(property="media_id", type="integer", format="int64", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class ArticleController extends Controller
{


    /**
     * @OA\Get(
     *     path="/articles",
     *     summary="Retrieve all articles",
     *     tags={"Articles"},
     *     @OA\Response(
     *         response=200,
     *         description="A list of articles",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Article"))
     *     )
     * )
     */
    public function index()
    {
        // دریافت همه مقالات
        return Article::with('category', 'media')->get();
    }

    /**
     * @OA\Post(
     *     path="/articles",
     *     summary="Create a new article",
     *     tags={"Articles"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "content", "category_id"},
     *             @OA\Property(property="title", type="string", example="Article Title"),
     *             @OA\Property(property="summary", type="string", example="A brief summary of the article"),
     *             @OA\Property(property="content", type="string", example="Full article content goes here"),
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="media_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="The article was created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Article")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function store(Request $request)
    {
        // ذخیره مقاله جدید
        $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string|max:500', 
            'content' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'media_id' => 'nullable|exists:media,id',
        ]);

        $article = Article::create($request->all());
        return response()->json($article, 201);
    }

    /**
     * @OA\Get(
     *     path="/articles/{id}",
     *     summary="Retrieve a specific article",
     *     tags={"Articles"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A single article",
     *         @OA\JsonContent(ref="#/components/schemas/Article")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article not found"
     *     )
     * )
     */
    public function show($id)
    {
        // نمایش مقاله خاص
        return Article::with('category', 'media')->findOrFail($id);
    }

    /**
     * @OA\Put(
     *     path="/articles/{id}",
     *     summary="Update a specific article",
     *     tags={"Articles"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="New Article Title"),
     *             @OA\Property(property="summary", type="string", example="Updated summary of the article"),
     *             @OA\Property(property="content", type="string", example="Updated content of the article"),
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="media_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The article was updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Article")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        // به‌روزرسانی مقاله
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'summary' => 'sometimes|nullable|string|max:500',
            'content' => 'sometimes|required|string',
            'category_id' => 'sometimes|required|exists:categories,id',
            'media_id' => 'sometimes|nullable|exists:media,id',
        ]);

        $article = Article::findOrFail($id);
        $article->update($request->all());
        return response()->json($article, 200);
    }

    /**
     * @OA\Delete(
     *     path="/articles/{id}",
     *     summary="Delete a specific article",
     *     tags={"Articles"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="The article was deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        // حذف مقاله
        Article::destroy($id);
        return response()->json(null, 204);
    }
}