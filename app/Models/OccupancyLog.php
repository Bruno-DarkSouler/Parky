<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['camera_zone_id', 'detected_objects_coords'])]
class OccupancyLog extends Model
{
    use HasUuids;
    
    public $timestamps = false;
    
    protected $table = 'occupancy_log';

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->created_at)) {
                $model->created_at = now();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'detected_objects_coords' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function cameraZone()
    {
        return $this->belongsTo(CameraZone::class);
    }
}
