<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cobertura extends Model
{
    use HasFactory;

    protected $table = 'cobertura';
    protected $primaryKey = 'id';
    public $timestamps = false;

        /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre',
        'detalle',
        'idPlan',
        'porcentajeCobertura'
    ];

        //relacion many to one
        public function plan(){
            return $this->belongsTo('App\Models\Plan','idPlan');
        }


}
