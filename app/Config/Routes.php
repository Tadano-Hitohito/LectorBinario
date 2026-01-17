<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');


$routes->get('/login', 'LoginController::index');
$routes->post('/login/autenticar', 'LoginController::autenticar');
$routes->get('/cerrar_sesion', 'LoginController::cerrar_sesion');


$routes->get('/analizar_roms', 'AnalizarRoms::index');
$routes->post('analizar_roms/procesar', 'AnalizarRoms::procesarBooleano');
$routes->get('analizar_roms/resultados', 'AnalizarRoms::resultados');
$routes->post('analizar_roms/calcular', 'AnalizarRoms::calcularBooleano');
$routes->get('analizar_roms/edicion', 'AnalizarRoms::resultados');
$routes->get('analizar_roms/limpiar', 'AnalizarRoms::limpiar');
$routes->get('analizar_roms/menu', 'AnalizarRoms::menu');
$routes->get('analizar_roms/seleccionar/(:segment)', 'AnalizarRoms::seleccionar/$1');
$routes->post('analizar_roms/subir_portada', 'AnalizarRoms::subirPortada');
$routes->get('analizar_roms/imagen/(:any)', 'AnalizarRoms::imagen/$1');
$routes->post('analizar_roms/renombrar', 'AnalizarRoms::renombrar');
