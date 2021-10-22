<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zona extends Model
{
    use HasFactory;

    protected $table = 'zona';
    protected $primaryKey = 'id';

        /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre'
    ];

    //relacion many to many
    public function planes(){
        return $this->belongsToMany('App\Models\Plan','plan_has_zona','zona_id','plan_id');
    }
}
