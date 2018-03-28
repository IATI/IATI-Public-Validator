<?php
/* $error_msg and $upload_msg are both set as global variables in:
 *  functions/fetch-data_from_urls.php
 *  functions/process_files.php
 * 
 * If the file upload is problematic, we issue and alert and present the upload form again
 * If successful we present a tabbed interface with info about the file.
*/

require_once 'pages/validate-xsd_functions.php';
require_once 'functions/detect_iati_version.php';
?>
<?php if( !isset($_SESSION['uploadedfilepath']) ) :?>
	 <?php header('Location: index.php'); ?>
<?php else: ?>
		<?php
			$file_path = $_SESSION['uploadedfilepath']; //Sanitise/Check this?
      require_once 'functions/get_xml.php';
      $xml = get_xml($file_path);
      if($xml === FALSE) return FALSE; // Need this to prevent entity security problem

      $detected_version = detect_iati_version($xml);

      //Get the right schema to validate against
      //Has the version been set by the user to a valid IATI version?
      //echo $_SESSION["version"];
      if(!isset($version) || $version == "auto") { //We put this in place so tests work! tests.php specifies the version already by this point.
        if (isset($_SESSION["version"]) && in_array($_SESSION["version"],$iati_versions)) {
          //$detected_version = check_iati_version($xml);
          $version = $_SESSION["version"];
          //echo $version;
        } else {
          //..else..lets try to detect the version
          //NB if user has selected auto detect, then we end up here as well
          //Check the detected version is a valid version
          if (!in_array($detected_version,$iati_versions)) {
            $version = $current_version;
            if (isset($detected_version) && $detected_version !=NULL) {
              if (isset($error_msg)) {
                 $error_msg .= '<br/>';
              } else {
                  $error_msg = '';
              }
              $error_msg .= htmlspecialchars($detected_version) . " is not a valid version of the IATI Standard";
            }
          } else {
            $version = $detected_version;
          }
          //echo $version;
        } 
        //If by now we don't have a valid version...
        if(!isset($version) || $version==FALSE) {
          //Use the default. Default is current live version of the standard.
          //echo "true"; echo $current_version;
          $version = $current_version; //$current_version is declared at the top of index.php
        }
      }
      //echo $version; //die;
      
      
        
			if ($xml->getElementsByTagName("iati-organisation")->length == 0) {
			$xsd = "iati-schemas/" . $version . "/iati-activities-schema.xsd";
          $schema = "Activity";
			} else {
			$xsd = "iati-schemas/" . $version . "/iati-organisations-schema.xsd";
          $schema = "Organisation";
			}

      $valid = $xml->schemaValidate($xsd); 
			
		?>
    
    <?php if(isset($error_msg)) :?>
		<div class="alert alert-error">
			<button type="button" class="close" data-dismiss="alert">×</button>
			<strong>Sorry.</strong> There are problems with this file.<br/>
			<?php echo $error_msg; ?>
		</div>
    <?php endif; ?>	
    
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
								<div class="alert alert-success">This file validates against the IATI <?php echo $schema; ?> Schema version <?php echo $version; ?></div>
                
                <?php 
                  if (isset($detected_version) && $detected_version !=NULL) {
                    if (!in_array($detected_version,$iati_versions)) {
                      echo '<h3 class="fail">Fail</h3>';
                      echo '<div class="alert alert-error">';
                      echo 'We detected version '. htmlspecialchars($detected_version) . ' in your file.<br/>You should use a recognised version of the schema.';
                      if ($valid == TRUE && $version != $detected_version) {
                         echo '<br/>You could consider declaring ' . $version . ' instead.';
                       }
                      echo '</div>';
                    } else {
                       echo '<h3 class="success">Success</h3>';
                       echo '<div class="alert alert-success">';
                       echo 'We detected version '. htmlspecialchars($detected_version) . ' declared in your file which is a recognised version of the schema.';
                       if ($valid == TRUE && $version != $detected_version && floatval($version) > floatval($detected_version)) {
                         echo '<br/>You could consider declaring ' . $version . ' instead.';
                       }
                       echo '</div>';
                    }
                  }
                ?>

							<?php else: ?>
								<h3 class="fail">Fail</h3>
								<div id="intext">
									<div class="alert alert-error">This file does NOT validate against the IATI <?php echo $schema; ?> Schema (version <?php echo $version; ?>)</div>

                  <?php if (in_array($version, array("2.01", "2.02", "2.03"))) {
                            echo '<div class="alert alert-info">NOTE: In version '.$version.', order of elements in the data is enforced.<br/>If only 1 error message is reported this MAY be because the order of elements is wrong. In this case, fix the error, and then try again</div>';
                          }
                  ?>
                  
                  <h4>Errors and Warnings</h4>
                  <div class="well well-large">
                    <?php echo libxml_error_count(); ?>
                  </div>

									See <a href="#extra">Extra info</a> for details about the errors.
                  
                  <?php 
                  if (isset($detected_version) && $detected_version !=NULL) {
                    if (!in_array($detected_version,$iati_versions)) {
                      echo '<h3 class="fail">Fail</h3>';
                      echo '<div class="alert alert-error">';
                      echo 'We detected version '. htmlspecialchars($detected_version) . ' in your file.<br/>You should use a recognised version of the schema.';
                      if ($valid == TRUE && $version != $detected_version) {
                         echo '<br/>You could consider declaring ' . $version . ' instead.';
                       }
                      echo '</div>';
                    } else {
                       echo '<h3 class="success">Success</h3>';
                       echo '<div class="alert alert-success">';
                       echo 'We detected version '. htmlspecialchars($detected_version) . ' declared in your file which is a recognised version of the schema.';
                       if ($valid == TRUE && $version != $detected_version && floatval($version) > floatval($detected_version)) {
                         echo '<br/>You could consider declaring ' . $version . ' instead.';
                       }
                       echo '</div>';
                    }
                  }
                ?>
                
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
