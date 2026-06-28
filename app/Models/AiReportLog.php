<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiReportLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'prompt',
        'generated_sql',
        'is_successful',
        'error_message',
        'execution_time_ms',
    ];
}
