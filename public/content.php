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
    <div class="panel table-list panel-primary">
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
<br/>
<div class="row">
  <div class="col-lg-12">
    <div class="panel table-list panel-primary">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-file-text"></i>&nbsp;Datatable</h3>
      </div>
      <div class="panel-body">
        <table id="datatable" class="datatable">
          <thead>
            <tr>
              <th data-searchable="1">Remote IP</th>
              <th data-searchable="1">Path</th>
              <th data-sort-order="desc,asc">Total Views</th>
              <th>Avg Response Time</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div><!-- /.row -->
