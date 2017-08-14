(function() {
  describe('NVD3', function() {
    return describe('Historical Bar Chart', function() {
      var builder, options, sampleData1;
      sampleData1 = [
        {
          key: 'Series 1',
          values: [[-1, -1], [0, 0], [1, 1], [2, 2]]
        }
      ];
      options = {
        x: function(d, i) {
          return i;
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
        width: 200,
        height: 200,
        color: nv.utils.defaultColor(),
        showLegend: true,
        showXAxis: true,
        showYAxis: true,
        rightAlignYAxis: false,
        noData: 'No Data Available'
      };
      builder = null;
      beforeEach(function() {
        builder = new ChartBuilder(nv.models.historicalBarChart());
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
        wrap = builder.$('g.nvd3.nv-historicalBarChart');
        return should.exist(wrap[0]);
      });
      it('clears chart objects for no data', function() {
        var groups;
        builder = new ChartBuilder(nv.models.historicalBarChart());
        builder.buildover(options, sampleData1, []);
        groups = builder.$('g');
        return groups.length.should.equal(0, 'removes chart components');
      });
      it('has correct structure', function() {
        var cssClass, cssClasses, j, len, results;
        cssClasses = ['.nv-x.nv-axis', '.nv-y.nv-axis', '.nv-barsWrap', '.nv-bars', '.nv-legendWrap'];
        results = [];
        for (j = 0, len = cssClasses.length; j < len; j++) {
          cssClass = cssClasses[j];
          results.push((function(cssClass) {
            return should.exist(builder.$("g.nvd3.nv-historicalBarChart " + cssClass)[0]);
          })(cssClass));
        }
        return results;
      });
      return it('can override axis ticks', function() {
        builder.model.xAxis.ticks(34);
        builder.model.yAxis.ticks(56);
        builder.model.update();
        builder.model.xAxis.ticks().should.equal(34);
        return builder.model.yAxis.ticks().should.equal(56);
      });
    });
  });

}).call(this);
