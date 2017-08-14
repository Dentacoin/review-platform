(function() {
  describe('NVD3', function() {
    return describe('MultiBar Chart', function() {
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
        }, {
          key: 'Series 2',
          values: [
            {
              label: 'America',
              value: 110
            }, {
              label: 'Europe',
              value: 230
            }, {
              label: 'Asia',
              value: 51
            }, {
              label: 'Africa',
              value: 78
            }
          ]
        }, {
          key: 'Series 3',
          values: [
            {
              label: 'America',
              value: 230
            }, {
              label: 'Europe',
              value: 280
            }, {
              label: 'Asia',
              value: 31
            }, {
              label: 'Africa',
              value: 13
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
        width: 200,
        height: 200,
        color: nv.utils.defaultColor(),
        showControls: true,
        showLegend: true,
        showXAxis: true,
        showYAxis: true,
        rightAlignYAxis: false,
        reduceXTicks: true,
        staggerLabels: true,
        rotateLabels: 0,
        noData: 'No Data Available',
        duration: 0
      };
      builder = null;
      beforeEach(function() {
        builder = new ChartBuilder(nv.models.multiBarChart());
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
        wrap = builder.$('g.nvd3.nv-multiBarWithLegend');
        return should.exist(wrap[0]);
      });
      it('clears chart objects for no data', function() {
        var groups;
        builder = new ChartBuilder(nv.models.multiBarChart());
        builder.buildover(options, sampleData1, []);
        groups = builder.$('g');
        return groups.length.should.equal(0, 'removes chart components');
      });
      it('has correct structure', function() {
        var cssClass, cssClasses, i, len, results;
        cssClasses = ['.nv-x.nv-axis', '.nv-y.nv-axis', '.nv-barsWrap', '.nv-multibar', '.nv-legendWrap', '.nv-controlsWrap'];
        results = [];
        for (i = 0, len = cssClasses.length; i < len; i++) {
          cssClass = cssClasses[i];
          results.push((function(cssClass) {
            return should.exist(builder.$("g.nvd3.nv-multiBarWithLegend " + cssClass)[0]);
          })(cssClass));
        }
        return results;
      });
      it('renders bars', function() {
        var bars;
        bars = builder.$("g.nvd3.nv-multiBarWithLegend .nv-multibar .nv-bar");
        return bars.should.have.length(12);
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
