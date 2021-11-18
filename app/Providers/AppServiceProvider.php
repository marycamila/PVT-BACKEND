<?php

namespace App\Providers;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Resources\Json\JsonResource;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->register(\L5Swagger\L5SwaggerServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
         // JSON response wihtout data key
         JsonResource::withoutWrapping();

         // Localization
         setlocale(LC_TIME, env('APP_LC_TIME', 'es_BO.utf8'));
         Carbon::setLocale(env('APP_LOCALE', 'es'));
 
         // Custom validations
         Validator::extend('alpha_spaces', function ($attribute, $value) {
             return preg_match('/^[\pL\s]+$/u', $value);
         });
    }
}
