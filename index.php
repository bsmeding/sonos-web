<?php
	require( "vendor/autoload.php" );
	require( "settings.php" );
	
	// Initialize SLIM webapp
	$app = new \Slim\Slim();
	
	// Initialize controller
	try {
		$sonos = new \duncan3dc\Sonos\Network;
		$controller = $sonos->getControllerByIp(SONOS_IP);
	} catch( Exception $e ) {
		$app->response->setStatus(404);
		echo $e->getMessage();
		exit;
	}
	
	// Setup slim REST interface. 
	// We always return JSON and allow access from other places
	$app->response->headers->set('Content-Type', 'application/json');
	$app->response->headers->set('Access-Control-Allow-Origin', '*');
	$app->get('/status', function () use($controller) {
		// Create a status object
		$status = array(
			"volume" => array(
				"level" => $controller->getVolume(),
				"muted" => $controller->isMuted(),
					
				"treble" => $controller->getTreble(),
				"bass" => $controller->getBass(),
				"loudness" => $controller->getLoudness(),
			),
			"state" => array(
				"id" => $controller->getState(),
				"name" => $controller->getStateName(),
				"details" => $controller->getStateDetails()
			),
			"options" => array(
				"shuffle" => $controller->getShuffle(),
				"repeat" => $controller->getRepeat(),
				"crossfade" => $controller->getCrossfade(),
			),
			"queue" => $controller->getQueue()->getTracks()
		);
		
		echo json_encode($status);
	});
	
	// Set volume
	$app->post( "/volume", function() use($controller, $app) {
		$newVolume = array_key_exists( "volume", $_POST ) ? $_POST[ "volume" ] : -1;
		
		if( $newVolume < 0 || $newVolume > 100 ) {
			$app->response->setStatus(400);
			echo json_encode( "Invalid volume given" );
			return;
		}
		
		$controller->setVolume($newVolume);
		echo json_encode( "OK" );
	});
	
	// Play
	$app->post( "/play", function() use($controller) {
		$controller->play();
		echo json_encode( "OK" );
	});
		
	// Pause
	$app->post( "/pause", function() use($controller) {
		$controller->pause();
		echo json_encode( "OK" );
	});

	// Next
	$app->post( "/next", function() use($controller) {
		$controller->next();
		echo json_encode( "OK" );
	});
	
	// Previous
	$app->post( "/previous", function() use($controller) {
		$controller->previous();
		echo json_encode( "OK" );
	});
		
	
	$app->run();
