<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['camera_id', 'geometry', 'total_length_meters', 'homography_matrix', 'status'])]
class CameraZone extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return [
            'geometry' => 'array',
            'homography_matrix' => 'array',
            'total_length_meters' => 'decimal:2',
        ];
    }

    public function camera()
    {
        return $this->belongsTo(Camera::class);
    }

    public function occupancyLogs()
    {
        return $this->hasMany(OccupancyLog::class);
    }
}
