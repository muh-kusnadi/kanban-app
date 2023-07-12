<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct()
    {

    }

    public function index()
    {
        $pageTitle = 'Task List';
        $tasks     = Task::all();
        return view('tasks.index', [
            'pageTitle' => $pageTitle,
            'tasks'     => $tasks
        ]);
    }

    public function edit($id)
    {
        $pageTitle = 'Edit Task';
        $tasks     = Task::find($id);;

        $task = $tasks[$id - 1];

        return view('tasks.edit', ['pageTitle' => $pageTitle, 'task' => $task]);
    }

    public function create()
    {
        $pageTitle = 'Add Task';
        return view('tasks.create', ['pageTitle' => $pageTitle]);
    }
}
