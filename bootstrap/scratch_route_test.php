<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/');
$kernel->handle($request);

$routes = app('router')->getRoutes();
$r = $routes->getByName('pengelola.dashboard');
echo "original: " . ($r ? $r->getName() : 'null') . PHP_EOL;

if ($r) {
    $cloned = clone $r;
    $cloned->name('pemilik.dashboard');
    echo "cloned name: " . $cloned->getName() . PHP_EOL;
    
    // Inject via reflection into nameList
    $ref = new ReflectionClass($routes);
    $nameListProp = $ref->getProperty('nameList');
    $nameListProp->setAccessible(true);
    $nameList = $nameListProp->getValue($routes);
    $nameList['pemilik.dashboard'] = $cloned;
    $nameListProp->setValue($routes, $nameList);
    
    echo "alias via reflection: " . ($routes->getByName('pemilik.dashboard') ? 'found' : 'not found') . PHP_EOL;
    echo "original after: " . ($routes->getByName('pengelola.dashboard') ? 'found' : 'not found') . PHP_EOL;
    
    // Test URL generation
    echo "URL pemilik.dashboard: " . url()->route('pemilik.dashboard') . PHP_EOL;
}
