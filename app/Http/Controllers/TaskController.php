<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    //

    public function index(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);

        $user = $request->user();

        $tasks = Task::with(['user', 'project'])->where('user_id', $user->id)->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'status' => 'success',
            'message' => 'Tasks retrieved successfully',
            'data' => [
                'tasks' => $tasks->items(),
                'pagination' => [
                    'current_page' => $tasks->currentPage(),
                    'per_page' => $tasks->perPage(),
                    'total' => $tasks->total(),
                    'last_page' => $tasks->lastPage(),
                    'from' => $tasks->firstItem(),
                    'to' => $tasks->lastItem(),
                ]
            ]
        ]);
    }


    public function findByProject(Request $request, $id)
    {
        $project = Project::where('id', $id)->first();
        if (!$project) {
            return response()->json(
                [
                    'status' => 'failed',
                    'message' => 'project not found'
                ],
                404
            );
        }
        $user = $request->user();
        $task = Task::with(['user', 'project'])->where('project_id', $project->id)->where('id', $user->id)->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Task retrieved successfully',
            'data' => $task,
        ]);
    }

    public function show(Request $request, $id)
    {
        $user  = $request->user();
        $task = Task::with(['user', 'project'])->where('id', $id)->where('id', $user->id)->first();

        if (!$task) {
            return response()->json([
                'status' => 'failed',
                'message' => 'task not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Task retrieved successfully',
            'data' => $task,
        ]);
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:done,progres,late',
            'project_id' => 'required|exists:project,id',
        ]);

        $user = $request->user();
        $validatedData['user_id'] = $user->id;

        $task = Task::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Task created successfully',
            'data' => $task,
        ]);
    }


    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:done,progres,late',
            'project_id' => 'required|exists:project,id',
        ]);

        $user = $request->user();
        $validatedData['user_id'] = $user->id;

        $task = Task::where('id', $id)->first();
        if (!$task) {
            return response()->json([
                'status' => 'failed',
                'message' => 'task not found',
            ], 404);
        }
        $task->update($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Task updated successfully',
            'data' => $task,
        ]);
    }


    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $task = Task::where('id', $id)->where('user_id', $user->id)->first();
        if (!$task) {
            return response()->json([
                'status' => 'failed',
                'message' => 'task not found',
            ], 404);
        }
        $task->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Task deleted successfully',
        ]);
    }
}
