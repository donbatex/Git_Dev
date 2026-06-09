<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Dedoc\Scramble\support\Generator\OpenApi;
use Dedoc\Scramble\support\Generator\SecurityScheme;
use Dedoc\Scramble\Scramble;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        RateLimiter::for('api', function (Request $request) {
            // $user = $request->user();
            // $userId = $user ? $user->id : $request->ip();

            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        Scramble::afterOpenApiGenerated(function (OpenApi $openApi) {
            // Add custom metadata to the OpenAPI documentation
            $openApi->secure(
                SecurityScheme::http('bearer', 'bearerAuth')
            );
           
        });
    }
}
