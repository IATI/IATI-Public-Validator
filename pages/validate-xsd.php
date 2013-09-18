<?php
/* $error_msg and $upload_msg are both set as global variables in:
 *  functions/fetch-data_from_urls.php
 *  functions/process_files.php
 * 
 * If the file upload is problematic, we issue and alert and present the upload form again
 * If successful we present a tabbed interface with info about the file.
*/

require_once 'pages/validate-xsd_functions.php';
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
      /* Some safety against XML Injection attack
     * see: http://phpsecurity.readthedocs.org/en/latest/Injection-Attacks.html
     */
      $raw_xml = file_get_contents($file_path);
      $collapsedXML = preg_replace("/[[:space:]]/", '', $raw_xml);
      //echo $collapsedXML;
      if(preg_match("/<!DOCTYPE/i", $collapsedXML)) {
          //throw new InvalidArgumentException(
         //     'Invalid XML: Detected use of illegal DOCTYPE'
         // );
          //echo "fail";
        return FALSE;
      }
      $loadEntities  = libxml_disable_entity_loader(true);
			$xml = new DOMDocument();
			$xml->loadXML($raw_xml);
      libxml_disable_entity_loader($loadEntities);
      
      //Get the right schema to validate agianst
      //First work out the version
      if (isset($_SESSION["version"]) && in_array($_SESSION["version"],$iati_versions)) {
        $version = $_SESSION["version"];
      } else {
        $version == $current_version; //$current_version is declared at the top of index.php
      }
        
			if ($xml->getElementsByTagName("iati-organisation")->length == 0) {
			$xsd = "http://iatistandard.org/downloads/" . $version . "/iati-activities-schema.xsd";
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
      //$reader->setParserProperty(XMLReader::VALIDATE, true);
      $valid = $reader->setSchema($xsd); //Validate against our schema
      while ($reader->read()) {
        if (!$reader->isValid()) {
          
          $invalid = TRUE;
          //echo "invalid";
          $valid = FALSE;
          break;
        }
      }
			/*if ($xml->schemaValidate($xsd)) {
				//$valid = TRUE;
        //echo "yeeesss";
			} else {
				//$valid = FALSE;
				//libxml_display_all_errors();
			}*/
			
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
