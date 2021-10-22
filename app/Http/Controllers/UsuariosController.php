<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlanResource;
use Illuminate\Http\Request;
use App\Models\User;

class UsuariosController extends ControllerBase
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $usuariosList = User::where('id','>','0');
        //dd($usuariosList);
        return PlanResource::collection($usuariosList->paginate(10));
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
        //$json = $request->

        $usuario = new User();
        $usuario->name = $request->nombre;
        $usuario->surname = $request->apellido;
        $usuario->email = $request->email;
        $usuario->role = $request->rol;
        $usuario->password = hash('sha256',$request->password);

        //dd(1321321);
        $request->validate([
            'nombre' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string'
        ]);
        //dd(00);
       // dd( $usuario->email );
        $isset_user = User::where('email','=',$usuario->email)->first();
        //dd( $isset_user );
        if(is_null($isset_user) || count($isset_user)==0){
            //dd(11);
            $usuario->save();
           /*$newUSer = User::create([
                'name' => $usuario->name,
                'email' => $usuario->email,
                'surname' => $usuario->email,
                'surname' => $usuario->email,
                'password' => bcrypt($usuario->password)
            ]);*/
            return $this->sendResponse($usuario, 'user create OK');
        }else{
            //dd(22);
            return $this->sendError('User already exist');
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
}
