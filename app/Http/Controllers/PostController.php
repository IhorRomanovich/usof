<?php

namespace App\Http\Controllers;

use App\Models\Comment as Comment;
use App\Models\Like as Like;
use App\Models\Post as Post;
use App\Models\PostCategory as PostCategory;
use App\Models\User as User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\Filters\Filter as Filter;
use Spatie\QueryBuilder\AllowedFilter as AllowedFilter;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\QueryBuilder as QBuilder;


class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['all', 'postByID', 'commentsByPostID', 'categoryByPostID']]);
    }

    protected function guard()
    {
        return Auth::guard('api');
    }

    public function all(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'filter.categories' => 'required',
            'filter.statuses' => 'required',
            'filter.date.from' => 'required|string',
            'filter.date.to' => 'required|string',
            'sorting' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 404);
        }

        $validatedData = $validator->validated();
        $filter = $validator->validated()['filter'];

        if (array_key_exists('sorting', $validatedData)) {
            $sorting = $validator->validated()['sorting'];
        }

        $posts = DB::table('posts');

        $filteredAndSortedPosts = $this->sortedPosts($this->filterPosts($posts, $filter), $sorting)->get();

        return response()->json($filteredAndSortedPosts);
    }

    private function filterPosts($posts, $filter)
    {
        if (!isset($filter)) {
            return $posts;
        }

        if (isset($filter['statuses'])) {
            $posts = $posts->whereIn('status', $filter['statuses']);
        }

        if (isset($filter['categories'])) {
            $posts = $posts->whereIn('id', function($query) use ($filter)
            {
                $query->select("t_id")
                      ->from('post_categories')
                      ->whereIn('post_categories.c_id', $filter['categories']);
            });
        }

        if (isset($filter['date']) && isset($filter['date']['from']) && isset($filter['date']['to'])) {
            $from =$filter['date']['from'];
            $to = $filter['date']['to'];
            $posts = $posts->whereBetween('created_at', [$from, $to]);
        }

        return $posts;

    }

    private function sortedPosts($posts, $sorting)
    {
        if (!$sorting) {
            return $posts->select('*')->selectSub(function ($q) {
                $q->from('likes')
                    ->whereRaw('likes.p_id = posts.id')
                    ->where('likes.islike', 1)
                    ->selectRaw('count(islike)');
            }, 'likes_count')
                ->orderBy('likes_count', 'desc');
        }

        return $posts->orderBy('updated_at', 'desc');
    }

    public function postByID($post_id, Request $request)
    {
        $validator = Validator::make(["post_id" => $post_id], [
            'post_id' => 'required|integer|exists:posts,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 404);
        }

        $post = Post::where('id', '=', $post_id)->get()->first();

        return response()->json($post);
    }

    public function commentsByPostID($post_id, Request $request)
    {
        $validator = Validator::make(["post_id" => $post_id], [
            'post_id' => 'required|integer|exists:posts,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 404);
        }

        $comments = Comment::where('post_id', '=', $post_id)->get();

        return response()->json($comments);
    }

    public function AddComment($post_id, Request $request)
    {
        $validator = Validator::make(array_merge($request->all(), ["post_id" => $post_id]), [
            'post_id' => 'required|integer|exists:posts,id',
            //'author_id' => 'required|integer|exists:users,id',
            'content' => 'required|string|between:1,255',
        ]);
        $me = auth()->user();

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $comment = Comment::create(array_merge($validator->validated(), ['author_id' => $me['id']]));

        return response()->json(['message' => 'Comment created successfully', 'comment' => $comment]);
    }

    public function categoryByPostID($post_id, Request $request)
    {
        $validator = Validator::make(["post_id" => $post_id], [
            'post_id' => 'required|integer|exists:posts,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 404);
        }

        // $comments = Category::where('p_id', '=', $post_id)->get();

        $comments = DB::table('categories')
            ->join('post_categories', 'categories.id', '=', 'post_categories.t_id')
            ->select('categories.name')
            ->where('post_categories.t_id', '=', $post_id)
            ->get();

        return response()->json($comments);
    }

    public function likesByPostID($post_id, Request $request)
    {
        $validator = Validator::make(["post_id" => $post_id], [
            'post_id' => 'required|integer|exists:posts,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 404);
        }

        // $comments = Category::where('p_id', '=', $post_id)->get();

        $likes = Like::whereColumn([
            ['islike', '=', 1],
            ['p_id', '=', $post_id]])
            ->count();

        $dislikes = Like::whereColumn([
            ['islike', '=', 0],
            ['p_id', '=', $post_id]])
            ->count();

        return response()->json($likes - $dislikes);

    }

    public function AddPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|between:10,255',
            'category_id.*' => 'required|integer|exists:categories,id',
            'body' => 'required|string|between:200,1000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $post_data = $validator->validated();
        $me = auth()->user();

        $post = Post::create(array_merge(
            ['body' => $post_data['body']],
            ['title' => $post_data['title']],
            ['slug' => Str::slug($post_data['title'], "-")],
            ['author_id' => $me['id']],
        ));

        $last_post_id = DB::table('posts')->latest('created_at')->first()->id;
        foreach ($validator->validated()['category_id'] as $val) {
            $post_categories = PostCategory::create(array_merge(
                ['c_id' => $val],
                ['t_id' => $last_post_id]
            ));
        }

        return response()->json(['message' => 'Post created successfully', 'post' => $post]);
    }

    public function AddLikeToPost($post_id, Request $request)
    {
        $validator = Validator::make(["p_id" => $post_id], [
            'p_id' => 'required|integer|exists:posts,id',
        ]);
        $me = auth()->user();

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $like_data = $validator->validated();

        //If like exist
        $my_like = DB::table('likes')
            ->select('id')
            ->where([
                ['author_id', '=', $me['id']],
                ['p_id', '=', $post_id],
            ])
            ->count();

        $like = 0;
        if ($my_like == 0) {
            $like = Like::create(array_merge(
                $validator->validated(),
                ['c_id' => 0,
                    'p_id' => $post_id,
                    'author_id' => $me['id'],
                    'islike' => true]
            ));
            return response()->json(['message' => 'Like created successfully', 'like' => $like]);
        } else {
            DB::table('likes')
                ->where([
                    ['author_id', '=', $me['id']],
                    ['p_id', '=', $post_id],
                ])->update(['islike' => true]);
            return response()->json(['message' => 'Like updated successfully', 'like' => $like]);
        }
    }

    public function UpdatePostData($post_id, Request $request)
    {
        $validator = Validator::make(array_merge($request->all(), ["post_id" => $post_id]), [
            'post_id' => 'required|integer|exists:posts,id',
            'title' => 'required|string|between:10,255',
            'category_id' => 'required|integer|exists:categories,id',
            'body' => 'required|string|between:200,1000',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $me = auth()->user();
        $author_id = DB::table('posts')
            ->select('author_id')
            ->where('id', '=', $post_id)
            ->get()->first();

        if ($me->id != $author_id->author_id) {
            if (!$me->hasRole('admin')) {
                return response()->json(['Error' => 'Permission denied'], 403);
            }
        }

        $post = Post::where('id', '=', $post_id)->get()->first();

        $newPostData = $validator->validated();

        $post->fill($newPostData);

        $post->save();

        return response()->json(['message' => 'User data updated successfully', 'user' => $post]);

    }

    public function DeletePostData($post_id, Request $request)
    {
        $validator = Validator::make(["post_id" => $post_id], [
            'post_id' => 'required|integer|exists:posts,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 404);
        }
        $me = auth()->user();

        //If post mine
        $my_like = DB::table('posts')
            ->select('id')
            ->where('author_id', '=', $me['id'])
            ->count();
        if ($my_like == 1 || $me->hasRole('admin')) {
            DB::table('posts')->where([
                ['id', '=', $post_id],
            ])->delete();

            return response()->json(['message' => 'Post deleted successfully']);
        } else {
            return response()->json(['message' => 'You must make a post before deleting it ;)']);
        }

        DB::table('posts')->delete($post_id);
    }

    public function DeletePostLike($post_id, Request $request)
    {
        $validator = Validator::make(["post_id" => $post_id], [
            'post_id' => 'required|integer|exists:posts,id',
        ]);

        $me = auth()->user();

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //If like exist
        $my_like = DB::table('likes')
            ->select('id')
            ->where('author_id', '=', $me['id'])
            ->count();

        if ($my_like == 1) {

            DB::table('likes')->where([
                ['p_id', '=', $post_id],
                ['author_id', '=', $me['id']],
            ])->delete();

            return response()->json(['message' => 'Like deleted successfully']);
        } else {
            return response()->json(['message' => 'You must like this post before deleting it']);
        }
    }

    public function makePostInactive($post_id, Request $request)
    {
        $validator = Validator::make(array_merge($request->all(), ["post_id" => $post_id]), [
            'post_id' => 'required|integer|exists:posts,id',
            'featured' => 'integer|min:0|max:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $me = auth()->user();
        $author_id = DB::table('posts')
            ->select('author_id')
            ->where('id', '=', $post_id)
            ->get()->first();

        if ($me->id != $author_id->author_id) {
            if (!$me->hasRole('admin')) {
                return response()->json(['Error' => 'Permission denied'], 403);
            }
        }

        $post = Post::where('id', '=', $post_id)->get()->first();

        $newPostData = $validator->validated();

        $post->fill($newPostData);

        $post->save();

        return response()->json(['message' => 'User data updated successfully', 'user' => $post]);
    }

    public function disikePost($post_id, Request $request)
    {
        $validator = Validator::make(["p_id" => $post_id], [
            'p_id' => 'required|integer|exists:posts,id',
        ]);
        $me = auth()->user();

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $like_data = $validator->validated();

        //If like or dislike exist
        $my_like = DB::table('likes')
            ->select('id')
            ->whereColumn([
                ['author_id', '=', $me['id']],
                ['p_id', '=', $post_id],
            ])
            ->count();

        $like = 0;
        if ($my_like == 0) {
            $like = Like::create(array_merge(
                $validator->validated(),
                ['c_id' => 0,
                    'p_id' => $post_id,
                    'author_id' => $me['id'],
                    'islike' => "0"]
            ));
            return response()->json(['message' => 'Dislike created successfully', 'dislike' => $like]);
        } else {
            DB::table('likes')
                ->whereColumn([
                    ['author_id', '=', $me['id']],
                    ['p_id', '=', $post_id],
                ])
                ->update(['islike' => 0]);
            return response()->json(['message' => 'Dislike updated successfully', 'dislike' => $like]);
        }
    }

}
