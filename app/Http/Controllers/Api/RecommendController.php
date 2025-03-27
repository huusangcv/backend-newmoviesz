<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recommend;
use Illuminate\Http\Request;

class RecommendController extends Controller
{
    public function store(Request $request) {
         $movieExist = Recommend::where("slug", $request->slug)->first();
         if($movieExist) {
            return response()->json([
                "status" => false,
                "data" => $movieExist,
                "message" => "Phim này đã được thêm vào danh sách đề cử"
            ]);
         }


         $movie = Recommend::create([
            "name_en" => $request->name_en,
            "name_vi" => $request->name_vi,
            "thumb" => $request->thumb,
            "slug" => $request->slug,
            "year" => $request->year,
            "rate" => $request->rate,
            "vote_count" => $request->vote_count,
            "time" => $request->time,
            "created_at" => now(),
         ]);

         if($movie) {
            return response()->json([
                "status" => true,
                "data" => $movie,
                "message" => "Thêm mới một phim thành công"
            ]);
        } else {
            return response()->json([
                "status" => false,
                "data" => $movie,
                "message" => "Có lỗi khi thêm phim, hãy thử lại"
            ]);
        }
    }

    public function index()
    {
        $q = request()->query('q');
        $limit = request()->query('limit', 100);
        
        $movies = Recommend::latest();

        if ($q) {
            $movies->where(function($query) use ($q) {
                $query->where('name_vi', 'like', '%' . $q . '%')
                    ->orWhere('name_en', 'like', '%' . $q . '%');
            });
        }

        return response()->json([
            'success' => true,
            'data' => $movies->paginate($limit),
            'message' => 'Users retrieved successfully'
        ]);
    }

    public function show($id)
    {
        $recommend = Recommend::find($id);
        if(!$recommend) {
            return response()-> json(
                [
                    'success' => false,
                    'message' => 'Không có phim để hiển thị'
                ],
                404
            );
        }

        return response()-> json(
            [
                'success' => true,
                'data' => $recommend,
                'message' => 'Danh sách phim đề cử'
            ]
        );
    }

    public function update(Request $request, string $id)
    {
        $movie = Recommend::find($id);
        if (!$movie) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'movie not found'
                ],
                404
            );
        }

        if ($request->name_vi !== 'null') {
            $movie->name_vi = $request->name_vi;
        }
        if ($request->name_en !== 'null') {
            $movie->name_en = $request->name_en;
        }
        if ($request->thumb !=='null') {
            $movie->thumb = $request->thumb;
        }

   
        $movie->save();

        return response()->json(
            [
                'success' => true,
                'data' => $movie,
                'message' => 'Cập nhật phim thành công'
            ]
        );
    }

    public function destroy(string $id)
    {
        $recommend = Recommend::find($id);
        if(!$recommend) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Không tìm thấy phim tương ứng'
                ],
                404
            );
        }
        
        $recommend->delete();
        return response()->json(
            [
                'success' => true,
                'message' => 'Đã xoá phim thành công'
            ]
        );
    }
}
