<?php
function libxml_error_count() {
	$errors = libxml_get_errors();
	$codes = array();
  $messages = array();
	foreach ($errors as $error) {
		$code = $error->code; 
		$codes[] = $code;
    $message = trim($error->message);
    $messages[] = $message;
	}
	$codes = array_unique($codes);
  $messages = array_unique($messages);
  //$messages = $codes = array("gg");
	//$response =  "<p class=\"text-error\">There are " . count($codes) . " different types of error code in the file.</p>";
  $response =  "<p class=\"text-error\">" . count($messages) . " " . (count($messages) == 1 ? '' : 'different') . " error message" . (count($messages) == 1 ? ' is' : 's are') . " reported.</p>";
  $response .=  "<p class=\"text-error\">" . count($codes) . " type" . (count($codes) == 1 ? '' : 's') . " of  error code" . (count($codes) == 1 ? ' is' : 's are') . " reported.</p>";
	$response .=  "<p class=\"text-info\">Fixing them will remove " . count($errors) . " errors from the file.</p>";
	return $response;
}

function libxml_display_all_errors() {
    $errors = libxml_get_errors();
    $codes = array();
    print("<table id='errors' class='table-striped'><thead><th>Line</th><th>Severity and code</th><th>Message</th></thead><tbody>");
    $i=1;
    if ($i % 2 == 0) {
		$class = 'even';
	} else {
		$class ='odd';
	}
    foreach ($errors as $error) {
		$code = $error->code; 
		//if (!in_array($code,$codes)) {
			$codes[] = $code;
			if ($i % 2 == 0) {
				$class = 'even';
			} else {
				$class ='odd';
			}
			$i++;
			print libxml_display_error($error,$class);
      if (strstr($error->message, "anyURI")) {
        $extra_message = 'The checking of URL\'s may be incorrect please see <a href="'. $host . 'common_errors.php">Common errors</a> for more info';
      }
		//}
    }
    print("</tbody></table>");
    if (isset($extra_message)) {
      echo '<br/><div class="alert alert-error">' . $extra_message . '</div>';
    }
    libxml_clear_errors();
}

function libxml_display_error($error,$class) {
	//print_r($error);
    $return = '<tr>';
     $return .= "<td>$error->line</td>";
    switch ($error->level) {
        case LIBXML_ERR_WARNING:
            $return .= "<td class='warning'><b>Warning $error->code</b></td>";
            break;
        case LIBXML_ERR_ERROR:
            $return .= "<td class='error'><b>Error $error->code</b></td>";
            break;
        case LIBXML_ERR_FATAL:
            $return .= "<td class='fatal'><b>Fatal Error $error->code</b></td>";
            break;
    }
    $return .= "<td>" . trim($error->message) . "</td>";
    //if ($error->file) {
       // $return .=    " in <b>" . basename($error->file) . "</b>";
    //}
    $return .= "</tr>";

    return $return;
}
?>
