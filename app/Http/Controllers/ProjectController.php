<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{


    public function findAll(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);

        $projects = Project::with('tasks')->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'status' => 'success',
            'message' => 'Data retrieved successfully',
            'data' => [
                'projects' => $projects->items(),
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

    public function show($id)
    {
        $project = Project::where('id', $id)->first();

        if (!$project) {
            return response()->json(
                [
                    'status' => 'failed',
                    'message' => 'project not found!'
                ],
                404
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Project retrieved successfully',
            'data' => $project,
        ]);
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $validatedData['status'] = 'progres';

        $project = Project::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Project created successfully',
            'data' => $project,
        ]);
    }
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:done,progres',
        ]);

        $project = Project::where('id', $id)->first();
        if (!$project) {
            return response()->json(
                [
                    'status' => 'failed',
                    'message' => 'project not found!'
                ],
                404
            );
        }

        if ($validatedData['status'] === 'done') {

            $unfinishedTasks = $project->tasks()->where('status', '!=', 'done')->count();

            if ($unfinishedTasks > 0) {
                return response()->json(
                    [
                        'status' => 'failed',
                        'message' => 'All tasks must be marked as done before the project can be marked as done.'
                    ],
                    400
                );
            }
        }

        $project->update($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Project updated successfully',
            'data' => $project,
        ]);
    }

    public function destroy($id)
    {
        $project = Project::where('id', $id)->first();
        if (!$project) {
            return response()->json(
                [
                    'status' => 'failed',
                    'message' => 'project not found!'
                ],
                404
            );
        }
        $project->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Project deleted successfully',
        ]);
    }
}
