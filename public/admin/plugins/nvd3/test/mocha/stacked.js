(function() {
  describe('NVD3', function() {
    return describe('Stacked Area Chart', function() {
      var builder, options, sampleData1, sampleData2;
      sampleData1 = [
        {
          key: 'Series 1',
          values: [[-1, -1], [0, 0], [1, 1], [2, 2]]
        }
      ];
      sampleData2 = [
        {
          key: 'Series 1',
          values: [[-1, -3], [0, 6], [1, 12], [2, 18]]
        }, {
          key: 'Series 2',
          values: [[-1, -4], [0, 7], [1, 13], [2, 14]]
        }
      ];
      options = {
        x: function(d) {
          return d[0];
        },
        y: function(d) {
          return d[1];
        },
        margin: {
          top: 30,
          right: 20,
          bottom: 50,
          left: 75
        },
        color: nv.utils.defaultColor(),
        showLegend: true,
        showControls: true,
        showXAxis: true,
        showYAxis: true,
        rightAlignYAxis: false,
        useInteractiveGuideline: true,
        noData: 'No Data Available',
        duration: 0,
        controlLabels: {
          stacked: 'Stacked',
          stream: 'Stream',
          expanded: 'Expanded'
        }
      };
      builder = null;
      beforeEach(function() {
        builder = new ChartBuilder(nv.models.stackedAreaChart());
        return builder.build(options, sampleData1);
      });
      afterEach(function() {
        return builder.teardown();
      });
      it('api check', function() {
        var opt, results;
        should.exist(builder.model.options, 'options exposed');
        results = [];
        for (opt in options) {
          results.push(should.exist(builder.model[opt](), opt + " can be called"));
        }
        return results;
      });
      it('renders', function() {
        var wrap;
        wrap = builder.$('g.nvd3.nv-stackedAreaChart');
        return should.exist(wrap[0]);
      });
      it('clears chart objects for no data', function() {
        var groups;
        builder = new ChartBuilder(nv.models.stackedAreaChart());
        builder.buildover(options, sampleData1, []);
        groups = builder.$('g');
        return groups.length.should.equal(0, 'removes chart components');
      });
      it('has correct structure', function() {
        var cssClass, cssClasses, i, len, results;
        cssClasses = ['.nv-x.nv-axis', '.nv-y.nv-axis', '.nv-stackedWrap', '.nv-legendWrap', '.nv-controlsWrap', '.nv-interactive'];
        results = [];
        for (i = 0, len = cssClasses.length; i < len; i++) {
          cssClass = cssClasses[i];
          results.push((function(cssClass) {
            return should.exist(builder.$("g.nvd3.nv-stackedAreaChart " + cssClass)[0]);
          })(cssClass));
        }
        return results;
      });
      it('formats y-Axis correctly depending on stacked style', function() {
        var chart, i, len, newTickFormat, tick, yTicks;
        chart = nv.models.stackedAreaChart();
        chart.yAxis.tickFormat(function(d) {
          return "<" + d + ">";
        });
        builder = new ChartBuilder(chart);
        builder.build(options, sampleData1);
        yTicks = builder.$('.nv-y.nv-axis .tick text');
        yTicks.should.have.length.greaterThan(2);
        for (i = 0, len = yTicks.length; i < len; i++) {
          tick = yTicks[i];
          tick.textContent.should.match(/<.*?>/);
        }
        chart.dispatch.changeState({
          style: 'expand'
        });
        chart.stacked.style().should.equal('expand');
        newTickFormat = chart.yAxis.tickFormat();
        newTickFormat(1).should.equal('100%');
        chart.dispatch.changeState({
          style: 'stacked'
        });
        chart.stacked.style().should.equal('stacked');
        newTickFormat = chart.yAxis.tickFormat();
        return newTickFormat(1).should.equal('<1>');
      });
      it('can override axis ticks', function() {
        builder.model.xAxis.ticks(34);
        builder.model.yAxis.ticks(56);
        builder.model.update();
        builder.model.xAxis.ticks().should.equal(34);
        return builder.model.yAxis.ticks().should.equal(56);
      });
      return it('if stacked.offset is "wiggle", y ticks is zero', function() {
        builder.model.stacked.offset('wiggle');
        builder.model.update();
        return builder.model.yAxis.ticks().should.equal(0);
      });
    });
  });

}).call(this);
