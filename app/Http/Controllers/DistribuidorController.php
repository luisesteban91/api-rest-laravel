<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Distribuidor;
use App\DBFunctions;

Class DistribuidorController extends Controller {
    
    public function __construct(){
        $this->middleware('api.auth', ['except'=>['index','show']]);
    }

    #METODO PARA MOSTRAR TODOS LOS DISTRIBUIDORES
    public function index(){
        $distribuidores = Distribuidoes::all();

        return response()->json([
            'code' => 200,
            'status' => 'succes',
            'distribuidoes' => $distribuidores
        ]);
    }

    #METODO PARA MOSTRAR LOS DISTRIBUIDORES CERCANOS
    public function show(Request $request){

        #RECOGER LOS DATOS POR POST
    	$json = $request->input('json', null);
        $params_array = json_decode($json, true);
        
        if(!empty($params_array)){
            #VALIDAR DATOS
            $validate = \Validator::make($params_array, [
                'lat' => 'required',
                'lng' => 'required'
            ]);

            if($validate->fails()){
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'no se ha econtrado alguna relacion'
                );
            }else{
                
                $lat = $params_array['lat'];
                $long = $params_array['lng'];

                $distribuidores = DBFunctions::getDistribuidores($lat, $long);

                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'distribuidores' => $distribuidores
                );                
            }
        }else{
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'no has enviado datos vacios'
            );
        }

        //var_dump($data);die();

        return response()->json($data, $data['code']);
        
    }
}   
