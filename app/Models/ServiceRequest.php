<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceRequest extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'Roads & Transport',
        'Water & Sanitation',
        'Waste Collection',
        'Street Lighting',
        'Public Spaces',
        'Other',
    ];

    public const STATUSES = [
        'Pending',
        'In Progress',
        'Resolved',
    ];

    protected $fillable = [
        'request_number',
        'user_id',
        'citizen_name',
        'category',
        'location',
        'description',
        'image_path',
        'status',
    ];

    public function citizen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function statusUpdates(): HasMany
    {
        return $this->hasMany(ServiceRequestStatusUpdate::class)->with('changedBy:id,name')->latest();
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $user->isAdmin() ? $query : $query->where('user_id', $user->id);
    }

    public function displayCitizenName(): string
    {
        return $this->citizen?->name ?? $this->citizen_name ?? '—';
    }
}
