<?php

namespace App\Http\Controllers;

use App\Models\Cuenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CuentaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $per_page = $request->per_page;
        $cuentas = DB::table('users as usr')
            ->select(
                'usr.id as user_id', 'usr.name as titular',
                'cue.id as cuenta_id', 'cue.valor'
            )
            ->leftjoin('cuentas AS cue', 'cue.user_id', '=', 'usr.id')
            ->paginate($per_page);
        
        return response()->json($cuentas);
    }

    public function get_users()
    {
        $users = DB::table('users as usr')
            ->select('usr.id', 'usr.name')
            ->leftjoin('cuentas AS cue', 'cue.user_id', '=', 'usr.id')
            ->where('cue.user_id', "=", null)
            ->get();
        
        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required|min:1',
            'valor' => 'required|min:0'
        ]);

        $cuenta = Cuenta::create($request->all());
        return response()->json([
            'cuenta' => $cuenta
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Cuenta  $cuenta
     * @return \Illuminate\Http\Response
     */
    public function show(Cuenta $cuenta)
    {
        return response()->json($cuenta, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Cuenta  $cuenta
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Cuenta $cuenta)
    {
        $this->validate($request, [
            'cuenta_id' => 'required|numeric|min:1',
            'valor' => 'required|numeric|min:1|max:100000000'
        ]);

        $affected = DB::table('cuentas')
              ->where('id', $request->cuenta_id)
              ->update(['valor' => $cuenta->valor + $request->valor]);
        return response()->json([
            'affected' => $affected
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cuenta  $cuenta
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Cuenta $cuenta)
    {
        $this->validate($request, [
            'cuenta_id' => 'required|numeric|min:1',
            'valor' => "required|numeric|min:1|max:{$cuenta->valor}"
        ]);

        $affected = DB::table('cuentas')
              ->where('id', $request->cuenta_id)
              ->update(['valor' => $cuenta->valor - $request->valor]);
        return response()->json([
            'affected' => $affected
        ], 200);
    }
}
