<?php
function url_for($path)
{
  if ($path[0] == '/') {
    return $_SERVER['CONTEXT_PREFIX'].$path;
  }
  else {
    return $path;
  }
}
?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title><?php echo $activemenu->menu_title; ?> - <?php echo $product['name'];?></title>
    <!-- dynamically load in style sheets -->
<?php
foreach ($css_scripts as $css_script) {
  echo '    <link href="'.url_for($css_script).'" rel="stylesheet" />'."\n";
}
?>
  </head>
  <body class="<?php
        echo $activemenu->content_title; ?>" data-context-path="<?php
        echo $_SERVER['CONTEXT_PREFIX'] ?>" data-view="<?php
        echo $activemenu->data_name ?>" data-filter="<?php
        if(isset($_GET['filter'])){ echo $_GET['filter'];} ?>">
    <div id="wrapper">
      <!-- Sidebar -->
      <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/"><?php echo $product['name'];?></a>
        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse navbar-ex1-collapse">
          <?php echo $navmenu->render(); ?>
          <ul class="nav navbar-nav navbar-right navbar-user">
            <li class="instance-choice">
              <select id="instance-picker" class="selectpicker" data-live-search="true" data-size="5" data-style="btn-inverse">
                <option>ALL</option>
                <option data-divider="true"></option>
                <?php
                foreach ($instances as $instance) {
                  echo "<option value='$instance'>$instance</option>";
                }
                ?>
              </select>
            </li>
            <li class="date-choice"><a>
              <div id="reportrange" class="pull-right" data-start="" data-end="">
                <i class="fa fa-calendar"></i>
                <span></span> <b class="caret"></b>
              </div>
            </a></li>

          </ul>
        </div><!-- /.navbar-collapse -->
      </nav>

      <div id="page-wrapper">

        <div class="row">
          <div class="col-lg-12">
            <div class="jumbotron jumbotron-ad hidden-print">
                <h1><?php echo $activemenu->content_title; ?><span class="jumbotron-status-icons"></span></h1>
            </div>

            <?php echo $release_notes; ?>
          </div>
        </div><!-- /.row -->
        <div class="row" id="summary-wrapper"></div>
        <div class="row" id="chart-wrapper"></div>
        <div class="row" id="list-wrapper"></div><?php
        if (file_exists("static/layouts/$request.php")) {
          echo "\n<!-- adding additional layout for '$request' -->\n";
          include_once "static/layouts/$request.php";
          echo "\n<!--    end additional layout for '$request' -->\n";
        } ?>
        <div class="row">
          <hr/>
          <div class="col-lg-6 footer-credits">
            <i class="fa fa-flask"></i> <span class='app-name'> <?php echo $product['name'];?></span> Version  <span class='app-version'><?php echo $product['version'];?></span>
          </div>
          <div class="col-lg-6 footer-credits last-update">
            <span class="last-update-label">Logs last imported on</span>
            <span class="last-update-value"><?php echo $product['last_update'];?></span>
          </div>
        </div><!-- /.row -->
      </div><!-- /#page-wrapper -->
    </div><!-- /#wrapper -->

    <!-- dynamically load in JavaScript -->
<?php
  foreach ($js_scripts as $js_script) {
    echo '    <script src="'.url_for($js_script).'" type="text/javascript"></script>'."\n";
  }
?>
  </body>
</html>
