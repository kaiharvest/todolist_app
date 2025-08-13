<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'is_finish',
        'priority',
        'due_date'
    ];

    protected $casts = [
        'is_finish' => 'boolean',
        'due_date' => 'date'
    ];

    // Priority levels berdasarkan kesulitan
    const PRIORITY_LEVELS = [
        'low' => 1,
        'normal' => 2,
        'high' => 3,
        'urgent' => 4
    ];

    // Fungsi untuk mendapatkan priority berdasarkan due_date
    public function getCalculatedPriorityAttribute()
    {
        if (!$this->due_date) {
            return $this->priority;
        }

        $daysUntilDue = Carbon::now()->diffInDays($this->due_date, false);
        
        // Jika tersisa 3 hari atau kurang, tingkatkan priority
        if ($daysUntilDue <= 3 && $daysUntilDue >= 0) {
            return 'urgent';
        }
        
        // Jika sudah lewat due date
        if ($daysUntilDue < 0) {
            return 'urgent';
        }

        return $this->priority;
    }

    // Fungsi untuk mendapatkan nilai numerik priority untuk sorting
    public function getPriorityValueAttribute()
    {
        $calculatedPriority = $this->getCalculatedPriorityAttribute();
        return self::PRIORITY_LEVELS[$calculatedPriority] ?? 2;
    }

    // Fungsi untuk mendapatkan status due date
    public function getDueDateStatusAttribute()
    {
        if (!$this->due_date) {
            return null;
        }

        $daysUntilDue = Carbon::now()->diffInDays($this->due_date, false);
        
        if ($daysUntilDue < 0) {
            return 'overdue';
        } elseif ($daysUntilDue <= 3) {
            return 'urgent';
        } elseif ($daysUntilDue <= 7) {
            return 'approaching';
        }
        
        return 'normal';
    }

    // Scope untuk mengurutkan berdasarkan priority
    public function scopeOrderByPriority($query)
    {
        return $query->get()->sortByDesc(function ($task) {
            return $task->priority_value;
        });
    }

    // Scope untuk task yang belum selesai
    public function scopeIncomplete($query)
    {
        return $query->where('is_finish', false);
    }

    // Scope untuk task yang sudah selesai
    public function scopeCompleted($query)
    {
        return $query->where('is_finish', true);
    }

    // Scope untuk task yang mendekati due date
    public function scopeUpcoming($query)
    {
        return $query->whereNotNull('due_date')
                    ->where('due_date', '>=', Carbon::now())
                    ->where('due_date', '<=', Carbon::now()->addDays(7));
    }

    // Scope untuk task yang overdue
    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')
                    ->where('due_date', '<', Carbon::now())
                    ->where('is_finish', false);
    }
}