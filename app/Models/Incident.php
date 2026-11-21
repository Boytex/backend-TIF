<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    public const STATUS_OPEN = 'ABIERTA';
    public const STATUS_IN_PROGRESS = 'EN PROCESO';
    public const STATUS_RESOLVED = 'RESUELTA';

    protected $fillable = [
        'title',
        'description',
        'status',
        'user_id',
        'technician_id',
        'category_id',
        'resolution',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->orderBy('created_at');
    }
}
