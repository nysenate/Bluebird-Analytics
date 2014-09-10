<div class="row">
  <div class="col-lg-4">
    <select multiple="multiple" id="select-dimension" name="my-select[]">
      <option value='instance.name'>Instance Name</option>
      <option value='path'>Path</option>
      <option value='action'>User Action</option>
      <option value='query'>Query</option>
      <option value='remote_ip'>Remote IP</option>
      <option value='office'>Office Location</option>
      <option value='time'>Time of Request</option>
    </select>
  </div>
  <div class="col-lg-4">
    <select multiple="multiple" id="select-observation" name="my-select[]" float>
      <option value='total_views'>Total Views</option>
      <option value='avg_response_time'>Avg Response Time</option>
      <option value='500_errors'>App Errors</option>
      <option value='503_errors'>DB Errors</option>
    </select>
  </div>
  <div class="col-lg-4">

    <div class="row">
      <label class="control-label col-lg-3" for="inputSuccess3">Select:</label>
      <div class="col-lg-9">
        <select id="saved-queries" class="form-control">
          <option value="0" data-dimensions="" data-observations="">Create New Query</option>
          <?php
            $result = $dbcon->query("SELECT * FROM datatable");
            while( $row = $result->fetch(PDO::FETCH_ASSOC) ) {
              echo "<option value='${row['id']}' data-dimensions='${row['dimensions']}' data-observations='${row['observations']}'>${row['name']}</option>\n";
            }?>
        </select>
      </div>
    </div>

    <hr/>
    <div class="row">
      <div class="col-lg-12">
        <div class="input-group">
          <input id="query-name"  class="form-control" type='text' placeholder="Enter query name"/>
          <div class="input-group-btn">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
              Action <span class="caret"></span>
            </button>
            <ul class="dropdown-menu pull-right">
              <li><a href="#" id="save-query">Save Query</a></li>
              <li><a href="#" id="delete-query">Delete Query</a></li>
            </ul>
          </div><!-- /btn-group -->
        </div><!-- /input-group -->
      </div>
    </div>
    <hr/>
    <button id="build-table" class="button btn-primary form-control">Build Table!</button>
  </div>
</div>
<br/>
<div class="row">
  <div class="col-lg-12">
    <div class="panel panel-primary">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-file-text"></i>&nbsp;Datatable</h3>
      </div>
      <div class="panel-body">
        <div id="datatable-container">
        </div>
      </div>
    </div>
  </div>
</div><!-- /.row -->
