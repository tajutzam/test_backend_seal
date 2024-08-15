<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    public function findAll(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);

        $projects = User::paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'status' => 'success',
            'message' => 'Data retrieved successfully',
            'data' => [
                'users' => $projects->items(),
                'pagination' => [
                    'current_page' => $projects->currentPage(),
                    'per_page' => $projects->perPage(),
                    'total' => $projects->total(),
                    'last_page' => $projects->lastPage(),
                    'from' => $projects->firstItem(),
                    'to' => $projects->lastItem(),
                ]
            ]
        ]);
    }

    public function findById($id)
    {
        $user = User::where('id', $id)->first();
        return response()->json([
            'status' => 'success',
            'message' => 'Data retrieved successfully',
            'data' => $user
        ]);
    }

    public function update(UpdateUserRequest $updateUserRequest)
    {
        $user = $updateUserRequest->user();

        if ($updateUserRequest->hasFile('avatar')) {
            $file = $updateUserRequest->file('avatar');
            $destinationPath = 'images';
            $filename = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->move($destinationPath, $filename);
            $user->avatar = $filePath;
        }

        $user->nik = $updateUserRequest->input('nik', $user->nik);
        $user->name = $updateUserRequest->input('name', $user->name);
        $user->email = $updateUserRequest->input('email', $user->email);

        if ($updateUserRequest->filled('password')) {
            $user->password = bcrypt($updateUserRequest->input('password'));
        }

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

    public function updateById(UpdateUserRequest $updateUserRequest, $id)
    {
        $user = User::where('id', $id)->first();

        if (!$user) {
            return response()->json(
                [
                    'status' => 'failed',
                    'message' => 'user not found'
                ],
                404
            );
        }

        if ($updateUserRequest->hasFile('avatar')) {
            $file = $updateUserRequest->file('avatar');
            $destinationPath = 'images';
            $filename = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->move($destinationPath, $filename);
            $user->avatar = $filePath;
        }

        $user->nik = $updateUserRequest->input('nik', $user->nik);
        $user->name = $updateUserRequest->input('name', $user->name);
        $user->email = $updateUserRequest->input('email', $user->email);

        if ($updateUserRequest->filled('password')) {
            $user->password = bcrypt($updateUserRequest->input('password'));
        }

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

    public function destroy(Request $request, $id)
    {
        try {
            User::where('id', $id)->deleteOrFail();
            return response()->json([
                'status' => 'success',
                'message' => 'User deleted successfully',
            ]);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => 'failed',
                    'message' => $th->getMessage()
                ],
                $th->getCode()
            );
        }
    }

    public function store(StoreUserRequest $storeUserRequest)
    {
        DB::beginTransaction();
        try {
            if ($storeUserRequest->has('avatar')) {
                $image = $storeUserRequest->file('avatar');
                $fileName = $image->getClientOriginalName();
                $image->move('images', $fileName);
                $data = $storeUserRequest->except('avatar');
                $data['avatar'] = $fileName;
                User::create($data);
                DB::commit();
                return response()->json(
                    [
                        'status' => 'success',
                        'message' => 'sukses add new user'
                    ],
                    201
                );
            } else {
                return response()->json(
                    [
                        'message' => 'avatar must be included!',
                        'status' => 'failed'
                    ],
                    400
                );
            }
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return response()->json(
                [
                    'message' => $th->getMessage(),
                    'status' => 'failed'
                ],
                $th->getCode()
            );
        }
    }
}
