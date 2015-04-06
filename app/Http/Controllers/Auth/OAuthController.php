<?php namespace Fooblog\Http\Controllers\Auth;

use Fooblog\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Auth;
use Input;
use Request;
use Response;
use Log;
use DB;
use Config;

class OAuthController extends Controller
{

    /**
     * Logs a user in via Oauth. Creates oauth access token corresponding to
     * the email and password specified.
     */
    public function store()
    {
        $auth = Auth::attempt(
            [ 
            'email' => Input::get('email'),
            'password' => Input::get('password') ]
        );

        if ( $auth ) {
            $userId = Auth::user()->id;
        }
        else
        {
            return response()
                ->json( ['message' => 'The credentials supplied are invalid.'] )
                ->setStatusCode( 400 );
        }

        $request = \OAuth2\HttpFoundationBridge\Request::createFromRequest(Request::instance());
        $response = new \OAuth2\HttpFoundationBridge\Response();

        $request->request->set( 'client_id', Input::get('client_id') );
        $request->request->set( 'client_secret', Input::get('client_secret') );

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

        $oAuth2Server->getAuthorizeController();
        
        $authCodeObj = $oAuth2Server->getResponseType('code');
        
        $authCode = $authCodeObj->createAuthorizationCode( Input::get('client_id'), $userId, null );
        $request->request->set('code', $authCode);
        $request->request->set('grant_type', 'authorization_code');
        
        $oauthResponse = $oAuth2Server->handleTokenRequest($request, $response);
        $oauthResponse->addParameters(['user_id' => Auth::user()->id]);

        return response()
            ->json( json_decode($oauthResponse->getContent()) )
            ->setStatusCode( 201 );
    }
}
