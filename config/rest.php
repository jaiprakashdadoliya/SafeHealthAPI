<?php

return [
    'supported_formats' => 
	    [
		    'json' => 'application/json',
		    'xml' => 'application/xml',
		    'html' => 'text/html',
		    'csv' => 'application/csv',
		    'jsonp' => 'application/javascript',
		    'php' => 'text/plain',
		    'serialized' => 'application/vnd.php.serialized',
	    ],
	    'http_status_codes' => 
	    [
	        'HTTP_OK' => 200,
	        'HTTP_CREATED' => 201,
	        'HTTP_NO_CONTENT' => 204,
	        'HTTP_NOT_MODIFIED' => 304,
	        'HTTP_BAD_REQUEST' => 400,
	        'HTTP_UNAUTHORIZED' => 401,
	        'HTTP_FORBIDDEN' => 403,
	        'HTTP_NOT_FOUND' => 404,
	        'HTTP_METHOD_NOT_ALLOWED' => 405,
	        'HTTP_NOT_ACCEPTABLE' => 406,
	        'HTTP_CONFLICT' => 409,
	        'HTTP_INTERNAL_SERVER_ERROR' => 500,
	        'HTTP_NOT_IMPLEMENTED' => 501,
	        'HTTP_EXCEPTION'	=> 300
	    ],
	    'rest_config' => 
	    [
	        'force_https' => false,
	        'rest_default_format' => 'json',
	        'rest_status_field_name' => 'code',
	        'rest_message_field_name' => 'message',
	        'rest_data_field_name' => 'result',
	        'rest_http_status_field_name' => 'status',
	        'rest_error_field_name'=>'error',
	        'allow_auth_and_keys' => true,
	        'rest_ip_whitelist_enabled' => false,
	        'rest_handle_exceptions' => true,
	        'rest_ip_whitelist' => '',
	        'rest_ip_blacklist_enabled' => false,
	        'rest_enable_logging' => false,
	        'rest_logs_table' => 'logs',
	        'rest_language' => 'english',
	    ]
];

