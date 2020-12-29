# SWOOLE TUTORIAL

# Описание:
Инструкция по установке и настройке Swoole

[Официальная документация]
(https://www.swoole.co.uk/docs/)


# Установка и настройка

[Инструкция по установке]
(https://github.com/swooletw/laravel-swoole/wiki/4.-Installation)

## Установка php расширения на Ubuntu

sudo apt-get install php7.4-dev

sudo pecl install swoole

php -i | grep php.ini

Добавить в php.ini строку
extension=swoole.so

Если в системе установлен xdebug, отключаем.

Отключение xdebug: В папке /etc/php/7.4/cli/config.d/ переименовать файл 20-xdebug.ini в 20-xdebug.ini.bak

Тоже самое в папке /etc/php/7.4/fpm/config.d/

sudo service php7.x-fpm restart && sudo service nginx restart

## Установка  пакета "swooletw/laravel-swoole" в Laravel проект

composer require swooletw/laravel-swoole

## Настройка  пакета "swooletw/laravel-swoole"

1. В массив providers в файле config/app.php добавить:

[

    'providers' => [

        SwooleTW\Http\LaravelServiceProvider::class,

    ],

]

2. Создание файла конфигурации swoole:

php artisan vendor:publish --tag=laravel-swoole

3. Создать файл конфигурации .env на основе содержимого файла .env.example и изменить настройки:

- Параметры для Swoole:
    1. SWOOLE_HTTP_HOST (http url swoole, default 127.0.0.1)
    2. SWOOLE_HTTP_PORT (swoole port, default 1215)
    3. SWOOLE_HANDLE_STATIC (Определите, следует ли использовать swoole для ответа на запрос статических файлов, default true)
    4. SWOOLE_HTTP_ACCESS_LOG (Доступ к логам, default true)
    5. SWOOLE_HTTP_DAEMONIZE (default true)
    6. SWOOLE_MAX_REQUEST (максимальное кол-во запросов для перезагрузки workera, для локальной разработки установить 1)
    7. SWOOLE_MAX_WAIT_TIME (максимальное время ожидания workera, default 300 секунд)
    8. SWOOLE_HTTP_WEBSOCKET (true, если надо использовать сокеты, default false)
    9. SWOOLE_HOT_RELOAD_ENABLE (для локальной разработки true, default false)
    10. SWOOLE_HOT_RELOAD_RECURSIVELY (для локальной разработки true, default false)
    11. SWOOLE_HOT_RELOAD_LOG (default true)
    12. SWOOLE_OB_OUTPUT (Если этот параметр включен, вывод консоли будет перенесен в содержимое ответа. default false)
    - Устанавливать только для локальной разработки:
        1. SWOOLE_HTTP_REACTOR_NUM=1
        2. SWOOLE_HTTP_WORKER_NUM=1
        3. SWOOLE_HTTP_TASK_WORKER_NUM=1

## Запуск

php artisan swoole:http restart

После внесения любых изменений в код, перезапустить команду 

php artisan swoole:http restart

Ссылка на поднятый домен

http://localhost:1215

Пример успешного запуска

Starting swoole http server...

Swoole http server started: <http://127.0.0.1:1215>

(You can run this command to ensure the swoole_http_server process is running: ps aux|grep "swoole")

## Проверка асинхронности
Пример тестового контроллера и вывод предполагаемых результатов

<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Swoole\Coroutine\System;

class SwooleController extends Controller
{
    public function test() 
    {
        $first = Carbon::now();
        System::sleep(3);

        return response()->json([
		$first, Carbon::now()
	]);
    }
}


Открыть две одинаковые вкладки с тестовым эндпоинтом и запустить отправку запроса в каждой из них одновременно.

Полученные значения времени должны отличаться на обеих вкладках +- 1 секунда.


