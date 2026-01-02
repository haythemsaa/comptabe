<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class BladeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Date formatting directive
        Blade::directive('dateFormat', function ($expression) {
            return "<?php echo \App\Helpers\FormatHelper::date($expression); ?>";
        });

        // Currency formatting directive
        Blade::directive('currency', function ($expression) {
            return "<?php echo \App\Helpers\FormatHelper::currency($expression); ?>";
        });

        // Number formatting directive
        Blade::directive('number', function ($expression) {
            return "<?php echo \App\Helpers\FormatHelper::number($expression); ?>";
        });

        // Percentage formatting directive
        Blade::directive('percentage', function ($expression) {
            return "<?php echo \App\Helpers\FormatHelper::percentage($expression); ?>";
        });
    }
}
