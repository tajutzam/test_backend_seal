<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    //

    public function authenticate(LoginRequest $loginRequest)
    {
        if (
            Auth::attempt([
                'email' => $loginRequest->email,
                'password' => $loginRequest->password
            ])
        ) {
            $user = Auth::user();
            $token = $user->createToken('api')->plainTextToken;
            return response()->json([
                'status' => 'success',
                'message' => 'sukses login',
                'token' => $token
            ],);
        } else {
            return $this->error("Invalid Credentials", 401);
        }
    }

    public function register(RegisterRequest $registerRequest)
    {
        DB::beginTransaction();
        try {
            if ($registerRequest->has('avatar')) {
                $image = $registerRequest->file('avatar');
                $fileName = $image->getClientOriginalName();
                $image->move('images', $fileName);
                $data = $registerRequest->except('avatar');
                $data['avatar'] = $fileName;
                User::create($data);
                DB::commit();
                return response()->json(
                    [
                        'status' => 'success',
                        'message' => 'sukses register'
                    ],
                    201
                );
            } else {
                return $this->error('avatar must be included!', 400);
            }
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return $this->error($th->getMessage(), 500);
        }
    }

    private function error($message, $code)
    {
        return response()->json(
            [
                'status' => 'failed',
                'message' => $message
            ],
            $code
        );
    }
}
