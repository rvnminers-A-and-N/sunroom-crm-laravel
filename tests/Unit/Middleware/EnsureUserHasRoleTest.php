<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    $this->middleware = new EnsureUserHasRole;
    $this->next = fn ($request) => response('ok', 200);
});

it('aborts with 403 when there is no authenticated user', function () {
    $request = Request::create('/admin/users', 'GET');

    $this->middleware->handle($request, $this->next, 'Admin');
})->throws(HttpException::class, 'Unauthorized.');

it('aborts with 403 when the user has the wrong role', function () {
    $user = User::factory()->create();
    $request = Request::create('/admin/users', 'GET');
    $request->setUserResolver(fn () => $user);

    $this->middleware->handle($request, $this->next, 'Admin');
})->throws(HttpException::class, 'Unauthorized.');

it('passes through when the user role matches the only allowed role', function () {
    $admin = User::factory()->admin()->create();
    $request = Request::create('/admin/users', 'GET');
    $request->setUserResolver(fn () => $admin);

    $response = $this->middleware->handle($request, $this->next, 'Admin');

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getContent())->toBe('ok');
});

it('passes through when the user role matches one of several allowed roles', function () {
    $manager = User::factory()->manager()->create();
    $request = Request::create('/managers-only', 'GET');
    $request->setUserResolver(fn () => $manager);

    $response = $this->middleware->handle($request, $this->next, 'Admin', 'Manager');

    expect($response->getStatusCode())->toBe(200);
});

it('returns the abort exception with the 403 status code', function () {
    try {
        $this->middleware->handle(Request::create('/x', 'GET'), $this->next, 'Admin');
    } catch (HttpException $e) {
        expect($e->getStatusCode())->toBe(403);

        return;
    }

    $this->fail('Expected HttpException was not thrown.');
});
