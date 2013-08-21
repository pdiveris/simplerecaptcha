<?php namespace Unodepiera\Simplerecaptcha;

use Illuminate\Support\ServiceProvider;

class SimplerecaptchaServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('unodepiera/simplerecaptcha');
		require __DIR__ . '/validation.php';
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app["simplerecaptcha"] = $this->app->share(function($app){
			return new Simplerecaptcha;
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}