/* Base class for AJAX reports */
// initialize the NYSS namespace
var NYSS = NYSS || {};

/* Widget class definitions */
(function ($,undefined) {

  /* **************************************************************************************
   * NYSS.ReportsCollection
   * A class for managing a series of reports
   ************************************************************************************** */
  var NRC = function(d) {
  	this.initialize(d);
  	return this;
  }

	NRC.prototype.icons = {
    parent_element: '.jumbotron .jumbotron-status-icons',
    error_class: 'fa fa-warning danger',
    error_parent_class: 'error-msg',
    error_parent_id: 'fa-icon-report-error',
    error_caption: 'Error',
    working_class: 'fa fa-cog fa-spin',
    working_parent_class: '',
    working_parent_id: 'fa-icon-report-working',
    working_caption: 'Loading',
  }

	NRC.prototype.customInit = function() {
  }

	NRC.prototype.displayError = function(msgs) {
    var $this = this;
    if ($this._error_icon) {
      $this._error_icon.fadeOut(1000, function(){ $this._error_icon.remove(); });
    }
    if (Array.isArray(msgs) && msgs.length) {
      $this._error_icon = new NYSS.MessageBox({
                                caption: this.icons.error_caption,
                                container_class: this.icons.error_parent_class,
                                icon_class: this.icons.error_class,
                                parent_target: this.icons.parent_element,
                                container_id: this.icons.error_parent_id,
                                messages:msgs
                              });
    }
    $this._error_icon.render();
  };

	NRC.prototype.displayWorking = function() {
    if (this._working_icon) { this._removeWorking(); }
  	this._working_icon = new NYSS.MessageBox({
                              caption:this.icons.working_caption,
                              icon_class:this.icons.working_class,
                              parent_target:this.icons.parent_element,
                              container_id:this.icons.working_parent_id,
                            });
  	this._working_icon.render();
  };

	NRC.prototype.removeWorking = function() {
    if (this._working_icon) {
    	this._working_icon.removeBox();
    }
  	this._working_icon = null;
  };

	NRC.prototype.initialize = function(d) {
    /* "private" variables */
  	this.reports = [];
  	this.messages = [];
  	this.target_wrapper = 'body';
  	this.ajax = null;
  	this.response = null;
  	this.pending = false;

  	this.setConfig(d);
  	this.customInit();
  };

	NRC.prototype.render = function(p, force) {
    if (force || !this.response) {
    	this.pending = true;
    	this.retrieveData(p);
      return false;
    }
  	this.pending = false;
  	this.report_obj = {};
    var $this = this,
        all_reports = (Array.isArray(this.response.data)) ? this.response.data : [];
    all_reports.forEach(function(v,k) {
      var this_type = v.properties.report_type.value.capitalize(true),
          this_id = v.properties.wrapper_id.value || 'no_id'
          ;
      if (['Summary','Chart','List'].indexOf(this_type) > -1) {
        if ($this[this_id] && $this[this_id].remove) {
          $this[this_id].remove();
        }
        $this[this_id] = new NYSS[this_type+'ReportWidget'](v);
        $this[this_id].render();
      }
    });
  };

	NRC.prototype.retrieveData = function(param) {
    if (this.ajax) { this.ajax.abort(); }
    var postdata = $.extend( {}, (param instanceof Object ? param : {}) ),
        ajaxparams = {
                       url:        $('body').data('context-path')+'/api/Reports',
                       timeout:    60000,
                       type:       'POST',
                       beforeSend: function(){this.displayWorking();},
                       context:  	this,
                       data:       postdata,
                     }
        ;
  	this.ajax = $.ajax(ajaxparams).always(this.retrieveDataReturn);
  };

	NRC.prototype.retrieveDataReturn = function(response, status, jqobj) {
  	this.removeWorking();
  	this.response = JSON.parse(status=='error' ? response.responseText : response);
  	this.messages = this.response.errors.map(function(v){ return v.msg; });
    if (this.response.errorcount) {
    	this.displayError( this.messages );
    }
    $(this).trigger('onDataUpdate');
    return true;
  };

	NRC.prototype.setConfig = function(d,undefined) {
    if (d===undefined) { d={}; }

    // create event onDataUpdate
  	this.on_data_update = d.on_data_update || this.updateData;
    if (typeof this.on_data_update == 'function') {
      $(this).on('onDataUpdate',this.on_data_update);
    }
  };

	NRC.prototype.updateData = function() {
    if (this.pending) { this.render(); }
  };

  NYSS.ReportsCollection = new NRC();
  /* **************************************************************************************
   * END NYSS.ReportsCollection definitions
   ************************************************************************************** */

  /* **************************************************************************************
   * BEGIN NYSS.ReportWidget definition
   ************************************************************************************** */
  /* Some default values for properties */
  var RW = function(d) {
    this.report_obj = null;
    this.init(d);
  }

  RW.init = function(d) {
    this._init_data = d;
    this.customInit();
  }

  /* Utility function to wrap a DOM object in a temporary element
   	this allows jQuery.find() to work on the former top-level element */
	RW._wrapHTML = function(h) {
    return $('<div id="_WIDGETWRAPPER"></div>').append($(h));
  }

  /* Utility function to remove a previously installed DOM wrapper */
	RW._unwrapHTML = function(h) {
    return $(h).attr('id')=="_WIDGETWRAPPER" ? $(h).contents() : $(h);
  };

	RW.generateLink = function(dest) {
    var d = String(dest),
        ret='#',
        sep='';
    if (d) {
      if (d[0]=='/') {
        d=d.substr(1);
        sep='/';
      }
      ret = [$('body').data('contextPath'),sep,d].join('');
    }
    return ret;
  };

  RW.remove = function() {
    if (this.report_obj && this.report_obj.remove) {
      this.report_obj.remove();
    }
    this.report_obj = null;
  }

  NYSS.ReportWidget = RW;
  /* **************************************************************************************
   * END NYSS.ReportsCollection definitions
   ************************************************************************************** */


  /* **************************************************************************************
   * BEGIN NYSS.SummaryReportWidget definitions
   ************************************************************************************** */
  NYSS.SummaryReportWidget = function SummaryReportWidget(d) {
    NYSS.ReportWidget.call(this,d);
  }
  NYSS.SummaryReportWidget.prototype = Object.create(NYSS.ReportWidget);
  NYSS.SummaryReportWidget.prototype.constructor = NYSS.SummaryReportWidget;

  NYSS.SummaryReportWidget.prototype.customInit = function() {
    var $this = this;
    $this.data = $this._init_data.rows;
    $this.props = { target_wrapper:'body' };
    $.each($this._init_data.properties, function(k,v) {
      $this.props[k] = v.value;
    });
    $.extend( $this.props,
              {  fields:$this._init_data.fields,
                 report_name:$this._init_data.report_name,
                 report_descript:$this._init_data.report_descript
              }
    );
    if (!$($this.props.target_wrapper).length) {
      $this.props.target_wrapper = 'body';
    }

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
  };

  NYSS.SummaryReportWidget.prototype.render = function() {
    var thishtml = this._wrapHTML($(this.base_html)),
        sumfield = this.props.fields[0];
  	thishtml.find('.summary-widget-panel-wrapper')
  	        .addClass(this.props.wrapper_class)
  	        .attr('id',this.props.wrapper_id);
  	thishtml.find('.summary-widget-datapoint-icon').addClass(this.props.icon_class);
  	thishtml.find('.summary-widget-datapoint-text').html(this.props.value_caption);
  	thishtml.find('.summary-widget-datapoint-value').html(this.data[0][sumfield.name]);
    if (this.props.link_icon && this.props.link_target && this.props.link_text) {
    	thishtml.find('.summary-widget-link-icon').addClass(this.props.link_icon);
    	thishtml.find('.summary-widget-link').attr('href',this.generateLink(this.props.link_target));
    	thishtml.find('.summary-widget-link-text').html(this.props.link_text);
    } else {
    	thishtml.find('.summary-widget-panel-footer-wrapper').remove();
    }
  	thishtml = this._unwrapHTML(thishtml);
    $(this.props.target_wrapper).find('#'+this.props.wrapper_id).remove();
  	thishtml.appendTo($(this.props.target_wrapper));
  	this.report_obj = thishtml;
  };
  /* **************************************************************************************
   * END NYSS.SummaryReportWidget definitions
   ************************************************************************************** */

  /* **************************************************************************************
   * BEGIN NYSS.ListReportWidget definitions
   ************************************************************************************** */
  NYSS.ListReportWidget = function ListReportWidget(d) {
    NYSS.ReportWidget.call(this,d);
  }
  NYSS.ListReportWidget.prototype = Object.create(NYSS.ReportWidget);
  NYSS.ListReportWidget.prototype.constructor = NYSS.ListReportWidget;

  NYSS.ListReportWidget.prototype.customInit = function() {
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
          '</div>';
  };

  NYSS.ListReportWidget.prototype.render = function() {
    var $this = this,
        props = {},
        search_for_index = function(a,b) {
          var ret = null;
          if (a.sort_order != 0) {
            return [b, (a.sort_order > 0 ? 'asc' : 'desc')];
          }
        }
        ;

    if ($this.report_obj) {
      $this.report_obj.remove();
      $this.report_obj = null;
    }
    for (xx in $this._init_data.properties) {
      props[xx] = $this._init_data.properties[xx].value;
    }

    var dtorder = $this._init_data.fields.map(search_for_index)
                                         .filter(function(a,b){return a;});

    var dtparams = {
                columns:$this._init_data.fields.map(
                          function(a){return {title:a.name, data:a.name, name:a.name} }),
                data:$this._init_data.rows,
                searching:false,
                paging:true,
                lengthChange:true,
                autoWidth:true,
                order:dtorder,
               };
    var thishtml = $this._wrapHTML($($this.base_html));
  	thishtml.find('.list-widget-panel-wrapper').addClass(props.wrapper_class).attr('id',props.wrapper_id);
  	thishtml.find('.list-widget-panel-title').html(props.report_title);
  	thishtml.find('.list-widget-panel-icon').addClass(props.icon_class);
  	thishtml.find('.list-widget-table-container').empty();

    if (props.link_text && props.link_target) {
    	thishtml.find('.list-widget-link').html(
        $('<a/>').attr('href',thisobj.generateLink(props.link_target))
                 .text(props.link_text)
                 .addClass(props.link_icon)
        );
    }
  	thishtml = $this._unwrapHTML(thishtml);
    $(props.target_wrapper).find('#'+props.wrapper_id).remove();
  	$this.report_obj = $('<table />')
                          .attr('id','ListReport-'+props.wrapper_id)
                          .appendTo($(thishtml).find('.list-widget-table-container'))
                          .attr('class','ListReportWidget-DataTable table table-bordered table-hover table-striped tablesorter');
  	$this.report_obj.dataTable($.extend({},dtparams));
    $(props.target_wrapper).append(thishtml);
  };
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
  NYSS.ChartReportWidget.prototype.ChartHoverCallback = function (index, options, content) {
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
  };

  NYSS.ChartReportWidget.prototype.customInit = function customInit() {
  	this.base_html = '<div class="chart-widget-panel-target"></div>';
  	this.default_graph = {
                           colors: chart_colors.getColors(),
                           tooltip: {crosshairs:true, shared:true},
                           plotOptions: {series:{connectNulls:true}},
                         };
  };

  NYSS.ChartReportWidget.prototype.render = function updateData() {
    var thisobj = this,
        seriesnames = thisobj._init_data.fields.map(function(v){return v.name;}),
        categories = thisobj._init_data.rows.map(function(v){return v.timerange;}),
        series = [],
        chart_options = {
          title: { text: thisobj._init_data.report_name },
          xAxis: {
              categories: categories,
              tickInterval: 3,
              minorTickInterval: 1,
              minorTickLength: 70,
              minorTickWidth:10
          },
          legend: {
              margin: 0
          },
        }
    seriesnames.forEach(function(v) {
      if (v!='timerange') {
        oneseries = { name: v };
        oneseries.data = thisobj._init_data.rows.map(
            function(vv) { return Number(vv[v]) || 0; }
            );
        series.push(oneseries);
      }
    });

    chart_options = $.extend(true,
                            {},
                            thisobj.default_graph,
                            chart_options,
                            JSON.parse(thisobj._init_data.properties.widget_properties.value),
                            { series:series }
                            );
    var thisid = 'onechart-'+thisobj._init_data.properties.wrapper_id.value;
    var onediv = $('<div/>').attr('id',thisid).addClass('chart-instance');
    $('#chart-wrapper').append(onediv);
    $('#'+thisid).highcharts(chart_options);
    $('#chart-wrapper').find('text').last().remove()
  };
  /* **************************************************************************************
   * END NYSS.ChartReportWidget definitions
   ************************************************************************************** */

})(jQuery);