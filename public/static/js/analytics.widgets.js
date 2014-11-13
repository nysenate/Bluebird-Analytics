// TODO: replace baseHTML with templating system

// initialize the NYSS namespace
var NYSS = NYSS || {};

/* Widget class definitions */
(function ($,undefined) {

  /* NYSS.AnalyticsWidget
     A base class for Chart, Summary, and List widgets for NYSS BB Analytics. */
  NYSS.AnalyticsWidget = function(d){
    /* some shortcut variables because typnig is hard
       used to help create non-enumerable methods */
    var _f = Object.defineProperty;
    var _p = Object.getPrototypeOf(this);

    /* Utility function to wrap a DOM object in a temporary element
       This allows jQuery.find() to work on the former top-level element */
    _f(_p, '_wrapHTML', {writable:true, value:
      function _wrapHTML(h) {
        return $('<div id="_WIDGETWRAPPER"></div>').html(h ? h : '');
      }
    });

    /* Utility function to remove a previously installed DOM wrapper */
    _f(_p, '_unwrapHTML', {writable:true, value:
      function _unwrapHTML(h) {
        return $(h).attr('id')=="_WIDGETWRAPPER" ? $(h).html() : h;
      }
    });

    /* ApplyData method
       This method should be overwritten by child classes.  It will handle
       the actual assignment of data points from this.data into the HTML.
       It must be able to receive (and return!) the jQuery object representing
       the widget. */
    _f(_p, 'ApplyData', {writable:true, value:
      function(h) {
        return h;
      }
    });

    /* PlaceBox method
       Appends the rendered box to the target element */
    _f(_p, 'PlaceBox', {writable:true, value:
      function(r,t) {
        if (!t) { t=this.get('baseTarget'); }
        var tt = $(t).first();
        if (tt.length==1) {
          tt.append(r);
        }
      }
    });

    /* RenderBox method
       Renders the HTML necessary to create the widget.  Expects to be passed
       an object containing JIT properties to be used in generating the HTML.
       Any properties passed will not be recorded in this.data. */
    _f(_p, 'RenderBox', {writable:true, value:
      function(d) {
        // JIT configuration could be passed here
        d=$.extend({},this.data,d);
        // wrap the HTML to make $.find() work as expected
        var ret=$(this._wrapHTML(d.baseHTML));
        // apply the data into the HTML
        ret = this.ApplyData(ret);
        // get rid of the wrapper
        ret = $(this._unwrapHTML(ret.html()));
        // check to see if the box should be placed
        if (this.autoAppend) {
          this.PlaceBox(ret,d.baseTarget);
        }
        return ret;
      }
    });

    _f(_p, 'get', {writable:true, value:
      function(d,undefined){return this.data.hasOwnProperty(d) ? this.data[d] : undefined;}
    });

    /* some possible properties of all widgets */
    this.data = {
                  baseTarget:'',
                  wrapperSize:'',
                  headerIcon:'',
                  linkIcon:'',
                  linkTarget:'',
                  linkText:'',
                  valueCaption:'',
                  baseHTML:''
    };

    /* determines if RenderBox will automatically append the resulting widget */
    this.autoAppend = true;

    /* populate this.data with passed parameters */
    $.extend(this.data,d);

  } /* end of NYSS.AnalyticsWidget */


  /* NYSS.AnalyticsSummaryWidget
     A class for rendering Summary widgets for NYSS BB Analytics. */
  NYSS.AnalyticsSummaryWidget = function(d){

    /* Call the parent constructor */
    NYSS.AnalyticsWidget.call(this,d);

    /* This object extends the NYSS.AnalyticsWidget object */
    this.prototype = Object.create(NYSS.AnalyticsWidget.prototype);
    this.prototype.constructor = NYSS.AnalyticsSummaryWidget;

    /* default data values for a summary widget */
    base_data = {
                  baseTarget:'#summary-wrapper',
                  wrapperSize:'col-lg-3',
                  headerIcon:'',
                  linkIcon:'fa fa-arrow-circle-right',
                  linkTarget:'',
                  linkText:'',
                  valueCaption:'',
                  baseHTML:
                      '<div class="summary-widget-panel-wrapper">' +
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
                      '</div>'
    };
    $.extend(this.data,base_data,d);

    /* some shortcut variables because typnig is hard  */
    var _f = Object.defineProperty;
    var _p = Object.getPrototypeOf(this);

    /* set the custom ApplyData routine */
    _f(_p,'ApplyData',{writable:true, value:
      function (e,undefined) {
        var d = {}
        var props = ['wrapperSize','widgetID','headerIcon','linkIcon','linkTarget','linkText','valueCaption','values']
        for (x in props) {
          var y = this.get(props[x]);
          if (y!=undefined) { d[props[x]] = y; }
        }
        if (d.hasOwnProperty('wrapperSize')) { e.find('.summary-widget-panel-wrapper').addClass(this.data.wrapperSize); }
        if (d.hasOwnProperty('widgetID')) { e.find('.summary-widget-panel-wrapper').attr('id',this.data.widgetID); }
        if (d.hasOwnProperty('headerIcon')) { e.find('.summary-widget-datapoint-icon').addClass(this.data.headerIcon); }
        if (d.hasOwnProperty('valueCaption')) { e.find('.summary-widget-datapoint-text').html(this.data.valueCaption); }
        if (d.hasOwnProperty('values')) {
          vh = '';
          $.each(this.data.values, function(k,v){ vh += v; });
          e.find('.summary-widget-datapoint-value').html(vh);
        }
        if (d.hasOwnProperty('linkIcon') && d.hasOwnProperty('linkTarget') && d.hasOwnProperty('linkText') &&
            d.linkIcon && d.linkTarget && d.linkText) {
          e.find('.summary-widget-link-icon').addClass(this.data.linkIcon);
          e.find('.summary-widget-link').attr('href',this.data.linkTarget);
          e.find('.summary-widget-link-text').html(this.data.linkText);
        } else {
          e.find('.summary-widget-panel-footer-wrapper').remove();
        }
        return e;
      }
    });

  } /* end of NYSS.AnalyticsSummaryWidget */


  /* NYSS.AnalyticsChartWidget
     A class for rendering Chart widgets for NYSS BB Analytics. */
  NYSS.AnalyticsChartWidget = function(d){

    /* Call the parent constructor */
    NYSS.AnalyticsWidget.call(this,d);

    /* This object extends the NYSS.AnalyticsWidget object */
    this.prototype = Object.create(NYSS.AnalyticsWidget.prototype);
    this.prototype.constructor = NYSS.AnalyticsChartWidget;

    /* default data values for a chart widget */
    base_data = {
                  baseTarget:'#chart-wrapper',
                  wrapperSize:'col-lg-12',
                  headerIcon:'fa fa-bar-chart-o',
                  valueCaption:'Page Views',
                  baseHTML:
                      '<div class="col-lg-12 chart-widget-panel-wrapper">' +
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
                      '</div>'
    };
    $.extend(this.data,base_data,d);
    this.autoAppend = false;

    /* some shortcut variables because typnig is hard  */
    var _f = Object.defineProperty;
    var _p = Object.getPrototypeOf(this);

    /* need a chart callback for hover */
    _f(_p,'ChartHoverCallback',{writable:true,value:
      function (index, options, content) {
        switch (this.data.granularity) {
          case 'hour':     fmt='ddd l, h A'; break;
          case 'day':      fmt='ddd l'; break;
          case 'month':    fmt='MMMM YYYY'; break;
          case 'minute':
          case '15minute':
          default:         fmt='ddd l, hh:mm A'; break;
        }
        time = moment(options.data[index]["ts"]).format(fmt);
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

    /* set the custom ApplyData routine */
    _f(_p,'ApplyData',{writable:true, value:
      function (e,undefined) {
        var d = {}
        var props = ['wrapperSize','widgetID','headerIcon','valueCaption'];
        for (x in props) {
          var y = this.get(props[x]);
          if (y!=undefined) { d[props[x]] = y; }
        }
        if (d.hasOwnProperty('wrapperSize')) { e.find('.chart-widget-panel-wrapper').addClass(this.data.wrapperSize); }
        if (d.hasOwnProperty('widgetID')) { e.find('.chart-widget-panel-target').attr('id',this.data.widgetID); }
        if (d.hasOwnProperty('headerIcon')) { e.find('.chart-widget-panel-icon').addClass(this.data.headerIcon); }
        if (d.hasOwnProperty('valueCaption')) { e.find('.chart-widget-panel-title').html(this.data.valueCaption); }

        var arrdata = function(dd){var ret=[];for (x in dd){ret.push(dd[x]);}return ret;}(this.data.values);
        $.extend(this.graphData,
              {
                data:   arrdata,
                element:this.data.widgetID,
                xkey:   this.data.xkey,
                ykeys:  this.data.ykeys,
                labels: this.data.labels,
                lineColors: this.data.lineColors
              }
        );

        if (this.data.widgetID!=undefined) {
          // place the core HTML, and display the element
          this.PlaceBox(this._unwrapHTML(e.html()));
          // the element MUST be visible for auto-height/width to be set properly
          $(this.get('baseTarget') + ' .chart').slideDown();
          if (!this.graphData.data.length) {
            // render "no data" notification
            var t = '<div class="chart-widget-no-data">No data available for this time period.</div>';
            $(this.get('baseTarget') + ' #' + this.data.widgetID).append(t);
          } else {
            // draw the graph
            var thisgraph = new Morris.Line(this.graphData);
          }
        }
        return e;
      }
    });

    // give the chart properties some defaults
    this.graphData = {
      pointSize: 3,
      lineColors: [],
      parseTime: true,
      continuousLine: false,
      grid: true,
      ymax: 'auto 100',
      hideHover: true,
      smooth: true,
      hoverCallback: this.ChartHoverCallback
    };

  } /* end of NYSS.AnalyticsChartWidget */


  /* NYSS.AnalyticsListWidget
     A class for rendering Chart widgets for NYSS BB Analytics. */
  NYSS.AnalyticsListWidget = function(d){

    /* Call the parent constructor */
    NYSS.AnalyticsWidget.call(this,d);

    /* This object extends the NYSS.AnalyticsWidget object */
    this.prototype = Object.create(NYSS.AnalyticsWidget.prototype);
    this.prototype.constructor = NYSS.AnalyticsListWidget;

    function _createHeader(i,t) {
      return '<i class="'+i+'"></i>'+t;
    }

    function _createLink(i,t) {
      return '<a href="#">'+t+'<i class="'+i+'"></i></a>';
    }

    function _createTableRow(i,ishead,undefined) {
      var et = (ishead !== undefined && ishead) ? 'th' : 'td';
      return "<tr>"+i.map(function(e,t){return "<"+et+">"+e+"</"+et+">"; }).join('')+"</tr>"
    }

    /* default data values for a list widget */
    base_data = {
                  baseTarget:'#list-wrapper',
                  wrapperSize:'col-lg-6',
                  headerIcon:'fa fa-building-o',
                  headerText:'Most Active Instances',
                  linkText:'View All Instances',
                  linkIcon:'fa fa-arrow-circle-right',
                  baseHTML:
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
                              '<table class="table table-bordered table-hover table-striped tablesorter list-widget-panel-target">' +
                              '</table>' +
                            '</div>' +
                            '<div class="text-right list-widget-link"></div>' +
                          '</div>' +
                        '</div>' +
                      '</div>'
    };

    $.extend(this.data,base_data,d);
    this.autoAppend = true;

    /* some shortcut variables because typnig is hard  */
    var _f = Object.defineProperty;
    var _p = Object.getPrototypeOf(this);

    /* set the custom ApplyData routine */
    _f(_p,'ApplyData',{writable:true, value:
      function (e,undefined) {
        var tHead = "<thead>"+_createTableRow(this.data.headers,true)+"</thead>";
        var tBody = '';
        var b = this.data.headers;
        if (this.data.values.length) {
          this.data.values.forEach(function(v,k){
            tp = [];
            for (one in v) { tp.push(v[one]); }
            tBody += _createTableRow(tp);
          });
        } else {
          tBody = '<tr><td class="list-widget-no-data" colspan="'+
                  this.data.headers.length+
                  '">No data available for this time period</td></tr>';
        }
        tBody = "<tbody>"+tBody+"</tbody>";
        tHTML = tHead+tBody;

        var d = {}
        var props = ['wrapperSize','widgetID','headerIcon','headerText','linkText','linkIcon'];
        for (x in props) {
          var y = this.get(props[x]);
          if (y!=undefined) { d[props[x]] = y; }
        }
        if (d.hasOwnProperty('wrapperSize')) { e.find('.list-widget-panel-wrapper').addClass(this.data.wrapperSize); }
        if (d.hasOwnProperty('widgetID')) { e.find('.list-widget-panel-target').attr('id',this.data.widgetID); }
        if (d.hasOwnProperty('headerIcon')) { e.find('.list-widget-panel-icon').addClass(this.data.headerIcon); }
        if (d.hasOwnProperty('headerText')) { e.find('.list-widget-panel-title').html(this.data.headerText); }
        if (d.hasOwnProperty('linkIcon')) { e.find('.list-widget-panel-icon').addClass(this.data.linkIcon); }

        if (d.hasOwnProperty('linkText') && (d.linkText && d.linkIcon)) {
          e.find('.list-widget-link').html(_createLink(this.data.linkIcon,this.data.linkText));
        }

        e.find('table.list-widget-panel-target').html(tHTML).slideDown();
        return e;
      }
    });

  } /* end of NYSS.AnalyticsListWidget */

})(jQuery);
