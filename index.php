<?php
//error_reporting(0);
session_start(); //We use sessions to track the uploaded file through the application
include "settings.php"; //site installation specifics
include "functions/process_files.php"; //used to deal with file uploads, pasting of code and fetching data from urls
include "vars.php"; // contains definitions such as the test pages, and iati standard versions

//Sanitize the $_GET vars
if (isset($_GET['version'])) {
  if (filter_var($_GET['version'], FILTER_VALIDATE_FLOAT)) {
    if ( in_array($_GET['version'], $iati_versions) ) {
      $_SESSION["version"] = $_GET['version'];
    }
  }
} else {
  if ( !isset($_SESSION["version"]) ) {
    $_SESSION["version"] = $current_version;
  } else {
    if (!in_array($_SESSION["version"], $iati_versions)) {
      $_SESSION["version"] = $current_version;
    }
  }
}

if (isset($_GET['test'])) {
	$test = filter_var($_GET['test'], FILTER_SANITIZE_STRING);
	if (!in_array($test,$tests)) {
		$test = "default";
	}
} else {
	$test = "default";
}

if (isset($_GET['perm'])) {
	$exisiting_file = filter_var($_GET['perm'], FILTER_SANITIZE_STRING);
  //echo $exisiting_file;
	if (file_exists("upload/" . $exisiting_file)) {
		$_SESSION['uploadedfilepath'] = $file_path = "upload/" . $exisiting_file;
    $_SESSION['wellformed'] = FALSE; //Set this so we go straight to the wellformed results
    //echo $_SESSION['uploadedfilepath'];
	} else {
    $error_msg = "The tempory link you are trying to reach does not exist or has expired";
  }
}
//Switch on test to decide which pages to load
switch ($test) {
	case "basic": //Menu - File Statistics - Basic info
		if (isset($_SESSION['uploadedfilepath'])) {
			$page =  "pages/basic.php";
		} else {
			$page = "pages/front.php";
		}
		break;
	case "xsd": //Menu - Tests - Vaildate
		if (isset($_SESSION['uploadedfilepath'])) {
			$page =  "pages/validate-xsd.php";
		} else {
			$page = "pages/front.php";
		}
		break;
  case "compliance1": //Menu - Tests - Compliance1
		if (isset($_SESSION['uploadedfilepath'])) {
			$page =  "pages/compliance1.php";
		} else {
			$page = "pages/front.php";
		}
		break;
  case "transparency": //Menu - Tests - Compliance1
		if (isset($_SESSION['uploadedfilepath'])) {
      $page =  "pages/transparency_index.php";
		} else {
			$page = "pages/front.php";
		}
		break;
	case "elements": //Menu - File Statistics - Elements
		if (isset($_SESSION['uploadedfilepath'])) {
			$page = "pages/found_elements.php";
		} else {
			$page = "pages/front.php";
		}
		break;
	case "reset": //Load New File clicked
		unset($_SESSION['uploadedfilepath']);
		unset ($_SESSION['wellformed']);
		unset($_SESSION['upload_msg']);
		if (isset($_SESSION['url'])) {
			unset($_SESSION['url']);
		}
		$page = "pages/front.php"; 
		break;
	default:
		$page = "pages/front.php"; 
		break;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>IATI Public Validator</title>
    <!--<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">-->

    <!-- Le styles -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/css/bootstrap-responsive.css" rel="stylesheet">
    <link href="assets/css/validate-me.css" rel="stylesheet">

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="<?php echo $host; ?>/favicon.ico"><!--
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="assets/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="assets/ico/apple-touch-icon-57-precomposed.png">-->
    <?php if (isset($zen_css)) { echo $zen_css; } //This is our zenddesk support code pulled from a custom settings.php file - most people can ignore this ?>
    <?php if (isset($google_analytics_code)) { echo $google_analytics_code; } ?>
  </head>

  <body>

  <div class="navbar navbar-inverse navbar-static-top">
    <div class="navbar-inner">
      <div class="container">
        <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </a>
        <!--<a class="brand" href="<?php echo $host ?>">IATI Public Validator</a>-->
        <div class="nav-collapse collapse">
          <ul class="nav">
            <li class="active"><a href="<?php echo $host ?>"><i class="icon-home"></i> Home</a></li>
            <li><a href="<?php echo $host ?>common_errors.php"><i class="icon-asterisk"></i> Common errors</a></li>
            <li><a href="<?php echo $host ?>developers.php"><i class="icon-asterisk"></i> Developers</a></li>
            <!--<li><a href="#about">About</a></li>
            <li><a href="#contact">Contact</a></li>-->
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </div>
  </div>
	<header class="jumbotron subhead" id="overview">
	  <div class="container">
		  <div class="row">
        <div class="span10">
          <p class="lead">
            <a href="<?php echo $host; ?>"><img src="assets/img/logo.png" width="" height="" alt="IATI Logo" /></a>
            IATI Public Validator
          </p>
        </div>
        <div span="2" class="reset">
          <?php if (isset($_SESSION['uploadedfilepath'])) :?>
            <p class="lead"><br/><a href="<?php echo $host; ?>?test=reset"" class="btn btn-large btn-success">Load New File</a></p>
          <?php endif; ?>
        </div>
      </div><!--end Row-->
	  </div><!-- /container -->
	</header>
	
	<div class="container">
		<div class="row">
      <div class="span10" style="float:right">
				<!-- Main hero unit for a primary marketing message or call to action -->
				<div class="hero-unit">
					<?php 
            //This little routine gives us the name of the file being tested to display to the user
            //It could be a file name or the URL or pasted code
						if (isset($_SESSION['url'])) { //Is it a URL?
							if (filter_var($_SESSION['url'], FILTER_VALIDATE_URL) == TRUE) {
								//Note this should have already been sanitised, so this is an additional (uneccesary?) check
								$testing_file_name = htmlentities($_SESSION['url']);
							}
						} elseif (isset($_SESSION['uploadedfilepath'])) { //Has it been either uploaded or pasted
							if (strstr($_SESSION['uploadedfilepath'], "paste")) { //Pasted code is saved with the filename like pasted.time()
								$testing_file_name = "Pasted code";
							} else {
                //Filename of type basename_time().xml
								$testing_file_name = basename($_SESSION['uploadedfilepath']);
                $extension = end(explode(".", $testing_file_name));
                $testing_file_name = explode("_",$testing_file_name);
                array_pop($testing_file_name);
                $testing_file_name = implode("_",$testing_file_name) . "." . $extension;
							}
						}
            //Finally only display the HTML if we have a file 
						if (isset($_SESSION['uploadedfilepath'])) {
              $day_time = get_time(basename($_SESSION['uploadedfilepath']));
              $day = $day_time[1];
              $time = $day_time[0];
              $today = date("z");
              if ($today == $day) {
                $day = "Today";
              } elseif ($today - $day == 1) {
                $day = "Yesterday";
              } else {
                $day = $today - $day . " days ago";
              }
                
							echo '<div class="alert alert-info"><strong>Testing:</strong> ' . $testing_file_name . '<br/><strong>Uploaded:</strong> ' . $day . ' at ' . $time . ' GMT</div>';
						}
					?>
					<?php 
            //This is where the main subject of the page is rendered
						include $page;
					?>
						
				</div><!--end Hero unit-->
			</div><!--end Row-->
			
      <!--Sidebar-->
			<div class="span2" style="float:left">
        
        <!--Version Switcher-->
				<div class="well sidebar-nav">
          <form method="get" action="index.php">
            <legend>Version</legend>
            <label for="version">Schema version<br />
            <select name="version" class="span1">
            <?php foreach ($iati_versions as $version) { ?>
              <option <?php if (isset($_SESSION["version"]) && $_SESSION["version"] == $version) { echo 'selected="selected"'; } ?> value="<?php echo $version ?>"><?php echo $version ?></option>
            <?php } ?>
            </select>
            <button type="submit" class="btn btn-primary">Switch</button>
            <?php if (isset($test) && $test != "default" && in_array($test,$tests)) { echo '<input type="hidden" name="test" value="' . $test . '" />'; } ?>
            <!--<input type="submit" value="Submit" />-->
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
	  <!--ABOUT-->
	  <hr>
	  <div class="row">
      <div class="span12">
        <h3>About the IATI Public Validator</h3>
        <p>This is a designed as a quick, simple service to allow people to check their IATI XML files.</p>
        <p>Because IATI files can be varied, complex or even very simple depending on the reporting organisation's needs, 'validation' is a difficult concept.</p>
        <p>This tool performs some basic checks around the XML, and then some compliance checks against the IATI Standard, an agreed set of political desires, that are not enforced by the IATI schema.</p>
        <p>Data submitted to the site is saved to allow us to test the data. Files are removed every three days as part of regular server maintenance.</p>
      </div>
    </div>
    <!--Other Sites-->
    <hr>
    <div class="row">
      <div class="span12">
        <h3>Other IATI Sites</h3>
      </div>
    </div>
    <!-- Example row of columns -->
    <div class="row">
      <div class="span4">
        <h4>IATI Standard</h4>
        <p>Documentation about the IATI standard can be found at <a href="http://iatistandard.org/">http://iatistandard.org/</a>.<br/> We also have a wiki at: <a href="http://wiki.iatistandard.org/">http://wiki.iatistandard.org/</a>. </p>
        <!--<p><a class="btn" href="#">View details &raquo;</a></p>-->
      </div>
      <div class="span4">
        <h4>IATI Data</h4>
        <p>Published IATI data can be found on the Registry at <a href="http://iatiregistry.org">http://iatiregistry.org</a>.</p>
        <!--<p><a class="btn" href="#">View details &raquo;</a></p>-->
     </div>
      <div class="span4">
        <h4>Support</h4>
        <p>The IATI knowledge base and support system can be found at <a href="http://support.iatistandard.org">http://support.iatistandard.org</a>.</p>
        <!--<p><a class="btn" href="#">View details &raquo;</a></p>-->
      </div>
    </div><!--end Row-->

    <hr>

       <!-- Footer
    ================================================== -->
    <footer class="footer">
      <div class="container">
        <p class="pull-right"><a href="#">Back to top</a></p>
        <p>IATI-Public Validator is free software. <br/>Source on <a href="https://github.com/caprenter/IATI-Public_Validator">GitHub</a>. <a href="https://github.com/caprenter/IATI-Public_Validator/issues?state=open">Submit issues</a>.</p>
        <p>
          Built with <a href="http://twitter.github.com/bootstrap">Bootstrap</a> Bootstrap is licensed under the <a href="http://www.apache.org/licenses/LICENSE-2.0">Apache License v2.0</a>.<br/>
          Icons from <a href="http://glyphicons.com">Glyphicons Free</a>, licensed under <a href="http://creativecommons.org/licenses/by/3.0/">CC BY 3.0</a>.
        </p>
        <!--<ul class="footer-links">
          <li><a href="http://blog.getbootstrap.com">Read the blog</a></li>
          <li><a href="https://github.com/caprenter/IATI-Public_Validator/issues?state=open">Submit issues</a></li>
          <li><a href="https://github.com/twitter/bootstrap/wiki">Roadmap and changelog</a></li>
        </ul>-->
      </div>
    </footer>

    </div> <!-- /container -->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster-->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
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
        $('#myTab a[href="#file"]').tab();
        $('#myTab a[href="#extra"]').tab();
        $('#myTab a[href="#passed"]').tab();
        $('#myTab a[href="#failed"]').tab();
        $('#myTab a[href="#warnings"]').tab();
        //$('#myTab a[href="#settings"]').tab();
      })
    </script>
    <script>
      //$(function ()  
      //  { $("#example").popover();  
      //  }); 
      </script>
      <?php if (isset($zen_script)) { echo $zen_script; } //This is our zenddesk support code pulled from a custom settings.php file - most people can ignore this ?>
  </body>
</html>
<?php
/*
 * 
 * name: get_time
 * @param $file_path The path to a file saved via upload
 * @return $time A string of hours, mins and seconds 
 * 
 */

function get_time($file_path) {
  $time = explode("_",$file_path);
  $time = array_pop($time);
  $time = trim($time,".xml"); //for the paste case!
  $day = date("z",$time);
  $time = date("H:i:s",$time);
  
  return array($time,$day);
}
