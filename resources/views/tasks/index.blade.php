@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="mb-4">My To-Do List</h1>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5>Total Tasks</h5>
                                <h3>{{ $stats['total'] }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5>Completed</h5>
                                <h3>{{ $stats['completed'] }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5>Pending</h5>
                                <h3>{{ $stats['incomplete'] }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h5>Overdue</h5>
                                <h3>{{ $stats['overdue'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter and Actions -->
                <div class="row mb-3">
                    <div class="col-md-8">
                        <form method="GET" action="{{ route('tasks.index') }}" class="row g-2">
                            <div class="col-md-4">
                                <select name="status" class="form-select" onchange="this.form.submit()">
                                    <option value="">All Status</option>
                                    <option value="incomplete" {{ request('status') == 'incomplete' ? 'selected' : '' }}>Incomplete</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                    <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="priority" class="form-select" onchange="this.form.submit()">
                                    <option value="">All Priority</option>
                                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Task
                        </a>
                    </div>
                </div>

                @if ($tasks->isEmpty())
                    <div class="alert alert-info text-center">
                        <h4>No tasks found</h4>
                        <p>You don't have any tasks yet. <a href="{{ route('tasks.create') }}" class="btn btn-primary btn-sm">Create your first task!</a></p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Priority</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Completed</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tasks as $task)
                                    <tr class="{{ $task->is_finish ? 'table-success' : ($task->due_date_status == 'overdue' ? 'table-danger' : ($task->due_date_status == 'urgent' ? 'table-warning' : '')) }}">
                                        <td>
                                            <strong>{{ $task->title }}</strong>
                                            @if($task->calculated_priority == 'urgent' && $task->priority != 'urgent')
                                                <span class="badge bg-danger ms-1">AUTO URGENT</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ Str::limit($task->description, 50) }}
                                        </td>
                                        <td>
                                            @php
                                                $priorityClass = [
                                                    'low' => 'bg-secondary',
                                                    'normal' => 'bg-primary', 
                                                    'high' => 'bg-warning',
                                                    'urgent' => 'bg-danger'
                                                ];
                                                $displayPriority = $task->calculated_priority;
                                            @endphp
                                            <span class="badge {{ $priorityClass[$displayPriority] }}">
                                                {{ ucfirst($displayPriority) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($task->due_date)
                                                <div>
                                                    <small class="text-muted">{{ $task->due_date->format('d M Y') }}</small>
                                                    @if($task->due_date_status)
                                                        <br>
                                                        @php
                                                            $statusClass = [
                                                                'overdue' => 'text-danger',
                                                                'urgent' => 'text-warning',
                                                                'approaching' => 'text-info',
                                                                'normal' => 'text-success'
                                                            ];
                                                            $statusText = [
                                                                'overdue' => 'Overdue',
                                                                'urgent' => 'Due Soon',
                                                                'approaching' => 'Approaching',
                                                                'normal' => 'On Track'
                                                            ];
                                                        @endphp
                                                        <small class="{{ $statusClass[$task->due_date_status] }}">
                                                            <i class="fas fa-clock"></i> {{ $statusText[$task->due_date_status] }}
                                                        </small>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">No due date</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($task->due_date_status == 'overdue')
                                                <span class="badge bg-danger">Overdue</span>
                                            @elseif($task->due_date_status == 'urgent')
                                                <span class="badge bg-warning">Due Soon</span>
                                            @elseif($task->due_date_status == 'approaching')
                                                <span class="badge bg-info">Approaching</span>
                                            @else
                                                <span class="badge bg-success">On Track</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($task->is_finish)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check"></i> Done
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-clock"></i> Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <!-- Toggle Complete Button -->
                                                <form action="{{ route('tasks.toggle', $task->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm {{ $task->is_finish ? 'btn-outline-secondary' : 'btn-outline-success' }}" 
                                                            title="{{ $task->is_finish ? 'Mark as Incomplete' : 'Mark as Complete' }}">
                                                        <i class="fas {{ $task->is_finish ? 'fa-undo' : 'fa-check' }}"></i>
                                                    </button>
                                                </form>
                                                
                                                <!-- Edit Button -->
                                                <a class="btn btn-sm btn-outline-warning" href="{{ route('tasks.edit', $task->id) }}" title="Edit Task">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <!-- Delete Button -->
                                                <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                            onclick="return confirm('Are you sure you want to delete this task?')" 
                                                            title="Delete Task">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .table-responsive {
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .btn-group .btn {
            margin-right: 2px;
        }
        
        .card {
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .badge {
            font-size: 0.75em;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(0,0,0,0.05);
        }
        
        .alert-dismissible .btn-close {
            padding: 0.5rem;
        }
    </style>
@endsection