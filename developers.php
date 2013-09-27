<?php
include "settings.php";
$toptab = "developers";
include "header.php";
?>
	<div class="container">

		
		
	  <!--ABOUT-->
	  <div class="row">
        <div class="span12">
			<h2>Developers</h2>
			<h3>Validation of IATI data files</h3>
			<p>If an IATI data file fails to validate against the schema, this is not the end of the world!</p>
      <p>Files fail validation for many good, practical reasons from a publishers point of view, and yet still provide useful accessible data for other people to use.<br/>However, valid files do make it much easier for data users to work with your data, and passing the 2 basic rules of:</p>
      <ul><li>Well formed XML</li><li>Validation against IATI schema</li></ul><p>should be seen as basic requirements of your data</p>
       
			<hr>
			<h3>About this tool</h3>
			<p>This tool is built using PHP's XML parsers. (Both the <a href="http://www.php.net/manual/en/book.dom.php">DOM</a> and <a href="http://php.net/manual/en/book.simplexml.php">SimpleXML</a> parsers)</p>
			
      <hr>
			<h3>Validation files on your local machine</h3>
			<p>xmllint is a really useful tool for checking file validation on your local machine. Information on <a href="http://wiki.iatistandard.org/tools/xml_validation/start">how to use xmllint with IATI data</a> can be found on our wiki.</p>
      <p>If you want to place the schema files on your local machine then be sure to place ALL the schema files in the same directory as they each reference each other. <br/>
      See <a href="http://iatistandard.org/schema">Schema on iatistandard.org</a>.</p>
			
			<hr>
			<h3>Online Validators</h3>
      <h4>W3C</h4>
      <p>The <a href="http://validator.w3.org/">W3C validator</a>, that many developers are familiar with for checking their web documents, can be used to check if a file is well-formed, but you cannot use it to validate against the IATI schema. As such you should take some of the warnings with a pinch of salt. Essentially the validator is a DTD based validator and IATI uses XML Schemas instead. See <a href="http://www.sitepoint.com/xml-dtds-xml-schema/">XML DTDs Vs XML Schema</a></p>
      <h4>Others</h4>
      <p>There are some online services that allow you to upload XML and a Schema against which to test. These FAIL on IATI documents because IATI uses linked schemas that reference each other. So in effect you need to upload 3 schema to be able to check your document.</p>
		</div>
	</div>
<?php
include "footer.php";
?>
