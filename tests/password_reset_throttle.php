<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['session.driver' => 'array', 'cache.default' => 'array']);
$request = Illuminate\Http\Request::create('/forgot-password', 'POST', ['email' => 'reset@example.test', 'password' => 'must-not-be-flashed']);
$session = $app->make('session')->driver();
$session->start();
$request->setLaravelSession($session);
$route = $app['router']->getRoutes()->getByName('password.email');
$request->setRouteResolver(fn () => $route);
$app->instance('request', $request);
$exception = new Illuminate\Http\Exceptions\ThrottleRequestsException('Too Many Attempts.', null, ['Retry-After' => 125]);
$response = $app->make(Illuminate\Contracts\Debug\ExceptionHandler::class)->render($request, $exception);
if ($response->getStatusCode() !== 302 || $response->getTargetUrl() !== route('password.request')) {
    throw new RuntimeException('OTP throttle should return to the recovery form.');
}
if (!str_contains($session->get('errors')->first('email'), '3 minute(s)')) {
    throw new RuntimeException('Retry message must reflect the remaining cooldown.');
}
if ($session->get('_old_input') !== ['email' => 'reset@example.test']) {
    throw new RuntimeException('Only email should be preserved.');
}
if (!in_array('throttle:3,10,password-otp:', $route->gatherMiddleware(), true)) {
    throw new RuntimeException('Password OTP requests must have an isolated throttle.');
}
echo "PASS: OTP throttle redirect, retry timing, safe email retention, and separate request counter.\n";
