<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
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

    public function createCategory(Request $req) {
        $input = [
            "name" => $req->json("name"),
            "slug" => str_slug($req->json("name"))
        ];

        $validate = Validator::make($input, [
            "name" => "required|min:3|max:30",
            "slug" => "required|unique:category,slug"
        ]);

        if($validate->fails()) {
            return response([
                "status" => false,
                "message" => $validate->errors()->first()
            ], 400);
        }

        $category = new Category($input);
        $category->save();

        return response([
            "status" => true,
            "message" => "Success add Category",
            "data" => $category
        ], 201);

    }

    public function deleteCategory($id) {
        $category = Category::find($id);
        if($category) {
            $category->delete();
            return response([
                "status" => true,
                "message" => "Success delete Category",
                "data" => $category
            ], 200);
        }

        return response([
            "status" => false,
            "message" => "Invalid category ID"
        ], 400);
    }

    public function getCategoryByID($id) {
        $category = Category::find($id);
        if($category) {
            return response([
                "status" => true,
                "message" => "Success get Category",
                "data" => $category
            ], 200);
        }
        return response([
            "status" => false,
            "message" => "Invalid category ID"
        ], 400);
    }

    public function getCategory() {
        $category = Category::all();
        return response([
            "status" => true,
            "message" => "Success get all category",
            "data" => $category
        ]);
    }

    public function updateCategory(Request $req, $id) {
        $category = Category::find($id);
        if($category) {
            $slug = str_slug($req->json("name"));
            $findSlug = Category::where("slug", $slug)->where("id", "!=", $category->id)->first();
            if($findSlug) {
                return response([
                    "status" => false,
                    "message" => "The slug has already been taken."
                ], 400);
            }

            $category->name = $req->json("name");
            $category->slug = $slug;
            $category->update();

            return response([
                "status" => true,
                "message" => "Success update Category",
                "data" => $category
            ], 200);
        }

        return response([
            "status" => false,
            "message" => "Invalid category ID"
        ], 400);
    }

    //
}
