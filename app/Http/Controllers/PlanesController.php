<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlanResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Plan;
use App\Http\Controllers\ControllerBase;
use App\Models\Cobertura;
use Illuminate\Database\QueryException;
use App\Models\Prestador;
use Illuminate\Support\Collection;

class PlanesController extends ControllerBase
{


    public function __construct()
    {
        //$this->middleware('client.credentials')->only(['index']);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {


        $planesList = Plan::with('prestadores','coberturas','archivo','isapre','isapre.archivo');

        $precioDesde = $request->query('precioDesde');
        $precioHasta = $request->query('precioHasta');

        $edades = $request->query('edades');
        $isCargas = $request->query('isCargas');
        //print_r($isCargas);
        //exit(1);
        // ya tenemos el array de edades y si es carga, falta hacer los calculos para filtrar
        //hay que basarse en el excel que envio marcelo


        $curl = curl_init();
        $opts = array('http' =>
                array(
                    'method'  => 'GET',
                    'timeout' => 55
                )
         );





        $context  = stream_context_create($opts);
        $jsonUF= file_get_contents('https://mindicador.cl/api',false,$context );
        $objUF = json_decode($jsonUF);
        $valorUf = $objUF->uf->valor;

        $precioDesdeUF = $precioDesde / $valorUf;
        $precioHastaUF = $precioHasta / $valorUf;

        /*if($precioDesde!=''){
            $planesList = $planesList->where('valor_base_uf', '>=', $precioDesdeUF);
        }
        if($precioHasta!=''){
            $planesList = $planesList->where('valor_base_uf', '<=', $precioHastaUF);
        }*/

        $planesListFinal = [];
        $planes = $planesList->paginate(1500);

        $i = 0;
        foreach ($planes as $key) {


            $valorPesos = ($key->valor_base_uf * $valorUf) + ($key->ges * $valorUf);

                 //print_r($valorPesos);
                // print_r('----');
                // print_r($precioHasta);

            if($precioDesde!='' && $precioHasta!='' ){
                 if($valorPesos<=$precioHasta && $valorPesos>= $precioDesde ){
                    $planesListFinal[$i] = $key;
                    $planesListFinal[$i]->valorPesos =  $valorPesos;
                    $i++;
                 }
            }else if($precioDesde=='' && $precioHasta!='' ){ //solo hasta
                if($valorPesos<=$precioHasta ){
                   $planesListFinal[$i] = $key;
                   $planesListFinal[$i]->valorPesos =  $valorPesos;
                   $i++;
                }
           }else if($precioDesde!='' && $precioHasta=='' ){ //solo desde
                if($valorPesos>= $precioDesde ){
                $planesListFinal[$i] = $key;
                $planesListFinal[$i]->valorPesos =  $valorPesos;
                $i++;
                }
            }else{
                $planesListFinal[$i] = $key;
                $planesListFinal[$i]->valorPesos =  $valorPesos;
                $i++;
            }


        }


        $meta['current_page'] = 1;
        $meta['last_page'] = 1;
        $meta['per_page'] = 10;
        $meta['to'] = $i;
        $meta['total'] = $i;


        return $this->sendResponse($planesListFinal, 'OK',$meta);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $plan = new Plan();
        $plan->codigo = $request->codigo;
        $plan->nombre = $request->nombre;
        $plan->isapre_id = $request->isapreId;
        $plan->idTipoPlan = $request->tipoPlanId;
        $plan->zona_id = $request->zonaId;
        $plan->valor_base_uf = $request->valorBaseUF;
        $plan->archivo_id = 3;// 3 =  banmedica
       // $plan->prestadores()->attach($request->prestadores);

        if($plan->save()){

            $plan->prestadores()->sync($request->prestadores);
            //coberturas
            foreach ($request->coberturas as $cobertura) {
                if(isset($cobertura['id'])){
                    //dd($cobertura);
                    $updateCobertura = Cobertura::find($cobertura['id']);
                    $updateCobertura->nombre = $cobertura['nombre'];
                    $updateCobertura->detalle = $cobertura['detalle'];
                    $updateCobertura->porcentajeCobertura = $cobertura['porcentajeCobertura'];

                    if(isset($cobertura['remove']) && !$cobertura['remove'] ){
                        $updateCobertura->save();
                        $updateCobertura->plan()->associate($plan);
                    }else{
                        $updateCobertura->plan()->dissociate();
                        $updateCobertura->delete();
                    }

                }else{
                    $newCobertura = new Cobertura();
                    $newCobertura->nombre = $cobertura['nombre'];
                    $newCobertura->detalle = $cobertura['detalle'];
                    $newCobertura->porcentajeCobertura = $cobertura['porcentajeCobertura'];
                    $newCobertura->idPlan = $plan->id;
                    $newCobertura->save();
                    $newCobertura->plan()->associate($plan);

                }
            }

            $objPrestadores = $request->jsonPrestadores;

            foreach ($objPrestadores as $objPrestador) {

                //$resultPrestador =  Prestador::where("name" , '=', $objPrestador->name)->first();
                //if ($resultPrestador === null) {
                    $prestador = new Prestador();
                    $prestador->nombre = $objPrestador->nombre;
                    $prestador->porc_hosp = $objPrestador->porc_hosp;
                    $prestador->porc_amb = $objPrestador->porc_amb;
                    $prestador->copago_amb = $objPrestador->copago_amb;
                    $prestador->urg_cob_amount = $objPrestador->urg_cob_amount;
                    $prestador->tipo_establecimiento = $objPrestador->tipo_establecimiento;
                    $prestador->complejidad_asistencial = $objPrestador->complejidad_asistencial;
                    $prestador->save();
                    $prestador->plan()->associate($plan);
                 //}

                 /*
                        "nombre": "Clínica Santa María",
                        "abv": "Cl. Santa María",
                        "rut": 90753000,
                        "porc_hosp": 100,
                        "porc_amb": 90,
                        "copago_amb": null,
                        "tipo_prestador": 1,
                        "urg_cob_amount": 90,
                        "urg_cob_unit": "%",
                        "ptje_cta_amb_preferente": 8.908547753259601,
                        "ptje_cta_hosp_preferente": 9.827945800605555,
                        "tipo_establecimiento": "Atención Cerrada",
                        "complejidad_asistencial": "Alta Complejidad"
                 */

                # code...
            }


            $planUpdated = Plan::with('prestadores','coberturas')->find($plan->id);
            return $this->sendResponse($planUpdated, 'updated OK');
        }else{
            $data = array(
                'status' => 'error',
                'code' => '400',
                'message' => 'Usuario no creado',
            );
            return response()->json($data,400);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $plan = Plan::with('prestadores','coberturas','archivo','isapre')->find($id);

        if (is_null($plan)) {
            return $this->sendError('Plan not found');
        }
        return $this->sendResponse($plan, 'plan');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        try {

            $plan = Plan::with('prestadores','coberturas')->find($id);
            if (is_null($plan)) {
                return $this->sendError('Plan not found');
            }

            $plan->codigo = $request->codigo;
            $plan->nombre = $request->nombre;
            $plan->valor_base_uf = $request->valorBaseUf;
            $plan->isapre_id = $request->isapreId;
            $plan->idTipoPlan = $request->tipoPlanId;
            $plan->zona_id = $request->zonaId;

            $plan->prestadores()->sync($request->prestadores);


            if($plan->save()){

                    //coberturas
                    foreach ($request->coberturas as $cobertura) {
                        if(isset($cobertura['id'])){
                            $updateCobertura = Cobertura::find($cobertura['id']);
                            $updateCobertura->nombre = $cobertura['nombre'];
                            $updateCobertura->detalle = $cobertura['detalle'];
                            $updateCobertura->porcentajeCobertura = $cobertura['porcentajeCobertura'];

                            if(isset($cobertura['remove']) && !$cobertura['remove'] ){
                                $updateCobertura->save();
                                $updateCobertura->plan()->associate($plan);
                            }else{
                                $updateCobertura->plan()->dissociate();
                                $updateCobertura->delete();
                            }

                        }else{

                            $newCobertura = new Cobertura();
                            $newCobertura->nombre = $cobertura['nombre'];
                            $newCobertura->detalle = $cobertura['detalle'];
                            $newCobertura->porcentajeCobertura = $cobertura['porcentajeCobertura'];
                            $newCobertura->idPlan = $plan->id;
                            $newCobertura->save();
                            $newCobertura->plan()->associate($plan);
                        }
                    }

                $planUpdated = Plan::with('prestadores','coberturas')->find($id);
                return $this->sendResponse($planUpdated, 'updated OK');
            }else{
                return $this->sendError('Error on updatee',[],'400-1');
            }
        } catch(QueryException $e){
            $error[]=$e;
            return $this->sendError('Error on update',$error,400);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $plan =  Plan::findOrFail($id);
        if($plan->delete()){
            return new PlanResource($plan);
        }else{
            $data = array(
                'status' => 'error',
                'code' => '400-1',
                'message' => 'Error al eliminar plan',
            );
            return response()->json($data,400);
        }
    }
}
