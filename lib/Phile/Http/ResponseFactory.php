<?php
/**
 * @author  PhileCMS
 * @link    https://philecms.com
 * @license http://opensource.org/licenses/MIT
 */

namespace Phile\Http;

use Interop\Http\Factory\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Response;

/**
 * Creates PSR-7 responses
 */
class ResponseFactory implements ResponseFactoryInterface
{
    /**
     * Creates a PSR-7 response
     */
    public function createResponse($code = 200): ResponseInterface
    {
        return new Response('php://memory', $code);
    }

    /**
     * Creates PSR-7 HTML response
     */
    public function createHtmlResponse($body, $code = 200): ResponseInterface
    {
        return new HtmlResponse($body, $code);
    }

    /**
     * Creates PSR-7 redirect response
     */
    public function createRedirectResponse($url, $code = 302): ResponseInterface
    {
        return new RedirectResponse($url, $code);
    }
}
