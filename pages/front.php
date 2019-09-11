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


<?php //Upload file not present, error message set, wellformed not set (this is set at false after data has been received and not yet tested)
if( (sizeof($_FILES)==0 && !isset($_SESSION['uploadedfilepath']) || isset($error_msg) ) || !isset($_SESSION['wellformed']) ) :?>
<?php //debug
  /*
  echo sizeof($_FILES) . '<br/>';
  echo $_SESSION['uploadedfilepath'] . '<br/>';
  echo $error_msg . '<br/>';
  echo $file_path . '<br/>';
  echo $_SESSION['wellformed'] . '<br/>';
  */
?>
	<p class="lead">Test IATI XML</p>
	<div class="row">
		<div class="span9">
			<ul class="nav nav-tabs" id="myTab">
				<li class="active"><a href="#status">Upload</a></li>
				<li><a href="#fileTab">Fetch file from web</a></li>
				<li><a href="#extra">Paste XML</a></li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane active" id="status">
					<div class="span6">
						<form action="index.php" method="post" enctype="multipart/form-data"  >
              <fieldset>
                <legend>Upload File</legend>
                <label for="file">File:</label>
                <input type="file" name="file" id="file" class="span5"/>
                <span class="help-block">Upload an XML file of IATI data.</span>
                <button type="submit" class="btn btn-primary">Upload</button>
              </fieldset>
						</form>
					</div>
				</div>
				<div class="tab-pane" id="fileTab">
					<!--<div class="span1">OR:</div>-->
					<div class="span6">
						<form method="post" action="index.php">
              <fieldset>
                <legend>Fetch data from the Web</legend>
                <label for="url">URL of file:</label>
                <input type="text" placeholder="Paste URL here" name="url" id="url" class="span5" />
                <span class="help-block">Enter an address of an IATI compliant XML file.</span>
                <button type="submit" class="btn btn-primary">Fetch Data</button>
              </fieldset>
						</form>
					</div>
				</div>
				<div class="tab-pane" id="extra">
					<div class="span6">
						<form action="index.php" method="post">
              <fieldset>
                <legend>Paste XML</legend>
                <label for="paste">XML:</label>
                <textarea rows="8" class="span5" name="paste" id="paste"></textarea>
                <span class="help-block">Paste your XML here.</span>
                <button type="submit" class="btn btn-primary">Submit</button>
              </fieldset>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
  
  <hr />
  <div class="row">
      <div class="span9">
        <h4>About the IATI Public Validator</h4>
        <p>This is a designed as a quick, simple service to allow people to check their IATI XML files.</p>
        <p>Because IATI files can be varied, complex or even very simple depending on the reporting organisation's needs, 'validation' is a difficult concept.</p>
        <p>This tool performs some basic checks around the XML, and then some compliance checks against the IATI Standard, an agreed set of political desires, that are not enforced by the IATI schema.</p>
      </div>
    </div>
  
<?php else: ?>
		<?php
			if (isset($_SESSION['uploadedfilepath'])) {
				$file_path = $_SESSION['uploadedfilepath']; //Sanitise/Check this?
			}

      require_once 'functions/get_xml.php';
      $dom = get_xml($file_path);
      if($dom === FALSE) return FALSE;

      if (isset($dom)) {
        $sxe = @simplexml_import_dom($dom);
      }
			//$sxe = simplexml_load_file($file_path);
			if (!$sxe) {
				$_SESSION['wellformed'] = FALSE;
				$test_result = '<div class="alert alert-error"><strong>Sorry.</strong> This is not a well-formed xml file</div>';
				$error_detail =  "Failed loading XML<br/><ul>";
				foreach(libxml_get_errors() as $error) {
					$error_detail .= "<li>" . $error->message . "</li>";
				}
				$error_detail . "</ul>";
			} else {
				$_SESSION['wellformed'] = TRUE;
				$test_result = '<div class="alert alert-success"><strong>Great.</strong> This is a well formed xml file.</div><div class="alert alert-info">This does NOT mean that it validates against the IATI schema.<br/><strong><a href="?test=xsd">Test Validation</a></strong></div>';
			}
      //foreach (libxml_get_errors() as $error) {
      //  echo $error;
      //}
		?>
		<h2>Well Formed XML test</h2>
		<ul class="nav nav-tabs" id="myTab">
		  <li class="active"><a href="#status">Status</a></li>
		  <li><a href="#fileTab">File Details</a></li>
		  <?php if (isset($error_detail)): ?>
			<li><a href="#extra">Extra info</a></li>
		  <?php endif; ?>
		  <!--<li><a href="#settings">Settings</a></li>-->
		</ul>

		<div class="tab-content">
		  <div class="tab-pane active" id="status">
			  <!--<h3>Status: We got a file!</h3>-->
				<!--<div class="row">
					<div class="span9">
						<div class="span5">-->
							<!--<h3>Well-formed check:</h3>-->

							<div>This check tells us if machines are going to be able to read this file.<br/><br/></div>
              <?php echo $test_result; ?>
							<?php if (isset($error_detail)): ?>
								<div id="intext">
									See <a href="#extra">Extra info</a> for details about the errors.
								</div>
								See <a href="<?php echo $host; ?>common_errors.php">Common errors</a> for help in understanding the errors.
							<?php endif; ?>

						<!--</div>
					</div>
				</div>-->
		    </div>
			<div class="tab-pane" id="fileTab">
			  <!--<div class="span3">-->
					<!--<h3>File Details:</h3>-->
					<div>
            <?php
              if (isset($_SESSION['upload_msg'])) {
                echo $_SESSION['upload_msg'];
              } else {
                echo "File: " . $testing_file_name . "<br/>";
                echo "Size: " . round(filesize($file_path) / 1024,2) . "KB";
              }
            ?>
          </div>
				<!--</div>-->
			</div>
		  <?php if (isset($error_detail)): ?>
			<div class="tab-pane" id="extra">
				<?php echo $error_detail; ?>
			</div>
		  <?php endif; ?>
		  <!--<div class="tab-pane" id="settings">4</div>-->
		</div>

<?php endif; ?>
