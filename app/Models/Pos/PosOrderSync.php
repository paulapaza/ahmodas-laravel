<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Pos\PosOrder;
use App\Enums\SyncStatus;

class PosOrderSync extends Model
{
    use HasFactory;

    protected $table = 'pos_order_syncs';

    protected $fillable = [
        'pos_order_id',
        'payload',
        'status',
        'error_message',
        'error_details',
        'attempts',
    ];

    protected $casts = [
        'payload' => 'array',
        'error_details' => 'array',
        'status' => SyncStatus::class,
    ];

    public function posOrder()
    {
        return $this->belongsTo(PosOrder::class, 'pos_order_id');
    }
}
