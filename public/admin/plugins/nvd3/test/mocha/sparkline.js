(function() {
  describe('NVD3', function() {
    return describe('Sparkline Chart', function() {
      var builder, options, sampleData1;
      sampleData1 = [
        {
          x: 1,
          y: 100
        }, {
          x: 2,
          y: 101
        }, {
          x: 3,
          y: 99
        }, {
          x: 4,
          y: 56
        }, {
          x: 5,
          y: 87
        }
      ];
      options = {
        x: function(d) {
          return d.x;
        },
        y: function(d) {
          return d.y;
        },
        margin: {
          top: 30,
          right: 20,
          bottom: 50,
          left: 75
        },
        width: 200,
        height: 50,
        xTickFormat: function(d) {
          return d;
        },
        yTickFormat: function(d) {
          return d.toFixed(2);
        },
        showLastValue: true,
        alignValue: true,
        rightAlignValue: false,
        noData: 'No Data Available'
      };
      builder = null;
      beforeEach(function() {
        builder = new ChartBuilder(nv.models.sparklinePlus());
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
      it('clears chart objects for no data', function() {
        var groups;
        builder = new ChartBuilder(nv.models.sparklinePlus());
        builder.buildover(options, sampleData1, []);
        groups = builder.$('g');
        return groups.length.should.equal(0, 'removes chart components');
      });
      it('renders', function() {
        var wrap;
        wrap = builder.$('g.nvd3.nv-sparklineplus');
        return should.exist(wrap[0]);
      });
      return it('has correct structure', function() {
        var cssClass, cssClasses, i, len, results;
        cssClasses = ['.nv-sparklineWrap', '.nv-sparkline', '.nv-minValue', '.nv-maxValue', '.nv-currentValue', '.nv-valueWrap'];
        results = [];
        for (i = 0, len = cssClasses.length; i < len; i++) {
          cssClass = cssClasses[i];
          results.push((function(cssClass) {
            return should.exist(builder.$("g.nvd3.nv-sparklineplus " + cssClass)[0]);
          })(cssClass));
        }
        return results;
      });
    });
  });

}).call(this);
