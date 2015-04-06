<?php namespace Fooblog\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Auth;
use DB;
use Config;

class OAuth
{

    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $storage = new \OAuth2\Storage\Pdo(
            DB::connection(
                Config::get(
                    'oauth2server.connection_name',
                    Config::get( 'database.default' )
                )
            )->getPdo()
        );

        $oAuth2Server = new \OAuth2\Server($storage, [
            'storage_engine'  => 'pdo',
            'access_lifetime' => 1800, // 30 minutes
            'refresh_token_lifetime' => 1800 // 30 minutes
            ] 
        );

        $oauthRequest  = \OAuth2\HttpFoundationBridge\Request::createFromGlobals();
        $response = new \OAuth2\HttpFoundationBridge\Response();
        $tokenData = $oAuth2Server->getAccessTokenData($oauthRequest);

        if ( $tokenData['user_id'] )
        {
            Auth::loginUsingId($tokenData['user_id']);
            return $next($request);
        }
        else
        {
            return response()->json('Unauthorized.', 401);
        }
    }

}
