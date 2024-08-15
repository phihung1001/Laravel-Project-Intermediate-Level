<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{

    // Lấy tất cả công việc
    public function index()
    {
        if(auth()->user()->role_id === 2) {
            $tasks = Task::all();
        } else if(auth()->user()->role_id === 3) {
            $tasks = Task::whereHas('user', function($query) {
                $query->where('team_id', auth()->user()->team_id);
            })->get();

        } else {
            $tasks = auth()->user()->tasks;
        }
        return response()->json($tasks);
    }

    //Show 1 cong viec
    public function show(Request $request, $id) {
        try{
            $tasks = Task::findOrfail($id);
            if(auth()->user()->role_id ===2) {
                return response()->json($tasks);
            } else {
                $tasks = auth()->user()->tasks()->findOrFail($id);
            }
            return response()->json($tasks);
        } catch (ModelNotFoundException) {
            return response()->json([
                'message' => 'Not Task',
            ], 404);
        }
//        if(auth()->user()->role ==='admin') {
//                $tasks = Task::findOrfail($id);
//            } else {
//                $tasks = auth()->user()->tasks()->findOrFail($id);
//            }
//            return response()->json($tasks);
    }

    // Thêm công việc mới
    public function store(Request $request)
    {
//        dd(1);
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'completed' => 'boolean',
        ]);
//        dd(1);

        $task = auth()->user()->tasks()->create($request->all());

        return response()->json($task, 201);
    }

    //Create Task cho user nao do
    public function create_task(Request $request, $id) {
        try{
            $user = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|nullable|string',
                'completed' => 'required|boolean',
            ]);
            // Nếu có lỗi validate
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            if(auth()->user()->role_id ===2) {
                $task = $user->tasks()->create($request->all());
                return response()->json($task);
            } else if(auth()->user()->role_id === 1) {
                return response()->json([
                    'message' => 'Khong du quyen',
                ],404);
            } else {
                if(auth()->user()->role_id === 3) {
                    if($user->team_id === auth()->user()->team_id) {
                        $task = $user->tasks()->create($request->all());
                        return response()->json($task, 201);
                    } else {
                        return response()->json([
                            'message' => 'Khong du quyen',
                        ],404);
                    }
                }
            }
        } catch (ModelNotFoundException) {
            return response()->json([
                'message' => 'Khong co user de them cong viec',
            ],404);
        }
    }

    //Update công việc
    public function update(Request $request, $id){

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'completed' => 'sometimes|boolean',
        ]);
        try {
            $task = Task::findOrFail($id);
            if (auth()->user()->role_id === 2) {
                $task->update($request->all());
                return response()->json($task, 200);
            } else if (auth()->user()->role_id === 3) {
                if($task->user->team_id === auth()->user()->team_id) {
                    $task->update($request->all());
                    return response()->json([$task],201);
                } else {
                    return response()->json([
                        'message' => 'Khong du quyen',
                    ],404);
                }
            } else if (auth()->user()->role_id === 1){
                $user = $task->user;
                if($user->id === auth()->user()->id) {
                    $task->update($request->all());
                    return response()->json([$task],201);
                } else {
                    return response()->json([
                        'message' => 'Khong du quyen',
                    ],404);
                }
            }
        } catch(ModelNotFoundException) {
            return response()->json([
                'message' => 'not task',
            ],404);
        }
    }

    //Xoa cong viec
    public function destroy($id) {

        try {
            $task = Task::findOrFail($id);
            if (auth()->user()->role_id === 2) {
                $task->delete();
                return response()->json(['message' => 'Task deleted successfully']);
            } else if (auth()->user()->role_id === 3) {
                if($task->user->team_id === auth()->user()->team_id) {
                    $task->delete();
                    return response()->json(['message' => 'Task deleted successfully']);
                } else {
                    return response()->json([
                        'message' => 'Khong du quyen',
                    ],404);
                }
            } else if (auth()->user()->role_id === 1){
                $user = $task->user;
                if($user->id === auth()->user()->id) {
                    $task->delete();
                    return response()->json(['message' => 'Task deleted successfully']);
                } else {
                    return response()->json([
                        'message' => 'Khong du quyen',
                    ],404);
                }
            }
        } catch(ModelNotFoundException) {
            return response()->json([
                'message' => 'not task',
            ],404);
        }
    }
    public function get_task($id) {
        try {
            $user = User::findOrFail($id);
            if(auth()->user()->role_id === 2) {
                $task = $user->tasks;
                return response()->json($task);
            } else if(auth()->user()->role_id === 3){
                if(auth()->user()->team_id === $user->team_id) {
                    $tasks = $user->tasks;
                    return response()->json($tasks);
                } else {
                    return response()->json([
                        'message' => 'Khong du quyen',
                    ],404);
                }
            } else {
                if(auth()->user()->id === $id) {
                    $tasks = $user->tasks;
                    return response()->json($tasks);
                }
            }
        } catch(ModelNotFoundException) {
            return response()->json([
                'message' => 'Not User'
            ],404);
        }
    }

}
