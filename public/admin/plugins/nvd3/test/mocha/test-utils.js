
/*
Utility to build an NVD3 chart.
 */

(function() {
  var ChartBuilder;

  ChartBuilder = (function() {
    function ChartBuilder(model) {
      this.model = model;
    }


    /*
    options: an object hash of chart options.
    data: sample data to pass in to chart.
    
    This method builds a chart and puts it on the <body> element.
     */

    ChartBuilder.prototype.build = function(options, data) {
      var opt, val;
      this.svg = document.createElement('svg');
      document.querySelector('body').appendChild(this.svg);
      for (opt in options) {
        val = options[opt];
        if (this.model[opt] == null) {
          console.warn(opt + " not property of model.");
        } else {
          this.model[opt](val);
        }
      }
      return this.updateData(data);
    };


    /*
    Update the data while preserving the chart model.
     */

    ChartBuilder.prototype.updateData = function(data) {
      return d3.select(this.svg).datum(data).call(this.model);
    };


    /*
    options: an object hash of chart options.
    data: sample data to pass in to initial chart render chart
    data2: sample data to pass to second chart render
    
    This method builds a chart, puts it on the <body> element, and then rebuilds using the second set of data
    Useful for testing the results of transitioning and the 'noData' state after a chart has had data
     */

    ChartBuilder.prototype.buildover = function(options, data, data2) {
      var chart, opt, val;
      this.svg = document.createElement('svg');
      document.querySelector('body').appendChild(this.svg);
      for (opt in options) {
        val = options[opt];
        if (this.model[opt] == null) {
          console.warn(opt + " not property of model.");
        } else {
          this.model[opt](val);
        }
      }
      chart = d3.select(this.svg);
      chart.datum(data).call(this.model);
      return chart.datum(data2).call(this.model);
    };

    ChartBuilder.prototype.teardown = function() {
      if (this.svg != null) {
        return document.querySelector('body').removeChild(this.svg);
      }
    };

    ChartBuilder.prototype.$ = function(cssSelector) {
      return this.svg.querySelectorAll(cssSelector);
    };

    return ChartBuilder;

  })();

}).call(this);
