<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;

    protected $table = 'collection';

    protected $fillable = ['thumb', 'name_en', 'name_vi', 'slug', 'user_id', 'watched'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
