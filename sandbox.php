<?php
use Phly\Http\Stream;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\RequestFactory;

require_once(__DIR__.'/tests/autoloader.php');

$request = RequestFactory::fromGlobals();

echo $request->getMethod();
echo "\n";

$bad = function($request) {
    /** @var Request $request */
    $request->respondWith('test', 'tested');
    $request = $request->withMethod('post');
    return $request->withMethod('post');
};
$new = $bad($request);

echo $request->respond()->get('test');
echo "\n";

echo $new->respond()->get('test');
echo "\n";

$stream = new Stream('php://memory', 'wb+');
$stream->write('this is a text');
echo $stream;
echo "\n";

$response = $request->respond()->asNotFound();

$response->send();