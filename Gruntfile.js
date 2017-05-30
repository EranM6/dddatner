module.exports = function(grunt) {

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        concat: {
            options: {
                separator: ';'
            },
            dist: {
                src: ['./public/js/**/*.js'],
                dest: './public/minified.js/<%= pkg.name %>.js'
            }
        },
        uglify: {
            options: {
                banner: '/*! <%= pkg.name %> <%= grunt.template.today("dd-mm-yyyy") %> */\n'
            },
            dist: {
                files: {
                    './public/minified.js/<%= pkg.name %>.min.js': ['<%= concat.dist.dest %>']
                }
            }
        },
        qunit: {
            files: ['test/**/*.html']
        },
        jshint: {
            files: ['Gruntfile.js', './public/js/**/*.js'],
            options: {
                // options here to override JSHint defaults
                reporter:'checkstyle' ,
                reporterOutput: 'jshint.xml' ,
                globals: {
                    angular: true,
                    jQuery: true,
                    console: true,
                    module: true,
                    document: true
                }
            }
        },
        'ftp-deploy': {
            build: {
                auth: {
                    host: 'ftp.byethost8.com',
                    port: 21,
                    authKey: 'byethost8'
                },
                src: './public/minified.js/',
                dest: '/htdocs/dddatner/public/minified.js/',
                exclusions: ['./public/minified.js/dddatner.min.js']
            }
        },
        watch: {
            local: {
                files: ['<%= jshint.files %>', '!Gruntfile.js'],
                tasks:['jshint', 'qunit', 'concat', 'uglify']
            },
            remote: {
                files: ['<%= jshint.files %>', '!Gruntfile.js'],
                tasks:['jshint', 'qunit', 'concat', 'uglify', 'ftp-deploy']
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-qunit');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-ftp-deploy');

    grunt.registerTask('prod', ['watch:remote']);
    grunt.registerTask('default', ['watch:local']);

};
