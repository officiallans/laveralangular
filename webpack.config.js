require('dotenv').config();
var production = process.env.NODE_ENV === 'production';
var webpack = require('webpack');
var path = require('path');
var fs = require("fs");

var plugins = [
    new webpack.ProvidePlugin({
        _: "lodash",
        moment: 'moment',
        Promise: 'bluebird'
    }),
    new webpack.DefinePlugin({
        'process.env': {
            'NODE_ENV': '"' + process.env.NODE_ENV + '"'
        }
    }),
    new webpack.NoEmitOnErrorsPlugin(),
    new webpack.ContextReplacementPlugin(/moment[\\\/]locale$/, /^\.\/(en-gb|uk|ru)$/),
    function () {
        this.plugin("done", function (stats) {
            var data = {};
            data.hash = stats.hash;
            require("fs").writeFileSync(
                path.join(__dirname, "/public/", "stats.json"),
                JSON.stringify(data));
        });
    }
];
var externals = {};

if (production) {
    plugins = plugins.concat([
        new webpack.optimize.OccurrenceOrderPlugin(),

        new webpack.optimize.MinChunkSizePlugin({
            minChunkSize: 10000
        }),

        new webpack.optimize.UglifyJsPlugin({
            mangle: true,
            compress: {
                warnings: false
            }
        })
    ]);

    externals.angular = 'angular';
}

module.exports = {
    entry: {
        main: path.join(__dirname, "/resources/assets/app.js")
    },
    devtool: production ? false : 'eval',
    output: {
        path: path.join(__dirname, "/public/app/"),
        chunkFilename: '[name].[chunkhash].js',
        filename: '[name].[hash].js',
        publicPath: 'app/'
    },
    externals: externals,
    module: {
        rules: [
            {
                test: /\.(jpe?g|png|gif|svg)$/i,
                use: [
                    {
                        loader: "file-loader",
                        options: {
                            "hash": "sha512",
                            "digest": "hex",
                            "name": "[hash].[ext]"
                        }
                    },
                    {
                        loader: "image-webpack-loader",
                        options: {
                            "bypassOnDebug": true,
                            "optimizationLevel": 7,
                            "interlaced": false
                        }
                    }
                ]
            },
            {
                test: /\.css$/,
                include: [path.join(__dirname, "node_modules")],
                use: [
                    'style-loader',
                    'css-loader'
                ]
            },
            {
                test: /\.css$/,
                exclude: [path.join(__dirname, "node_modules")],
                use: [
                    'style-loader',
                    {
                        loader: "css-loader",
                        options: {
                            "importLoaders": 1,
                        }
                    }
                ]
            },
            {
                test: /\.pcss$/,
                use: [
                    'style-loader',
                    {
                        loader: "css-loader",
                        options: {
                            "importLoaders": 1,
                        }
                    },
                    'postcss-loader'
                ]
            },
            {
                test: /\.(ttf|svg|woff|woff2|eot)/,
                use: [
                    {
                        loader: "url-loader",
                        options: {
                            "limit": 102400,
                            "name": "[name].[ext]?[hash]",
                        }
                    }
                ]
            },
            {
                test: /\.html/,
                use: [
                    'html-loader'
                ]
            },
            {
                test: /\.js$/,
                exclude: [path.join(__dirname, "node_modules")],
                use: [
                    {
                        loader: 'babel-loader',
                        options: {
                            presets: ['es2015']
                        }
                    }
                ]
            }
        ]
    },
    plugins: plugins,
    resolve: {
        modules: [path.join(__dirname, "node_modules")]
    },
    devServer: {
        contentBase: path.join(__dirname, "public"),
        port: 8080,
        publicPath: '/app/',
        proxy: [
            {
                path: '/api',
                target: 'http://form.box/'
            }, {
                path: '/storage',
                target: 'http://form.box/'
            },
            {
                path: '/',
                target: 'http://form.box/'
            }
        ]
    }
};
