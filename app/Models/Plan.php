<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;
    protected $table = 'plan';
    protected $primaryKey = 'id';

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'codigo',
        'nombre',
        'valor_base_uf'
        ,'isapre_id',
        'idTipoPlan'
        ,'zona_id'
        ,'archivo_id'
        //,'idPrestador'
    ];

    //relacion One to many


       //relacion One to many
    public function archivo(){
        return $this->belongsTo('App\Models\Archivo','archivo_id');
    }

    //relacion many to many
    public function prestadores(){
        return $this->hasMany('App\Models\Prestador','plan_id');
    }

    //relacion One to many
    public function coberturas(){
        return $this->hasMany('App\Models\Cobertura','idPlan');
    }

    //relacion One to many
    public function isapre(){
        return $this->belongsTo('App\Models\Isapre','isapre_id');
    }

        //relacion many to many
    public function zonas(){
        return $this->belongsToMany('App\Models\Zona','plan_has_zona','plan_id','zona_id');
    }

}
