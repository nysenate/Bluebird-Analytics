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

<div class="row" id='uptime'>
  <div class="col-lg-12">
    <div class="panel chart panel-primary">
      <div class="panel-heading">
        <div class="pull-left">
          <h3 class="panel-title" ><i class="fa fa-bar-chart-o"></i> Overview</h3>
        </div>
        <div class="pull-right">
          <div class="btn-group btn-group-xs">
            <button type="button" class="btn active btn-default">All</button>
            <button type="button" class="btn btn-default">Apache</button>
            <button type="button" class="btn btn-default">Database</button>
            <select id="performance-picker" class="btn-group-xs" multiple title='Custom' data-selected-text-format="count">
              <optgroup label="Apache">
                <option value='0'>App Errors</option>
                <option value='1'>Database Errors</option>
                <option value='2'>Response Time</option>
                <option value='3'>Page Views (x1000)</option>
              </optgroup>
              <optgroup label="Database" disabled>
                <option value='4'>Queries</option>
                <option value='5'>Slow Queries</option>
                <option value='6'>queries</option>
                <option value='7'>Max Connections</option>
                <option value='8'>questions</option>
                <option value='9'>innodb_lock_avg</option>
                <option value='10'>innodb_lock_max</option>
              </optgroup>
            </select>
          </div>
        </div>
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
