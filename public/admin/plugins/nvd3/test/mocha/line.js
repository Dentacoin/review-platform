(function() {
  describe('NVD3', function() {
    return describe('Line Chart', function() {
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
          classed: 'dashed',
          values: [[-1, -3], [0, 6], [1, 12], [2, 18]]
        }, {
          key: 'Series 2',
          values: [[-1, -4], [0, 7], [1, 13], [2, 14]]
        }, {
          key: 'Series 3',
          values: [[-1, -5], [0, 7.2], [1, 11], [2, 18.5]]
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
        height: 400,
        width: 800,
        showLegend: true,
        showXAxis: true,
        showYAxis: true,
        rightAlignYAxis: true,
        useInteractiveGuideline: true,
        noData: 'No Data Available',
        duration: 0,
        clipEdge: false,
        isArea: function(d) {
          return d.area;
        },
        defined: function(d) {
          return true;
        },
        interpolate: 'linear'
      };
      builder = null;
      beforeEach(function() {
        builder = new ChartBuilder(nv.models.lineChart());
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
        wrap = builder.$('g.nvd3.nv-lineChart');
        return should.exist(wrap[0]);
      });
      it('no data text', function() {
        var noData;
        builder = new ChartBuilder(nv.models.lineChart());
        builder.build(options, []);
        noData = builder.$('.nv-noData');
        return noData[0].textContent.should.equal('No Data Available');
      });
      it('clears chart objects for no data', function() {
        var groups;
        builder = new ChartBuilder(nv.models.lineChart());
        builder.buildover(options, sampleData1, []);
        groups = builder.$('g');
        return groups.length.should.equal(0, 'removes chart components');
      });
      it('has correct structure', function() {
        var cssClass, cssClasses, i, len, results;
        cssClasses = ['.nv-x.nv-axis', '.nv-y.nv-axis', '.nv-linesWrap', '.nv-legendWrap', '.nv-line', '.nv-scatter', '.nv-legend'];
        results = [];
        for (i = 0, len = cssClasses.length; i < len; i++) {
          cssClass = cssClasses[i];
          results.push((function(cssClass) {
            return should.exist(builder.$("g.nvd3.nv-lineChart " + cssClass)[0]);
          })(cssClass));
        }
        return results;
      });
      it('can override axis ticks', function() {
        builder.model.xAxis.ticks(34);
        builder.model.yAxis.ticks(56);
        builder.model.update();
        builder.model.xAxis.ticks().should.equal(34);
        return builder.model.yAxis.ticks().should.equal(56);
      });
      return it('can add custom CSS class to series', function() {
        var lines;
        builder.updateData(sampleData2);
        lines = builder.$('.nv-linesWrap .nv-groups .nv-group.dashed');
        return lines.length.should.equal(1, 'dashed class exists');
      });
    });
  });

}).call(this);
