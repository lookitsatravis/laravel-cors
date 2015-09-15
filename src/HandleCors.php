<?php namespace Barryvdh\Cors;

use Closure;
use Asm89\Stack\CorsService;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Events\Dispatcher;

class HandleCors
{
    /**
     * The Exception Handler
     *
     * @var ExceptionHandler
     */
    protected $exceptionHandler;

    /**
     * The CORS service
     *
     * @var CorsService
     */
    protected $cors;

    /**
     * The Event Dispatcher
     *
     * @var Dispatcher
     */
    protected $dispatcher;


	/**
	 * @param CorsService $cors
	 */
	public function __construct(CorsService $cors, ExceptionHandler $exceptionHandler, Dispatcher $dispatcher)
	{
		$this->cors = $cors;
		$this->exceptionHandler = $exceptionHandler;
        $this->dispatcher = $dispatcher;
	}

	/**
	 * Handle an incoming request. Based on Asm89\Stack\Cors by asm89
	 * @see https://github.com/asm89/stack-cors/blob/master/src/Asm89/Stack/Cors.php
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
     public function handle($request, Closure $next)
 	{
 		if ($this->isSameDomain($request) || ! $this->cors->isCorsRequest($request)) {
 			return $next($request);
 		}

 		if ( ! $this->cors->isActualRequestAllowed($request)) {
 			abort(403);
 		}

         $cors = $this->cors;

         $this->dispatcher->listen('kernel.handled', function($request, $response) use ($cors) {
             $cors->addActualRequestHeaders($response, $request);
         });

 	    return $next($request);
 	}

	/**
	 * @param  \Illuminate\Http\Request  $request
	 * @return bool
	 */
	protected function isSameDomain($request)
	{
		return $request->headers->get('Origin') == $request->getSchemeAndHttpHost();
	}
}
