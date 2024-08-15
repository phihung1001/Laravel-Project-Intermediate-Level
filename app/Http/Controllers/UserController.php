<?php

namespace App\Http\Controllers;
use App\Models\Task;
use App\Models\Teams;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Psr\Container\NotFoundExceptionInterface;
use Tymon\JWTAuth\JWTAuth;

class UserController extends Controller
{
    //Định nghĩa middleware cho tất cả các phương thức trong controller ngoại trừ login, register
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }
    // Xử lý đăng nhập
    public function login(Request $request)
    {
        // Validate dữ liệu
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = request(['email', 'password']);

        // Kiểm tra thông tin đăng nhập
        if (! $token = Auth::attempt($credentials)) {
            // Đăng nhập thất bại
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        // Đăng nhập thành công
        return $this->respondWithToken($token);
    }

    //update nguoi dung
    public function update_user(Request $request) {

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users',
            'password' => 'string|min:8|confirmed',
            'team_id' => 'integer',
            'role_id' => 'integer',
        ]);
        // Nếu có lỗi validate
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = auth()->user();
        $user->update($request->all());
        return response()->json($user, 200);
    }

    //Xử lí đăng kí
    public function register(Request $request) {
        // Validate dữ liệu
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'team_id' => 'integer',
            'role_id' => 'integer',
        ]);
        // Nếu có lỗi validate
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }


        // Tạo người dùng mới
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'team_id' => $request->team_id,
            'role_id' =>$request->role_id,
        ]);
        $credentials =  request(['email', 'password']);

        // Tạo token JWT cho người dùng mới
        $token = auth()->attempt($credentials);

        // Trả về phản hồi JSON với thông tin người dùng và token
        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    //get all user trong team
    public function get_user() {
        if(auth()->user()->role_id === 3) {
            $user = User::where('team_id', auth()->user()->team_id)->get();
        } else if(auth()->user()->role_id ===2){
            $user = User::all();
        } else {
            return response()->json([
                'message' => 'Khong du quyen'
            ], 403);
        }
        return response()->json($user);
    }

    //Admin, Leader update user bat kiapi
    public function update_user2(Request $request, $id)
    {
        // Validate dữ liệu
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users',
            'password' => 'string|min:8|confirmed',
            'team_id' => 'integer',
            'role_id' => 'integer',
        ]);
        // Nếu có lỗi validate
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        try {
            $user = User::findOrFail($id);

            if (auth()->user()->role_id === 2) {
                $user->update($request->all());
            } else if (auth()->user()->role_id === 3) {
                if(auth()->user()->team_id === $user->team_id && $user->role_id !==2 && $user->role_id !==3) {
                    $user->update($request->all());
                } else {
                    return response()->json([
                        'message' => 'Khong du quyen'
                    ], 403);
                }
                return response()->json($user);
            } else {
                return response()->json([
                    'message' => 'Khong du quyen'
                ], 403);
            }
            return response()->json($user);
        } catch(ModelNotFoundException) {
            return response()->json([
                'message' => 'Not User'
            ], 404);
        }

    }
    //Delete user random
    public function delete_user2($id){
        try{
            $user =User::findOrFail($id);
            if(auth()->user()->role_id ===2 && $user->role_id !==2 ) {
                $user->delete();
            } else if(auth()->user()->role_id ===3) {
                 if(auth()->user()->team_id === $user->team_id && $user->role_id !==2 && $user->role_id !==3) {
                    $user->delete();
                 } else {
                    return response()->json([
                        'message' => 'Khong du quyen'
                    ],404);
                 }
                } else {
                    return response()->json([
                        'message' => 'Khong du quyen'
                    ],404);
                }
            return response()->json([
                'message' => 'Delete success'
            ]);
        } catch(ModelNotFoundException) {
            return response()->json([
                'message' => 'Not User'
            ], 404);
        }
    }

    //Create User trong team
    public function create_user2(Request $request) {

        // Validate dữ liệu
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'team_id' => 'integer',
            'role_id' => 'integer',
        ]);
        // Nếu có lỗi validate
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        // Tạo người dùng mới

        if(auth()->user()->role_id===2) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'team_id' => $request->team_id,
                'role_id' =>$request->role_id,
            ]);
            return response()->json($user, 201);
        } else if(auth()->user()->role_id===3) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'team_id' => auth()->user()->team_id,
                'role_id' =>$request->role_id,
            ]);
            return response()->json($user,201);
        } else {
            return response()->json([
                'message'=> 'Khong du quyen'
            ], 404);
        }
    }

    // Admin, leader xem tat ca user trong 1 team nao do
    public function get_user2($id)
    {
        try{
            $team = Teams::findOrFail($id);

            if(auth()->user()->role_id === 2) {
                $user = User::where('team_id', $id)->get();
                return response()->json($user);
            } else if(auth()->user()->role_id === 3) {
                if(auth()->user()->team_id === $id) {
                    $user = User::where('team_id', $id)->get();
                    return response()->json($user);
                } else {
                    return response()->json([
                        'message' => 'Khong du quyen'
                    ], 404);
                }
            } else {
                return response()->json([
                    'message' => 'Khong du quyen'
                ],404);
            }
        } catch(ModelNotFoundException) {
            return response()->json([
                'message' => 'Not User'
            ], 404);
        }

    }


    //Xem thong tin user
    public function me()
    {
        return response()->json(Auth::user());
    }

    public function logout()
    {
//        dd( auth()->user());
        auth()->logout(true);

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    private function middleware(string $string, array $array)
    {
    }
}
