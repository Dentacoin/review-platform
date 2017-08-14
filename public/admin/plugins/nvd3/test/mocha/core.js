(function() {
  describe('NVD3', function() {
    return describe('Core', function() {
      var objects;
      objects = ['window.nv', 'd3_time_range', 'nv.utils', 'nv.models', 'nv.charts', 'nv.graphs', 'nv.logs', 'nv.dispatch', 'nv.log', 'nv.deprecated', 'nv.render', 'nv.addGraph'];
      describe('has', function() {
        var i, len, obj, results;
        results = [];
        for (i = 0, len = objects.length; i < len; i++) {
          obj = objects[i];
          results.push(it(" " + obj + " object", function() {
            return should.exist(eval(obj));
          }));
        }
        return results;
      });
      return describe('has nv.dispatch with default', function() {
        var dispatchDefaults, event, i, len, results;
        dispatchDefaults = ['render_start', 'render_end'];
        results = [];
        for (i = 0, len = dispatchDefaults.length; i < len; i++) {
          event = dispatchDefaults[i];
          results.push((function(event) {
            return it(event + " event", function() {
              return assert.isFunction(nv.dispatch[event]);
            });
          })(event));
        }
        return results;
      });
    });
  });

}).call(this);
