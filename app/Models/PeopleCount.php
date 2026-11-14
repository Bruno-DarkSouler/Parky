<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeopleCount extends Model
{
    protected $table = 'people_counts';

    protected $fillable = [
        'cantidad',
        'fecha_hora',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->fecha_hora)) {
                $model->fecha_hora = now();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'fecha_hora' => 'datetime',
        ];
    }
}

