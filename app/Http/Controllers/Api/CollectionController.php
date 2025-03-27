<?php

namespace App\Http\Controllers;
use App\Models\Collection;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function register(Request $request) {
     
        // User
        $collection = Collection::create([
            "name_en" => $request->name_en,
            "name_vi" => $request->name_vi,
            "thumb" => $request->thumb,
            "slug" => $request->slug,
            "user_id" => $request->user_id,
            "watched" => $request->watched,
        ]);

        if($collection) {
            return response()->json([
                "status" => true,
                "data" => $collection
            ]);
        }
    }
}
