(function() {
  describe('NVD3', function() {
    return describe('Scatter Chart', function() {
      var builder, options, sampleData1, sampleData2;
      sampleData1 = [
        {
          key: 'Series 1',
          slope: 0.5,
          intercept: 0.2,
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
        width: 200,
        height: 200,
        color: nv.utils.defaultColor(),
        showDistX: true,
        showDistY: true,
        showLegend: true,
        showXAxis: true,
        showYAxis: true,
        rightAlignYAxis: false,
        noData: 'No Data Available',
        duration: 0
      };
      builder = null;
      beforeEach(function() {
        builder = new ChartBuilder(nv.models.scatterChart());
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
          should.exist(builder.model[opt], opt + " exists");
          results.push(should.exist(builder.model[opt](), opt + " can be called"));
        }
        return results;
      });
      it('renders', function() {
        var wrap;
        wrap = builder.$('g.nvd3.nv-scatterChart');
        return should.exist(wrap[0]);
      });
      it('clears chart objects for no data', function() {
        var groups;
        builder = new ChartBuilder(nv.models.scatterChart());
        builder.buildover(options, sampleData1, []);
        groups = builder.$('g');
        return groups.length.should.equal(0, 'removes chart components');
      });
      it('has correct structure', function() {
        var cssClass, cssClasses, j, len, results;
        cssClasses = ['.nv-background', '.nv-x.nv-axis', '.nv-y.nv-axis', '.nv-scatterWrap', '.nv-distWrap', '.nv-legendWrap'];
        results = [];
        for (j = 0, len = cssClasses.length; j < len; j++) {
          cssClass = cssClasses[j];
          results.push((function(cssClass) {
            return should.exist(builder.$("g.nvd3.nv-scatterChart " + cssClass)[0]);
          })(cssClass));
        }
        return results;
      });
      it('has data points', function() {
        var points;
        points = builder.$('.nv-groups .nv-series-0 .nv-point');
        return points.should.have.length(4);
      });
      it('has a legend', function() {
        var legend;
        legend = builder.$('.nv-legendWrap');
        return should.exist(legend, 'legend exists');
      });
      it('shows no data message', function() {
        var noData;
        builder.teardown();
        builder.build(options, []);
        noData = builder.$('text.nv-noData');
        should.exist(noData[0]);
        return noData[0].textContent.should.equal('No Data Available');
      });
      it('can update with new data', function() {
        var points1, points2;
        d3.select(builder.svg).datum(sampleData2);
        builder.model.update();
        points1 = builder.$('.nv-groups .nv-series-0 .nv-point');
        points1.should.have.length(4);
        points2 = builder.$('.nv-groups .nv-series-1 .nv-point');
        return points2.should.have.length(4);
      });
      it('scatterPlusLineChart', function() {
        var lines, sampleData3, wrap;
        builder.teardown();
        sampleData3 = [
          {
            key: 'Series 1',
            values: [[-1, -3], [0, 6], [1, 12], [2, 18]],
            slope: 0.1,
            inercept: 5
          }
        ];
        builder.build(options, sampleData3);
        wrap = builder.$('g.nvd3.nv-scatterChart');
        should.exist(wrap[0]);
        lines = builder.$('g.nvd3 .nv-regressionLinesWrap .nv-regLines');
        return should.exist(lines[0], 'regression lines exist');
      });
      it('sets legend.width same as availableWidth', function() {
        return builder.model.legend.width().should.equal(builder.model.scatter.width());
      });
      it('translates nv-wrap after legend height calculated', function() {
        var i, j, sampleData4, transform;
        builder.teardown();
        sampleData4 = [];
        for (i = j = 0; j <= 40; i = ++j) {
          sampleData4.push({
            key: "Series " + i,
            values: [[Math.random(), Math.random()]]
          });
        }
        builder.build(options, sampleData4);
        transform = builder.$('.nv-wrap')[0].getAttribute('transform');
        return transform.should.equal('translate(75,830)');
      });
      it('can override axis ticks', function() {
        builder.model.xAxis.ticks(34);
        builder.model.yAxis.ticks(56);
        builder.model.update();
        builder.model.xAxis.ticks().should.equal(34);
        return builder.model.yAxis.ticks().should.equal(56);
      });
      it('only appends one nv-point-clips group', function(done) {
        var builder2;
        builder2 = new ChartBuilder(nv.models.scatterChart());
        builder2.build(options, sampleData1);
        return window.setTimeout(function() {
          builder2.model.update();
          return window.setTimeout((function() {
            var pointClips;
            pointClips = builder2.svg.querySelector('.nv-point-clips');
            should.exist(pointClips, 'nv-point-clips exists');
            builder2.svg.querySelector('.nv-wrap.nv-scatter').childElementCount.should.equal(3);
            builder2.teardown();
            return done();
          }), 500);
        }, 500);
      });
      it('sets nv-single-point class if only one data point', function() {
        var singleData;
        builder.teardown();
        singleData = [
          {
            key: 'Series1',
            values: [[1, 1]]
          }
        ];
        builder.build(options, singleData);
        builder.svg.querySelector('.nv-wrap.nv-scatter').className.should.contain('nv-single-point');
        builder.updateData(sampleData1);
        builder.svg.querySelector('.nv-wrap.nv-scatter').className.should.not.contain('nv-single-point');
        builder.updateData(singleData);
        return builder.svg.querySelector('.nv-wrap.nv-scatter').className.should.contain('nv-single-point');
      });
      return it('should set color property if not specified', function() {
        var singleData;
        builder.teardown();
        singleData = [
          {
            key: 'Series1',
            values: [[1, 1]]
          }
        ];
        builder.build(options, singleData);
        return should.exist(singleData[0].color);
      });
    });
  });

}).call(this);
