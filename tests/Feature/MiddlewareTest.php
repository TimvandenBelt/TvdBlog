<?php

namespace Tests\Feature;

use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\TrustHosts;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_hosts_middleware()
    {
        $trustHosts = new TrustHosts($this->app);

        $this->assertEquals(
            [
                "^(.+\.)?" .
                preg_quote(parse_url(config("app.url"), PHP_URL_HOST)) .
                '$',
            ],
            $trustHosts->hosts(),
        );
    }

    public function test_redirect_if_authenticated_middleware()
    {
        $middleware = new RedirectIfAuthenticated();
        $request = Request::create(route("login"), "GET");

        $response = $middleware->handle($request, function () {
            return 200;
        });
        $this->assertEquals(200, $response);

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $middleware->handle($request, function () {});
        $this->assertEquals(302, $response->getStatusCode());
    }
}
