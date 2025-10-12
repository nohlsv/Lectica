<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessLog extends Model
{
    protected $fillable = [
        'user_id', 'route', 'method', 'accessed_at'
    ];
    
    protected $casts = [
        'accessed_at' => 'datetime',
    ];
    
    public $timestamps = false;
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

