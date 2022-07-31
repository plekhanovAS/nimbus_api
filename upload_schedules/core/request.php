<?php
// Заголовки
header('Cache-Control: no-cache, must-revalidate');
header('X-Content-Type-Options: nosniff');

// Вывод ошибок
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Переменные
$httpGet = $_GET;
$DEPOT = '1234'; // Номер депо
$TOKEN = ''; // Токен

$URL_ROUTES = 'https://nimbus.wialon.com/api/depot/'.$DEPOT.'/routes'; //ссылка для запроса маршрутов
$URL_STOPS = 'https://nimbus.wialon.com/api/depot/'.$DEPOT.'/stops'; //ссылка для запроса остановок
$URL_PATTERNS = 'https://nimbus.wialon.com/api/depot/'.$DEPOT.'/patterns'; //ссылка для запроса схем действий

$HEADERS = array(
    'accept: application/json',
    'Authorization: '.$TOKEN,
);

// Массивы
$Arr_STOPS = [];
$Arr_PATTERNS = [];
$Arr_ROUTE = [];

// Функции
function arrSort ($arr){
    usort($arr, function ($a, $b) {
        return $a['id'] <=> $b['id'];
    });
}

function get_curl ($url, $heads){
    $curl = curl_init($url); // Инициализируем CURL
    curl_setopt($curl, CURLOPT_URL, $url); // Задаем параметр CURL - URL
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // Задаем параметр CURL - Получить ответ
    curl_setopt($curl, CURLOPT_HTTPHEADER, $heads); // Задаем параметр CURL - Заголовки
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $resp = curl_exec($curl);  // Получаем ответ сервера
    curl_close($curl); // Закрываем CURL

    return $resp; // Отправляем ответ
}

function set_select_routes($data){
    $arr = json_decode($data,true);
    foreach ($arr as $routes){
        $route = $routes;
        arrSort($route);
        foreach ($route as $item => $value){
            $select_data .= '<option value="'.$value['id'].'">'.$value['n'].' ['.$value['d'].']</option>';
        }
    }
    return $select_data;
}

function get_stops($data){
    global $Arr_STOPS;
    $i = 0;
    $arr = json_decode($data,true);
    foreach ($arr as $stops){
        $stop = $stops;
        foreach ($stop as $item => $value){
            $Arr_STOPS[$i] = [
                'id' => $value['id'],
                'n' => $value['n']
            ];
            $i++;
        }
    }
}

function get_patterns($data){
    global $Arr_PATTERNS;
    $i = 0;
    $arr = json_decode($data,true);
    foreach ($arr as $patterns){
        $pattern = $patterns;
        foreach ($pattern as $item => $value){
            $Arr_PATTERNS[$i] = [
                'id' => $value['id'],
                'n' => $value['n']
            ];
            $i++;
        }
    }
}

function get_routes($data,$uid){
    global $Arr_ROUTE;
    global $Arr_STOPS;
    global $Arr_PATTERNS;
    $arr = json_decode($data,true);
    foreach ($arr as $routes){
        $route = $routes;
        foreach ($route as $item => $value){
            if ($value['id'] == $uid){
                $Arr_ROUTE = [
                    "name" => $value["n"],
                    "disc" => $value["d"],
                    "stops" => $value["st"],
                    "timetbl" => $value["tt"],
                    ];
            }
        }
    }

    for ($n=0; $n < count($Arr_ROUTE['stops']); $n++){
        for ($i=0; $i < count($Arr_STOPS); $i++){
            if ($Arr_STOPS[$i]['id'] == $Arr_ROUTE['stops'][$n]['id']){
                $Arr_ROUTE['stops'][$n]['p'] = $Arr_STOPS[$i]['n'];
            }
        }
    }

    for ($n=0; $n < count($Arr_ROUTE['timetbl']); $n++){
        for ($i=0; $i < count($Arr_PATTERNS); $i++){
            if ($Arr_PATTERNS[$i]['id'] == $Arr_ROUTE['timetbl'][$n]['ptrn']){
                $Arr_ROUTE['timetbl'][$n]['ptrn'] = $Arr_PATTERNS[$i]['n'];
            }
        }
    }
}

// Основная часть
switch ($httpGet['req_type']) { // Определяем тип запроса
    case "get_routes":
        header('Content-Type: text/html; charset=utf-8'); // Определяем тип файла
        $RESP_stops = get_curl($URL_STOPS, $HEADERS);
        $RESP_stops = get_stops($RESP_stops);

        $RESP_patterns = get_curl($URL_PATTERNS, $HEADERS);
        $RESP_patterns = get_patterns($RESP_patterns);

        $resp_routes = get_curl($URL_ROUTES, $HEADERS);
        $resp_routes = get_routes($resp_routes,$httpGet['uid']);

        echo json_encode($Arr_ROUTE);
        break;

    case "get_select_routes":
        header('Content-Type: text/html; charset=utf-8'); // Определяем тип файла
        $RESP = get_curl($URL_ROUTES, $HEADERS);
        $RESP = set_select_routes($RESP);
        echo $RESP;
        break;
}
?>