(function() {
  var minutesSpecHelper, secondsSpecHelper,
    bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; },
    extend = function(child, parent) { for (var key in parent) { if (hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; },
    hasProp = {}.hasOwnProperty,
    indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; };

  Morris.Line = (function(superClass) {
    extend(Line, superClass);

    function Line(options) {
      this.hilight = bind(this.hilight, this);
      this.onHoverOut = bind(this.onHoverOut, this);
      this.onHoverMove = bind(this.onHoverMove, this);
      this.onGridClick = bind(this.onGridClick, this);
      if (!(this instanceof Morris.Line)) {
        return new Morris.Line(options);
      }
      Line.__super__.constructor.call(this, options);
    }

    Line.prototype.init = function() {
      if (this.options.hideHover !== 'always') {
        this.hover = new Morris.Hover({
          parent: this.el
        });
        this.on('hovermove', this.onHoverMove);
        this.on('hoverout', this.onHoverOut);
        return this.on('gridclick', this.onGridClick);
      }
    };

    Line.prototype.defaults = {
      lineWidth: 3,
      pointSize: 4,
      lineColors: ['#0b62a4', '#7A92A3', '#4da74d', '#afd8f8', '#edc240', '#cb4b4b', '#9440ed'],
      pointStrokeWidths: [1],
      pointStrokeColors: ['#ffffff'],
      pointFillColors: [],
      smooth: true,
      xLabels: 'auto',
      xLabelFormat: null,
      xLabelMargin: 24,
      hideHover: false
    };

    Line.prototype.calc = function() {
      this.calcPoints();
      return this.generatePaths();
    };

    Line.prototype.calcPoints = function() {
      var k, len, ref, results, row, y;
      ref = this.data;
      results = [];
      for (k = 0, len = ref.length; k < len; k++) {
        row = ref[k];
        row._x = this.transX(row.x);
        row._y = (function() {
          var len1, m, ref1, results1;
          ref1 = row.y;
          results1 = [];
          for (m = 0, len1 = ref1.length; m < len1; m++) {
            y = ref1[m];
            if (y != null) {
              results1.push(this.transY(y));
            } else {
              results1.push(y);
            }
          }
          return results1;
        }).call(this);
        results.push(row._ymax = Math.min.apply(Math, [this.bottom].concat((function() {
          var len1, m, ref1, results1;
          ref1 = row._y;
          results1 = [];
          for (m = 0, len1 = ref1.length; m < len1; m++) {
            y = ref1[m];
            if (y != null) {
              results1.push(y);
            }
          }
          return results1;
        })())));
      }
      return results;
    };

    Line.prototype.hitTest = function(x) {
      var index, k, len, r, ref;
      if (this.data.length === 0) {
        return null;
      }
      ref = this.data.slice(1);
      for (index = k = 0, len = ref.length; k < len; index = ++k) {
        r = ref[index];
        if (x < (r._x + this.data[index]._x) / 2) {
          break;
        }
      }
      return index;
    };

    Line.prototype.onGridClick = function(x, y) {
      var index;
      index = this.hitTest(x);
      return this.fire('click', index, this.data[index].src, x, y);
    };

    Line.prototype.onHoverMove = function(x, y) {
      var index;
      index = this.hitTest(x);
      return this.displayHoverForRow(index);
    };

    Line.prototype.onHoverOut = function() {
      if (this.options.hideHover !== false) {
        return this.displayHoverForRow(null);
      }
    };

    Line.prototype.displayHoverForRow = function(index) {
      var ref;
      if (index != null) {
        (ref = this.hover).update.apply(ref, this.hoverContentForRow(index));
        return this.hilight(index);
      } else {
        this.hover.hide();
        return this.hilight();
      }
    };

    Line.prototype.hoverContentForRow = function(index) {
      var content, j, k, len, ref, row, y;
      row = this.data[index];
      content = "<div class='morris-hover-row-label'>" + row.label + "</div>";
      ref = row.y;
      for (j = k = 0, len = ref.length; k < len; j = ++k) {
        y = ref[j];
        content += "<div class='morris-hover-point' style='color: " + (this.colorFor(row, j, 'label')) + "'>\n  " + this.options.labels[j] + ":\n  " + (this.yLabelFormat(y)) + "\n</div>";
      }
      if (typeof this.options.hoverCallback === 'function') {
        content = this.options.hoverCallback(index, this.options, content, row.src);
      }
      return [content, row._x, row._ymax];
    };

    Line.prototype.generatePaths = function() {
      var coords, i, r, smooth;
      return this.paths = (function() {
        var k, ref, ref1, results;
        results = [];
        for (i = k = 0, ref = this.options.ykeys.length; 0 <= ref ? k < ref : k > ref; i = 0 <= ref ? ++k : --k) {
          smooth = typeof this.options.smooth === "boolean" ? this.options.smooth : (ref1 = this.options.ykeys[i], indexOf.call(this.options.smooth, ref1) >= 0);
          coords = (function() {
            var len, m, ref2, results1;
            ref2 = this.data;
            results1 = [];
            for (m = 0, len = ref2.length; m < len; m++) {
              r = ref2[m];
              if (r._y[i] !== void 0) {
                results1.push({
                  x: r._x,
                  y: r._y[i]
                });
              }
            }
            return results1;
          }).call(this);
          if (coords.length > 1) {
            results.push(Morris.Line.createPath(coords, smooth, this.bottom));
          } else {
            results.push(null);
          }
        }
        return results;
      }).call(this);
    };

    Line.prototype.draw = function() {
      var ref;
      if ((ref = this.options.axes) === true || ref === 'both' || ref === 'x') {
        this.drawXAxis();
      }
      this.drawSeries();
      if (this.options.hideHover === false) {
        return this.displayHoverForRow(this.data.length - 1);
      }
    };

    Line.prototype.drawXAxis = function() {
      var drawLabel, k, l, labels, len, prevAngleMargin, prevLabelMargin, results, row, ypos;
      ypos = this.bottom + this.options.padding / 2;
      prevLabelMargin = null;
      prevAngleMargin = null;
      drawLabel = (function(_this) {
        return function(labelText, xpos) {
          var label, labelBox, margin, offset, textBox;
          label = _this.drawXAxisLabel(_this.transX(xpos), ypos, labelText);
          textBox = label.getBBox();
          label.transform("r" + (-_this.options.xLabelAngle));
          labelBox = label.getBBox();
          label.transform("t0," + (labelBox.height / 2) + "...");
          if (_this.options.xLabelAngle !== 0) {
            offset = -0.5 * textBox.width * Math.cos(_this.options.xLabelAngle * Math.PI / 180.0);
            label.transform("t" + offset + ",0...");
          }
          labelBox = label.getBBox();
          if (((prevLabelMargin == null) || prevLabelMargin >= labelBox.x + labelBox.width || (prevAngleMargin != null) && prevAngleMargin >= labelBox.x) && labelBox.x >= 0 && (labelBox.x + labelBox.width) < _this.el.width()) {
            if (_this.options.xLabelAngle !== 0) {
              margin = 1.25 * _this.options.gridTextSize / Math.sin(_this.options.xLabelAngle * Math.PI / 180.0);
              prevAngleMargin = labelBox.x - margin;
            }
            return prevLabelMargin = labelBox.x - _this.options.xLabelMargin;
          } else {
            return label.remove();
          }
        };
      })(this);
      if (this.options.parseTime) {
        if (this.data.length === 1 && this.options.xLabels === 'auto') {
          labels = [[this.data[0].label, this.data[0].x]];
        } else {
          labels = Morris.labelSeries(this.xmin, this.xmax, this.width, this.options.xLabels, this.options.xLabelFormat);
        }
      } else {
        labels = (function() {
          var k, len, ref, results;
          ref = this.data;
          results = [];
          for (k = 0, len = ref.length; k < len; k++) {
            row = ref[k];
            results.push([row.label, row.x]);
          }
          return results;
        }).call(this);
      }
      labels.reverse();
      results = [];
      for (k = 0, len = labels.length; k < len; k++) {
        l = labels[k];
        results.push(drawLabel(l[0], l[1]));
      }
      return results;
    };

    Line.prototype.drawSeries = function() {
      var i, k, m, ref, ref1, results;
      this.seriesPoints = [];
      for (i = k = ref = this.options.ykeys.length - 1; ref <= 0 ? k <= 0 : k >= 0; i = ref <= 0 ? ++k : --k) {
        this._drawLineFor(i);
      }
      results = [];
      for (i = m = ref1 = this.options.ykeys.length - 1; ref1 <= 0 ? m <= 0 : m >= 0; i = ref1 <= 0 ? ++m : --m) {
        results.push(this._drawPointFor(i));
      }
      return results;
    };

    Line.prototype._drawPointFor = function(index) {
      var circle, k, len, ref, results, row;
      this.seriesPoints[index] = [];
      ref = this.data;
      results = [];
      for (k = 0, len = ref.length; k < len; k++) {
        row = ref[k];
        circle = null;
        if (row._y[index] != null) {
          circle = this.drawLinePoint(row._x, row._y[index], this.colorFor(row, index, 'point'), index);
        }
        results.push(this.seriesPoints[index].push(circle));
      }
      return results;
    };

    Line.prototype._drawLineFor = function(index) {
      var path;
      path = this.paths[index];
      if (path !== null) {
        return this.drawLinePath(path, this.colorFor(null, index, 'line'), index);
      }
    };

    Line.createPath = function(coords, smooth, bottom) {
      var coord, g, grads, i, ix, k, len, lg, path, prevCoord, x1, x2, y1, y2;
      path = "";
      if (smooth) {
        grads = Morris.Line.gradients(coords);
      }
      prevCoord = {
        y: null
      };
      for (i = k = 0, len = coords.length; k < len; i = ++k) {
        coord = coords[i];
        if (coord.y != null) {
          if (prevCoord.y != null) {
            if (smooth) {
              g = grads[i];
              lg = grads[i - 1];
              ix = (coord.x - prevCoord.x) / 4;
              x1 = prevCoord.x + ix;
              y1 = Math.min(bottom, prevCoord.y + ix * lg);
              x2 = coord.x - ix;
              y2 = Math.min(bottom, coord.y - ix * g);
              path += "C" + x1 + "," + y1 + "," + x2 + "," + y2 + "," + coord.x + "," + coord.y;
            } else {
              path += "L" + coord.x + "," + coord.y;
            }
          } else {
            if (!smooth || (grads[i] != null)) {
              path += "M" + coord.x + "," + coord.y;
            }
          }
        }
        prevCoord = coord;
      }
      return path;
    };

    Line.gradients = function(coords) {
      var coord, grad, i, k, len, nextCoord, prevCoord, results;
      grad = function(a, b) {
        return (a.y - b.y) / (a.x - b.x);
      };
      results = [];
      for (i = k = 0, len = coords.length; k < len; i = ++k) {
        coord = coords[i];
        if (coord.y != null) {
          nextCoord = coords[i + 1] || {
            y: null
          };
          prevCoord = coords[i - 1] || {
            y: null
          };
          if ((prevCoord.y != null) && (nextCoord.y != null)) {
            results.push(grad(prevCoord, nextCoord));
          } else if (prevCoord.y != null) {
            results.push(grad(prevCoord, coord));
          } else if (nextCoord.y != null) {
            results.push(grad(coord, nextCoord));
          } else {
            results.push(null);
          }
        } else {
          results.push(null);
        }
      }
      return results;
    };

    Line.prototype.hilight = function(index) {
      var i, k, m, ref, ref1;
      if (this.prevHilight !== null && this.prevHilight !== index) {
        for (i = k = 0, ref = this.seriesPoints.length - 1; 0 <= ref ? k <= ref : k >= ref; i = 0 <= ref ? ++k : --k) {
          if (this.seriesPoints[i][this.prevHilight]) {
            this.seriesPoints[i][this.prevHilight].animate(this.pointShrinkSeries(i));
          }
        }
      }
      if (index !== null && this.prevHilight !== index) {
        for (i = m = 0, ref1 = this.seriesPoints.length - 1; 0 <= ref1 ? m <= ref1 : m >= ref1; i = 0 <= ref1 ? ++m : --m) {
          if (this.seriesPoints[i][index]) {
            this.seriesPoints[i][index].animate(this.pointGrowSeries(i));
          }
        }
      }
      return this.prevHilight = index;
    };

    Line.prototype.colorFor = function(row, sidx, type) {
      if (typeof this.options.lineColors === 'function') {
        return this.options.lineColors.call(this, row, sidx, type);
      } else if (type === 'point') {
        return this.options.pointFillColors[sidx % this.options.pointFillColors.length] || this.options.lineColors[sidx % this.options.lineColors.length];
      } else {
        return this.options.lineColors[sidx % this.options.lineColors.length];
      }
    };

    Line.prototype.drawXAxisLabel = function(xPos, yPos, text) {
      return this.raphael.text(xPos, yPos, text).attr('font-size', this.options.gridTextSize).attr('font-family', this.options.gridTextFamily).attr('font-weight', this.options.gridTextWeight).attr('fill', this.options.gridTextColor);
    };

    Line.prototype.drawLinePath = function(path, lineColor, lineIndex) {
      return this.raphael.path(path).attr('stroke', lineColor).attr('stroke-width', this.lineWidthForSeries(lineIndex));
    };

    Line.prototype.drawLinePoint = function(xPos, yPos, pointColor, lineIndex) {
      return this.raphael.circle(xPos, yPos, this.pointSizeForSeries(lineIndex)).attr('fill', pointColor).attr('stroke-width', this.pointStrokeWidthForSeries(lineIndex)).attr('stroke', this.pointStrokeColorForSeries(lineIndex));
    };

    Line.prototype.pointStrokeWidthForSeries = function(index) {
      return this.options.pointStrokeWidths[index % this.options.pointStrokeWidths.length];
    };

    Line.prototype.pointStrokeColorForSeries = function(index) {
      return this.options.pointStrokeColors[index % this.options.pointStrokeColors.length];
    };

    Line.prototype.lineWidthForSeries = function(index) {
      if (this.options.lineWidth instanceof Array) {
        return this.options.lineWidth[index % this.options.lineWidth.length];
      } else {
        return this.options.lineWidth;
      }
    };

    Line.prototype.pointSizeForSeries = function(index) {
      if (this.options.pointSize instanceof Array) {
        return this.options.pointSize[index % this.options.pointSize.length];
      } else {
        return this.options.pointSize;
      }
    };

    Line.prototype.pointGrowSeries = function(index) {
      return Raphael.animation({
        r: this.pointSizeForSeries(index) + 3
      }, 25, 'linear');
    };

    Line.prototype.pointShrinkSeries = function(index) {
      return Raphael.animation({
        r: this.pointSizeForSeries(index)
      }, 25, 'linear');
    };

    return Line;

  })(Morris.Grid);

  Morris.labelSeries = function(dmin, dmax, pxwidth, specName, xLabelFormat) {
    var d, d0, ddensity, k, len, name, ref, ret, s, spec, t;
    ddensity = 200 * (dmax - dmin) / pxwidth;
    d0 = new Date(dmin);
    spec = Morris.LABEL_SPECS[specName];
    if (spec === void 0) {
      ref = Morris.AUTO_LABEL_ORDER;
      for (k = 0, len = ref.length; k < len; k++) {
        name = ref[k];
        s = Morris.LABEL_SPECS[name];
        if (ddensity >= s.span) {
          spec = s;
          break;
        }
      }
    }
    if (spec === void 0) {
      spec = Morris.LABEL_SPECS["second"];
    }
    if (xLabelFormat) {
      spec = $.extend({}, spec, {
        fmt: xLabelFormat
      });
    }
    d = spec.start(d0);
    ret = [];
    while ((t = d.getTime()) <= dmax) {
      if (t >= dmin) {
        ret.push([spec.fmt(d), t]);
      }
      spec.incr(d);
    }
    return ret;
  };

  minutesSpecHelper = function(interval) {
    return {
      span: interval * 60 * 1000,
      start: function(d) {
        return new Date(d.getFullYear(), d.getMonth(), d.getDate(), d.getHours());
      },
      fmt: function(d) {
        return (Morris.pad2(d.getHours())) + ":" + (Morris.pad2(d.getMinutes()));
      },
      incr: function(d) {
        return d.setUTCMinutes(d.getUTCMinutes() + interval);
      }
    };
  };

  secondsSpecHelper = function(interval) {
    return {
      span: interval * 1000,
      start: function(d) {
        return new Date(d.getFullYear(), d.getMonth(), d.getDate(), d.getHours(), d.getMinutes());
      },
      fmt: function(d) {
        return (Morris.pad2(d.getHours())) + ":" + (Morris.pad2(d.getMinutes())) + ":" + (Morris.pad2(d.getSeconds()));
      },
      incr: function(d) {
        return d.setUTCSeconds(d.getUTCSeconds() + interval);
      }
    };
  };

  Morris.LABEL_SPECS = {
    "decade": {
      span: 172800000000,
      start: function(d) {
        return new Date(d.getFullYear() - d.getFullYear() % 10, 0, 1);
      },
      fmt: function(d) {
        return "" + (d.getFullYear());
      },
      incr: function(d) {
        return d.setFullYear(d.getFullYear() + 10);
      }
    },
    "year": {
      span: 17280000000,
      start: function(d) {
        return new Date(d.getFullYear(), 0, 1);
      },
      fmt: function(d) {
        return "" + (d.getFullYear());
      },
      incr: function(d) {
        return d.setFullYear(d.getFullYear() + 1);
      }
    },
    "month": {
      span: 2419200000,
      start: function(d) {
        return new Date(d.getFullYear(), d.getMonth(), 1);
      },
      fmt: function(d) {
        return (d.getFullYear()) + "-" + (Morris.pad2(d.getMonth() + 1));
      },
      incr: function(d) {
        return d.setMonth(d.getMonth() + 1);
      }
    },
    "week": {
      span: 604800000,
      start: function(d) {
        return new Date(d.getFullYear(), d.getMonth(), d.getDate());
      },
      fmt: function(d) {
        return (d.getFullYear()) + "-" + (Morris.pad2(d.getMonth() + 1)) + "-" + (Morris.pad2(d.getDate()));
      },
      incr: function(d) {
        return d.setDate(d.getDate() + 7);
      }
    },
    "day": {
      span: 86400000,
      start: function(d) {
        return new Date(d.getFullYear(), d.getMonth(), d.getDate());
      },
      fmt: function(d) {
        return (d.getFullYear()) + "-" + (Morris.pad2(d.getMonth() + 1)) + "-" + (Morris.pad2(d.getDate()));
      },
      incr: function(d) {
        return d.setDate(d.getDate() + 1);
      }
    },
    "hour": minutesSpecHelper(60),
    "30min": minutesSpecHelper(30),
    "15min": minutesSpecHelper(15),
    "10min": minutesSpecHelper(10),
    "5min": minutesSpecHelper(5),
    "minute": minutesSpecHelper(1),
    "30sec": secondsSpecHelper(30),
    "15sec": secondsSpecHelper(15),
    "10sec": secondsSpecHelper(10),
    "5sec": secondsSpecHelper(5),
    "second": secondsSpecHelper(1)
  };

  Morris.AUTO_LABEL_ORDER = ["decade", "year", "month", "week", "day", "hour", "30min", "15min", "10min", "5min", "minute", "30sec", "15sec", "10sec", "5sec", "second"];

}).call(this);
