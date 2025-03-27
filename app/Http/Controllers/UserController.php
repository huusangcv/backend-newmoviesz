<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $q = request()->query('q');
        $limit = request()->query('limit', 100);
        
        $users = User::latest();

        if ($q) {
            $users->where(function($query) use ($q) {
                $query->where('name', 'like', '%' . $q . '%')
                    ->orWhere('email', 'like', '%' . $q . '%');
            });
        }

        return response()->json([
            'success' => true,
            'data' => $users->paginate($limit),
            'message' => 'Users retrieved successfully'
        ]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        // Kiểm tra xem email đã tồn tại trong cơ sở dữ liệu chưa
        $existingUser = User::where('email', $request->email)->first();

        if ($existingUser) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Email đã tồn tại trong hệ thống'
                ], 400
            );
        }

        // Nếu không tồn tại, tạo mới user
        $user = new User;
        $user->fill($request->all());
        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json(
            [
                'success' => true,
                'data' => $user,
                'message' => 'Tạo mới một người dùng thành công'
            ], 201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = User::find($id);
        if(!$user) {
            return response()-> json(
                [
                    'success' => false,
                    'message' => 'User not found'
                ],
                404
            );
        }

        return response()-> json(
            [
                'success' => true,
                'data' => $user,
                'message' => 'Users retrived successfully'
            ]
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
     public function update(UserRequest $request, string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'User not found'
                ],
                404
            );
        }

        if ($user->name === $request->name) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Email đã tồn tại'
                ],
            );
        }

        if ($request->name) {
            $user->name = $request->name;
        }
        if ($request->email !== 'null') {
            $user->email = $request->email;
        }

        if ($request->password) {
            $user->password = bcrypt($request->password);
        }

        if ($request->email_verified_at === 'verify') {
            $user->email_verified_at = now();
        }
        
        if ($request->email_verified_at === 'null') {
            $user->email_verified_at = null;
        }
       

        $user->save();

        return response()->json(
            [
                'success' => true,
                'data' => $user,
                'message' => 'Cập nhật người dùng thành công'
            ]
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        if(!$user) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Không tìm thấy người dùng'
                ],
                404
            );
        }

        if($user && $user->id === 1) {
            return response()->json(
                [
                    'success' => false,
                    'message' => "Không thể xoá người dùng này"
                ]
            );
        }
        
        $user->delete();
        return response()->json(
            [
                'success' => true,
                'message' => 'Xoá người dùng thành công'
            ]
        );
    }

   
}
