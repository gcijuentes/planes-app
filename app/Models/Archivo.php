<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Archivo extends Model
{
    use HasFactory;
    protected $table = 'archivo';
    protected $primaryKey = 'id';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'url',
        'nombre',
    ];

    //relacion many to one

    //relacion One to many
    public function planes(){
        return $this->hasMany('App\Models\plan','archivo_id');
    }


}
