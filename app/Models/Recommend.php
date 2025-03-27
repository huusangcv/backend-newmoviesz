<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
class Recommend extends Model
{
    use HasFactory,HasApiTokens;

    protected $table = 'recommend';

    protected $fillable = ['name_en', 'name_vi','thumb', 'slug','year', 'rate','vote_count','time'];
}
