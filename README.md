
# Вводная инструкция по работе со swoole

*Swoole — технология, которая используется в нашей компании для обеспечения асинхронной работы в PHP. В отличии от синхронного кода, асинхронный не является блокирующим, т.е. во время ожидания ответа от внешнего ресурса, основной поток программы продолжает выполнение и занимается другой работой. Фактически это значит, что асинхронный код порождает гораздо меньшее количество процессов, нежели синхронный, что сказывается как на обьёме занимаемой приложением памяти так и на скорости работы приложения.*

[**ВНИМАНИЕ: после каждого внесения изменений в код требуется перезапускать swoole**](https://github.com/MineFinanceGroup/swoole-tutorial/blob/master/README.md#%D0%B7%D0%B0%D0%BF%D1%83%D1%81%D0%BA-%D0%B8-%D0%BF%D0%B5%D1%80%D0%B5%D0%B7%D0%B0%D0%BF%D1%83%D1%81%D0%BA-swoole)

- [Официальная документация](https://www.swoole.co.uk/docs/)
- [Официальная инструкция по установке laravel-swoole](https://github.com/swooletw/laravel-swoole/wiki/4.-Installation)


## Установка и настройка

### Установка php расширения на Ubuntu

```
sudo apt-get install php7.4-dev
sudo pecl install swoole
php -i | grep php.ini
```

Добавить в php.ini строку:
```
extension=swoole.so
```

#### Отключение xdebug в случае его наличия
*В случае если у вас установлен xdebug, требуется его отключить во избежание конфликтов.*

1. Переименовать файл 20-xdebug.ini в 20-xdebug.ini.bak в директориях:
- /etc/php/7.4/cli/config.d/ 
- /etc/php/7.4/fpm/config.d/

2. Перезапустить php:
```
sudo service php7.x-fpm restart 
```

3. Перезапустить nginx если используется:
```
sudo service nginx restart
```


#### Установка и настройка пакета "swooletw/laravel-swoole" в Laravel проект

```
composer require swooletw/laravel-swoole
php artisan vendor:publish --tag=laravel-swoole
```

В массив providers в файле config/app.php добавить строку:
```
SwooleTW\Http\LaravelServiceProvider::class
```

2. Добавить в .env конфигурацию:

```
SWOOLE_HTTP_HOST=127.0.0.1
SWOOLE_HTTP_PORT=1215
SWOOLE_HANDLE_STATIC=true
SWOOLE_HTTP_ACCESS_LOG=true
SWOOLE_HTTP_DAEMONIZE=true
SWOOLE_MAX_REQUEST=3000
SWOOLE_MAX_WAIT_TIME=300
SWOOLE_HTTP_WEBSOCKET=false
SWOOLE_HOT_RELOAD_ENABLE=false
SWOOLE_HOT_RELOAD_RECURSIVELY=false
SWOOLE_HOT_RELOAD_LOG=true
SWOOLE_OB_OUTPUT=false
```

**Некоторые примечания:**
SWOOLE_HANDLE_STATIC — Обрабатывать ли статические файлы через swoole
SWOOLE_HTTP_ACCESS_LOG — Доступ к логам через HTTP
SWOOLE_MAX_REQUEST — Максимальное количество запросов для перезагрузки worker'a. Для локальной разработки установить 1.


### Запуск и перезапуск swoole

После внесения любых изменений в код, необходимо выполнить команду:
```
php artisan swoole:http restart
```

Ссылка на домен по умолчанию: http://localhost:1215


## Тест корректной асинхронной работы

*Смысл теста заключается в том, что при наличии асинхронности но с максимум одним worker'ом swoole, два одинаковых одновременно запущенных процесса будут выполняться параллельно (и завершатся почти одновременно), тогда как в случае синхронного выполнения, процессы выполнятся по очереди (и завершатся в разное время)*

1. Для теста в .env требуется добавить настройки, которые ограничат максимальное количество worker'ов swoole до одного:
SWOOLE_HTTP_REACTOR_NUM=1
SWOOLE_HTTP_WORKER_NUM=1
SWOOLE_HTTP_TASK_WORKER_NUM=1

2. Добавить в routes/api.php тестовый route

Route::get('swoole-test', function (){
    $timeStarted = Carbon::now();
    \Swoole\Coroutine\System::sleep(3);

    return response()->json([
    	$timeStarted, 
	\Carbon\Carbon::now()
    ]);    
});

3. [Перезапустить swoole](https://github.com/MineFinanceGroup/swoole-tutorial/blob/master/README.md#%D0%B7%D0%B0%D0%BF%D1%83%D1%81%D0%BA-%D0%B8-%D0%BF%D0%B5%D1%80%D0%B5%D0%B7%D0%B0%D0%BF%D1%83%D1%81%D0%BA-swoole)

4. Сделать два почти одновременных запроса через [postman](https://www.postman.com/) на /api/swoole-test 
При корректной работе асинхронности, результаты должны почти совпадать по времени запуска и завершения.


