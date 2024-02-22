<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserController extends Controller
{
    protected function responseWithSuccess($message, $data, $statusCode)
    {
        return response()->json([
            'success' => [
                'message' => $message,
                'data' => $data,
            ],
        ], $statusCode);
    }
    protected function responseExceptionError($message, $trace, $statusCode)
    {
        return response()->json([
            'error' => [
                'message' => $message,
                'trace' => $trace,
            ],
        ], $statusCode);
    }
    public function register(Request $request)
    {


        $data = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($data->fails()) {
            return response()->json([
                'errors' => $data->errors(),
            ], 422);
        }


        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),

        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            
        ], 200);
    }
    public function login(Request $request)
    {
        $data = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($data->fails()) {
            return response()->json([
                'errors' => $data->errors(),
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid login details',
            ], 401);
        }

        $user = User::where('email', $request['email'])->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
          
        ], 200);
    }

    public function user(Request $request)
    {
        try {
            $user = User::where('id', Auth::user()->id)->first();
            $data['messages'] = "Profile Details";
            $data['profile_info'] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ];
            $response = $this->responseWithSuccess($data['messages'], $data, 200);
            return $response;
        } catch (\Throwable $th) {
            return $this->responseExceptionError($th->getMessage(), $th->getTrace(), 500);
        }
    }
    public function AllUser(Request $request)
    {
        try {
            // Fetch all users
            $users = User::all();
    
            // Map user details
            $userDetails = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name, 
                    'email' => $user->email,
                    'phone' => $user->phone,
                ];
            });
            $data['messages'] = "Profile Details";
           
            $response = $this->responseWithSuccess($data['messages'], $userDetails, 200);
            return $response;
        } catch (\Throwable $th) {
            return $this->responseExceptionError($th->getMessage(), $th->getTrace(), 500);
        }
    }
    
}