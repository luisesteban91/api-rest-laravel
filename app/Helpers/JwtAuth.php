<?php
	namespace App\Helpers;

	use Firebase\JWT\JWT;
	use Illuminate\Support\Facades\DB;
	use App\User;

	/**
	 * 
	 */
	class JwtAuth{

		public $key;

		public function __construct(){
			$this->key = 'esto_es_una_clave_super_secreta-99887766';
		}

		public function signup($email, $password, $getToken = null){
			#Buscar si existe el usuario con sus credenciales
			$user = User::where([
				'email'=> $email,
				'password' => $password
			])->first();

			#comprobar si son correctas(objecto)
			$signup = false;
			if(is_object($user)){
				$signup = true;
			}

			#Generar el token con los datos del usaurio identificado
			if($signup){
				$token = array(
					'sub' => $user->id,
					'email' => $user->email,
					'name' => $user->name,
					'surname' => $user->surname,
					'description' => $user->description,
					'image' => $user->image,
					'iat' => time(),
					'exp' => time() + (7 * 24 * 60 * 60)
				);

				$jwt = JWT::encode($token, $this->key, 'HS256');
				$decoded = JWT::decode($jwt, $this->key, ['HS256']);

				#devolver los datos decodificados o el token, en funcion de un parametro
				if(is_null($getToken)){
					$data = $jwt;
				}else{
					$data = $decoded;
				}


			}else{
				$data = array(
					'status' => 'Error',
					'message' => 'Login Incorrecto.'
				);
			}

			return $data;
		}

		public function checkToken($jwt, $getIndentity = false){
			$aut = false;

			try{
				$jwt = str_replace('"', '', $jwt);
				$decoded = JWT::decode($jwt, $this->key, ['HS256']);
			}catch(\UnexpectedValueException $e){
				$aut = false;
			}catch(\DomainException $e){
				$aut = false;
			}

			if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){ //sub verificar si el id del usuario esta en el token
				$aut = true;
			}else{
				$aut =false;
			}

			if($getIndentity){
				return $decoded;
			}
			return $aut;
		}

	}

?>