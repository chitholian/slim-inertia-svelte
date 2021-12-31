const path = require('path');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const mp = require('webpack-manifest-plugin')

module.exports = (env, argv) => {
    return {
        mode: argv.mode || 'development',
        devServer: {
            port: 9000,
            proxy: {
                "!/dist/**/*": "http://localhost:8000"
            },
            devMiddleware: {
                writeToDisk: true,
            }
        },
        plugins: [new MiniCssExtractPlugin({
            filename: '[name].[contenthash].css',
        }), new mp.WebpackManifestPlugin({
            publicPath: 'dist/',
        })],
        entry: {
            app: ['./src/js/app.js', './src/scss/app.scss'],
        },
        output: {
            path: path.resolve(__dirname, 'public/dist'),
            clean: true,
            filename: '[name].[contenthash].js',
        },
        module: {
            rules: [
                {
                    test: /\.svelte$/i,
                    use: {
                        loader: 'svelte-loader',
                        options: {
                            compilerOptions: {
                                dev: argv.mode !== 'production',
                            }
                        },
                    },
                },
                {
                    test: /\.s[ac]ss$/i,
                    use: [
                        MiniCssExtractPlugin.loader,
                        'css-loader',
                        'sass-loader',
                    ]
                }
            ]
        },
        optimization: {
            moduleIds: 'deterministic',
            splitChunks: {
                cacheGroups: {
                    vendor: {
                        test: /[\\/]node_modules[\\/]/,
                        name: 'vendor',
                        chunks: 'all'
                    }
                }
            }
        }
    }
}
