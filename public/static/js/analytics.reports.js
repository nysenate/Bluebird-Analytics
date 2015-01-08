/* Base class for AJAX reports */
// initialize the NYSS namespace
var NYSS = NYSS || {};

/* Widget class definitions */
(function ($,undefined) {
  // set up a shortcut to add non-enumerable properties
  var defProp = function(obj, name, value, props) {
    var defaults = { writable:true, enumerable:false, value:value }
    var tp = $.extend({}, defaults, props);
    Object.defineProperty( obj, name, tp );
  };

  /* **************************************************************************************
   * NYSS.ReportWidget
   * A base class for Chart, Summary, and List report widgets for NYSS BB Analytics.
   ************************************************************************************** */
  NYSS.ReportWidget = function(d){
    this.initialize(d);
  } /* end of NYSS.ReportWidget */

  /* Utility function to wrap a DOM object in a temporary element
     This allows jQuery.find() to work on the former top-level element */
  defProp(NYSS.ReportWidget, '_wrapHTML', function _wrapHTML(h) {
    return $('<div id="_WIDGETWRAPPER"></div>').append($(h));
  });

  /* Utility function to remove a previously installed DOM wrapper */
  defProp(NYSS.ReportWidget, '_unwrapHTML', function _unwrapHTML(h) {
    return $(h).attr('id')=="_WIDGETWRAPPER" ? $(h).contents() : $(h);
  });

  /* NYSS.ReportWidget.abort()
     Aborts the current AJAX request, if active.
     Parameters: none
     Return: none
     */
  defProp(NYSS.ReportWidget, 'abort', function abort(msg) {
    if (this.jqxhr && this.jqxhr.hasOwnProperty('abort')) {
      this.jqxhr.abort();
    }
    this.jqxhr = null;
    if (msg) { NYSS.ReportWidget.messages.push(String(msg)); }
  });

  defProp(NYSS.ReportWidget, 'customInit', function customInit() {
  });

  defProp(NYSS.ReportWidget, 'displayError', function displayError(response) {
    var target_elem = '.jumbotron .jumbotron-status-icons';
    var icon_type = 'fa fa-warning danger';
    if (this.error_icon) { this.error_icon.fadeOut(1000,function(){$(erricon).remove()}); }
    this.error_icon = new NYSS.MessageBox({
                          caption:this.report_type,
                          container_class:'error-msg',
                          icon_class:icon_type,
                          parent_target:target_elem,
                          container_id:'fa-icon-error-' + response.req,
                          messages:response.errors.map(function(v){return v.msg}),
                        });
    this.error_icon.render();
  });

  defProp(NYSS.ReportWidget, 'displayWorking', function displayWorking(icon_type, target_elem) {
    if (!target_elem) { target_elem = '.jumbotron .jumbotron-status-icons'; }
    if (!icon_type) { icon_type = 'fa-cog fa-spin'; }
    if (this.working_icon) { this.removeWorking(); }
    this.working_icon = new NYSS.MessageBox({
                          caption:this.report_type,
                          icon_class:icon_type,
                          parent_target:target_elem,
                          container_id:'loading_note_'+this.report_type,
                        });
    this.working_icon.render();
  });

  defProp(NYSS.ReportWidget, 'generateLink', function generateLink(dest) {
    d = String(dest);
    var ret='#';
    if (d) {
      var sep='';
      if (d[0]=='/') {
        d=d.substr(1);
        sep='/';
      }
      ret = [$('body').data('contextPath'),sep,d].join('');
    }
    return ret;
  });

  defProp(NYSS.ReportWidget, 'initialize', function initialize(d) {
    /* "private" variables */
    // the AJAX request
    this.jqxhr = null;
    // report configuration
    this.report = {};
    this.filter = {};
    // internal flag to track if data is available
    this.has_data = false;
    // internal flag to track rendering after AJAX
    this.pending_render = false;

    /* public properties */
    // error/status messages
    this.report_obj = {};
    this.messages    = [];
    this.report_data = {};
    this.target_wrapper = 'body';
    this.working_icon = null;
    this.on_data_update = null;

    this.report_type = this.constructor.name.replace(/ReportWidget$/,'').capitalize();
    if (!this.report_type) { this.report_type = 'Base'; }

    this.setConfig(d);
    this.customInit();
  });

  /* NYSS.ReportWidget.parseSettings()
     Takes in a report configuration object and parses it.
     Parameters:
       param (object): a config structure
     Return: none
     */
  defProp(NYSS.ReportWidget, 'parseSettings', function parseSettings(param) {
    return {reports:this.report, filters:this.filter};
  });

  defProp(NYSS.ReportWidget, 'receiveData', function receiveData(response,status,jqobj) {
    this.has_data = (this.onDataReturn(response, status, jqobj) && status=='success');
    if (this.pending_render) { this.render(); }
  });

  defProp(NYSS.ReportWidget, 'remove', function remove(n) {
    if (this.report_obj[n]) {
      this.report_obj[n].remove();
      this.report_obj[n] = null;
    }
  });

  defProp(NYSS.ReportWidget, 'removeWorking', function removeWorking() {
    if (this.working_icon) {
      this.working_icon.removeBox();
    }
    this.working_icon = null;
  });

  defProp(NYSS.ReportWidget, 'render', function render(r) {
    if (!this.has_data) {
      this.pending_render = true;
      this.retrieveData(r);
      return false;
    }
    this.displayData();
    this.pending_render = false;
  });

  /* NYSS.ReportWidget.retrieveData()
     Execute an AJAX call using current report settings, and pass the response
     to data parsing
     Parameters: none
     Return: none
     */
  defProp(NYSS.ReportWidget, 'retrieveData', function retrieveData(param) {
    if (this.report_type) {
      // abort any existing call
      this.has_data = false;
      this.abort();
      var postdata = $.extend( {}, this.parseSettings(), (param instanceof Object ? param : {}) );
      var ajaxparams = {
                         url:        $('body').data('context-path')+'/api/'+this.report_type.toLowerCase(),
                         timeout:    60000,
                         type:       'POST',
                         beforeSend: function(){this.displayWorking();},
                         context:    this,
                         data:       postdata,
                       }
      this.jqxhr = $.ajax(ajaxparams).always(this.retrieveDataReturn);
    }
  });

  defProp(NYSS.ReportWidget, 'retrieveDataReturn', function retrieveDataReturn(response, status, jqobj) {
    this.removeWorking();
    this.report_data = JSON.parse(status=='error' ? response.responseText : response);
    if (this.report_data.errorcount) {
      this.displayError(this.report_data);
    }
    $(this).trigger('onDataUpdate',this.report_data);
    return true;
  });

  defProp(NYSS.ReportWidget, 'setConfig', function setConfig(d,undefined) {
    if (d===undefined) { d={}; }
    this.report = d.reports || {};
    this.filter = d.filters || {};
    this.target_wrapper = d.target_wrapper || 'body';
    this.on_data_update = d.on_data_update || this.updateData;

    if (typeof this.on_data_update == 'function') { $(this).on('onDataUpdate',this.on_data_update); }
  });

  defProp(NYSS.ReportWidget, 'updateData', function updateData() {
    console.log("No handler defined for AJAX data updates!");
  });
  /* **************************************************************************************
   * END NYSS.ReportWidget definitions
   ************************************************************************************** */

  /* **************************************************************************************
   * BEGIN NYSS.ListReportWidget definitions
   ************************************************************************************** */
  NYSS.ListReportWidget = function ListReportWidget(d) {
    NYSS.ReportWidget.call(this,d);
  }
  NYSS.ListReportWidget.prototype = Object.create(NYSS.ReportWidget);
  NYSS.ListReportWidget.prototype.constructor = NYSS.ListReportWidget;

  defProp(NYSS.ListReportWidget.prototype, 'customInit', function customInit() {
    this.base_html =
          '<div class="list-widget-panel-wrapper">' +
            '<div class="panel table-list panel-primary list-widget-panel">' +
              '<div class="panel-heading list-widget-panel-header">' +
                '<h3 class="panel-title list-widget-panel-title-container">' +
                  '<i class="list-widget-panel-icon"></i>' +
                  '<span class="list-widget-panel-title"></span>' +
                '</h3>' +
              '</div>' +
              '<div class="panel-body list-widget-panel-body">' +
                '<div class="table-responsive list-widget-table-container">' +
                '</div>' +
                '<div class="text-right list-widget-link"></div>' +
              '</div>' +
            '</div>' +
          '</div>'
    this.default_list = {
                          baseTarget:'#list-wrapper',
                          wrapperSize:'col-sm-6',
                          titleIcon:'fa fa-building-o',
                          titleText:'',
                          linkText:'',
                          linkIcon:'fa fa-arrow-circle-right',
                          linkURL:''
                        };
  });

  defProp(NYSS.ListReportWidget.prototype, 'updateData', function updateData() {
    var thisobj = this;
    this.report.forEach(function(v,k) {
      var search_for_index = function(a,b) {
        var rev = (a.indexOf('!') == 0),
            len = v.datapoints.length,
            i   = 0,
            idx = -1;
        if (rev) { a=a.substr(1); }
        for (;i<len;) {
          if (v.datapoints[i++].field == a) {
            idx = i-1;
            break;
          }
        }
        if (idx>=0) {
          return [idx,(rev?'desc':'asc')];
        } else {
          console.log('Invalid orderBy field in '+v.report_name+': '+a);
        }
      };
      var onereport = thisobj.report_data.data[v.report_name];
      if (thisobj.report_obj[v.report_name]) {
        thisobj.report_obj[v.report_name].remove();
        thisobj.report_obj[v.report_name] = null;
      }
      var props = $.extend({}, thisobj.default_list, v.props);
      var dtorder = (v.props.orderBy || [])
                    .map(search_for_index)
                    .filter(function(a,b){return a;});

      var dtparams = {
                  columns:v.datapoints.map(function(a){return {title:a.header, data:a.field, name:a.field} }),
                  data:onereport,
                  searching:false,
                  paging:true,
                  lengthChange:true,
                  autoWidth:true,
                  order:dtorder,
                 };
      var thishtml = thisobj._wrapHTML($(thisobj.base_html));
      thishtml.find('.list-widget-panel-wrapper').addClass(props.wrapperSize).attr('id',props.widgetID);
      thishtml.find('.list-widget-panel-title').html(props.titleText);
      thishtml.find('.list-widget-panel-icon').addClass(props.titleIcon);
      thishtml.find('.list-widget-table-container').empty();

      if (props.linkText && props.linkURL) {
        thishtml.find('.list-widget-link').html(
          $('<a/>').attr('href',thisobj.generateLink(props.linkURL))
                   .text(props.linkText)
                   .addClass(props.linkIcon)
          );
      }
      thishtml = thisobj._unwrapHTML(thishtml);
      $(thisobj.target_wrapper).find('#'+props.widgetID).remove();
      thisobj.report_obj[v.report_name] = $('<table />')
                                            .attr('id','ListReport-'+v.report_name)
                                            .appendTo($(thishtml).find('.list-widget-table-container'))
                                            .attr('class','ListReportWidget-DataTable table table-bordered table-hover table-striped tablesorter');
      thisobj.report_obj[v.report_name].dataTable($.extend({},dtparams));
      $(thisobj.target_wrapper).append(thishtml);
    });
  });
  /* **************************************************************************************
   * END NYSS.ListReportWidget definitions
   ************************************************************************************** */


  /* **************************************************************************************
   * BEGIN NYSS.ChartReportWidget definitions
   ************************************************************************************** */
  NYSS.ChartReportWidget = function ChartReportWidget(d) {
    NYSS.ReportWidget.call(this,d);
  }
  NYSS.ChartReportWidget.prototype = Object.create(NYSS.ReportWidget);
  NYSS.ChartReportWidget.prototype.constructor = NYSS.ChartReportWidget;

  /* need a chart callback for hover */
  defProp(NYSS.ChartReportWidget.prototype,'ChartHoverCallback', function (index, options, content) {
    if (arguments.length) {
      switch (widgets.chart.filter.granularity) {
        case 'hour':     fmt='ddd l, h A'; break;
        case 'day':      fmt='ddd l'; break;
        case 'month':    fmt='MMMM YYYY'; break;
        case 'minute':
        case '15minute':
        default:         fmt='ddd l, hh:mm A'; break;
      }
      time = moment(options.data[index]["timerange"]).format(fmt);
      var h = '<div class="morris-hover-row-label">'+time+'</div>';
      $.each(options.data[index], function( key, values ) {
        if($.inArray(key, options.ykeys)!==-1){
          var id = $.inArray(key, options.ykeys);
          h += '<div class="morris-hover-point" style="color:'+options.lineColors[id]+
               '">'+options.labels[id]+': '+values+'</div>';
        }
      });
      return h;
    }
  });

  defProp(NYSS.ChartReportWidget.prototype, 'customInit', function customInit() {
    this.base_html =
        '<div class="chart-widget-panel-wrapper">' +
          '<div class="panel chart panel-primary chart-widget-panel">' +
            '<div class="panel-heading chart-widget-panel-header">' +
              '<h3 class="panel-title chart-widget-panel-title-container">' +
                '<i class="chart-widget-panel-icon"></i>' +
                '<span class="chart-widget-panel-title"></span>' +
              '</h3>' +
            '</div>' +
            '<div class="panel-body chart-widget-panel-body">' +
              '<div class="chart-widget-panel-target"></div>' +
            '</div>' +
          '</div>' +
        '</div>';
    this.default_graph = {
      pointSize: 3,
      lineColors: [],
      parseTime: true,
      continuousLine: false,
      grid: true,
      ymax: 'auto 100',
      hideHover: true,
      smooth: true,
      resize:true,
      hoverCallback: this.ChartHoverCallback
    };
  });

  defProp(NYSS.ChartReportWidget.prototype, 'updateData', function updateData() {
    var thisobj = this;
    this.report.forEach(function(v,k) {
      var thishtml = thisobj._wrapHTML($(thisobj.base_html));
      thishtml.find('.chart-widget-panel-wrapper')
              .addClass(v.props.wrapperSize)
              .attr('id','chart-widget-'+v.report_name);
      thishtml.find('.chart-widget-panel-target').attr('id','chart-widget-target-'+v.report_name);
      thishtml.find('.chart-widget-panel-icon').addClass(v.props.headerIcon);
      thishtml.find('.chart-widget-panel-title').html(v.props.valueCaption);
      thishtml = thisobj._unwrapHTML(thishtml);
      v.props.graphData = v.props.graphData || {};
      if (!v.props.graphData.lineColors) { v.props.graphData.lineColors = chart_colors.getColors(); }
      v.props.graphData = $.extend( { lineColors:chart_colors.getColors() },
                                    thisobj.default_graph, v.props.graphData,
                                    {
                                      data:       thisobj.report_data.data[v.report_name],
                                      element:    thishtml.find('.chart-widget-panel-target').attr('id')
                                    }
                                  );
      $(thisobj.target_wrapper).find('#chart-widget-'+v.report_name).remove();
      if (v.props.graphData.data.length<1) { thishtml.find('.chart-widget-panel-body').prepend('<h3>No data available</h2>'); }
      thishtml.appendTo(thisobj.target_wrapper).find('.chart').slideDown();
      if (v.props.graphData.data.length>=1) { thishtml.graphObj = new Morris.Line(v.props.graphData); }
      thisobj.report_obj[v.report_name] = thishtml;
    });
  });
  /* **************************************************************************************
   * END NYSS.ChartReportWidget definitions
   ************************************************************************************** */

  /* **************************************************************************************
   * BEGIN NYSS.SummaryReportWidget definitions
   ************************************************************************************** */
  NYSS.SummaryReportWidget = function SummaryReportWidget(d) {
    NYSS.ReportWidget.call(this,d);
  }
  NYSS.SummaryReportWidget.prototype = Object.create(NYSS.ReportWidget);
  NYSS.SummaryReportWidget.prototype.constructor = NYSS.SummaryReportWidget;

  defProp(NYSS.SummaryReportWidget.prototype, 'customInit', function customInit() {
    this.base_html ='<div class="summary-widget-panel-wrapper">' +
                      '<div class="summary-widget-panel panel panel-info">' +
                        '<div class="summary-widget-panel-header panel-heading">' +
                          '<div class="summary-widget-datapoint-wrapper row">' +
                            '<div class="summary-widget-datapoint-icon col-xs-2"></div>' +
                            '<div class="summary-widget-datapoint col-xs-10 text-right">' +
                              '<p class="summary-widget-datapoint-value announcement-heading"></p>' +
                              '<p class="summary-widget-datapoint-text"></p>' +
                            '</div>' +
                          '</div>' +
                        '</div>' +
                        '<div class="summary-widget-panel-footer-wrapper panel-footer">' +
                          '<a class="summary-widget-link" href="">' +
                          '<div class="summary-widget-panel-footer row">' +
                            '<div class="summary-widget-link-text col-xs-10"></div>' +
                            '<div class="summary-widget-link-icon col-xs-2 text-right"></div>' +
                          '</div>' +
                          '</a>' +
                        '</div>' +
                      '</div>' +
                    '</div>';
    this.default_summary = {
                            wrapperSize:'col-sm-3 col-xs-6',
                            headerIcon:'',
                            linkIcon:'fa fa-arrow-circle-right',
                            linkTarget:'',
                            linkText:'',
                            valueCaption:'',
                           };
  });

  defProp(NYSS.SummaryReportWidget.prototype, 'updateData', function updateData() {
    var thisobj = this;
    this.report.forEach(function(v,k) {
      var thishtml = thisobj._wrapHTML($(thisobj.base_html));
      var props = $.extend({}, thisobj.default_summary, v.props);
      thishtml.find('.summary-widget-panel-wrapper').addClass(props.wrapperSize).attr('id',props.wrapperID);
      thishtml.find('.summary-widget-datapoint-icon').addClass(props.headerIcon);
      thishtml.find('.summary-widget-datapoint-text').html(props.valueCaption);
      thishtml.find('.summary-widget-datapoint-value').html(thisobj.report_data.data[v.datapoints[0].field]);
      if (props.linkIcon && props.linkTarget && props.linkText) {
        thishtml.find('.summary-widget-link-icon').addClass(props.linkIcon);
        thishtml.find('.summary-widget-link').attr('href',thisobj.generateLink(props.linkTarget));
        thishtml.find('.summary-widget-link-text').html(props.linkText);
      } else {
        thishtml.find('.summary-widget-panel-footer-wrapper').remove();
      }
      thishtml = thisobj._unwrapHTML(thishtml);
      $(thisobj.target_wrapper).find('#'+props.wrapperID).remove();
      thishtml.appendTo(thisobj.target_wrapper);
      thisobj.report_obj[v.report_name] = thishtml;
    });

  });
  /* **************************************************************************************
   * END NYSS.SummaryReportWidget definitions
   ************************************************************************************** */

})(jQuery);