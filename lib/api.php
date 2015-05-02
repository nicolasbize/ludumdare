<?php

// http://stackoverflow.com/questions/3128062/is-this-safe-for-providing-jsonp
function api_isValidJSONPCallback($subject) {
     $identifier_syntax
       = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*+$/u';

     $reserved_words = array('break', 'do', 'instanceof', 'typeof', 'case',
       'else', 'new', 'var', 'catch', 'finally', 'return', 'void', 'continue', 
       'for', 'switch', 'while', 'debugger', 'function', 'this', 'with', 
       'default', 'if', 'throw', 'delete', 'in', 'try', 'class', 'enum', 
       'extends', 'super', 'const', 'export', 'import', 'implements', 'let', 
       'private', 'public', 'yield', 'interface', 'package', 'protected', 
       'static', 'null', 'true', 'false');

     return preg_match($identifier_syntax, $subject)
         && ! in_array(mb_strtolower($subject, 'UTF-8'), $reserved_words);
}

function api_emitJSON( $out, $debug ) {
	// By default, PHP will make '/' slashes in to '\/'. These flags fix that //
	$out_format = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
	
	// If 'pretty' mode (i.e. readable) //
	if ( isset($_GET['pretty']) ) {
		$out_format |= JSON_PRETTY_PRINT;
	}
	
	// Debug Info //
	if ( $debug ) {
		$url = getenv('REDIRECT_URL');
		if ( $url ) {
			$out['debug'] = array();
			$out['debug']['url'] = $url;
		}
		$query = getenv('REDIRECT_QUERY_STRING');
		if ( $query ) {
			$out['debug']['query'] = $query;
		}
	}
	
	// JSONp //
	$prefix = "";
	$suffix = "";
	if ( isset($_GET['callback']) ) {
		$callback = $_GET['callback'];
		if ( api_isValidJSONPCallback($callback) ) {
			$prefix = $callback . "(";
			$suffix = ");";
		}
		else {
			http_response_code(400);
			exit(1);
		}
	}
	
	// Output the Page //
	header('Content-Type: application/json');
	echo $prefix . str_replace('</', '<\/', json_encode($out,$out_format)) . $suffix;
}

?>
