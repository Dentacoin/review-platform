(function() {
  describe('NVD3', function() {
    return describe('Axis', function() {
      var axisOptions, builder, options, sampleData1;
      sampleData1 = [
        {
          key: 'Series 1',
          values: [[-1, -1], [0, 0], [1, 1], [2, 2]]
        }
      ];
      options = {
        x: function(d) {
          return d[0];
        },
        y: function(d) {
          return d[1];
        }
      };
      axisOptions = {
        margin: {
          top: 0,
          right: 0,
          bottom: 0,
          left: 0
        },
        width: 75,
        height: 60,
        axisLabel: 'Date',
        showMaxMin: true,
        scale: d3.scale.linear(),
        rotateYLabel: true,
        rotateLabels: 0,
        staggerLabels: false,
        axisLabelDistance: 12,
        duration: 0
      };
      builder = null;
      beforeEach(function() {
        var axis, opt, val;
        builder = new ChartBuilder(nv.models.lineChart());
        builder.build(options, sampleData1);
        axis = builder.model.xAxis;
        for (opt in axisOptions) {
          val = axisOptions[opt];
          axis[opt](val);
        }
        return builder.model.update();
      });
      afterEach(function() {
        return builder.teardown();
      });
      it('api check', function() {
        var axis, opt, results, val;
        axis = builder.model.xAxis;
        results = [];
        for (opt in axisOptions) {
          val = axisOptions[opt];
          results.push(should.exist(axis[opt](), opt + " can be called"));
        }
        return results;
      });
      it('x axis structure', function() {
        var axis, axisLabel, expected, i, j, len, maxMin, tick, ticks;
        axis = builder.$('.nv-x.nv-axis');
        should.exist(axis[0], '.nv-axis exists');
        maxMin = builder.$('.nv-x.nv-axis .nv-axisMaxMin');
        maxMin.should.have.length(2);
        maxMin[0].textContent.should.equal('-1');
        maxMin[1].textContent.should.equal('2');
        ticks = builder.$('.nv-x.nv-axis .tick');
        ticks.should.have.length(2);
        expected = ['0', '1'];
        for (i = j = 0, len = ticks.length; j < len; i = ++j) {
          tick = ticks[i];
          tick.textContent.should.equal(expected[i]);
        }
        axisLabel = builder.$('.nv-x.nv-axis .nv-axislabel');
        should.exist(axisLabel[0], 'axis label exists');
        return axisLabel[0].textContent.should.equal('Date');
      });
      it('y axis structure', function() {
        var axis, expected, i, j, len, maxMin, results, tick, ticks;
        axis = builder.$('.nv-y.nv-axis');
        should.exist(axis[0], '.nv-axis exists');
        maxMin = builder.$('.nv-y.nv-axis .nv-axisMaxMin');
        maxMin.should.have.length(2);
        maxMin[0].textContent.should.equal('-1');
        maxMin[1].textContent.should.equal('2');
        ticks = builder.$('.nv-y.nv-axis .tick');
        ticks.should.have.length(4);
        expected = ['-1', '0', '1', '2'];
        results = [];
        for (i = j = 0, len = ticks.length; j < len; i = ++j) {
          tick = ticks[i];
          results.push(tick.textContent.should.equal(expected[i]));
        }
        return results;
      });
      it('axis rotate labels', function() {
        var axis, j, k, len, len1, maxMin, results, tick, ticks, transform;
        axis = builder.model.xAxis;
        axis.rotateLabels(30);
        builder.model.update();
        ticks = builder.$('.nv-x.nv-axis .tick text');
        for (j = 0, len = ticks.length; j < len; j++) {
          tick = ticks[j];
          transform = tick.getAttribute('transform');
          transform.should.match(/rotate\(30 0,\d+?.*?\)/);
        }
        maxMin = builder.$('.nv-x.nv-axis .nv-axisMaxMin text');
        results = [];
        for (k = 0, len1 = maxMin.length; k < len1; k++) {
          tick = maxMin[k];
          transform = tick.getAttribute('transform');
          results.push(transform.should.match(/rotate\(30 0,\d+?.*?\)/));
        }
        return results;
      });
      it('axis stagger labels', function() {
        var axis, i, j, len, prevTransform, results, tick, ticks, transform;
        axis = builder.model.xAxis;
        axis.staggerLabels(true);
        builder.model.update();
        ticks = builder.$('.nv-x.nv-axis .tick text');
        prevTransform = '';
        results = [];
        for (i = j = 0, len = ticks.length; j < len; i = ++j) {
          tick = ticks[i];
          transform = tick.getAttribute('transform');
          transform.should.not.equal(prevTransform);
          transform.should.match(/translate\(0,(12|0)\)/);
          results.push(prevTransform = transform);
        }
        return results;
      });
      it('axis orientation', function(done) {
        var axis;
        axis = builder.model.xAxis;
        axis.orient('top');
        builder.model.update();
        axis.orient('right');
        builder.model.update();
        return done();
      });
      return it('has CSS class "zero" to mark zero tick', function() {
        var tick;
        tick = builder.$('.nv-x.nv-axis .tick.zero');
        tick.length.should.equal(1, 'x axis zero');
        tick = builder.$('.nv-y.nv-axis .tick.zero');
        return tick.length.should.equal(1, 'y axis zero');
      });
    });
  });

}).call(this);
