<?php

namespace App\Http\Controllers;

use App\Models\Category as Category;
use App\Models\Post as Post;
use App\Models\User as User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

// all
// categoryByID
// getAllPostsByCategory
// addCategory

// updateCategoryData
// deleteCategoryData

class CategoriesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['all', 'categoryByID', 'getAllPostsByCategory']]);
    }

    protected function guard()
    {
        return Auth::guard('api');
    }

    public function all(Request $request)
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    public function categoryByID($category_id, Request $request)
    {
        $validator = Validator::make(["id" => $category_id], [
            'id' => 'required|integer|exists:posts,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 404);
        }

        $category = Category::where('id', '=', $category_id)->get()->first();

        return response()->json($category);
    }

    public function getAllPostsByCategory($category_id, Request $request)
    {
        $validator = Validator::make(["id" => $category_id], [
            'id' => 'required|integer|exists:posts,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 404);
        }

        $posts = Post::where('id', '=', $category_id)->get();

        return response()->json($comments);
    }

    public function addCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:10,255',
        ]);

        $me = auth()->user();

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $category_data = $validator->validated();

        $category = Category::create(array_merge(
            $validator->validated(),
            ['slug' => Str::slug($category_data['name'], "-")],

        ));

        return response()->json(['message' => 'Comment created successfully', 'comment' => $comment]);
    }

    public function updateCategoryData($category_id, Request $request)
    {
        $validator = Validator::make(array_merge($request->all(), ["id" => $category_id]), [
            'id' => 'required|integer|exists:posts,id',
            'name' => 'required|string|between:10,255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $me = auth()->user();

        if (!$me->hasRole('admin')) {
            return response()->json(['Error' => 'Permission denied'], 403);
        }

        $category = Category::where('id', '=', $category_id)->get()->first();

        $newCategoryData = $validator->validated();

        $category->fill($newCategoryData);

        $category->save();

        return response()->json(['category' => 'Category data updated successfully', 'category' => $category]);
    }

    public function DeleteCategoryData($category_id, Request $request)
    {
        $validator = Validator::make(["id" => $category_id], [
            'id' => 'required|integer|exists:posts,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 404);
        }
        $me = auth()->user();

        if ($me->hasRole('admin')) {
            DB::table('categories')->where([
                ['id', '=', $category_id],
            ])->delete();

            return response()->json(['message' => 'Post deleted successfully']);
        } else {
            return response()->json(['message' => 'You must make a post before deleting it ;)']);
        }
    }
}
