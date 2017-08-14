(function() {
  describe('NVD3', function() {
    return describe('Bullet Chart', function() {
      var builder1, options, sampleData1;
      sampleData1 = {
        title: 'Revenue',
        subtitle: 'US$ in thousands',
        ranges: [10, 20, 30],
        measures: [40],
        markers: [50, 100]
      };
      options = {
        orient: 'left',
        margin: {
          top: 60,
          right: 70,
          bottom: 80,
          left: 90
        },
        color: nv.utils.defaultColor(),
        ranges: function(d) {
          return d.ranges;
        },
        markers: function(d) {
          return d.markers;
        },
        measures: function(d) {
          return d.measures;
        },
        width: 100,
        height: 110,
        tickFormat: function(d) {
          return d.toFixed(2);
        },
        noData: 'No Data Available'
      };
      builder1 = null;
      beforeEach(function() {
        builder1 = new ChartBuilder(nv.models.bulletChart());
        return builder1.build(options, sampleData1);
      });
      afterEach(function() {
        return builder1.teardown();
      });
      it('api check', function() {
        var opt, results;
        should.exist(builder1.model.options, 'options exposed');
        results = [];
        for (opt in options) {
          results.push(should.exist(builder1.model[opt](), opt + " can be called"));
        }
        return results;
      });
      it('renders', function() {
        var wrap;
        wrap = builder1.$('g.nvd3.nv-bulletChart');
        return should.exist(wrap[0]);
      });
      it('displays multiple markers', function() {
        var markers;
        markers = document.querySelectorAll('.nv-markerTriangle');
        return markers.length.should.equal(2);
      });
      it('has correct g.nvd3.nv-bulletChart position', function() {
        var chart;
        chart = builder1.$('g.nvd3.nv-bulletChart');
        return chart[0].getAttribute('transform').should.be.equal('translate(90,60)');
      });
      it("has correct structure", function() {
        var cssClass, cssClasses, j, len, results;
        cssClasses = ['.nv-bulletWrap', '.nv-bullet', '.nv-rangeMax', '.nv-rangeAvg', '.nv-rangeMin', '.nv-measure', '.nv-markerTriangle', '.nv-titles', '.nv-title', '.nv-subtitle'];
        results = [];
        for (j = 0, len = cssClasses.length; j < len; j++) {
          cssClass = cssClasses[j];
          results.push((function(cssClass) {
            return should.exist(builder1.$("g.nvd3 " + cssClass)[0]);
          })(cssClass));
        }
        return results;
      });
      return describe("applies correctly option", function() {
        var builder, sampleData;
        builder = null;
        sampleData = null;
        beforeEach(function() {
          builder = new ChartBuilder(nv.models.bulletChart());
          return sampleData = {
            title: 'Revenue',
            subtitle: 'US$ in thousands',
            ranges: [10, 20, 30],
            measures: [40],
            markers: [50]
          };
        });
        afterEach(function() {
          return builder.teardown();
        });
        describe("orient", function() {
          it('left', function() {
            var i, j, len, offsetCurrent, offsetPrevious, pattern, results, tick, ticks;
            options = {
              orient: 'left'
            };
            builder.build(options, sampleData);
            ticks = builder.$(".nv-tick");
            offsetPrevious = 0;
            offsetCurrent = 0;
            pattern = /translate\((.*),0\)/;
            results = [];
            for (i = j = 0, len = ticks.length; j < len; i = ++j) {
              tick = ticks[i];
              offsetPrevious = offsetCurrent;
              offsetCurrent = parseInt(ticks[i].getAttribute('transform').match(pattern)[1]);
              if (i > 0) {
                results.push(expect(offsetPrevious).to.be.below(offsetCurrent));
              } else {
                results.push(void 0);
              }
            }
            return results;
          });
          return it('right', function() {
            var i, j, len, offsetCurrent, offsetPrevious, pattern, results, tick, ticks;
            options = {
              orient: 'right'
            };
            builder.build(options, sampleData);
            ticks = builder.$(".nv-tick");
            offsetPrevious = 0;
            offsetCurrent = 0;
            pattern = /translate\((.*),0\)/;
            results = [];
            for (i = j = 0, len = ticks.length; j < len; i = ++j) {
              tick = ticks[i];
              offsetPrevious = offsetCurrent;
              offsetCurrent = parseInt(ticks[i].getAttribute('transform').match(pattern)[1]);
              if (i > 0) {
                results.push(expect(offsetPrevious).to.be.above(offsetCurrent));
              } else {
                results.push(void 0);
              }
            }
            return results;
          });
        });
        it("noData", function() {
          options = {
            noData: 'No Data Available'
          };
          builder.build(options, {});
          return builder.svg.textContent.should.be.equal('No Data Available');
        });
        it('clears chart objects for no data', function() {
          var groups;
          builder = new ChartBuilder(nv.models.bulletChart());
          builder.buildover(options, sampleData, []);
          groups = builder.$('g');
          return groups.length.should.equal(0, 'removes chart components');
        });
        it('margin', function() {
          options = {
            margin: {
              top: 10,
              right: 20,
              bottom: 30,
              left: 40
            }
          };
          builder.build(options, sampleData);
          return builder.$(".nv-bulletChart")[0].getAttribute('transform').should.be.equal("translate(40,10)");
        });
        it("color", function() {
          options = {
            color: function() {
              return "#000000";
            }
          };
          builder.build(options, sampleData);
          return expect(builder.$(".nv-measure")[0].getAttribute("style")).to.contain("fill: rgb(0, 0, 0)");
        });
        it('width', function() {
          options = {
            margin: {
              top: 0,
              right: 0,
              bottom: 0,
              left: 0
            },
            width: 300
          };
          builder.build(options, sampleData);
          return parseInt(builder.$(".nv-rangeMax")[0].getAttribute('width')).should.be.equal(300);
        });
        return it('height', function() {
          options = {
            margin: {
              top: 0,
              right: 0,
              bottom: 0,
              left: 0
            },
            height: 300
          };
          builder.build(options, sampleData);
          return parseInt(builder.$(".nv-rangeMax")[0].getAttribute('height')).should.be.equal(300);
        });
      });
    });
  });

}).call(this);
