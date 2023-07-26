<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tags;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class PostController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function createPost(Request $req) {
        $user = Auth::user();
        $data = $req->all();
        $data["user_id"] = $user->id;
        $data["slug"] = str_slug($req->title);
        $data["category"] = explode(",", $req->category);
        $data["tags"] = explode(",", $req->tags);
        
        $rules = [
            "user_id" => "required|exists:users,id",
            "title" => "required|min:5|max:50",
            "text" => "required|min:50|max:5000",
            "category.*" => "exists:category,id",
            "tags.*" => "exists:tags,id",
            "image" => [
                File::image()
                    ->min(8)
                    ->max(3 * 1024)
                    ->dimensions(Rule::dimensions()->maxWidth(1200)->maxHeight(1200)),
            ]
        ];

        $valid = Validator::make($data, $rules);
        if($valid->fails()) {
            return response([
                "status" => false,
                "message" => $valid->errors()->first()
            ], 400);
        }

        $findSimilarPost = Post::where("user_id", $user->id)->where("slug", $data["slug"])->first();
        if($findSimilarPost) {
            return response([
                "status" => false,
                "message" => "You have a similar post, please delete or update your similar or current post.",
                "data" => $findSimilarPost
            ], 400);
        }

        $path = "";
        if($data["image"]) {
            $file_name = str_replace(" ", "_", str_replace(":", "", Str::random(10) . "_". Carbon::now())) . ".jpg";
            $req->file("image")->move(base_path() . '/public/image', $file_name);
            $path = url() . "/image" . "/" . $file_name;
        }

        $post = new Post($data);
        $post->category = json_encode(array_unique($data["category"]));
        $post->tags = json_encode(array_unique($data["tags"]));
        $post->image = $path;
        $post->save();

        return response([
            "status" => true,
            "message" => "Success add Post",
            "data" => $post
        ], 201);
    }

    public function getPost() {
        $post = Post::all();
        $res = [];

        foreach($post as $p) {
            $arrCate = json_decode($p->category);
            $getCategory = [];
            foreach($arrCate as $c) {
                $cate = Category::find($c);
                if($cate) {
                    $getCategory[] = $cate;
                }
            }

            $arrTags = json_decode($p->tags);
            $getTags = [];
            foreach($arrTags as $c) {
                $tags = Tags::find($c);
                if($tags) {
                    $getTags[] = $tags;
                }
            }

            $p->category = $getCategory;
            $p->tags = $getTags;

            $res[] = $p;
        }

        return response([
            "status" => true,
            "message" => "Success get all Posts",
            "data" => $res
        ], 200);
    }

    public function getPostByID($id) {
        $p = Post::find($id);
        if($p) {

            $arrCate = json_decode($p->category);
            $getCategory = [];
            foreach($arrCate as $c) {
                $cate = Category::find($c);
                if($cate) {
                    $getCategory[] = $cate;
                }
            }
    
            $arrTags = json_decode($p->tags);
            $getTags = [];
            foreach($arrTags as $c) {
                $tags = Tags::find($c);
                if($tags) {
                    $getTags[] = $tags;
                }
            }
    
            $p->category = $getCategory;
            $p->tags = $getTags;
    
            return response([
                "status" => true,
                "message" => "Success get Post",
                "data" => $p
            ], 200);
        }

        return response([
            "status" => false,
            "message" => "Invalid post ID"
        ], 400);

    }

    public function updatePost(Request $req, $id) {
        $user = Auth::user();
        $data = $req->all();
        if($req->title) $data["slug"] = str_slug($req->title);
        if($req->category !== null) $data["category"] = explode(",", $req->category);
        if($req->tags !== null) $data["tags"] = explode(",", $req->tags);
        
        $currentPost = Post::find($id);

        if(!$currentPost) {
            return response([
                "status" => false,
                "message" => "Invalid Post ID"
            ], 400);
        }

        if($currentPost->user_id != $user->id) {
            return response([
                "status" => false,
                "message" => "You don't have an access to update this post"
            ], 403);
        }

        $rules = [
            "title" => "min:5|max:50",
            "text" => "min:50|max:5000",
            "category.*" => "exists:category,id",
            "tags.*" => "exists:tags,id",
            "image" => [
                File::image()
                    ->min(8)
                    ->max(3 * 1024)
                    ->dimensions(Rule::dimensions()->maxWidth(1200)->maxHeight(1200)),
            ]
        ];

        $valid = Validator::make($data, $rules);
        if($valid->fails()) {
            return response([
                "status" => false,
                "message" => $valid->errors()->first()
            ], 400);
        }

        if($req->title) {
            $findSimilarPost = Post::where("user_id", $user->id)->where("slug", $data["slug"])->where("id", "!=", $id)->first();
            if($findSimilarPost) {
                return response([
                    "status" => false,
                    "message" => "You have a similar post, please delete or update your similar or current post.",
                    "data" => $findSimilarPost
                ], 400);
            }
        }

        if($data["image"]) {
            if($currentPost->image && file_exists(base_path() . "/public" . str_replace(url(), "", $currentPost->image))) {
                unlink(base_path() . "/public" . str_replace(url(), "", $currentPost->image));
            }
            $file_name = str_replace(" ", "_", str_replace(":", "", Str::random(10) . "_". Carbon::now())) . ".jpg";
            $req->file("image")->move(base_path() . '/public/image', $file_name);
            $path = url() . "/image" . "/" . $file_name;
            $currentPost->image = $path;
        }

        if($req->category !== null) $currentPost->category = json_encode(array_unique($data["category"]));
        if($req->tags !== null) $currentPost->tags = json_encode(array_unique($data["tags"]));
        if($req->title) {
            $currentPost->title = $data["title"];
            $currentPost->slug = $data["slug"];
        }
        if($req->text) $currentPost->text = $data["text"];
        $currentPost->update();

        return response([
            "status" => true,
            "message" => "Success Update Post",
            "data" => $currentPost
        ], 200);
    }

    public function deletePost($id) {
        $findPost = Post::find($id);
        if($findPost) {
            $user = Auth::user();
            if($findPost->user_id == $user->id){
                if($findPost->image && file_exists(base_path() . "/public" . str_replace(url(), "", $findPost->image))) {
                    unlink(base_path() . "/public" . str_replace(url(), "", $findPost->image));
                }
                $findPost->delete();
                return response([
                    "status" => true,
                    "message" => "Success delete post",
                    "data" => $findPost
                ]);
            } else {
                return response([
                    "status" => false,
                    "message" => "You don't have an access to delete this post"
                ], 403);
            }
        }
        return response([
            "status" => false,
            "message" => "Invalid Post ID"
        ], 400);
    }

    //
}
