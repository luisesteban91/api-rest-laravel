<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;

class UserController extends Controller
{
    public function pruebas(Request $request){
    	return "Accion de Pruebas de User Controller";
    }

    public function register(Request $request){
    /*
    	//recoger valor de una variable GET
    	$name = $request->input('name');
    	$surname = $request->input('surname');
    	return "Accion de registro de usuarios $name  $surname";
    */

    	#Recoger los datos del usaurio por post
    	$json = $request->input('json', null);
    	$params = json_decode($json); //objeto
    	$params_array = json_decode($json, true);//array

    	//var_dump($params_array."aqui");die(); //imprimir los datos
    	

        if(!empty($params) && !empty($params_array)){
            #Limpiar datos
            $params_array = array_map('trim', $params_array);

            #Validar Datos
            $validate = \Validator::make($params_array, [
                'name' => 'required|alpha',
                'surname' => 'required|alpha',
                'email' => 'required|email|unique:users', //validar emial unico en la tabla users #Comporbar si el usuario ya existe(duplicado)
                'password' => 'required',
            ]);

            if($validate->fails()){
                //validacion a fallado
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El Usuario no se ha creado',
                    'errors' => $validate->errors()
                );
            }else{
                //Validacion Correctamente


                #Cifrar la contraseña
                //$pwd = password_hash($params->password, PASSWORD_BCRYPT, ['cost' => 4]);
                $pwd = hash('sha256', $params->password);
                
                #crear el usuario
                $user = new User();
                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->role = "ROLE_USUARIO";

                #Guardar Usuario
                $user->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El Usuario se ha creado correctamente',
                    'user' => $user
                );
            }
        }else{
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Los datos recibidos de User no son correctos',
                'params' => $params,
                'params_array' => $params_array
            );
        }

    	#Cifrar la contraseña

    	#Comporbar si el usuario ya existe(duplicado)

    	#crear el usuario

    	return response()->json($data, $data['code']);
    }


    public function login(Request $request){

        $jwtAuth = new \JwtAuth();


        #Recibir los datos por POST
        $json = $request->input('json', null);
        $params = json_decode($json); //crea objeto
        $params_array = json_decode($json, true); // y crea en array

        #VALIDAR ESOS DATOS

        $validate = \Validator::make($params_array, [
            'email' => 'required|email', //validar emial unico en la tabla users #Comporbar si el usuario ya existe(duplicado)
            'password' => 'required'
        ]);

            if($validate->fails()){
                //validacion a fallado
                $signup = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El Usuario no se ha podido identificar',
                    'errors' => $validate->errors()
                );
            }else{
                #CIFRAR EL PASSWORD
                $pwd = hash('sha256', $params->password);

                #DEVOLVER EL TOKEN DE DATOS
                $signup = $jwtAuth->signup($params->email, $pwd);
                if(!empty($params->gettoken)){
                    $signup = $jwtAuth->signup($params->email, $pwd, true);
                }
            }        

        return response()->json($signup, 200);
    }

    public function update(Request $request){

        #COMPROBAR QUE EL USUARIO ESTE IDETIFICADO
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        #RECOGER LOS DATOS POR POST
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if($checkToken && !empty($params_array)){

            #sacar usuario identificado
            $user = $jwtAuth->checkToken($token, true);

            #VALIDAR DATOS
            $validate = \Validator::make($params_array, [
                'name' => 'required|alpha',
                'surname' => 'required|alpha',
                'email' => 'required|email|unique:users,'.$user->sub //validar emial unico en la tabla users #Comporbar si el usuario ya existe(duplicado) con la excepcion de que en este caso se update se utiliza el $user->sub
            ]);

            #QUITAR LOS CAMPOS QUE NO QUIERO ACTUALIZAR
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['create_at']);
            unset($params_array['remember_token']);

            #ACTUZALIZAR USUARIO EN BD
            $user_update = User::where('id', $user->sub)->update($params_array);

            #DEVOLVER ARRAY CON RESULTADO
            $data = array(
                'code' => 200,
                'status' => 'succesfull',
                'message' => 'El usuario se ha actualizado correctamente',
                'user' => $user,
                'changes' => $params_array
            );
        }else{
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'El usuario no esta identificado'
            );
        }
        
        return response()->json($data, $data['code']);
    }

    #METODO PARA SUBIR IMAGEN
    public function upload(Request $request){
        #RECOGER LOS DATOS DE LA PETICION
        $image = $request->file('file0');

        #VALIDACION DE IMAGEN
        $validate = \Validator::make($request->all(), [ 
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);


        
        #GUARDAR IMAGEN
        if(!$image || $validate->fails()){
            #DEVOLVER EL RESULTADO
            $data = array(
                'code' => 400,
                'status' => 'error',
                'image' => 'error al subir la imagen'
            );
        }else{
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image)); //se creo un disco llamado usersm que es una carpeta creada en \config\filesystem.php

            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            );
        }

        #DEVOLVER EL RESULTADO

        //return response($data, $data['code'])->header('Content-type','text/plain');
        return response()->json($data, $data['code']);
    }

    #CREAR METODO GETIMAGE PARA OBTENER LA IMAGEN DECLARADA LA RUTA EN WEB.PHP CON METODO GET Y FORSOSAMENTE DEBE TENER PARAMETRO DE TIPO FILENAME
    public function getImage($filename){
        #VALIDAR SI EXISTE LA IMAGEN
        $isset = \Storage::disk('users')->exists($filename);

        if($isset){
            #OBTENENER LA IMAGEN
            $file = \Storage::disk('users')->get($filename);    

            return new Response($file, 200);
        }else{
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'La imagen no existe',
            );
        }

        return response()->json($data, $data['code']);
    }

    #METODO PARA DEVOLVER LOS DATOS DEL USUARIO
    public function detail($id){
        $user = User::find($id);

        if(is_object($user)){
            $data = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user
            );
        }else{
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'El usuario no existe',
            );
        }
        return response()->json($data, $data['code']);
    }
}
