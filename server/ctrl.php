<?php
/**
 * FileAPI upload controller (example)
 */

include    './FileAPI.class.php';


if( !empty($_SERVER['HTTP_ORIGIN']) ){
	// Enable CORS
	FileAPI::enableCORS();
}


if( $_SERVER['REQUEST_METHOD'] == 'OPTIONS' ){
	exit;
}


if( strtoupper($_SERVER['REQUEST_METHOD']) == 'POST' ){
	$files	= FileAPI::getFiles(); // Retrieve File List
	$images	= array();


	// Fetch all image-info from files list
	fetchImages($files, $images);


	// JSONP callback name
	$jsonp	= isset($_REQUEST['callback']) ? trim($_REQUEST['callback']) : null;


	// JSON-data for server response
	$json	= array(
		  'images'	=> $images
		, 'data'	=> array('_REQUEST' => $_REQUEST, '_FILES' => $files)
	);


	// Server response: "HTTP/1.1 200 OK"
	FileAPI::makeResponse(array(
		  'status' => FileAPI::OK
		, 'statusText' => 'OK'
		, 'body' => $json
	), $jsonp);
	exit;
}




function fetchImages($files, &$images, $name = 'file'){
	if( isset($files['tmp_name']) ){
		$mime = $files['type'];
		$filename = $files['tmp_name'];

		if( strpos($mime, 'image/') !== false ){
			$size = getimagesize($filename);
			$base64 = base64_encode(file_get_contents($filename));

			$images[$name] = array(
				  'width'	=> $size[0]
				, 'height'	=> $size[1]
				, 'mime'	=> $mime
				, 'size'	=> filesize($filename)
				, 'dataURL'	=> 'data:'. $mime .';base64,'. $base64
			);
		}
	}
	else {
		foreach( $files as $name => $file ){
			fetchImages($file, $images, $name);
		}
	}
}
?>
