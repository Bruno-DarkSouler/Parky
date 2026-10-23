<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['user_id', 'plate', 'brand', 'model', 'year', 'color', 'width_meters', 'length_meters', 'status'])]
class Car extends Model
{
    use HasUuids;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
