<?php namespace App\www;

use Phlex\Routing\Router;

class App{

	public function route() {
		$router = new Router( \Phlex\Request\Request::getCurrent() );
		$router
			->any('/API/*', function(){
				$router = new Router( \Phlex\Request\Request::getCurrent() );
				return $router
					->any('*', function(){ return false; 'APIKeyCheckController, returns false on ok, renders error on error'; })
					->get('/API/product/*', function(){ return false; })
					->post('/API/product/*', function(){ return false; })
				;
			})
			->get('/articleData/*', function(){ return ArticleController::factory()->someData(); })
			->get('/article/*', function(){ return ArticleController::factory()->articlePage(); })
		;
	}

}
