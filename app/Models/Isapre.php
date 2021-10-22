<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Isapre extends Model
{
    use HasFactory;

    protected $table = 'isapre';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nombre'
        ,'rut'
        ,'archivo_id'
    ];

    //relacion One to many
    public function planes(){
        return $this->hasMany('App\Models\Plan','isapre_id');
    }

    public function archivo(){
        return $this->belongsTo('App\Models\Archivo','archivo_id');
    }
}
