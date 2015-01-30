<?php
namespace Tuum\Web\Psr7;

use Phly\Http\Stream;

class StreamFactory
{
    /**
     * @param string $text
     * @return Stream
     */
    public static function string($text)
    {
        $stream = new Stream('php://memory', 'wb+');
        $stream->write($text);
        return $stream;
    }
}