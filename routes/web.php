<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
#CARGANDO CLASES
use App\Http\Middleware\ApiAuthMiddleware;
############################
# Rutas  de Prueba         #
############################

Route::get('/', function () {
    return view('welcome');
});
Route::get('/welcome', function () {
    return view('welcome');
});

#http://localhost:8888/master-fullstack/api-rest-laravel/public/pruebas/luis%20esteban
Route::get('/pruebas/{nombre?}', function($nombre = null){
	$texto = "<h2>Texto desde una ruta</h2>";
	$texto .= 'Nombre: '.$nombre;

	return view('pruebas', array(
		'texto' => $texto
	));
});

Route::get('/animales', 'PruebasController@index');
Route::get('/test-orm', 'PruebasController@testOrm');

############################
# Rutas  de API            # 
############################
		/*
			+GET: Conseguir datos o recursos
			+POST Guardar datos o hacer logica desde un formulario
			+PUT ACtualizar datos o recursos
			+DELETE Eliminar datos o recursos
			+RESOURCE : CREA AUTOMATICAMENTE TODAS LAS RUTAS 'php artisan route:list'
		*/
		#rutas de preuba
		// Route::get('/usuario/pruebas','UserController@pruebas');
		// Route::get('/category/pruebas','CategoryController@pruebas');
		// Route::get('/entrada/pruebas','PostController@pruebas');

		#Rutas del controller de usaurios
		Route::post('/api/register','UserController@register');
		Route::post('/api/login','UserController@login');
		Route::put('/api/user/update','UserController@update');
		Route::post('/api/user/upload', 'UserController@upload')->middleware(ApiAuthMiddleware::class); //despues de crear ApiAuthMiddleware para llamarlo
		Route::get('/api/user/avatar/{filename}', 'UserController@getImage');
		Route::get('/api/user/detail/{id}', 'UserController@detail');

		#Rutas del controller de CATEGORIAS
		Route::resource('/api/category', 'CategoryController');

		#Rutas del controlador de entradas(pots)
		Route::resource('/api/post', 'PostController');
		Route::post('/api/post/upload', 'PostController@upload');
		Route::get('/api/post/image/{filename}', 'PostController@getImage');
		Route::get('/api/post/category/{id}', 'PostController@getPostByCategory');
		Route::get('/api/post/user/{id}', 'PostController@getPostsByUser');

		#RUTAS DE DISTRIBUIDORES
		Route::post('/api/distribuidor', 'DistribuidorController@show');













