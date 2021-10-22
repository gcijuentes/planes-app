<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestador extends Model
{
    use HasFactory;

    protected $table = 'prestador';
    protected $primaryKey = 'id';
    public $timestamps = false;

        /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre',
        'rut',
        'plan_id'
    ];

    //relacion many to many
    public function plan(){
        return $this->belongsTo('App\Models\Plan','plan_id');
    }

}
