<?php
use Phly\Http\Stream;
use Tuum\Web\Psr7\RequestFactory;

require_once(__DIR__.'/tests/autoloader.php');

$request = RequestFactory::fromGlobals();

echo $request->getMethod();
echo "\n";

echo $request->respond()->with('name', 'tuum')->asText('test response')->getBody();
echo "\n";

echo $request->respond()->asJson(['response'=>'json'])->getBody();
echo "\n";

$stream = new Stream('php://memory', 'wb+');
$stream->write('this is a text');
echo $stream;
echo "\n";

$response = $request->respond()->asNotFound();

$response->send();