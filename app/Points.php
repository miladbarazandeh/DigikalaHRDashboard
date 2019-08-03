<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Points extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'employee_id', 'appraiser_id', 'parameter_id', 'point',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
