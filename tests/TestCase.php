<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    use AuthManager;
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://form.box';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        Artisan::call('migrate:refresh');
        Artisan::call('db:seed');

        return $app;
    }
}
