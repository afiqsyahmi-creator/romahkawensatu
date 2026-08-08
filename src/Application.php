<?php
declare(strict_types=1);

namespace App;

use Authentication\AuthenticationService;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Middleware\AuthenticationMiddleware;
use Cake\Core\Configure;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Psr\Http\Message\ServerRequestInterface;

class Application extends BaseApplication implements AuthenticationServiceProviderInterface
{
    public function bootstrap(): void
    {
        parent::bootstrap();
        $this->addPlugin('Authentication');
    }

    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $middlewareQueue
            ->add(new ErrorHandlerMiddleware(Configure::read('Error'), $this))
            ->add(new AssetMiddleware(['cacheTime' => Configure::read('Asset.cacheTime')]))
            ->add(new RoutingMiddleware($this))
            ->add(new BodyParserMiddleware())
            ->add(new CsrfProtectionMiddleware(['httponly' => true]))
            ->add(new AuthenticationMiddleware($this));

        return $middlewareQueue;
    }

    /**
     * Identify ADMINS only, by email + password_hash, against the `admin`
     * table (the Admins model). Customers never authenticate.
     */
    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        // ── Two different URL forms are needed ────────────────────────────
        //
        //  loginUrl is matched by DefaultUrlChecker:
        //    _getUrlFromRequest()  PREPENDS the request base  → /romahkawensatu/admin/login
        //    Router::url()          PREPENDS _requestContext   → /romahkawensatu/admin/login
        //    → loginUrl = '/admin/login' (bare route, no base)
        //
        //  unauthenticatedRedirect is used by AuthenticationService:
        //    parse_url() — does NOT prepend anything
        //    → must be the FULL path including the base: /romahkawensatu/admin/login
        //
        $base  = $request->getAttribute('base');
        $loginUrl           = '/admin/login';
        $unauthenticatedUrl = $base . '/admin/login';

        $service = new AuthenticationService([
            'unauthenticatedRedirect' => $unauthenticatedUrl,
            'queryParam' => 'redirect',
        ]);

        // Session first, so a logged-in admin stays logged in across requests.
        $service->loadAuthenticator('Authentication.Session');

        // The login form posts inputs named "email" and "password".
        // The identifier (current format: 'className' key) maps those to the
        // ADMIN table columns + the Admins model.
        $service->loadAuthenticator('Authentication.Form', [
            'loginUrl' => $loginUrl,
            'fields' => [
                'username' => 'email',
                'password' => 'password',
            ],
            'identifier' => [
                'className' => 'Authentication.Password',
                'fields' => [
                    'username' => 'email',
                    'password' => 'password_hash',
                ],
                'resolver' => [
                    'className' => 'Authentication.Orm',
                    'userModel' => 'Admins',
                ],
            ],
        ]);

        return $service;
    }
}