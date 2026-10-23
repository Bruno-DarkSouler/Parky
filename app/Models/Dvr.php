<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;

#[Fillable(['user_id', 'lat', 'lon', 'ip', 'port', 'user', 'password', 'status'])]
#[Hidden(['password'])]
class Dvr extends Model
{
    use HasUuids;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cameras()
    {
        return $this->hasMany(Camera::class);
    }
}
