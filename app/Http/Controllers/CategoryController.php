<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Category;

class CategoryController extends Controller
{

	public function __construct(){
		$this->middleware('api.auth', ['except'=>['index','show']]);
	}

    public function index(){
    	$categories = Category::all();
    	return response()->json([
    		'code' => 200,
            'status' => 'success',
    		'categories' => $categories
    	]);
    }

    #METODO QUE MUESTRA SOLO UNA CATEGORIA CON EL ID
    public function show($id){
    	$category = Category::find($id);

    	if(is_object($category)){
    		$data = array(
                'code' => 200,
                'status' => 'success',
                'category' => $category
            );
    	}else{
    		$data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'la categoria no existe'
            );
    	}
    	return response()->json($data, $data['code']);
    }

    #	METODO PARA GUARDAR UNA CATEGORIA
    public  function store(Request $request){
    	#RECOGER LOS DATOS POR POST
    	$json = $request->input('json', null);
    	$params_array = json_decode($json, true);

    	if(!empty($params_array)){

	    	#VALIDAR LOS DATOS
	    	$validate = \Validator::make($params_array, [
	    		'name' => 'required'
	    	]);

	    	#GUARDAR LA CATEGORIA
	    	if($validate->fails()){
	    		$data = array(
	    			'code' => 400,
	    			'status' => 'error',
	    			'message' => 'no se a guardado la categoria.'
	    		);
	    	}else{
	    		$category = new Category();
	    		$category->name = $params_array['name'];
	    		$category->save();

	    		$data = array(
	    			'code' => 200,
	    			'status' => 'success',
	    			'categoria' => $category
	    		);
	    	}
	    }else{
	    	$data = array(
	    			'code' => 400,
	    			'status' => 'error',
	    			'message' => 'no as enviado ninguna categoria Nueva'
	    		);
	    }

    	#DEVOLVER RESULTADO
    	return response()->json($data, $data['code']);
    }

    public function update($id, Request $request){
    	#RECOGER LOS DATOS POR POST
    	$json = $request->input('json', null);
    	$params_array = json_decode($json, true);

    	if(!empty($params_array)){

	    	#VALIDAR LOS DATOS
	    	$validate = \Validator::make($params_array, [
	    		'name' =>'required'
	    	]);

	    	#QUITAR LO QUE NO QUIERO ACTUALIZAR
	    	unset($params_array['id']);
	    	unset($params_array['created_at']);

	    	#ACTUALIZAR EL REGISTRO CATEGORIA
	    	$category = Category::where('id', $id)->update($params_array);

	    	$data = array(
		    	'code' => 200,
		    	'status' => 'success',
		    	'category' => $params_array
		    );

    	}else{
    		$data = array(
	    		'code' => 400,
	    		'status' => 'error',
	    		'message' => 'no as enviado ninguna categoria'
	    	);
    	}
    	#DEVOLVER RESPUESTA
    	return response()->json($data, $data['code']);
    }
}
