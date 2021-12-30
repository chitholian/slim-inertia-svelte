const path = require('path');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const mp = require('webpack-manifest-plugin')

module.exports = {
    mode: 'development',
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
                use: ['svelte-loader']
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
