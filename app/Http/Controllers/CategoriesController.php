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
            'id' => 'required|integer|exists:categories,id',
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
            'id' => 'required|integer|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 404);
        }

        $posts = Post::where('id', '=', $category_id)->get();

        return response()->json($posts);
    }

    public function addCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|between:10,255|unique:categories',
        ]);

        $me = auth()->user();

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $category_data = array_merge(
            $validator->validated(),
            ['slug' => Str::slug($validator->validated()['title'], "-")],);

        $category = Category::create(
            $category_data
        );

        return response()->json(['message' => 'Category created successfully', 'category' => $category]);
    }

    public function updateCategoryData($category_id, Request $request)
    {
        $validator = Validator::make(array_merge($request->all(), ["id" => $category_id]), [
            'id' => 'required|integer|exists:categories,id',
            'title' => 'string|between:10,255,unique:categories,title',
            'description' => 'string|between:10,255|unique:categories,description',
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

        if (array_key_exists('title', $newCategoryData)) {
            $newCategoryData['slug'] = Str::slug($newCategoryData['title'], "-");
        }

        $category->fill($newCategoryData);

        $category->save();

        return response()->json(['message' => 'Category data updated successfully', 'category' => $category]);
    }

    public function DeleteCategoryData($category_id, Request $request)
    {
        $validator = Validator::make(["id" => $category_id], [
            'id' => 'required|integer|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 404);
        }
        $me = auth()->user();

        if ($me->hasRole('admin')) {
            DB::table('categories')->where([
                ['id', '=', $category_id],
            ])->delete();

            return response()->json(['message' => 'Category deleted successfully']);
        } else {
            return response()->json(['message' => 'You must make a category before deleting it ;)']);
        }
    }
}
