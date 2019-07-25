<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Forms extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'parameters', 'categories', 'values',
    ];

    protected $casts = [
        'parameters' => 'array',
        'categories'=>  'array',
        'values'=> 'array',
    ];
}
