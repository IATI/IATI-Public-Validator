<?php
//error_reporting(0);
session_start(); //We use sessions to track the uploaded file through the application
include "settings.php"; //site installation specifics
include "functions/process_files.php"; //used to deal with file uploads, pasting of code and fetching data from urls
include "vars.php"; // contains definitions such as the test pages, and iati standard versions
include "functions/index.php"; // contains the extra functions needed for this index page

//Sanitize the $_GET vars
if (isset($_GET['version-drop-down'])) {
  if (filter_var($_GET['version-drop-down'], FILTER_VALIDATE_FLOAT)) {
    if ( in_array($_GET['version-drop-down'], $iati_versions) ) {
      $_SESSION["version"] = $_GET['version-drop-down'];
    }
  } else {
    if ($_GET['version-drop-down'] === "auto") {
      $_SESSION["version"] = "auto";
    }
  }
} else {
  if ( !isset($_SESSION["version"]) ) {
    //$_SESSION["version"] = $current_version;
    $_SESSION["version"] = "auto";
  } else {
    if (!in_array($_SESSION["version"], $iati_versions) && $_SESSION["version"] != "auto") {
      //$_SESSION["version"] = $current_version;
      $_SESSION["version"] = "auto";
    }
  }
}

if (isset($_GET['test'])) {
	$test = filter_var($_GET['test'], FILTER_SANITIZE_STRING);
	if (!array_key_exists($test,$tests)) {
		$test = "default";
	}
} else {
	$test = "default"; //NB default will return us to the front page but retain session info
}

if (isset($_GET['perm'])) {
	$exisiting_file = filter_var($_GET['perm'], FILTER_SANITIZE_STRING);
	if (file_exists("upload/" . $exisiting_file)) {
		$_SESSION['uploadedfilepath'] = $file_path = "upload/" . $exisiting_file;
    $_SESSION['wellformed'] = FALSE; //Set this so we go straight to the wellformed results
    //echo $_SESSION['uploadedfilepath'];
	} else {
    $error_msg = "The temporary link you are trying to reach does not exist or has expired";
  }
}


$toptab = "home";
include "header.php";
?>
	<div class="container">
		<div class="row">
      <div class="span10" style="float:right">
				<!-- Main hero unit for a primary marketing message or call to action -->
				<div class="hero-unit">
					<?php
            if (isset ($test) && $test != "reset") { //we need this because session info does not get reset until we call get_page("reset") below!
              echo uploaded_file_info();
            }
            //This is where the main subject of the page is rendered
						include get_page($test);
					?>

				</div><!--end Hero unit-->
			</div><!--end Row-->

      <!--Sidebar-->
			<div class="span2" style="float:left">

        <!--Version Switcher-->
				<div class="well sidebar-nav">
          <form method="get" action="index.php">
            <fieldset>
              <legend>Version</legend>
              <label for="version-drop-down">Schema version</label>
              <select id="version-drop-down" name="version-drop-down" class="span1">
                <option <?php if (!isset($_SESSION["version"]) || $_SESSION["version"] == "auto") { echo 'selected="selected"'; } ?> value="auto">Auto Detect</option>
              <?php foreach ($iati_versions as $version) { ?>
                <option <?php if (isset($_SESSION["version"]) && $_SESSION["version"] == $version) { echo 'selected="selected"'; } ?> value="<?php echo $version ?>"><?php echo $version ?></option>
              <?php } ?>
              </select>
              <button id="switch" type="submit" class="btn btn-primary">Switch</button>
              <?php if (isset($test) && $test != "default" && in_array($test,$tests)) { echo '<input type="hidden" name="test" value="' . $test . '" />'; } ?>
              <!--<input type="submit" value="Submit" />-->
            </fieldset>
          </form>
        </div>

        <!--Tests Navigation-->
				<div class="well sidebar-nav">
					<?php if (isset($_SESSION['uploadedfilepath'])): //Only show links if we have a file?>
					<ul class="nav nav-list">
					  <li class="nav-header">Tests</li>
					  <li><a href="<?php echo $host; ?>">Well Formed</a></li>
					  <?php if (isset($_SESSION['wellformed']) && $_SESSION['wellformed'] == TRUE): //Only show validation options if we have a well formed file?>
              <li><a href="<?php echo $host; ?>?test=xsd">Validate</a></li>
					  <?php endif; ?>
					</ul>
					<?php else: //We don't have a file to test so display some instructions?>
            <p>Let us test your data.</p>
            <p>Upload a file, paste some code or point us to a file on the internet and we can give you some basic information about how well the data performs against the IATI standard.</p>
            <p>Files over 10MB in size may be rejected by the application.</p>
            <p>Errors on line number 65,536 or greater may state an incorrect line number.</p>
					<?php endif; ?>
        </div><!--/.well -->

        <!--File Statistics Navigation-->
        <?php if (isset($_SESSION['wellformed']) && $_SESSION['wellformed'] == TRUE): ?>
          <div class="well sidebar-nav">
            <ul class="nav nav-list">
              <li class="nav-header">File Statistics</li>
              <li><a href="<?php echo $host; ?>?test=basic">Basic Info</a></li>
              <li><a href="<?php echo $host; ?>?test=elements">Elements</a></li>
            </ul>
          </div><!--/.well -->
        <?php endif; ?>



			</div><!--end Sidebar-->

		</div><!--end Row-->

    <?php if (isset($_SESSION['uploadedfilepath'])) : ?>
		<div class="row">
      <div class="span10 offset2">
        <h4>Share these results</h4>
        <p>
          The link below will return you to the home page with the file you are inspecting pre-loaded into the system.<br/>
          Links expire approximately 3 days after a file has been submitted.<br/>
          <a href="<?php echo $host . "?perm=" . htmlentities(basename($_SESSION['uploadedfilepath'])); ?>">Share these results</a><br/>
        </p>
      </div>
    </div>
    <?php endif; ?>
<?php

$extrascripts = <<<HTML
    <script>
      //This is the tabs
      $('#myTab a').click(function (e) {
        e.preventDefault();
        $(this).tab('show');
      })
      $('#intext a').click(function (e) {
        e.preventDefault();
        $('#myTab a[href="#extra"]').tab('show');
      })
      $(function () {
        $('#myTab a[href="#status"]').tab('show');
        $('#myTab a[href="#fileTab"]').tab();
        $('#myTab a[href="#extra"]').tab();
        $('#myTab a[href="#passed"]').tab();
        $('#myTab a[href="#failed"]').tab();
        $('#myTab a[href="#warnings"]').tab();
        //$('#myTab a[href="#settings"]').tab();
      })
    </script>
HTML;

include "footer.php";
