<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthCheckLog extends Model
{
    protected $fillable = [
        'owner_uuid',
        'method',
        'path',
        'status_code',
        'overall_ok',
        'checks',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'checks' => 'array',
    ];
}
