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
              <li<?php if ($toptab=='home') { ?> class="active"<?php } ?>><a href="<?php echo $host ?>"><i class="icon-home"></i> Home</a></li>
              <li<?php if ($toptab=='common_errors') { ?> class="active"<?php } ?>><a href="<?php echo $host ?>common_errors.php"><i class="icon-asterisk"></i> Common errors</a></li>
              <li<?php if ($toptab=='developers') { ?> class="active"<?php } ?>><a href="<?php echo $host ?>developers.php"><i class="icon-asterisk"></i> Developers</a></li>
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
            <p class="lead"><br/><a href="<?php echo $host; ?>?test=reset" class="btn btn-large btn-success">Load New File</a></p>
          <?php endif; ?>
        </div>
      </div><!--end Row-->
    </div><!-- /container -->
  </header>
