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
        <p>IATI-Public Validator is free software. <br/>Source on <a href="https://github.com/IATI/IATI-Public_Validator">GitHub</a>. <a href="https://github.com/IATI/IATI-Public_Validator/issues?state=open">Submit issues</a>.</p>
        <p>
          Built with <a href="http://twitter.github.com/bootstrap">Bootstrap</a> Bootstrap is licensed under the <a href="http://www.apache.org/licenses/LICENSE-2.0">Apache License v2.0</a>.<br/>
          Icons from <a href="http://glyphicons.com">Glyphicons Free</a>, licensed under <a href="http://creativecommons.org/licenses/by/3.0/">CC BY 3.0</a>.
        </p>
        <!--<ul class="footer-links">
          <li><a href="http://blog.getbootstrap.com">Read the blog</a></li>
          <li><a href="https://github.com/IATI/IATI-Public_Validator/issues?state=open">Submit issues</a></li>
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
    <?php if (isset($extrascripts)) echo $extrascripts; // Extra scripts defined in index.php ?>
    <?php if (isset($zen_script)) { echo $zen_script; } //This is our zenddesk support code pulled from a custom settings.php file - most people can ignore this ?>
  </body>
</html>
