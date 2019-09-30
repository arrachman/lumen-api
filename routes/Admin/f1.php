<?php

use Illuminate\Http\Request;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods:  POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers:  Content-Type, X-Auth-Token, Origin, Authorization');

routeGetSearch('coa');
routeGetSearch('contact');
routeGetSearch('currency');
routeGetSearch('item');

$source = 'Area';
routeGet($source, 'show');
routeGetById($source, 'showById');
routePost($source, 'insert');
routePut($source, 'update');
routeGetDelete($source, 'delete');

$source = 'Bank';
routeGet($source, 'show');
routeGetById($source, 'showById');
routePost($source, 'insert');
routePut($source, 'update');
routeGetDelete($source, 'delete');

$source = 'Branch';
routeGet($source, 'show');
routeGetById($source, 'showById');
routePost($source, 'insert');
routePut($source, 'update');
routeGetDelete($source, 'delete');

$source = 'City';
routeGet($source, 'show');
routeGetById($source, 'showById');
routePost($source, 'insert');
routePut($source, 'update');
routeGetDelete($source, 'delete');

$source = 'Class';
routeGet($source, 'show');
routeGetById($source, 'showById');
routePost($source, 'insert');
routePut($source, 'update');
routeGetDelete($source, 'delete');

$source = 'Coa';
routeGet($source, 'show');
routeGetById($source, 'showById');
routePost($source, 'insert');
routePut($source, 'update');
routeGetDelete($source, 'delete');

$source = 'Contact';
routeGet($source, 'show');
routeGetById($source, 'showById');
routePost($source, 'insert');
routePut($source, 'update');
routeGetDelete($source, 'delete');

$source = 'Color';
routeGet($source, 'show');
routeGetById($source, 'showById');
routePost($source, 'insert');
routePut($source, 'update');
routeGetDelete($source, 'delete');

$source = 'Country';
routeGet($source, 'show');
routeGetById($source, 'showById');
routePost($source, 'insert');
routePut($source, 'update');
routeGetDelete($source, 'delete');

$source = 'Item';
routeGet($source, 'show');
routeGetById($source, 'showById');
routePost($source, 'insert');
routePut($source, 'update');
routeGetDelete($source, 'delete');