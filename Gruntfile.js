/**
 * @project:   App Icon
 * 
 * @author     Fabian Bitter
 * @copyright  (C) 2016 Fabian Bitter (www.bitter.de)
 * @version    1.2.1
 */

var packageName = "app_icon";

module.exports = function (grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        version: {
            php: {
                options: {
                    pkg: {
                        version: function() {
                            var s = grunt.file.read('controller.php');
                            var re = /\$pkgVersion[\s*]=[\s*][\'|\"](.*)[\'|\"]/g              
                            var m = re.exec(s);

                            if (m.length) {
                                return m[1];
                            }

                            return false;
                        }()
                    },
                    prefix: '@version\\s*'
                },
                src: [
                    'dist/*.php', 'dist/**/*.php', 'dist/**/**/*.php', 'dist/**/**/**/*.php', 'dist/**/**/**/**/*.php'
                ]
            }
        },
        composer: {
            options: {
                usePhp: true,
                composerLocation: './node_modules/getcomposer/composer.phar'
            },
            dev: {
                options: {
                    flags: ['ignore-platform-reqs']
                }
            },
            release: {
                options: {
                    flags: ['no-dev']
                }
            }
        },
        copy: {
            main: {
                files: [
                    {src: ['controller.php'], dest: "dist/", filter: 'isFile'},
                    {src: ['icon.png'], dest: "dist/", filter: 'isFile'},
                    {src: ['controllers/**'], dest: "dist/"},
                    {src: ['views/**'], dest: "dist/"},
                    {src: ['elements/**'], dest: "dist/"},
                    {src: ['single_pages/**'], dest: "dist/"},
                    {src: ['src/**'], dest: "dist/"},
                    {src: ['languages/**'], dest: "dist/"},
                    {src: ['icon.png'], dest: "dist/"},
                    {src: ['CHANGELOG'], dest: "dist/"}
                ]
            }
        },
        compress: {
            main: {
                options: {
                    archive: 'build/' + packageName + '.zip'
                },
                files: [
                    {src: ['**'], dest: packageName, expand: true, cwd: 'dist/'}
                ]
            }
        },
        clean: {
            dist: ['dist'],
            composer: ['vendor', 'composer.lock']
        },
        phpcsfixer: {
            app: {
                dir: 'dist'
            },
            options: {
                bin: './vendor/friendsofphp/php-cs-fixer/php-cs-fixer',
                usingCache: "no",
                quiet: true
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-compress');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-composer');
    grunt.loadNpmTasks('grunt-php-cs-fixer');
    grunt.loadNpmTasks('grunt-version');
    grunt.loadNpmTasks('grunt-contrib-copy');

    grunt.registerTask('default', ['clean:composer', 'composer:release:install', 'clean:dist', 'copy', 'version', 'clean:composer', 'composer:dev:install', 'phpcsfixer', 'compress:main', 'clean:dist']);
};