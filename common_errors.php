<?php
include "settings.php";
$toptab = "common_errors";
include "header.php";
?>
	
	<div class="container">
		
		
	  <!--ABOUT-->
	  <div class="row">
        <div class="span12">
			<h2>Common Errors</h2>
			<p>Don't panic!</p>
			<p>Errors in your XML files are pretty common. Often what looks like a whole load of errors can easily be fixed. Below are the most common errors we encounter in people's files.</p>
			<hr>
			<h3>Well Formed XML</h3>
			<p>Well Formed XML means that machines will be able to read your data. If it is not well formed, they can't and we need to fix it. Some common problems are outlined below:</p>
			<h4>Opening and ending tag mismatch</h4>
			<p>Usually this is down to a typo somewhere. Each tag or element must be consistent. <br/>&lt;iati-activities&gt;&lt;/iati-activities&gt; is good &lt;iatiactivities&gt;&lt;/iati-activities&gt; is bad.</p>
			<h4>PCDATA invalid Char value 25</h4>
      <p>XML files are fussy about the data they contain. This error means that somewhere you have a character in the XML that shouldn't be there. These can be quite hard to track down. Sometimes they can be things copied an pasted from Word documents like curly quotes.</p>
      <h4>xmlParseEntityRef: no name</h4>
      <p>This often refers to an unencoded ampersand (&amp;). These should be declared in your text as &amp;amp;</p>
      <hr>
			<h3>Validation Errors</h3>
      <h4>attribute 'url': ... is not a valid value of the atomic type 'xs:anyURI'.</h4>
      <p>URLs must be 'encoded' in XML - so ampersands (&amp;) should be written as (&amp;amp;), spaces as %20, and so on. <br />N.B. The parser we use INCORRECTLY rejects some URLs in this service, so if you get this error a lot, then try another validation method.</p>
			<h4>attribute 'iso-date': '' is not a valid value of the atomic type 'xs:date'.</h4>
			<p>Dates in IATI should look like YYYY-MM-DD, e.g. the 22nd August 2012 would be written as: 2012-08-22.</p>
			<h4> attribute 'generated-datetime': '2011-10-17 14:00:00' is not a valid value of the atomic type 'xs:dateTime'.</h4>
			<p>A datetime (as opposed to a date) specifies both a time and a date. A common error is to miss out the 'T' that separates the date from the time. <br/>
			So in the example above, this would validate: 2011-10-17T14:00:00<br/>
			Other common errors are not providing the Date as YYYY-MM-DD or the time as HH:MM:SS<br/>
			You can also specify a timezone and sometimes people don't get that bit quite right.</p>
			<h4>attribute 'percentage': '' is not a valid value of the atomic type 'xs:positiveInteger'.</h4>
			<p>In some circumstances we require you to supply just a number. People sometimes include commas, currency symbols, percentage signs, or write their number as a decimal. If you want to say twenty percent, just write 20.</p>
			<h4>The attribute '' is not allowed.</h4>
			<p>This means you've put something in that doesn't need to be there. Simply remove it!</p>
			<h4>Character content other than whitespace is not allowed because the content type is 'element-only'.</h4>
			<p>This usually means something has been typed where it shouldn't have been. All content should be inside the IATI element tags. This is usually a sign that something has strayed outside!</p>
			
			
		</div>
	</div>
<?php
include "footer.php";
?>
