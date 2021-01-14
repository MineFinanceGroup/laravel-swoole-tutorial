# Вводная инструкция по работе со swoole  
  
*Swoole — технология, которая используется в нашей компании для обеспечения асинхронной работы в PHP. В отличие от синхронного кода, асинхронный не является блокирующим, т.е. во время ожидания ответа от внешнего ресурса, основной поток программы продолжает выполнение и занимается другой работой. Фактически это значит, что асинхронный код порождает гораздо меньшее количество процессов, нежели синхронный, что сказывается как на объёме занимаемой приложением памяти, так и на скорости работы приложения.*  
  
[**ВНИМАНИЕ: после каждого внесения изменений в код требуется перезапускать swoole**](https://github.com/MineFinanceGroup/swoole-tutorial/blob/master/README.md#%D0%B7%D0%B0%D0%BF%D1%83%D1%81%D0%BA-%D0%B8-%D0%BF%D0%B5%D1%80%D0%B5%D0%B7%D0%B0%D0%BF%D1%83%D1%81%D0%BA-swoole)  
  
- [Официальная документация](https://www.swoole.co.uk/docs/)  
- [Официальная инструкция по установке laravel-swoole](https://github.com/swooletw/laravel-swoole/wiki/4.-Installation)  
  
  
## Установка и настройка  
  
### Установка php расширения на Ubuntu  
  
```bash  
sudo apt-get install php7.x-dev  
sudo pecl install swoole  
php -i | grep php.ini  
```  
  
Добавить в php.ini строку:  
```  
extension=swoole.so  
```  
  
#### Отключение xdebug в случае его наличия  
*В случае если у вас установлен xdebug, требуется его отключить во избежание конфликтов.*  
 
Переименовать файл 20-xdebug.ini в 20-xdebug.ini.bak в директории /etc/php/7.x/cli/config.d/   
  
### Установка и настройка пакета "swooletw/laravel-swoole" в Laravel проект  
  
```bash  
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
SWOOLE_HTTP_PID_FILE='/tmp/swoole-{project-name}.pid'
```  
  
**Некоторые примечания:**  
- SWOOLE_HANDLE_STATIC — Обрабатывать ли статические файлы через swoole  
- SWOOLE_HTTP_ACCESS_LOG — Доступ к логам через HTTP  
- SWOOLE_MAX_REQUEST — Максимальное количество запросов для перезагрузки worker'a. Для локальной разработки установить 1.  
- SWOOLE_HTTP_PID_FILE — Необходимо что бы файл был уникальным
  
### Запуск и перезапуск swoole  
  
После внесения любых изменений в код, необходимо выполнить команду:  
```bash  
php artisan swoole:http restart  
```  
  
Ссылка на приложение по умолчанию: http://localhost:1215  
  
  
## Тест корректной асинхронной работы  
  
*Смысл теста заключается в том, что при наличии асинхронности, но одним worker'ом swoole, два одинаковых одновременно запущенных процесса будут выполняться параллельно (и завершатся почти одновременно), тогда как в случае синхронного выполнения, процессы выполнятся по очереди (и завершатся в разное время)*  
  
1. Для теста в .env требуется добавить настройки, которые ограничат максимальное количество worker'ов swoole до одного:  
```  
SWOOLE_HTTP_REACTOR_NUM=1  
SWOOLE_HTTP_WORKER_NUM=1  
SWOOLE_HTTP_TASK_WORKER_NUM=1  
```  
  
2. Добавить в routes/api.php тестовый route  
```php  
Route::get('swoole-test', function (){  
    $timeStarted = \Carbon\Carbon::now(); 
    \Swoole\Coroutine\System::sleep(3);  
    return response()->json([
        'start' => $timeStarted, 
        'end' => \Carbon\Carbon::now()
    ]); 
});  
```
  
3. [Перезапустить swoole](https://github.com/MineFinanceGroup/swoole-tutorial/blob/master/README.md#%D0%B7%D0%B0%D0%BF%D1%83%D1%81%D0%BA-%D0%B8-%D0%BF%D0%B5%D1%80%D0%B5%D0%B7%D0%B0%D0%BF%D1%83%D1%81%D0%BA-swoole)  
  
4. Сделать два почти одновременных запроса через [Postman](https://www.postman.com/) на /api/swoole-test   
При корректной работе асинхронности, результаты должны почти совпадать по времени запуска и завершения.  
  
## Использование  
При работе со swoole необходимо использовать асинхронные решения для работы со сторонними инструментами: БД, Redis, HTTP запросы и другими.

В данном репозитории в app/Swoole содержатся реализации асинхронных классов для работы  со сторонними инструментами:
- [БД (MySQL)  - Services/PdoCoroutine.php](https://github.com/MineFinanceGroup/swoole-tutorial#%D0%B1%D0%B4-mysql)
- [Redis - Services/RedisCoroutine.php](https://github.com/MineFinanceGroup/swoole-tutorial#redis-%D0%BF%D0%BE%D0%B4%D0%B4%D0%B5%D1%80%D0%B6%D0%B8%D0%B2%D0%B0%D0%B5%D1%82-%D0%B2%D1%81%D0%B5-redis-%D1%84%D1%83%D0%BD%D0%BA%D1%86%D0%B8%D0%B8)
- [HTTP запросы - Traits/FetcherTrait.php](https://github.com/MineFinanceGroup/swoole-tutorial#http-%D0%B7%D0%B0%D0%BF%D1%80%D0%BE%D1%81%D1%8B)

### Примеры использования:
#### БД (MySQL) 
```php
use App\Swoole\Services\PdoCoroutine;

$pdoCoroutine = new PdoCoroutine();  

//Select: returned rows array
$sqlQuery = 'SELECT id, name, status FROM tableName WHERE status = :status';
$rows = $pdoCoroutine->select($sqlQuery, ['status' => 'active']); 

//Select one row: returned array with row OR null
$sqlQuery = 'SELECT id, name, status FROM tableName WHERE id = :id LIMIT 1';
$row = $pdoCoroutine->selectRow($sqlQuery, ['id' => 1]); 

//Insert: returned last insert id
$sqlQuery = 'INSERT INTO tableName (name, status, created_at, updated_at) VALUES (:name, :status, NOW(), NOW())';
$insertId = $pdoCoroutine->insert($sqlQuery, [
    'name' => 'Test',
    'status' => 'new',
]);

//Update: returned number of rows that has been updated
$sqlQuery = 'UPDATE tableName SET status = :new_status, updated_at = NOW() WHERE status = :old_status';
$numberOfRowsUpdated = $pdoCoroutine->update($sqlQuery, [
    'old_status' => 'pending',
    'new_status' => 'closed',
]);

//Delete: returned number of rows that has been deleted
$sqlQuery = 'DELETE FROM tableName WHERE id = :id LIMIT 1';
$numberOfRowsDeleted = $pdoCoroutine->delete($sqlQuery, ['id' => 1]);     
```
#### Redis (поддерживает все Redis функции)
```php
use App\Swoole\Services\RedisCoroutine;

$redisCoroutine = new RedisCoroutine();

$item1 = $redisCoroutine->get('key1');

$redisCoroutine->set('key2', 'item2');

$redisCoroutine->expire('key2', 60 * 60);
  
$redisCoroutine->del('key3');

$redisCoroutine->incr('key4');

$redisCoroutine->decrBy('key5', 10);
```
#### HTTP запросы

```php
use App\Swoole\Traits\FetcherTrait;  
  
class TestApiClient  
{  
    use FetcherTrait;  
    
    private $url = 'https://api.test.com';  
  
    private $token;  
  
    public function __construct(string $token)  
    {  
        $this->token = $token;  
    }
    
    public function request(string $endpoint, ?array $data = [], string $method = 'GET'): object  
    {  
        try {  
	    return $this->fetch(  
	        "{$this->url}/{$endpoint}",  
		$data,  
		$method,  
		['Authorization' => "Bearer {$this->token}"]  
	    );  
	} catch (Exception $e) {  
	    throw new Exception("Http client error: {$e->getMessage()}");  
	}  
    }
}  
  
$testApiClient = new TestApiClient('TOKEN');  
  
$items = $testApiClient->request('items');  
  
$newItems = $testApiClient->request(  
    'items',  
    [
        'name' => 'Test',  
	'status' => 'new',  
    ],  
    'POST'  
);
```
