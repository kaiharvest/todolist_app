@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">
                            <i class="fas fa-plus-circle"></i> Add New Task
                        </h3>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>Whoops!</strong> There were some problems with your input:
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('tasks.store') }}" method="POST" id="taskForm">
                            @csrf
                            
                            <!-- Title -->
                            <div class="mb-3">
                                <label for="title" class="form-label">
                                    <i class="fas fa-heading"></i> Title <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       id="title"
                                       name="title" 
                                       class="form-control @error('title') is-invalid @enderror" 
                                       value="{{ old('title') }}" 
                                       placeholder="Enter task title..."
                                       required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label for="description" class="form-label">
                                    <i class="fas fa-align-left"></i> Description
                                </label>
                                <textarea id="description"
                                          name="description" 
                                          class="form-control @error('description') is-invalid @enderror" 
                                          rows="4" 
                                          placeholder="Enter task description...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Priority -->
                            <div class="mb-3">
                                <label for="priority" class="form-label">
                                    <i class="fas fa-flag"></i> Priority <span class="text-danger">*</span>
                                </label>
                                <select id="priority" 
                                        name="priority" 
                                        class="form-select @error('priority') is-invalid @enderror" 
                                        required>
                                    <option value="">Select Priority Level</option>
                                    @foreach($priorityOptions as $value => $label)
                                        <option value="{{ $value }}" {{ old('priority') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <small>
                                        <i class="fas fa-info-circle"></i> 
                                        Priority will automatically become "Urgent" if due date is within 3 days.
                                    </small>
                                </div>
                            </div>

                            <!-- Due Date -->
                            <div class="mb-3">
                                <label for="due_date" class="form-label">
                                    <i class="fas fa-calendar-alt"></i> Due Date
                                </label>
                                <input type="date" 
                                       id="due_date"
                                       name="due_date" 
                                       class="form-control @error('due_date') is-invalid @enderror" 
                                       value="{{ old('due_date') }}"
                                       min="{{ date('Y-m-d') }}">
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <small>
                                        <i class="fas fa-info-circle"></i> 
                                        Optional. Leave blank if no specific due date.
                                    </small>
                                </div>
                            </div>

                            <!-- Completed Status -->
                            <div class="mb-4">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           id="is_finish"
                                           class="form-check-input" 
                                           name="is_finish" 
                                           value="1"
                                           {{ old('is_finish') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_finish">
                                        <i class="fas fa-check-circle"></i> Mark as completed
                                    </label>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Save Task
                                </button>
                                <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-resize textarea
        document.getElementById('description').addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        // Form validation feedback
        document.getElementById('taskForm').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const priority = document.getElementById('priority').value;
            
            if (!title || !priority) {
                e.preventDefault();
                alert('Please fill in all required fields (Title and Priority).');
                return false;
            }
        });

        // Priority color preview
        document.getElementById('priority').addEventListener('change', function() {
            const priorityColors = {
                'low': 'secondary',
                'normal': 'primary',
                'high': 'warning',
                'urgent': 'danger'
            };
            
            const selectedPriority = this.value;
            const colorClass = priorityColors[selectedPriority] || 'secondary';
            
            // Remove existing classes
            this.classList.remove('border-secondary', 'border-primary', 'border-warning', 'border-danger');
            
            // Add new class
            if (selectedPriority) {
                this.classList.add(`border-${colorClass}`);
            }
        });
    </script>

    <style>
        .card {
            border-radius: 0.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            border-radius: 0.5rem 0.5rem 0 0;
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
        }
        
        .btn {
            border-radius: 0.375rem;
            font-weight: 500;
        }
        
        .text-danger {
            color: #dc3545 !important;
        }
        
        .form-text {
            margin-top: 0.25rem;
        }
        
        .gap-2 {
            gap: 0.5rem;
        }
    </style>
@endsection