/*global module:false*/
/*global require:false*/
/*jshint -W097*/
"use strict";

module.exports = function(grunt) {
 
    // load all grunt tasks
    require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks);
 
    grunt.initConfig({

        'pkg': grunt.file.readJSON('package.json'),
 
        // watch for changes and run sass
        'watch': {                        
            'php': {
                'files': [
                    'includes/**/*.php',
                    'templates/**/*.php'
                ],
                'tasks': ['copy']
            },
            'sass': {
                'files': [ 
                    'assets/css/',
                    'assets/css/**'
                ],
                'tasks': ['sass:dist']
            }, 
        },

        // Sass
        'sass': {
            'dist': {
                'files': {                    
                    'assets/css/charitable-admin-pages.css' : 'assets/css/scss/charitable-admin-pages.scss', 
                    'assets/css/charitable-admin-menu.css'  : 'assets/css/scss/charitable-admin-menu.scss', 
                    'assets/css/charitable-admin.css'       : 'assets/css/scss/charitable-admin.scss',
                    'assets/css/charitable-plupload-fields.css' : 'assets/css/scss/charitable-plupload-fields.scss',
                    'assets/css/charitable-datepicker.css'  : 'assets/css/scss/charitable-datepicker.scss',
                    'assets/css/charitable.css'             : 'assets/css/scss/charitable.scss',
                    'assets/css/modal.css'                  : 'assets/css/scss/modal.scss'
                }
            }
        },

        'checktextdomain' : {
            'options' : {
                'text_domain': 'charitable',
                'create_report_file': true,
                'keywords': [
                    '__:1,2d',
                    '_e:1,2d',
                    '_x:1,2c,3d',
                    'esc_html__:1,2d',
                    'esc_html_e:1,2d',
                    'esc_html_x:1,2c,3d',
                    'esc_attr__:1,2d',
                    'esc_attr_e:1,2d',
                    'esc_attr_x:1,2c,3d',
                    '_ex:1,2c,3d',
                    '_n:1,2,3,4d',
                    '_nx:1,2,4c,5d',
                    '_n_noop:1,2,3d',
                    '_nx_noop:1,2,3c,4d',
                    ' __ngettext:1,2,3d',
                    '__ngettext_noop:1,2,3d',
                    '_c:1,2d',
                    '_nc:1,2,4c,5d'
                ]
            },
            'files' : {
                'src' : [
                    '**/*.php', // Include all files
                    '!node_modules/**', // Exclude node_modules/
                    '!build/.*'// Exclude build/
                ],
                'expand' : true
            }
        },

        'makepot' : {
            'target' : {
                'options' : {
                    'domainPath' : '/i18n/languages/',    // Where to save the POT file.
                    'exclude' : ['build/.*'],
                    'mainFile' : 'charitable.php',    // Main project file.
                    'potFilename' : 'charitable.pot',    // Name of the POT file.
                    'potHeaders' : {
                        'poedit' : true,                 // Includes common Poedit headers.
                        'x-poedit-keywordslist': true // Include a list of all possible gettext functions.
                                },
                    'type' : 'wp-plugin',    // Type of project (wp-plugin or wp-theme).
                    'updateTimestamp' : true,    // Whether the POT-Creation-Date should be updated without other changes.
                    processPot: function( pot, options ) {
                        pot.headers['report-msgid-bugs-to'] = 'https://www.wpcharitable.com/';
                        pot.headers['last-translator'] = 'WP-Translations (http://wp-translations.org/)';
                        pot.headers['language-team'] = 'WP-Translations <wpt@wp-translations.org>';
                        pot.headers['language'] = 'en_US';
                        var translation, // Exclude meta data from pot.
                            excluded_meta = [
                                'Plugin Name of the plugin/theme',
                                'Plugin URI of the plugin/theme',
                                'Author of the plugin/theme',
                                'Author URI of the plugin/theme'
                            ];
                            
                            for ( translation in pot.translations[''] ) {
                                if ( 'undefined' !== typeof pot.translations[''][ translation ].comments.extracted ) {
                                    if ( excluded_meta.indexOf( pot.translations[''][ translation ].comments.extracted ) >= 0 ) {
                                        console.log( 'Excluded meta: ' + pot.translations[''][ translation ].comments.extracted );
                                            delete pot.translations[''][ translation ];
                                    }
                                }
                            }

                        return pot;
                    }
                }
            }
        },

        // javascript linting with jshint
        'jshint' : {
            'options' : {
                'jshintrc' : '.jshintrc',
                'force' : true
            },
            'all' : [
                'Gruntfile.js'
            ]
        },        

        // uglify to concat, minify, and make source maps
        'uglify' : {
            'options' : {
                'compress' : {
                    'global_defs' : {
                        "EO_SCRIPT_DEBUG": false
                    },
                    'dead_code' : true
                    },
                'banner' : '/*! <%= pkg.title %> <%= pkg.version %> <%= grunt.template.today("yyyy-mm-dd HH:MM") %> */\n'
            },
            'build' : {
                'files' : [{
                    'expand' : true,   // Enable dynamic expansion.
                    'src' : [ 
                        'assets/js/*.js',                         
                        '!assets/js/*.min.js', 
                        '!assets/js/libraries/*.js',
                        'assets/js/libraries/leanModal.js'
                    ], // Actual pattern(s) to match.
                    'ext' : '.min.js',   // Dest filepaths will have this extension.
                }]
            }
        },

        // minify CSS
        'cssmin' : {
            'target' : {
                'files' : [{
                    'expand' : true,
                    'cwd' : 'assets/css',
                    'dest' : 'assets/css',
                    'src' : ['*.css', '!*.min.css'],
                    'ext' : '.min.css'
                }]
            }
        },

        // Clean up build directory
        'clean' : {
            'main' : ['build/<%= pkg.name %>']
        },

        // Copy the theme into the build directory
        'copy' : {
            'main' : {
                'src' :  [
                    '**',
                    '!bin/**',
                    '!composer.json',
                    '!composer.lock', 
                    '!phpunit.xml',
                    '!phpcs.ruleset.xml',
                    '!phpunit.xml.dist',
                    '!node_modules/**',
                    '!build/**',
                    '!.git/**',
                    '!Gruntfile.js',
                    '!package.json',
                    '!.gitignore',
                    '!.tx/**',
                    '!tests/**',
                    '!**/Gruntfile.js',
                    '!**/package.json',
                    '!**/README.md',
                    '!**/*~', 
                    '!assets/css/scss/**'
                ],
                'dest' : 'build/<%= pkg.name %>/'
            }
        },

        //Compress build directory into <name>.zip and <name>-<version>.zip
        'compress' : {
            'main' : {
                'options' : {
                    'mode' : 'zip',
                    'archive' : './build/<%= pkg.name %>-<%= pkg.version %>.zip'
                },
                'expand' : true,
                'cwd' : 'build/<%= pkg.name %>/',
                'src' : ['**/*'],
                'dest' : '<%= pkg.name %>/'
            }
        },

    });


    // Default task. - grunt watch
    grunt.registerTask( 'default', 'watch' );
    
    // Build task(s).
    grunt.registerTask( 'build-scripts', [ 'uglify' ] );
    grunt.registerTask( 'build-styles', [ 'cssmin' ] );
    grunt.registerTask( 'build', [ 'uglify', 'cssmin', 'makepot', 'clean', 'copy', 'compress' ] );

    // grunt.registerTask('default', ['watch']);
    // grunt.registerTask('build', ['sync', 'jshint', 'uglify', 'makepot']);
};