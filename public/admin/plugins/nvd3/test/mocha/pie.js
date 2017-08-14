(function() {
  describe('NVD3', function() {
    return describe('Pie Chart', function() {
      var builder, options, sampleData1;
      sampleData1 = [
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
        showLegend: true,
        valueFormat: function(d) {
          return d.toFixed(2);
        },
        showLabels: true,
        labelsOutside: true,
        donut: false,
        donutRatio: 0.5,
        labelThreshold: 0.02,
        labelType: 'key',
        noData: 'No Data Available',
        duration: 0,
        startAngle: false,
        endAngle: false,
        padAngle: false,
        cornerRadius: 0,
        labelSunbeamLayout: false
      };
      builder = null;
      beforeEach(function() {
        builder = new ChartBuilder(nv.models.pieChart());
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
      describe('renders', function() {
        var labels, wrap;
        wrap = null;
        labels = null;
        beforeEach(function() {
          wrap = builder.$('g.nvd3.nv-pieChart');
          return labels = wrap[0].querySelectorAll('.nv-label text');
        });
        it('.nv-pieChart', function() {
          return should.exist(wrap[0]);
        });
        it('can access margin', function() {
          var m;
          builder.model.margin({
            top: 31,
            right: 21,
            bottom: 51,
            left: 76
          });
          m = builder.model.margin();
          return m.should.deep.equal({
            top: 31,
            right: 21,
            bottom: 51,
            left: 76
          });
        });
        return describe('labels correctly', function() {
          var i, item, j, len, results;
          it("[" + sampleData1.length + "] labels", function() {
            return wrap[0].querySelectorAll('.nv-label').should.have.length(sampleData1.length);
          });
          results = [];
          for (i = j = 0, len = sampleData1.length; j < len; i = ++j) {
            item = sampleData1[i];
            results.push((function(item, i) {
              return it("label '" + item.label + "'", function() {
                return item.label.should.be.equal(labels[i].textContent);
              });
            })(item, i));
          }
          return results;
        });
      });
      it('clears chart objects for no data', function() {
        var groups;
        builder = new ChartBuilder(nv.models.pieChart());
        builder.buildover(options, sampleData1, []);
        groups = builder.$('g');
        return groups.length.should.equal(0, 'removes chart components');
      });
      it('has correct structure', function() {
        var cssClass, cssClasses, j, len, results;
        cssClasses = ['.nv-pieWrap', '.nv-pie', '.nv-pieLabels', '.nv-legendWrap'];
        results = [];
        for (j = 0, len = cssClasses.length; j < len; j++) {
          cssClass = cssClasses[j];
          results.push((function(cssClass) {
            return should.exist(builder.$("g.nvd3.nv-pieChart " + cssClass)[0]);
          })(cssClass));
        }
        return results;
      });
      it('can handle donut mode and options', function(done) {
        builder.teardown();
        options.donut = true;
        options.labelSunbeamLayout = true;
        options.startAngle = function(d) {
          return d.startAngle / 2 - Math.PI / 2;
        };
        options.endAngle = function(d) {
          return d.endAngle / 2 - Math.PI / 2;
        };
        builder.build(options, sampleData1);
        return done();
      });
      it('can handle cornerRadius and padAngle options', function(done) {
        builder.teardown();
        options.padAngle = 5;
        options.cornerRadius = 5;
        builder.build(options, sampleData1);
        return done();
      });
      return it('can render pie labels in other formats', function() {
        var builder2, expected, i, j, k, label, labels, len, len1, opts, results;
        opts = {
          x: function(d) {
            return d.label;
          },
          y: function(d) {
            return d.value;
          },
          labelType: 'value',
          valueFormat: d3.format('.2f')
        };
        builder2 = new ChartBuilder(nv.models.pie());
        builder2.build(opts, [sampleData1]);
        labels = builder2.$('.nv-pieLabels .nv-label text');
        labels.length.should.equal(4);
        expected = ['100.00', '200.00', '50.00', '70.00'];
        for (i = j = 0, len = labels.length; j < len; i = ++j) {
          label = labels[i];
          label.textContent.should.equal(expected[i]);
        }
        builder2.teardown();
        opts.labelType = 'percent';
        opts.valueFormat = d3.format('%');
        builder2.build(opts, [sampleData1]);
        labels = builder2.$('.nv-pieLabels .nv-label text');
        labels.length.should.equal(4);
        expected = ['24%', '48%', '12%', '17%'];
        results = [];
        for (i = k = 0, len1 = labels.length; k < len1; i = ++k) {
          label = labels[i];
          results.push(label.textContent.should.equal(expected[i]));
        }
        return results;
      });
    });
  });

}).call(this);
