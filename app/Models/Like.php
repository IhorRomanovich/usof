<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'p_id',
        'c_id',
        'author_id',
        'islike',
        // 'like_date',
    ];

    // /**
    //  * The attributes that should be cast to native types.
    //  *
    //  * @var array
    //  */
    // protected $casts = [
    //     'like_date' => 'datetime',
    // ];
}
