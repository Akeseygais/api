<?php
use Illuminate\Http\Request;

// Получение информации о запросе (файлы, данные ввода) - https://laravel.ru/docs/v5/requests

// Маршрутизация https://laravel.ru/docs/v5/routing


/*

1. Прочитать про маршрутизацию и получения информации о запросе Laravel.
2. Понять мои функции. 
3. Сделать 2-3 своих функций из оставшихся.

*/


Route::get('auth', function (Request $request) { // заменить ::get на ::post, что был POST-запрос, как в задании.
	$login = $request->input('login');
	$password = $request->input('password');
	if ($login == "admin" && $password == "admin")
	{
		$answer=array( 'status' => 'true', 'token' => 't123456'); // Здесь можно вызывать функцию генерирования токена.
		return response(json_encode($answer))->setStatusCode(200, 'Successful authorization');
		
	}
	else
	{
		$answer=array('status' => 'false','message' => 'Invalid authorization data');
		
		return response(json_encode($answer))->setStatusCode(401, 'Invalid authorization data');
	}
	return $name;
});


Route::get('posts', function (Request $request) {// заменить ::get на ::post, что был POST-запрос, как в задании.


	#if(!is_token_valid($request)) // Проверка авторизации // Закомментировал, чтобы не мешало тестировать
	{
	#	return not_valid_token(); // Токен не валиден, авторизация не прошла, отправляем сообщение об этом
	}

	$title = $request->input('title');
	$anons = $request->input('anons');
	$text = $request->input('text');
	$tags = $request->input('tags');
	$image = $request->input('image');
	
	
	
	$answer=save_post(array('title'=>$title, 'anons'=>$anons,'text'=>$text,'tags'=>$tags,'image'=>$image)); // ФУнкция возвращает номер добавленного блюда. Если добавить не получилось, то возвращает 0.
	// Функция добавления также возвращает строку message, если добавление не удалось. 
	
	if ($answer['post_id']>0) // Вместо true должна быть функция проверки правильности и полноты введенных данных!
	{

		$answer=array( 'status' => 'true', 'post_id' => $answer['post_id']);
		
		return response(json_encode($answer))->setStatusCode(200, 'Successful creation');
		
	}
	else
	{
		$answer=array('status' => 'false','message' => $answer['message']);
		
		return response(json_encode($answer))->setStatusCode(400, 'Creating error');
	}
	return $name;
});



Route::delete('posts/{id}', function (Request $request, $id) {// Обращаю внимание, запрос типа delete !
// Обращаю внимание на параметр маршрутов {id}! Написано об этом в: https://laravel.ru/docs/v5/routing#%D0%BE%D0%B1%D1%8F%D0%B7%D0%B0%D1%82%D0%B5%D0%BB%D1%8C%D0%BD%D1%8B%D0%B5 
// Параметр {id} становится переменной $id


	#if(!is_token_valid($request)) // Проверка авторизации // Закомментировал, чтобы не мешало тестировать
	{
	#	return not_valid_token(); // Токен не валиден, авторизация не прошла, отправляем сообщение об этом
	}
	
	if (delete_post($id))
	{
		$answer=array( 'status' => 'true');
		return response(json_encode($answer))->setStatusCode(201, 'Successful delete');		
	}
	else
	{
		$answer=array( 'message' => 'Post not found');
		return response(json_encode($answer))->setStatusCode(404, 'Post not found');
	}

	
});


function save_post($array) // Эта функция сохраняет ОДНО блюдо.
{
	$all=read_posts(); // Считываем все блюда
	$id=1;
	if (empty($all)) // Если блюд нет 
	{
		$array=array($id=>$array); // Создаем массив, где у первого блюда будет ключ - 1
		save_posts($array); // Сохраняем массив блюд
	}
	else // Если блюда уже есть
	{
		$id=max(array_keys($all)); // Получаем максимальный номер из всех ключей блюда
		$id++; // 
		$all[$id]=$array; // К общему массиву блюд добавляем еще одно
		save_posts($all); // Сохраняем все блюда
	}
	
	return (array( 'post_id' => $id, 'message' => "")); // Возвращаем результат
}

function delete_post($id) // Функция удаления блюда
{
	$all=read_posts();  // Считываем все блюда
	if (empty($all))// Если блюд нет 
	{
		return false; // Возвращаем false
	}
	else
	{
		if(array_key_exists($id,$all)) // Если блюдо с таким ключом есть
		{
			unset($all[$id]); // Убираем блюдо с этим ключом из общего массива
			save_posts($all); // Сохраняем все блюда
			return true; // Возвращаем true
		}
		else
		{
			return false;
		}
	}	
	
}
function save_posts($all) // Записывает в файл список всех блюд
{
	$data= json_encode($all);
	file_put_contents ("posts.json", $data);
}


function read_posts() // Считывает из файла список всех блюд
{
	$string = file_get_contents ("posts.json");
	$data= json_decode($string, TRUE);
	return $data;
}

function is_token_valid($request) // Проверяет правильность токена
{
	$auth_token = $request->header('Authorization');
	
	$token=explode(" ",$auth_token)[1]; // Так как к нам пришла строка Bearer t123456, нужно взять только вторую часть
	
	if ($token == 't123456')
	{
		return true;
	}
	return false;
}


function not_valid_token()
{
	$answer=array('message' => "Unauthorized");
	return response(json_encode($answer))->setStatusCode(401, 'Unauthorized');
}


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/
