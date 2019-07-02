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

    public function create(Request $request){
        #RECOGER LOS DATOS POR POST
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if(!empty($params_array)){
            #VALIDAR DATOS
            $validate = \Validator::make($params_array, [
                'clave_interna' => 'required',
                'nombre' => 'required',
                'estado' => 'required',
                'codigo_postal' => 'required',
                'lat' => 'required',
                'lng' => 'required',
                'estatus' => 'required',
                'domicilio' => 'required',
                'zona' => 'required'
            ]);

            if($validate->fails()){
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'no se ha econtrado alguna relacion'
                );
            }else{
                
               
                #GUARDAR ARTICULO
	    		$distribuidor = new Distribuidor();
	    		$distribuidor->clave_interna = $params->clave_interna;
	    		$distribuidor->nombre = $params->nombre;
	    		$distribuidor->estado = $params->estado;
	    		$distribuidor->codigo_postal = $params->codigo_postal;
                $distribuidor->lat = $params->lat;
                $distribuidor->lng = $params->lng;
                $distribuidor->estatus = $params->estatus;
                $distribuidor->domicilio = $params->domicilio;
                $distribuidor->zona = $params->zona;
                
	    		$distribuidor->save();
                

                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'distribuidores' => $distribuidor
                );                
            }
        }else{
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'no has enviado datos vacios'
            );
        }
        return response()->json($data, $data['code']);
    }
}   
