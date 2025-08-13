<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Task::query();

        // Filter berdasarkan status
        if ($request->has('status')) {
            switch ($request->status) {
                case 'completed':
                    $query->completed();
                    break;
                case 'incomplete':
                    $query->incomplete();
                    break;
                case 'overdue':
                    $query->overdue();
                    break;
                case 'upcoming':
                    $query->upcoming();
                    break;
            }
        }

        // Filter berdasarkan priority
        if ($request->has('priority') && $request->priority !== '') {
            $query->where('priority', $request->priority);
        }

        // Ambil semua tasks
        $tasks = $query->get();

        // Urutkan berdasarkan priority (termasuk yang dihitung berdasarkan due_date)
        $tasks = $tasks->sortByDesc(function ($task) {
            return $task->priority_value;
        });

        // Data untuk statistik
        $stats = [
            'total' => Task::count(),
            'completed' => Task::completed()->count(),
            'incomplete' => Task::incomplete()->count(),
            'overdue' => Task::overdue()->count(),
            'upcoming' => Task::upcoming()->count()
        ];

        return view('tasks.index', compact('tasks', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $priorityOptions = [
            'low' => 'Low',
            'normal' => 'Normal', 
            'high' => 'High',
            'urgent' => 'Urgent'
        ];

        return view('tasks.create', compact('priorityOptions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'due_date' => 'nullable|date|after_or_equal:today',
        ]);

        Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
            'is_finish' => $request->has('is_finish')
        ]);

        return redirect()->route('tasks.index')
            ->with('success', 'Tugas berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $task = Task::findOrFail($id);
        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $task = Task::findOrFail($id);
        
        $priorityOptions = [
            'low' => 'Low',
            'normal' => 'Normal', 
            'high' => 'High',
            'urgent' => 'Urgent'
        ];

        return view('tasks.edit', compact('task', 'priorityOptions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'due_date' => 'nullable|date',
        ]);

        $task = Task::findOrFail($id);
        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
            'is_finish' => $request->has('is_finish')
        ]);

        return redirect()->route('tasks.index')
            ->with('success', 'Tugas berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return redirect()->route('tasks.index')
            ->with('success', 'Tugas berhasil dihapus!');
    }

    /**
     * Toggle task completion status
     */
    public function toggleComplete(string $id)
    {
        $task = Task::findOrFail($id);
        $task->update([
            'is_finish' => !$task->is_finish
        ]);

        $message = $task->is_finish ? 'Tugas berhasil diselesaikan!' : 'Tugas berhasil dibuka kembali!';
        
        return redirect()->route('tasks.index')
            ->with('success', $message);
    }

    /**
     * Get tasks dashboard data
     */
    public function dashboard()
    {
        $totalTasks = Task::count();
        $completedTasks = Task::completed()->count();
        $incompleteTasks = Task::incomplete()->count();
        $overdueTasks = Task::overdue()->count();
        $upcomingTasks = Task::upcoming()->count();

        // Tasks berdasarkan priority
        $priorityStats = [
            'low' => Task::where('priority', 'low')->count(),
            'normal' => Task::where('priority', 'normal')->count(),
            'high' => Task::where('priority', 'high')->count(),
            'urgent' => Task::where('priority', 'urgent')->count(),
        ];

        // Recent tasks
        $recentTasks = Task::orderBy('created_at', 'desc')
                          ->take(5)
                          ->get();

        // Urgent tasks (due in 3 days or overdue)
        $urgentTasks = Task::where('is_finish', false)
                          ->whereNotNull('due_date')
                          ->where('due_date', '<=', Carbon::now()->addDays(3))
                          ->orderBy('due_date', 'asc')
                          ->get();

        return view('tasks.dashboard', compact(
            'totalTasks',
            'completedTasks', 
            'incompleteTasks',
            'overdueTasks',
            'upcomingTasks',
            'priorityStats',
            'recentTasks',
            'urgentTasks'
        ));
    }
}