<div class="row">
  <div class="col-lg-3">
    <div class="panel summary panel-info">
      <div class="panel-heading">
        <div class="row">
          <div class="col-xs-2">
            <i class="fa fa-bar-chart-o fa-5x"></i>
          </div>
          <div class="col-xs-10 text-right">
            <p class="announcement-heading" id="uptime"> </p>
            <p class="announcement-text">Uptime</p>
          </div>
        </div>
      </div>

      <a href="#uptime">
        <div class="panel-footer announcement-bottom">
          <div class="row">
            <div class="col-xs-10">
              Uptime Overview
            </div>
            <div class="col-xs-2 text-right">
              <i class="fa fa-arrow-circle-right"></i>
            </div>
          </div>
        </div>
      </a>
    </div>
  </div>

   <div class="col-lg-3">
    <div class="panel summary panel-info">
      <div class="panel-heading">
        <div class="row">
          <div class="col-xs-2">
            <i class="fa fa-cloud fa-5x"></i>
          </div>
          <div class="col-xs-10 text-right">
            <p class="announcement-heading" id="app_errors"></p>
            <p class="announcement-text">Application errors</p>
          </div>
        </div>
      </div>

      <a href="#application">
        <div class="panel-footer announcement-bottom">
          <div class="row">
            <div class="col-xs-10">
              Application Overview
            </div>
            <div class="col-xs-2 text-right">
              <i class="fa fa-arrow-circle-right"></i>
            </div>
          </div>
        </div>
      </a>
    </div>
  </div>

  <div class="col-lg-3">
    <div class="panel summary panel-info">
      <div class="panel-heading">
        <div class="row">
          <div class="col-xs-2">
            <i class="fa fa-hdd-o fa-5x"></i>
          </div>
          <div class="col-xs-10 text-right">
            <p class="announcement-heading"id="db_errors"></p>
            <p class="announcement-text">Database errors</p>
          </div>
        </div>
      </div>

      <a href="#database">
        <div class="panel-footer announcement-bottom">
          <div class="row">
            <div class="col-xs-10">
              Database Overview
            </div>
            <div class="col-xs-2 text-right">
              <i class="fa fa-arrow-circle-right"></i>
            </div>
          </div>
        </div>
      </a>
    </div>
  </div>


  <div class="col-lg-3">
    <div class="panel summary panel-info">
      <div class="panel-heading">
        <div class="row">
          <div class="col-xs-2">
            <i class="fa fa-rotate-right fa-5x"></i>
          </div>
          <div class="col-xs-10 text-right">
            <p class="announcement-heading" id="response_time"></p>
            <p class="announcement-text">Average Response Time </p>
          </div>
        </div>
      </div>

      <a href="#pagespeed">
        <div class="panel-footer announcement-bottom">
          <div class="row">
            <div class="col-xs-10">
              Pagespeed Overview
            </div>
            <div class="col-xs-2 text-right">
              <i class="fa fa-arrow-circle-right"></i>
            </div>
          </div>
        </div>
      </a>
    </div>
  </div>


</div><!-- /.row -->

<div class="row scrollto" id='uptime'>
  <div class="col-lg-12">
    <div class="panel chart panel-primary">
      <div class="panel-heading">

        <h3 class="panel-title" ><i class="fa fa-bar-chart-o"></i> OverView</h3>
      </div>
      <div class="panel-body">
        <div id="overview"></div>
      </div>
    </div>
  </div>
</div><!-- /.row -->

<div class="row">
  <div class="col-lg-8">
    <div class="panel table-list panel-primary">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-file-text"></i> Slow Page Log</h3>
      </div>
      <div class="panel-body">
        <div class="table-responsive">
          <table id="slow_queries" class="table table-bordered table-hover table-striped tablesorter">
          </table>
        </div>
        <div class="text-right">
          <a href="#">View all slow pages <i class="fa fa-arrow-circle-right"></i></a>
        </div>
      </div>
    </div>
  </div>
</div>
