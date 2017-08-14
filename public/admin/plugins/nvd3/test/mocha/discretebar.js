(function() {
  describe('NVD3', function() {
    return describe('Discrete Bar Chart', function() {
      var builder, options, sampleData1;
      sampleData1 = [
        {
          key: 'Series 1',
          values: [
            {
              label: 'America',
              value: 100
            }, {
              label: 'Europe',
              value: 200
            }, {
              label: 'Asia',
              value: 50
            }, {
              label: 'Africa',
              value: 70
            }
          ]
        }
      ];
      options = {
        x: function(d) {
          return d.label;
        },
        y: function(d) {
          return d.value;
        },
        margin: {
          top: 30,
          right: 20,
          bottom: 50,
          left: 75
        },
        color: nv.utils.defaultColor(),
        showXAxis: true,
        showYAxis: true,
        rightAlignYAxis: false,
        staggerLabels: true,
        showValues: true,
        valueFormat: function(d) {
          return d.toFixed(2);
        },
        noData: 'No Data Available',
        duration: 0
      };
      builder = null;
      beforeEach(function() {
        builder = new ChartBuilder(nv.models.discreteBarChart());
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
        wrap = builder.$('g.nvd3.nv-discreteBarWithAxes');
        return should.exist(wrap[0]);
      });
      it('clears chart objects for no data', function() {
        var groups;
        builder = new ChartBuilder(nv.models.discreteBarChart());
        builder.buildover(options, sampleData1, []);
        groups = builder.$('g');
        return groups.length.should.equal(0, 'removes chart components');
      });
      it('has correct structure', function() {
        var cssClass, cssClasses, i, len, results;
        cssClasses = ['.nv-x.nv-axis', '.nv-y.nv-axis', '.nv-barsWrap', '.nv-discretebar'];
        results = [];
        for (i = 0, len = cssClasses.length; i < len; i++) {
          cssClass = cssClasses[i];
          results.push((function(cssClass) {
            return should.exist(builder.$("g.nvd3.nv-discreteBarWithAxes " + cssClass)[0]);
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
