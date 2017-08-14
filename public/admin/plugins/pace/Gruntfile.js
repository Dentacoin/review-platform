(function() {
  var Path, ThemeUtils, fs, themeColors;

  Path = require('path');

  fs = require('fs');

  ThemeUtils = require('./docs/lib/themes.coffee');

  themeColors = {
    black: '#000000',
    white: '#ffffff',
    silver: '#d6d6d6',
    red: '#ee3148',
    orange: '#eb7a55',
    yellow: '#fcd25a',
    green: '#22df80',
    blue: '#2299dd',
    pink: '#e90f92',
    purple: '#7c60e0'
  };

  module.exports = function(grunt) {
    grunt.registerTask('themes', 'Compile the pace theme files', function() {
      var done, options;
      done = this.async();
      options = grunt.config('themes');
      return grunt.file.glob(options.src, function(err, files) {
        var body, color, colorName, file, i, len, name, path;
        for (colorName in themeColors) {
          color = themeColors[colorName];
          for (i = 0, len = files.length; i < len; i++) {
            file = files[i];
            body = ThemeUtils.compileTheme(fs.readFileSync(file).toString(), {
              color: color
            });
            body = "/* This is a compiled file, you should be editing the file in the templates directory */\n" + body;
            name = Path.basename(file);
            name = name.replace('.tmpl', '');
            path = Path.join(options.dest, colorName, name);
            fs.writeFileSync(path, body);
          }
        }
        return done();
      });
    });
    grunt.initConfig({
      pkg: grunt.file.readJSON("package.json"),
      coffee: {
        compile: {
          files: {
            'pace.js': 'pace.coffee',
            'docs/lib/themes.js': 'docs/lib/themes.coffee'
          }
        }
      },
      watch: {
        coffee: {
          files: ['pace.coffee', 'docs/lib/themes.coffee', 'templates/*'],
          tasks: ["coffee", "uglify", "themes"]
        }
      },
      uglify: {
        options: {
          banner: "/*! <%= pkg.name %> <%= pkg.version %> */\n"
        },
        dist: {
          src: 'pace.js',
          dest: 'pace.min.js'
        }
      },
      themes: {
        src: 'templates/*.tmpl.css',
        dest: 'themes'
      }
    });
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-coffee');
    return grunt.registerTask('default', ['coffee', 'uglify', 'themes']);
  };

}).call(this);
