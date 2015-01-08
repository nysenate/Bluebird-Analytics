<div id="custom-query-wrapper">
  <h2>Select a saved query, or build a new one</h2>
  <div class="row" id="custom-query-controls">
    <label class="control-label col-sm-1" for="query-choice">Select:</label>
    <select id="query-choice" name="query-choice" class="col-sm-5">
      <option value="0">Create New Query</option>
      <option value="-1" disabled="disabled">Loading Saved Queries . . .</option>
    </select>
    <div class="col-sm-6 row">
      <button id="query-choice-go" name="query-choice-go" class="btn btn-sm btn-primary col-sm-3 col-sm-offset-1">Select</button>
      <button id="show-editor" data-toggle="collapse" data-target="#builder-controls" name="show-builder" class="btn btn-sm col-sm-3 col-sm-offset-1">Show Builder</button>
      <i class="fa fa-refresh col-sm-offset-3" id="reload-all-controls" title="Reload all Controls" style="opacity:.3"></i>
    </div>
  </div>
  <div class="row collapse" id="builder-controls">
    <div class="col-sm-3">
      <label class="control-label col-sm-12" for="builder-fieldname">Select Datapoint</label>
      <select id="builder-fieldname" name="builder-fieldname"></select>
    </div>
    <div class="col-sm-3">
      <fieldset class="form-group-sm">
        <legend class="h5">Mode for this field:</legend>
        <div><input class="radio-inline" type="radio" name="builder-fieldmode" id="builder-fieldmode-none"><label for="builder-fieldmode-none">&nbsp;&nbsp;None</label></div>
        <div><input class="radio-inline" type="radio" name="builder-fieldmode" id="builder-fieldmode-group"><label for="builder-fieldmode-group">&nbsp;&nbsp;Group</label></div>
        <div><input class="radio-inline" type="radio" name="builder-fieldmode" id="builder-fieldmode-calc"><label for="builder-fieldmode-calc">&nbsp;&nbsp;Calculated</label></div>
        <div><input class="radio-inline" type="radio" name="builder-fieldmode" id="builder-fieldmode-count"><label for="builder-fieldmode-count">&nbsp;&nbsp;Count</label></div>
        <div><input class="radio-inline" type="radio" name="builder-fieldmode" id="builder-fieldmode-countd"><label for="builder-fieldmode-countd">&nbsp;&nbsp;CountD</label></div>
        <div><input class="radio-inline" type="radio" name="builder-fieldmode" id="builder-fieldmode-sum"><label for="builder-fieldmode-sum">&nbsp;&nbsp;Sum</label></div>
        <div><input class="radio-inline" type="radio" name="builder-fieldmode" id="builder-fieldmode-avg"><label for="builder-fieldmode-avg">&nbsp;&nbsp;Average</label></div>
      </fieldset>
    </div>
  </div>
  <hr />
  <div class="row">
    <div class="col-lg-12">
      <div class="panel panel-primary">
        <div class="panel-heading">
          <h3 class="panel-title"><i class="fa fa-file-text"></i>&nbsp;Datatable</h3>
        </div>
        <div class="panel-body">
          <div id="datatable-container">Select or create a query using the controls above.
          </div>
        </div>
      </div>
    </div>
  </div><!-- /.row -->
</div>
<div id="custom-query-wrapper-old">
  <div class="row">
    <div>
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
    <div>
      <select multiple="multiple" id="select-observation" name="my-select[]" float>
        <option value='total_views'>Total Views</option>
        <option value='avg_response_time'>Avg Response Time</option>
        <option value='500_errors'>App Errors</option>
        <option value='503_errors'>DB Errors</option>
      </select>
    </div>
    <div>

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
</div><!-- #custom-query-wrapper -->