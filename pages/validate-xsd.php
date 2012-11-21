<?php
/* $error_msg and $upload_msg are both set as global variables in:
 *  functions/fetch-data_from_urls.php
 *  functions/process_files.php
 * 
 * If the file upload is problematic, we issue and alert and present the upload form again
 * If successful we present a tabbed interface with info about the file.
*/
?>
<?php if(isset($error_msg)) :?>
		<div class="alert alert-error">
			<button type="button" class="close" data-dismiss="alert">×</button>
			<strong>Sorry.</strong> We can't test that file.<br/>
			<?php echo $error_msg; ?>
		</div>
<?php endif; ?>	
	

<?php if( !isset($_SESSION['uploadedfilepath']) ) :?>
	 <?php header('Location: index.php'); ?>
<?php else: ?>
		<?php
			$file_path = $_SESSION['uploadedfilepath']; //Sanitise/Check this?
			libxml_use_internal_errors(true);
			if (file_exists($file_path)) {
        //echo "found file";
      }
			$xml = new DOMDocument();
			$xml->load($file_path);
      
      //Get the right schema to validate agianst
      //First work out the version
      if (isset($_SESSION["version"]) && in_array($_SESSION["version"],$iati_versions)) {
        $version = $_SESSION["version"];
      } else {
        $version == $current_version; //$current_version is declared at the top of index.php
      }
        
      if ($version == $current_version) { //Current version is always at downloads/
        $version_string = "";
      } else {
        $version_string = $version . "/"; //Old versions are always at downloads/{version}/
      } 
			if ($xml->getElementsByTagName("iati-organisation")->length == 0) {
			$xsd = "http://dev.iatistandard.org/downloads/" . $version_string . "iati-activities-schema.xsd";
			//$xsd = $host . "/iati-schema/iati-activities-schema.xsd";
			$schema = "Activity";
      //echo $file_path;
      //print_r($xml);
			
			//if ($myinputs['org'] == "1") { //sanitized $_GET['orgs']
			//  continue;
			//}
			} else {
			$xsd = "http://iatistandard.org/downloads/" . $version_string . "iati-organisations-schema.xsd";
			$schema = "Organisation";
			}
			$reader = new XMLReader();

      $reader->open($file_path);
      $valid = $reader->setSchema($xsd); //Validate against our schema
      while ($reader->read()) {
        if (! $reader->isValid()) {
          
          $invalid = TRUE;
          //echo "invalid";
          $valid = FALSE;
          break;
        }
      }
			if ($xml->schemaValidate($xsd)) {
				//$valid = TRUE;
        //echo "yeeesss";
			} else {
				//$valid = FALSE;
				//libxml_display_all_errors();
			}
			
		?>
		<h2>Validation against the IATI <?php echo $schema; ?> Schema (version <?php echo $version; ?>)</h2>
		<ul class="nav nav-tabs" id="myTab">
		  <li class="active"><a href="#status">Status</a></li>
		  <?php if ($valid == FALSE): ?>
			<li><a href="#extra">Extra info</a></li>
		  <?php endif; ?>
		  <!--<li><a href="#settings">Settings</a></li>-->
		</ul>
		 
		<div class="tab-content">
		  <div class="tab-pane active" id="status">
			  
				<!--<div class="row">
					<div class="span9">
						<div class="span5">-->
							<?php if ($valid == TRUE): ?>
								<h3 class="success">Success</h3>
								<div class="alert alert-success">This file validates against the IATI <?php echo $schema; ?> Schema (version <?php echo $version; ?>) </div>
							<?php else: ?>
								<h3 class="fail">Fail</h3>
								<div id="intext">
									<div class="alert alert-error">This file does NOT validate against the IATI <?php echo $schema; ?> Schema (version <?php echo $version; ?>)</div>
									<?php echo libxml_error_count(); ?> <br/><br/>
									See <a href="#extra">Extra info</a> for details about the errors.
								</div>
									See <a href="<?php echo $host; ?>common_errors.php">Common errors</a> for help in understanding the errors.
								
							<?php endif; ?>
						<!--</div>
					</div>
				</div>-->
		    </div>
		  <?php if ($valid == FALSE): ?>
			<div class="tab-pane" id="extra">
				<p style="font-size:1.1em">See <a href="<?php echo $host; ?>common_errors.php">Common errors</a> for help in understanding the errors.</p>
				<?php libxml_display_all_errors(); ?>
			</div>
		  <?php endif; ?>
		  <!--<div class="tab-pane" id="settings">4</div>-->
		</div>
 
<?php endif; ?>
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
		//}
    }
    print("</tbody></table>");
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
