# bibioo-php
A Simple/Minimal PHP Web Framework.


## Project Description
bibioo-php is a simple/minimal PHP web framework. It's easy to use and requires no complex setup. Just setup your Database details and you are good to go.

## Getting Started
To start. Let's first clone this repository with the below command    
> git clone https://github.com/ciriusrob/bibioo-php.git

Now we change directory into the cloned project
> cd bibioo-php

Next is to setup your database (MySQL/MariaDB only) plus a few config values.
The `Config.php` file can be loacted in `<PROJECT_ROOT/Api/Config.php>`
```php
<?php

 // DATABASE
$_config['DB']                              = 'bibioo_db';  // Change this
$_config['DB_USER']                         = 'root';       // Change this
$_config['DB_PASSWORD']                     = '';           // Change this
$_config['DB_HOST']                         = '127.0.0.1';  // Change this

// GENERAL
$_config['SESSION_NAME']                    = '_bibioo_api';
$_config['SESSION_COOKIE_HTTP_ONLY']        = 1;
```

Once the approriate config is set. We can start by creating some endpoints.

### Creating An Endpoint To Fetch and Return Items In The Database
All Api endpoints must be created in the `index.php` file located in the project root.

```php
<?php
 Route::get('/api/v1/items', function ($page = 1, $limit = 2, $otherBy = ['id'], $sortDirection = 'DESC') {
    
    $database = Database::getInstance();

    if ( !is_array($otherBy) ) {
        $otherBy = explode(",", urldecode($otherBy));
    }

    $result = $database->findPaged('items', $page, $limit, $otherBy, $sortDirection);

    $pager = [
      'page' => $page,
      'limit' => $limit,
      'sortParams' => $otherBy,
      'sortDirection' => $sortDirection, 
      'total' => $result['count']
    ];

    $response = Response::newInstance(0, $result['data'], $pager);

    $response->render();
});
```

### Creating An Endpoint To Save An Item Into The Database
```php
<?php
Route::post('/api/v1/items', function ($payload = null) {
    
    $database = Database::getInstance();

    $id = $database->save('items', $payload);

    if ( $id ) {
        $payload['id'] = $id;
    }
    $response = Response::newInstance(0, $payload);

    $response->render();
});
```

### Creating An Endpoint To Get An Item By ID
```php
<?php
Route::get('/api/v1/items/:id', function ($id) {
    
    $database = Database::getInstance();

    $result = $database->findById('items', $id);

    $response = Response::newInstance(0, $result);

    $response->render();
});
```

### Creating An Endpoint To Update An Existing Item In The Database
```php
Route::put('/api/v1/items/:id', function ($payload = null, $id) {
    
    $database = Database::getInstance();

    $updated = $database->updateRecord('items', $id, $payload);

    $response = Response::newInstance(0, $updated);

    $response->render();
});
```

### Creating An Endpoint To Delete An Item By ID
```php
Route::delete('/api/v1/items/:id', function ($id) {
    
    $database = Database::getInstance();

    $deleted = $database->delete($id, 'items');

    $response = Response::newInstance(0, $deleted);
    
    $response->render();
});
```