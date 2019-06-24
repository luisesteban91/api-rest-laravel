<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;
use App\Helpers\JwtAuth;


class PostController extends Controller
{

    public function __construct(){
		$this->middleware('api.auth', ['except'=>[
			'index',
			'show',
			'getImage',
			'getPostByCategory',
			'getPostsByUser'
		]]);
	}

	public function index(){
		$posts = Post::all()->load('category'); //load('') para llamar los datos de tabla relacionada

		return response()->json([
			'code' => 200,
			'status' => 'success',
			'posts' => $posts
 		], 200);
	}

	public function show($id){
		$post = Post::find($id)->load('category')
							   ->load('user');

		if(is_object($post)){
			$data = array(
                'code' => 200,
                'status' => 'success',
                'posts' => $post
            );
		}else{
			$data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'la entrada no existe'
            );
		}
		return response()->json($data, $data['code']);
	}

	public function store(Request $request){
		#RECOGER DATOS DE POST
		$json = $request->input('json', null);
		$params = json_decode($json);
		$params_array = json_decode($json, true);

		if(!empty($params_array)){
			#CONSEGUIR USUARIO IDENTIFICADO
			#CONSEGUIR USUARIO ESTE IDETIFICADO
        	$user = $this->getIdentity($request);

	    	#VALIDAR LOS DATOS
	    	$validate = \Validator::make($params_array, [
	    		'title' => 'required',
	    		'content' => 'required',
	    		'category_id' => 'required',
	    		'image' => 'required'
	    	]);

	    	#GUARDAR ARTICULO
	    	if($validate->fails()){
	    		$data = array(
	    			'code' => 400,
	    			'status' => 'error',
	    			'message' => 'no se a guardado el Post, faltan datos'
	    		);
	    	}else{
	    		#GUARDAR ARTICULO
	    		$post = new Post();
	    		$post->user_id = $user->sub;
	    		$post->category_id = $params->category_id;
	    		$post->title = $params->title;
	    		$post->content = $params->content;
	    		$post->image = $params->image;
	    		$post->save();

	    		$data = array(
	    			'code' => 200,
	    			'status' => 'success',
	    			'categoria' => $post
	    		);
	    	}
	    }else{
	    	$data = array(
	    		'code' => 400,
	    		'status' => 'error',
	    		'message' => 'no as enviado los datos correctamente'
	    	);
	    }	

		#DEVOLVER RESPUESTA
		return response()->json($data, $data['code']);
	}

	public function update($id, Request $request){
		#Recoger los datos por Post
		$json = $request->input('json', null);
		$params = json_decode($json);
		$params_array = json_decode($json, true);

		//datos para devolver por default
		$data = array(
			'code' => 400,
			'status' => 'error',
			'message' => 'Datos enviados icorrectos'
		);

		if(!empty($params_array)){
			#Validar Datos
			$validate = \Validator::make($params_array, [
				'title' => 'required',
				'content' => 'required',
				'category_id' => 'required'
			]);

			if($validate->fails()){
				$data['errors'] = $validate->errors();
				return response()->json($data, $data['code']);
			}
			#ELIMINAR LO QUE NO QUEREMOS ACTUALIZAR
			unset($params_array['id']);
			unset($params_array['user_id']);
		    unset($params_array['created_at']);
		    unset($params_array['user']);

		    #CONSEGUIR USUARIO ESTE IDETIFICADO
        	$user = $this->getIdentity($request);

			#ACTUALIZAR EL REGISTRO EN CONCRETO
			$post = Post::where('id', $id)
						->where('user_id', $user->sub)
						->first(); //solo el propio usuario puede borrar su post

			if(!empty($post) && is_object($post)){
				//Actualizar eñ registro en concreto
				$post ->update($params_array);

				#DEVOLVER RESPEUSTA
				$data = array(
					'code' => 200,
					'status' => 'success',
					'post' => $post,
					'changes' => $params_array
				);
			}
			/*$where =[
				'id' => $id,
				'user_id' => $user->sub
			];

		    $post = Post::updateOrCreate($where, $params_array);//updateOrCreate obtiene los datos actuzalizados los antes de actulizar
			*/
		}
		return response()->json($data, $data['code']);
	}
	public function destroy($id, Request $request){
		#CONSEGUIR USUARIO ESTE IDETIFICADO
        $user = $this->getIdentity($request);

		#CONSEGUIR EL REGISTRO
		$post = Post::where('id', $id)
						->where('user_id', $user->sub)
						->first(); //solo el propio usuario puede borrar su post

		if(!empty($post)){
			#BORRARLO
			$post->delete();

			#DEVOLVER ALGO
			$data = array(
				'code' => 200,
				'status' => 'success',
				'post' => $post
			);
		}else{
			$data = array(
				'code' => 400,
				'status' => 'error',
				'message' => 'el Post no existe'
			);
		}
		return response()->json($data, $data['code']);
	}

	#METODO PARA IDENTIFICAR AL PROPIO USAURIO
	public function getIdentity(Request $request){
		#CONSEGUIR USUARIO ESTE IDETIFICADO
        $jwtAuth = new \JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
	}

	public function upload(Request $request){
		#RECOGER LA IMAGEN DE LA PETICION
		$image = $request->file('file0');

		#VALIDAR IMAGEN
		$validate = \Validator::make($request->all(), [
			'file0' =>'required|image|mimes:jpg,jpeg,png,gif'
		]);

		#GUARDAR LA IMAGEN
		if(!$image || $validate->fails()){
			$data = [
				'code' => 400,
				'status' => 'error',
				'message' => 'error al subir la imagen'
			];
		}else{
			$image_name = time().$image->getClientOriginalName();
			\Storage::disk('images')->put($image_name, \File::get($image));
			
			$data = [
				'code' => 200,
				'status' => 'success',
				'image' => $image_name
			];
		}

		return response()->json($data, $data['code']);
	}

	public function getImage($filename){
		#COMPROBAR SI EXISTE EL FICHERO
		$isset = \Storage::disk('images')->exists($filename);

		if($isset){
			#CONSEGUIR LA IMAGEN
			$file = \Storage::disk('images')->get($filename);
			#DEVOLVER LA IMAGEN
			return new Response($file, 200);
		}else{
			$data = [
				'code' => 404,
				'status' => 'error',
				'message' => 'la imagen no existe'
			];
		}
		
		return response()->json($data, $data['code']);

	}

	public function getPostByCategory($id){
		$posts = Post::where('category_id', $id)->get();

		return response()->json([
			'status' => 'success',
			'posts' => $posts
		], 200);
	}

	public function getPostsByUser($id){
		$posts = Post::where('user_id', $id)->get();

		return response()->json([
			'status' => 'success',
			'posts' => $posts
		], 200);
	}
}	
