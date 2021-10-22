<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Plan;


class SearchController extends Controller
{

    public function getPlanes(){


        $planes = DB::table('plan')->get();
        //var_dump($planes);
        //return 'Holi';

        return response()->json(array('planes'=>$planes,'status'=>'success'),200);


    }

    public function getCardPlanes(){

        $salida= array();
        $planes = Plan::with('prestadores','coberturas')->get();

      // var_dump($planesEntidad);
       //die();
        //return 'Holi';

        return response()->json(array('planesCard'=>$planes,'status'=>'success'),200);


    }

    //
}
