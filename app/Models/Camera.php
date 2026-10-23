<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['dvr_id', 'channel', 'name', 'status'])]
class Camera extends Model
{
    use HasUuids;

    public function dvr()
    {
        return $this->belongsTo(Dvr::class);
    }

    public function cameraZones()
    {
        return $this->hasMany(CameraZone::class);
    }
}
