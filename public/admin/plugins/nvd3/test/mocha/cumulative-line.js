(function() {
  describe('NVD3', function() {
    return describe('Cumulative Line Chart', function() {
      var builder1, eventTooltipData, options, sampleData1, sampleData2;
      sampleData1 = [
        {
          key: 'Series 1',
          values: [[-1, -1], [0, 0], [1, 1], [2, 2]],
          average: 1.3
        }
      ];
      sampleData2 = [
        {
          key: 'Series 1',
          values: [[-1, -3], [0, 6], [1, 12], [2, 18]],
          average: 12.3
        }, {
          key: 'Series 2',
          values: [[-1, -4], [0, 7], [1, 13], [2, 14]]
        }
      ];
      eventTooltipData = {
        mouseX: 1250,
        mouseY: 363,
        pointXValue: 1271774227712.8547
      };
      options = {
        x: function(d) {
          return d[0];
        },
        y: function(d) {
          return d[1];
        },
        margin: {
          top: 10,
          right: 20,
          bottom: 30,
          left: 40
        },
        color: nv.utils.defaultColor(),
        showLegend: true,
        showXAxis: true,
        showYAxis: true,
        rightAlignYAxis: false,
        useInteractiveGuideline: true,
        noData: 'No Data Available',
        average: function(d) {
          return d.average;
        },
        duration: 0,
        noErrorCheck: false
      };
      builder1 = null;
      beforeEach(function() {
        var elements, results;
        builder1 = new ChartBuilder(nv.models.cumulativeLineChart());
        builder1.build(options, sampleData1);
        elements = document.getElementsByClassName('nvtooltip');
        results = [];
        while (elements[0]) {
          results.push(elements[0].parentNode.removeChild(elements[0]));
        }
        return results;
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
        wrap = builder1.$('g.nvd3.nv-cumulativeLine');
        return should.exist(wrap[0]);
      });
      it('has the element with .nv-cumulativeLine class right positioned', function() {
        var cumulativeLine;
        cumulativeLine = builder1.$('g.nvd3.nv-cumulativeLine');
        return cumulativeLine[0].getAttribute('transform').should.be.equal("translate(40,30)");
      });
      it('clears chart objects for no data', function() {
        var builder, groups;
        builder = new ChartBuilder(nv.models.cumulativeLineChart());
        builder.buildover(options, sampleData1, []);
        groups = builder.$('g');
        return groups.length.should.equal(0, 'removes chart components');
      });
      it('has correct structure', function() {
        var cssClass, cssClasses, i, len, results;
        cssClasses = ['.nv-interactive', '.nv-interactiveLineLayer', '.nv-interactiveGuideLine', '.nv-y.nv-axis', '.nv-x.nv-axis', '.nv-background', '.nv-linesWrap', '.nv-line', '.nv-scatterWrap', '.nv-scatter', '.nv-indexLine', '.nv-avgLinesWrap', '.nv-legendWrap', '.nv-controlsWrap', '.tempDisabled'];
        results = [];
        for (i = 0, len = cssClasses.length; i < len; i++) {
          cssClass = cssClasses[i];
          results.push((function(cssClass) {
            return should.exist(builder1.$("g.nvd3 " + cssClass)[0]);
          })(cssClass));
        }
        return results;
      });
      return describe("applies correctly option", function() {
        var builder, sampleData;
        builder = null;
        sampleData = sampleData1;
        beforeEach(function() {
          return builder = new ChartBuilder(nv.models.cumulativeLineChart());
        });
        afterEach(function() {
          return builder.teardown();
        });
        xit('margin', function() {
          options = {
            margin: {
              top: 10,
              right: 20,
              bottom: 30,
              left: 40
            }
          };
          builder.build(options, sampleData);
          return builder.$(".nv-cumulativeLine")[0].getAttribute('transform').should.be.equal("translate(40,10)");
        });
        it("color", function() {
          var legendSymbol;
          options.color = function() {
            return "#000000";
          };
          builder.build(options, sampleData);
          legendSymbol = builder.$(".nv-cumulativeLine .nv-legend-symbol");
          expect(legendSymbol[0].getAttribute("style")).to.contain("fill: rgb(0, 0, 0)");
          return expect(legendSymbol[0].getAttribute("style")).to.contain("stroke: rgb(0, 0, 0)");
        });
        describe("showLegend", function() {
          it('true', function() {
            options.showLegend = true;
            builder.build(options, sampleData);
            return builder.$(".nv-cumulativeLine .nv-legendWrap *").length.should.not.be.equal(0);
          });
          return it('false', function() {
            options = {
              showLegend: false
            };
            builder.build(options, sampleData);
            return builder.$(".nv-cumulativeLine .nv-legendWrap *").length.should.be.equal(0);
          });
        });
        describe('showXAxis', function() {
          it('true', function() {
            options.showXAxis = true;
            builder.build(options, sampleData);
            return builder.$(".nv-cumulativeLine .nv-axis.nv-x *").length.should.not.be.equal(0);
          });
          it('false', function() {
            options.showXAxis = false;
            builder.build(options, sampleData);
            return builder.$(".nv-cumulativeLine .nv-axis.nv-x *").length.should.be.equal(0);
          });
          return it('can override axis ticks', function() {
            builder.build(options, sampleData);
            builder.model.xAxis.ticks(34);
            builder.model.yAxis.ticks(56);
            builder.model.update();
            builder.model.xAxis.ticks().should.equal(34);
            return builder.model.yAxis.ticks().should.equal(56);
          });
        });
        describe('showYAxis', function() {
          it('true', function() {
            options.showYAxis = true;
            builder.build(options, sampleData);
            return builder.$(".nv-cumulativeLine .nv-axis.nv-y *").length.should.not.be.equal(0);
          });
          return it('false', function() {
            options.showYAxis = false;
            builder.build(options, sampleData);
            return builder.$(".nv-cumulativeLine .nv-axis.nv-y *").length.should.be.equal(0);
          });
        });
        describe('rightAlignYAxis', function() {
          it('true', function() {
            options.rightAlignYAxis = true;
            builder.build(options, sampleData);
            return builder.$(".nv-cumulativeLine .nv-axis.nv-y")[0].getAttribute('transform').should.be.equal("translate(870,0)");
          });
          return it('false', function() {
            options.rightAlignYAxis = false;
            builder.build(options, sampleData);
            return assert.isNull(builder.$(".nv-cumulativeLine .nv-axis.nv-y")[0].getAttribute('transform'));
          });
        });
        describe("useInteractiveGuideline", function() {
          it("true", function() {
            options.useInteractiveGuideline = true;
            builder.build(options, sampleData);
            return builder.$(".nv-cumulativeLine .nv-interactiveLineLayer").should.have.length(1);
          });
          return it("false", function() {
            options.useInteractiveGuideline = false;
            builder.build(options, sampleData);
            return builder.$(".nv-cumulativeLine .nv-interactiveLineLayer").should.have.length(0);
          });
        });
        describe("noErrorCheck", function() {
          xit("true", function() {
            options.noErrorCheck = true;
            return builder.build(options, sampleData);
          });
          return xit("false", function() {
            options.noErrorCheck = false;
            return builder.build(options, sampleData);
          });
        });
        it("noData", function() {
          options.noData = "error error";
          builder.build(options, []);
          return builder.svg.textContent.should.be.equal('error error');
        });
        it("x", function() {
          options.x = function(d) {
            return d[1];
          };
          builder.build(options, sampleData);
          return builder.model.x()([1, 2]).should.be.equal(2);
        });
        it("y", function() {
          options.y = function(d) {
            return d[0];
          };
          builder.build(options, sampleData);
          return builder.model.y()({
            display: {
              y: 1
            }
          }).should.be.equal(1);
        });
        it("average", function() {
          options.average = function(d) {
            return d.avg;
          };
          builder.build(options, sampleData);
          return builder.model.average()({
            avg: 1
          }).should.be.equal(1);
        });
        return it("duration", function() {
          options.duration = 100;
          builder.build(options, sampleData);
          return builder.model.duration().should.be.equal(100);
        });
      });
    });
  });

}).call(this);
