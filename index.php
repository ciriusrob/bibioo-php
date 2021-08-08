<?php
/**
 * Created by Robert Wilson.
 * Date: 12/30/2016
 * Time: 7:02 AM
 */
require_once 'Utils/Session.php';
require_once 'Utils/Utils.php';
require_once 'Api/Route.php';
require_once 'Database/Database.php';
require_once 'Utils/Response.php';
require_once 'Utils/CurlRequest.php';
require_once 'Utils/Logger.php';

use \Api\Route;
use \Utils\Session;
use \Utils\Response;
use \Database\Database;
use \Utils\Logger;

/**
 * @description Get all clients
 * */

Route::get('/api/v1/items', function ($page = 1, $limit = 2, $otherBy = ['id'], $sortDirection = 'DESC') {
    
    $database = Database::getInstance();

    if ( !is_array($otherBy) ) {
        $otherBy = explode(",", urldecode($otherBy));
    }

    $result = $database->findPaged('items', $page, $limit, $otherBy, $sortDirection);

    $pager = ['page' => $page,'limit' => $limit,'sortParams' => $otherBy,'sortDirection' => $sortDirection, 'total' => $result['count']];

    $response = Response::newInstance(0, $result['data'], $pager);
    
    $response->render();
});

Route::post('/api/v1/items', function ($payload = null) {
    
    $database = Database::getInstance();

    $id = $database->save('items', $payload);

    if ( $id ) {
        $payload['id'] = intval($id);
    }
    
    $response = Response::newInstance(0, $payload);

    $response->render();
});

Route::put('/api/v1/items/:id', function ($payload = null, $id) {
    
    $database = Database::getInstance();

    $updated = $database->updateRecord('items', $id, $payload);

    $response = Response::newInstance(0, $updated);

    $response->render();
});

Route::delete('/api/v1/items/:id', function ($id) {
    
    $database = Database::getInstance();

    $deleted = $database->delete($id, 'items');

    $response = Response::newInstance(0, $deleted);
    
    $response->render();
});

Route::start();