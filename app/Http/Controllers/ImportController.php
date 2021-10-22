<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\PlanesController;
use App\Models\Prestador;
use App\Models\Plan;
use App\Models\Cobertura;
use App\Models\Zona;
use App\Models\Archivo;

class ImportController extends Controller
{


    protected $planesControler;
    public function __construct(PlanesController $planesControler)
    {
       $this->planesControler = $planesControler;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $curl = curl_init();

        $opts = array('http' =>
        array(
            'method'  => 'GET',
            'timeout' => 55
        )
         );

        $context  = stream_context_create($opts);

        //$json = file_get_contents('https://tu7porciento.cl/dataMining/getPlanes.php?params=F--0.9--x|I--1,2,3,4,5,6|T--1,2,3|M--0,1500000|U--29,762.00|B--1|O--1|S--||C-');

        //$json = file_get_contents('https://queplan.cl/api/buscarplanes/comparar/mLZWH3ggnsjqCmttJVGLz/metadata');



        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://queplan.cl/api/buscarplanes/comparar/rXS3902SDF-UaAJ1xp210/metadata',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{"precio":40.045558497050145,"preciomin":0.6674259749508358,"carga":[],"cotizante":[{"sexo":"Masculino","ingreso":1037000,"edad":25}],"filtros":{"cobertura":[{"nombre":"Con parto","seleccionado":true,"elementosenbusqueda":172},{"nombre":"Reducido en parto","seleccionado":true,"elementosenbusqueda":0}],"comerciali":[{"nombre":"Comercializable","seleccionado":true},{"nombre":"No Comercializable","seleccionado":true}],"modalidadplan":[{"nombre":"Libre elección","seleccionado":true,"elementosenbusqueda":0},{"nombre":"Preferentes","seleccionado":true,"elementosenbusqueda":172},{"nombre":"Cerrados","seleccionado":true,"elementosenbusqueda":0}],"isapres":["80"],"prestador":null,"region":null,"filtroCobertura":{}},"pagina":2,"cantidadElementos":50,"order":"DESC"}',
            CURLOPT_HTTPHEADER => array(
              'Content-Type: application/json',
              'Cookie: 15e5a1eb-6326-4e0e-8ee8-a621db008bc5=s%3A05b6f3af-ffb3-44fb-87aa-4e17be7ec86c.DRWCbiNR%2B7gLHPJ0fCxN93Pv5%2BC7l%2FCibCZuymzrYXo; 6ef1ccca-b0c1-41ce-9315-a0675acaa5dd=s%3Ab3748c9e-de0d-417e-a20a-91750b5026f2.zM6%2BbH9XWlf4LXd9WRPdBunC2AchUrk3tVEIgQnZGnM; 828bb482-dd07-4bbe-9a80-715aaf2ada2d=s%3A554b2db1-d841-446a-9ff2-f60899955a8b.%2BZR5zAmLZTsF4rmKnBGxE%2BDAuihvEB%2BwytPLnmw81No; a2f06a96-7f92-490f-bea1-a514124761ec=s%3A7dfabb47-147f-45f8-b5b3-70740a1f3546.R4%2BBkhTfbRr8Z%2FLpueCemMwahlPNyoUPiqEjacvyDFg'
            ),
          ));


          $response = curl_exec($curl);

          //print_r($response);
          //exit(1);
          //curl_close($curl);

          $obj = json_decode($response);

          //echo $response;

        foreach ($obj->data as $key) {

           //print_r($key);
            //exit(1);
            $plan = new Plan();
            $plan->codigo = $key->codigoplan;
            $plan->nombre = $key->nombreplan;
            $plan->isapre_id = 7;//vidatres


            if($key->modalidadplan == 'Prestador Preferente'){
                $plan->idTipoPlan = 3;
            }else if($key->modalidadplan == 'Libre elección'){
                $plan->idTipoPlan = 2;
            }else{//($key->tipo == 'Cerrado')
                $plan->idTipoPlan = 1;
            }


            $urlExternaPDF = 'https://tu7porciento.cl/pdf-planes/'.$plan->codigo.'.pdf';
                //voy a buscar el pdf
                //https://tu7porciento.cl/pdf-planes/13-PREFC4A-21.pdf

                if($this->get_http_response_code($urlExternaPDF) != "200"){
                    //echo "error al traer PDF url: ". $urlExternaPDF;
                    $archivoDefault = Archivo::find(1);
                            $plan->archivo_id = $archivoDefault->id;
                            $plan->archivo()->associate($archivoDefault);
                }else{
                    $file_name = $plan->codigo.'.pdf';
                    // dd(file_put_contents( public_path()."/".$file_name,file_get_contents('https://tu7porciento.cl/pdf-planes/'.$plan->codigo.'.pdf')));exit(1);

                    $pdfObtenido = file_get_contents($urlExternaPDF,false, $context);
                    if($pdfObtenido!=null){
                        if(!file_put_contents( public_path()."../pdf_planes/".$file_name,$pdfObtenido)) {

                            $archivoDefault = Archivo::find(1);
                            $plan->archivo_id = $archivoDefault->id;
                            $plan->archivo()->associate($archivoDefault);

                        }else{
                            $newarchivo = new Archivo();
                            $newarchivo->url = '/pdf_planes/'.$file_name;
                            $newarchivo->nombre = $file_name;
                            $newarchivo->save();


                            $plan->archivo_id = $newarchivo->id;
                            $plan->archivo()->associate($newarchivo);
                        }
                    }

                }

            //$plan->valor_base_uf = $key->planUF;
            //$plan->archivo_id = 3;// 3 =  banmedica

           // $plan->ges = $key->ges;

            //sacar base del siguiente link



            $jsonBaseUF = file_get_contents('https://queplan.cl/api/buscarplanes/cargasycotizantes/?codigoplan=%22'.$key->codigoplan.
            '%22&cotizante=%5B%7B%22sexo%22%3A%22Masculino%22%2C%22edad%22%3A60%2C%22ingreso%22%3A1700000%7D%5D&carga=%5B%5D',false,$context );
            if($jsonBaseUF!=null){
                $objBaseUf = json_decode($jsonBaseUF);

                if($objBaseUf!=null  && sizeof($objBaseUf)>0){
                    $plan->valor_base_uf = $objBaseUf[0]->preciobasef;
                    $plan->ges = $objBaseUf[0]->precioges;
                   // print_r($plan);exit(1);
                }
            }



            if($plan->save()){

                //asociacion con regiones
                //logica para las zonas
                $arrayRegiones = str_split($key->regioncomer);
                $index = 1;
                foreach($arrayRegiones as $rg){
                    if($rg ==='S'){
                        $zona = Zona::find($index);
                        $zona->planes()->attach($plan->id);
                    }
                    $index++;
                }
                //asociacion con regiones



                $newCobertura = new Cobertura();
                $newCobertura->nombre = 'Hospitalaria';
                $newCobertura->detalle = 'Hospitalaria';
                $newCobertura->porcentajeCobertura = $key->porc_hosp_le;
                $newCobertura->idPlan = $plan->id;
                $newCobertura->save();
                $newCobertura->plan()->associate($plan);

                $newCobertura = new Cobertura();
                $newCobertura->nombre = 'Ambulatoria';
                $newCobertura->detalle = 'Ambulatoria';
                $newCobertura->porcentajeCobertura = $key->porc_amb_le;
                $newCobertura->idPlan = $plan->id;
                $newCobertura->save();
                $newCobertura->plan()->associate($plan);

                $newCobertura = new Cobertura();
                $newCobertura->nombre = 'Urgencia';
                $newCobertura->detalle = 'Urgencia';
                $newCobertura->porcentajeCobertura = $key->porc_urg_le;
                $newCobertura->idPlan = $plan->id;
                $newCobertura->save();
                $newCobertura->plan()->associate($plan);


                if($key->preferentes!=null){
                    foreach ($key->preferentes as $prestador ) {
                        $newPrestador = new Prestador();
                        $newPrestador->nombre = $prestador->nombre;
                        $newPrestador->rut = $prestador->rut;
                        $newPrestador->urg_cob_amount = $prestador->urg_cob_amount;
                        $newPrestador->ptje_cta_amb_preferente = $prestador->ptje_cta_amb_preferente;
                        $newPrestador->ptje_cta_hosp_preferente = $prestador->ptje_cta_hosp_preferente;
                        $newPrestador->plan_id = $plan->id;
                        $newPrestador->save();
                        $newPrestador->plan()->associate($plan);

                    }
                }


                $urlExternaPDF = 'https://tu7porciento.cl/pdf-planes/'.$plan->codigo.'.pdf';
                //voy a buscar el pdf
                //https://tu7porciento.cl/pdf-planes/13-PREFC4A-21.pdf

            /*    if($this->get_http_response_code($urlExternaPDF) != "200"){
                    echo "error";
                }else{
                    $file_name = $plan->codigo.'.pdf';
                    // dd(file_put_contents( public_path()."/".$file_name,file_get_contents('https://tu7porciento.cl/pdf-planes/'.$plan->codigo.'.pdf')));exit(1);

                    $pdfObtenido = file_get_contents($urlExternaPDF,false, $context);
                    if($pdfObtenido!=null){
                        if(!file_put_contents( public_path()."/pdf_planes/".$file_name,$pdfObtenido)) {
                            echo "File downloading failed.";
                        }else{
                            $newarchivo = new Archivo();
                            $newarchivo->url = '/pdf_planes/'.$file_name;
                            $newarchivo->nombre = $file_name;
                            $newarchivo->save();
                            //$plan->archivo()->associate($newarchivo);
                        }
                    }

                }*/




                //dd(1111111111111111111111);exit(1);
                //$planUpdated = Plan::with('prestadores','coberturas')->find($plan->id);
                //return $this->sendResponse($planUpdated, 'Created OK');
            }else{
                $data = array(
                    'status' => 'error',
                    'code' => '400',
                    'message' => 'Plan no creado',
                );
                return response()->json($data,400);
            }




        }


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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    function get_http_response_code($url) {
        $headers = get_headers($url);
        return substr($headers[0], 9, 3);
    }

}
