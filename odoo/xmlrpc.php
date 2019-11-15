<?php
	function xmlrpc_request($url, $method, $args) {
	    $request = xmlrpc_encode_request($method, $args);
	    $context = stream_context_create(array('http' => array(
	        'method' => "POST",
	        'header' => "Content-Type: text/xml",
	        'content' => $request
	    )));
	    $file = file_get_contents($url, false, $context);
	    $response = xmlrpc_decode($file);
	    if ($response && is_array($response) && xmlrpc_is_fault($response)) {
	    	trigger_error("xmlrpc: $response[faultString] ($response[faultCode])");
	        return null;
	    }
	    return $response;
	}
?>