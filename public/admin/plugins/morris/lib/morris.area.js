(function() {
  var extend = function(child, parent) { for (var key in parent) { if (hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; },
    hasProp = {}.hasOwnProperty;

  Morris.Area = (function(superClass) {
    var areaDefaults;

    extend(Area, superClass);

    areaDefaults = {
      fillOpacity: 'auto',
      behaveLikeLine: false
    };

    function Area(options) {
      var areaOptions;
      if (!(this instanceof Morris.Area)) {
        return new Morris.Area(options);
      }
      areaOptions = $.extend({}, areaDefaults, options);
      this.cumulative = !areaOptions.behaveLikeLine;
      if (areaOptions.fillOpacity === 'auto') {
        areaOptions.fillOpacity = areaOptions.behaveLikeLine ? .8 : 1;
      }
      Area.__super__.constructor.call(this, areaOptions);
    }

    Area.prototype.calcPoints = function() {
      var j, len, ref, results, row, total, y;
      ref = this.data;
      results = [];
      for (j = 0, len = ref.length; j < len; j++) {
        row = ref[j];
        row._x = this.transX(row.x);
        total = 0;
        row._y = (function() {
          var k, len1, ref1, results1;
          ref1 = row.y;
          results1 = [];
          for (k = 0, len1 = ref1.length; k < len1; k++) {
            y = ref1[k];
            if (this.options.behaveLikeLine) {
              results1.push(this.transY(y));
            } else {
              total += y || 0;
              results1.push(this.transY(total));
            }
          }
          return results1;
        }).call(this);
        results.push(row._ymax = Math.max.apply(Math, row._y));
      }
      return results;
    };

    Area.prototype.drawSeries = function() {
      var i, j, k, l, len, range, ref, ref1, results, results1, results2;
      this.seriesPoints = [];
      if (this.options.behaveLikeLine) {
        range = (function() {
          results = [];
          for (var j = 0, ref = this.options.ykeys.length - 1; 0 <= ref ? j <= ref : j >= ref; 0 <= ref ? j++ : j--){ results.push(j); }
          return results;
        }).apply(this);
      } else {
        range = (function() {
          results1 = [];
          for (var k = ref1 = this.options.ykeys.length - 1; ref1 <= 0 ? k <= 0 : k >= 0; ref1 <= 0 ? k++ : k--){ results1.push(k); }
          return results1;
        }).apply(this);
      }
      results2 = [];
      for (l = 0, len = range.length; l < len; l++) {
        i = range[l];
        this._drawFillFor(i);
        this._drawLineFor(i);
        results2.push(this._drawPointFor(i));
      }
      return results2;
    };

    Area.prototype._drawFillFor = function(index) {
      var path;
      path = this.paths[index];
      if (path !== null) {
        path = path + ("L" + (this.transX(this.xmax)) + "," + this.bottom + "L" + (this.transX(this.xmin)) + "," + this.bottom + "Z");
        return this.drawFilledPath(path, this.fillForSeries(index));
      }
    };

    Area.prototype.fillForSeries = function(i) {
      var color;
      color = Raphael.rgb2hsl(this.colorFor(this.data[i], i, 'line'));
      return Raphael.hsl(color.h, this.options.behaveLikeLine ? color.s * 0.9 : color.s * 0.75, Math.min(0.98, this.options.behaveLikeLine ? color.l * 1.2 : color.l * 1.25));
    };

    Area.prototype.drawFilledPath = function(path, fill) {
      return this.raphael.path(path).attr('fill', fill).attr('fill-opacity', this.options.fillOpacity).attr('stroke', 'none');
    };

    return Area;

  })(Morris.Line);

}).call(this);
