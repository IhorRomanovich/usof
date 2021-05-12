<?php

namespace App\Http\Controllers;

use App\Models\Comment as Comment;
use App\Models\User as User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

// getCommentByID
// getLikesByCommentID
// likeComment
// updateCommentData

// deleteCommentData
// deleteLikeUnderCommentary

class CommentsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    protected function guard()
    {
        return Auth::guard('api');
    }

    public function getCommentByID($comment_id, Request $request)
    {
        $validator = Validator::make(["id" => $comment_id], [
            'id' => 'required|integer|exists:posts,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 404);
        }

        $category = Comment::where('id', '=', $comment_id)->get()->first();

        return response()->json($category);
    }

    public function getLikesByCommentID($comment_id, Request $request)
    {
        $validator = Validator::make(["c_id" => $comment_id], [
            'c_id' => 'required|integer|exists:posts,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 404);
        }

        $likes = Like::where('c_id', '=', $comment_id)->get()->count();

        return response()->json($likes);
    }

    public function likeComment($comment_id, Request $request)
    {
        $validator = Validator::make(["c_id" => $comment_id], [
            'c_id' => 'required|integer|exists:posts,id',
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
        $like = 0;
        if ($my_like == 0) {
            $like = Like::create(array_merge(
                $validator->validated(),
                ['p_id' => 0,
                    'author_id' => $me['id'],
                    'islike' => "1"]
            ));
            return response()->json(['message' => 'Like created successfully', 'like' => $like]);
        } else {
            return response()->json(['Error' => 'You cant suck urself twice, sorry', 'like' => $like]);
        }
    }

    public function updateCommentData($comment_id, Request $request)
    {
        $validator = Validator::make(array_merge($request->all(), ["comment_id" => $comment_id]), [
            'comment_id' => 'required|integer|exists:posts,id',
            'name' => 'required|string|between:10,255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $me = auth()->user();
        $author_id = DB::table('comments')
            ->select('author_id')
            ->where('id', '=', $comment_id)
            ->get()->first();

        if ($me->id != $author_id->author_id) {
            if (!$me->hasRole('admin')) {
                return response()->json(['Error' => 'Permission denied'], 403);
            }
        }

        $comment = Comment::where('id', '=', $comment_id)->get()->first();

        $newCommentData = $validator->validated();

        $comment->fill($newCommentData);

        $comment->save();

        return response()->json(['comment' => 'Comment data updated successfully', 'comment' => $comment]);
    }

    public function deleteCommentData($comment_id, Request $request)
    {
        $validator = Validator::make(["id" => $comment_id], [
            'id' => 'required|integer|exists:posts,id',
        ]);

        $me = auth()->user();

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //If comment exist
        $my_comment = DB::table('comments')
            ->select('id')
            ->where('author_id', '=', $me['id'])
            ->count();

        if ($my_comment == 1 || $me->hasRole('admin')) {

            DB::table('comments')->where([
                ['id', '=', $comment_id],
            ])->delete();

            return response()->json(['comment' => 'Comment deleted successfully']);
        } else {
            return response()->json(['Error' => 'No such comment exist']);
        }
    }
}
