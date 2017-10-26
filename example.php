<?php

/**
 * @author Artsiom Kirkor, info@kas.by 
 * @copyright dm.kas.by 2017
 */


namespace App\Shop;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Route;


class ShopServiceProvider extends ServiceProvider {

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
		$ds = DIRECTORY_SEPARATOR;
		$basedir = dirname( dirname( __DIR__ ) ) . $ds;

		$this->loadRoutesFrom( $basedir . 'routes.php' );
		$this->loadViewsFrom( $basedir . 'views', 'shop' );

		$this->publishes( [ $basedir . 'config/shop.php' => config_path( 'shop.php' ) ], 'config' );
		$this->publishes( [ dirname( $basedir ) . $ds . 'public' => public_path( 'packages/app/shop' ) ], 'public' );
	}


	/**
	 * Register the serv. provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->mergeConfigFrom( dirname( dirname( __DIR__ ) ) . DIRECTORY_SEPARATOR . 'default.php', 'shop');

		$this->app->singleton('App\Shop\Base\App', function($app) {
			return new \App\Shop\Base\App($app['config']);
		});

		$this->app->singleton('App\Shop\Base\Config', function($app) {
			return new \App\Shop\Base\Config($app['config'], $app['App\Shop\Base\App']);
		});

		$this->app->singleton('App\Shop\Base\I18n', function($app) {
			return new \App\Shop\Base\I18n($this->app['config'], $app['App\Shop\Base\App']);
		});

		$this->app->singleton('App\Shop\Base\Locale', function($app) {
			return new \App\Shop\Base\Locale($app['config']);
		});

		$this->app->singleton('App\Shop\Base\Context', function($app) {
			return new \App\Shop\Base\Context($app['session.store'], $app['App\Shop\Base\Config'], $app['App\Shop\Base\Locale'], $app['App\Shop\Base\I18n']);
		});

		$this->app->singleton('App\Shop\Base\Page', function($app) {
			return new \App\Shop\Base\Page($app['config'], $app['App\Shop\Base\App'], $app['App\Shop\Base\Context'], $app['App\Shop\Base\Locale'], $app['App\Shop\Base\View']);
		});

		$this->app->singleton('App\Shop\Base\Support', function($app) {
			return new \App\Shop\Base\Support($app['App\Shop\Base\Context'], $app['App\Shop\Base\Locale']);
		});

		$this->app->singleton('App\Shop\Base\View', function($app) {
			return new \App\Shop\Base\View($app['config'], $app['App\Shop\Base\I18n'], $app['App\Shop\Base\Support']);
		});


		$this->commands( array(
			'App\Shop\Command\AccountCommand',
			'App\Shop\Command\CacheCommand',
			'App\Shop\Command\SetupCommand',
			'App\Shop\Command\JobsCommand',
		) );
	}


	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array(
			'App\Shop\Base\App', 'App\Shop\Base\I18n', 'App\Shop\Base\Context',
			'App\Shop\Base\Config', 'App\Shop\Base\Locale', 'App\Shop\Base\View',
			'App\Shop\Base\Page', 'App\Shop\Base\Support',
			'App\Shop\Command\AccountCommand', 'App\Shop\Command\CacheCommand',
			'App\Shop\Command\SetupCommand', 'App\Shop\Command\JobsCommand',
		);
	}

}