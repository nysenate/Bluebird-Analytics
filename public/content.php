<div class="row">
  <div class="col-lg-4">
    <select multiple="multiple" id="select-dimension" name="my-select[]">
      <option value='instance'>Instance</option>
      <option value='path'>Path</option>
      <option value='remote_ip'>Remote IP</option>
    </select>
  </div>
  <div class="col-lg-4">
    <select multiple="multiple" id="select-observation" name="my-select[]" float>
      <option value='total_views'>Total Views</option>
      <option value='avg_response_time'>Avg Response Time</option>
    </select>
  </div>
  <div class="col-lg-4" style="text-align:center;">
    <button id="build-table" class="button">Build Table!</button>
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
