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

    /**
     * @param string $file_loc
     * @return Stream
     */
    public static function file($file_loc)
    {
        if (is_string($file_loc)) {
            return new Stream(fopen($file_loc, 'rb'));
        }
        return stream_get_contents($file_loc);
    }
}