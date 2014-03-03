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
    <br/><br/>
    Select a saved query:
    <select id="saved-queries">
      <option data-dimensions="remote_ip" data-observations="avg_response_time,total_views" selected>Custom</option>
      <option data-dimensions="path,remote_ip" data-observations="total_views">Test1</option>
      <option data-dimensions="remote_ip,path" data-observations="avg_response_time">Test2</option>
    </select>
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
