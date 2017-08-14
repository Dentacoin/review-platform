(function() {
  describe('NVD3', function() {
    return describe('Box Plot', function() {
      var builder, options, sampleData1, sampleData2, sampleData3;
      sampleData1 = [
        {
          label: 'Sample A',
          values: {
            Q1: 120,
            Q2: 150,
            Q3: 200,
            whisker_low: 115,
            whisker_high: 210,
            outliers: [50, 100, 225]
          }
        }
      ];
      sampleData2 = [
        {
          label: 'Sample A',
          values: {
            Q1: 120,
            Q2: 150,
            Q3: 200,
            whisker_low: 115,
            whisker_high: 210,
            outliers: []
          }
        }
      ];
      sampleData3 = [
        {
          label: 'Sample A',
          values: {
            Q1: 120,
            Q2: 150,
            Q3: 200,
            whisker_low: 115,
            whisker_high: 210,
            outliers: [50, 100, 225]
          }
        }, {
          label: 'Sample B',
          values: {
            Q1: 300,
            Q2: 350,
            Q3: 400,
            whisker_low: 2255,
            whisker_high: 400,
            outliers: [175]
          }
        }
      ];
      options = {
        x: function(d) {
          return d.label;
        },
        y: function(d) {
          return d.values.Q3;
        },
        margin: {
          top: 30,
          right: 20,
          bottom: 50,
          left: 75
        },
        color: nv.utils.defaultColor(),
        height: 400,
        width: 800,
        showXAxis: true,
        showYAxis: true,
        noData: 'No Data Available',
        duration: 0,
        maxBoxWidth: 75
      };
      builder = null;
      beforeEach(function() {
        builder = new ChartBuilder(nv.models.boxPlotChart());
        return builder.build(options, sampleData1);
      });
      afterEach(function() {
        return builder.teardown();
      });
      it('api check', function() {
        var opt;
        should.exist(builder.model.options, 'options exposed');
        for (opt in options) {
          should.exist(builder.model[opt](), opt + " can be called");
        }
        return builder.model.update();
      });
      it('renders', function() {
        var wrap;
        wrap = builder.$('g.nvd3.nv-boxPlotWithAxes');
        return should.exist(wrap[0]);
      });
      it('no data text', function() {
        var noData;
        builder = new ChartBuilder(nv.models.boxPlotChart());
        builder.build(options, []);
        noData = builder.$('.nv-noData');
        return noData[0].textContent.should.equal('No Data Available');
      });
      it('has correct structure', function() {
        var cssClass, cssClasses, i, len, results;
        cssClasses = ['.nv-x.nv-axis', '.nv-y.nv-axis', '.nv-barsWrap', '.nv-wrap', '.nv-boxplot', '.nv-boxplot-median', '.nv-boxplot-tick.nv-boxplot-low', '.nv-boxplot-whisker.nv-boxplot-low', '.nv-boxplot-tick.nv-boxplot-high', '.nv-boxplot-whisker.nv-boxplot-high'];
        results = [];
        for (i = 0, len = cssClasses.length; i < len; i++) {
          cssClass = cssClasses[i];
          results.push((function(cssClass) {
            return should.exist(builder.$("g.nvd3.nv-boxPlotWithAxes " + cssClass)[0]);
          })(cssClass));
        }
        return results;
      });
      it('Has boxplots', function() {
        var boxes;
        builder = new ChartBuilder(nv.models.boxPlotChart());
        builder.buildover(options, sampleData3, []);
        boxes = builder.$('.nv-boxplot-box');
        return boxes.length.should.equal(2, 'boxplots exist');
      });
      it('Has outliers', function() {
        var outliers;
        builder = new ChartBuilder(nv.models.boxPlotChart());
        builder.buildover(options, sampleData1, []);
        outliers = builder.$('.nv-boxplot .nv-boxplot-outlier');
        return outliers.length.should.equal(3, 'outliers exist');
      });
      return it('Has no outliers', function() {
        var outliers;
        builder = new ChartBuilder(nv.models.boxPlotChart());
        builder.buildover(options, sampleData2, []);
        return outliers = builder.$('.nv-boxplot-outlier');
      });
    });
  });

}).call(this);
