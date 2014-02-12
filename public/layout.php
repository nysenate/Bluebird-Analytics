<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?php echo $navigation[$request][$sub]['name'] ?> - <?php echo $product['name'];?></title>

    <!-- dynamically load in style sheets -->
    <?php
    foreach ($scripts['css'] as $key => $value) {
      echo '<link href="'.url_for($value['src']).'" rel="stylesheet" >'."\n\t";
    }
    ?>
  </head>

  <body data-view="<?php echo $navigation[$request][$sub]['view'] ?>" class="<?php echo $navigation[$request][$sub]['name'] ?>">
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
          <ul class="nav navbar-nav side-nav">

            <?php
            foreach ($navigation as $key => $value) {
              // var_dump($value['class']);
              $class = (isset($value['overview']['class'])) ? $value['overview']['class'] : '' ;
              if (count($navigation[$key]) === 1) {
                echo '<li class="'.$class.'"> <a href="'.url_for($value['overview']['link']).'"> <i class="fa '.$value['overview']['icon'].'"></i> '.$value['overview']['name'].' </a>';
              }else{
                echo ' <li class="dropdown '.$class.'"> <a data-toggle="dropdown" class="dropdown-toggle" href="'.$value['overview']['link'].'"> <i class="fa '.$value['overview']['icon'].'"></i> '.$value['overview']['name'].'<b class="caret"></b> </a> <ul class="dropdown-menu">';
                foreach ($navigation[$key] as $child => $dropdown) {
                  if ($child !== 'overview') {
                    $class = (isset($dropdown['class'])) ? $dropdown['class'] : '' ;

                    echo '<li class="'.$class.'"> <a  href="'.url_for($dropdown['link']).'"> <i class="fa '.$dropdown['icon'].'"></i> '.$dropdown['name'].' </a> </li>';
                  }
                }
                echo '</ul>';
              }
              echo ' </li>';
            }
            // exit();
            ?>
          </ul>

          <ul class="nav navbar-nav navbar-right navbar-user">
            <li class="instance-choice">
              <select id="instance-picker" class="selectpicker" data-size="5" data-style="btn-inverse">
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
                <h1><?php echo $navigation[$request][$sub]['about'] ?></h1>
            </div>

            <?php echo $release_notes; ?>
          </div>
        </div><!-- /.row -->

        <?php
          if (!empty($layout_content)) {
            include($navigation[$request][$sub]['content']);
          }
          else {
            include('404.html');
          }

        ?>

        <div class="row">
          <hr/>
          <div class="col-lg-12 footer-credits">
            <i class="fa fa-flask"></i> <span class='app-name'> <?php echo $product['name'];?></span> Version  <span class='app-version'><?php echo $product['version'];?></span>
          </div>
        </div><!-- /.row -->
      </div><!-- /#page-wrapper -->
    </div><!-- /#wrapper -->

    <!-- dynamically load in JavaScript -->
    <?php
      foreach ($scripts['js'] as $key => $value) {
        echo '<script src="'.url_for($value['src']).'"></script>'."\n\t";
      }
    ?>
  </body>
</html>
