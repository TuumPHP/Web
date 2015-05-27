<?php
namespace Tuum\Web\Psr7;

use Zend\Diactoros\Stream;
use Psr\Http\Message\StreamInterface;

class StreamFactory
{
    /**
     * @param string|StreamInterface|resource $stream
     * @return Stream
     */
    public static function make($stream)
    {
        if ($stream instanceof StreamInterface) {
            return $stream;
        }
        if (is_string($stream)) {
            return self::string($stream);
        }
        if (is_resource($stream)) {
            return new Stream($stream);
        }
        if (is_object($stream) && method_exists($stream, '__toString')) {
            return self::string($stream->__toString());
        }
        throw new \RuntimeException('unknown type of input to make a stream object.');
    }

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
     * @param string|resource $file_loc
     * @return Stream
     */
    public static function file($file_loc)
    {
        if (is_string($file_loc)) {
            return new Stream(fopen($file_loc, 'rb'));
        }
        if (is_resource($file_loc)) {
            return new Stream($file_loc);
        }
        throw new \RuntimeException('unknown type of input to make a stream object.');
    }
}