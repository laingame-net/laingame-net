const path = require('path');
const VueLoaderPlugin = require('vue-loader/lib/plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const {CleanWebpackPlugin} = require('clean-webpack-plugin');
const FixStyleOnlyEntriesPlugin = require('webpack-fix-style-only-entries');

module.exports = (env, options) => {
    const devMode = options.mode === 'development';

    return {
        mode: devMode ? 'development' : 'production',
        devtool: devMode ? 'eval' : false,

        entry: {
            main: './src',
            style: './src/index.scss',
        },

        output: {
            path: path.resolve(__dirname, 'dist/'),
            publicPath: '/build/',
            filename: '[name].js',
            library: '[name]'
        },

        module: {
            rules: [
                // ... другие правила
                {
                    test: /\.vue$/,
                    loader: 'vue-loader'
                },
                {
                    test: /\.js$/,
                    loader: 'babel-loader',
                    exclude: file => (
                        /node_modules/.test(file) &&
                        !/\.vue\.js/.test(file)
                    )
                },
                {
                    test: /\.css$/,
                    use: [
                        MiniCssExtractPlugin.loader,
                        // process.env.NODE_ENV !== 'production' ? 'vue-style-loader' : ,
                        'css-loader'
                    ]
                },
                {
                    test: /\.scss$/,
                    use: [
                        MiniCssExtractPlugin.loader,
                        'css-loader',
                        'sass-loader'
                    ]
                },
                {
                    test: /\.(png|svg|jpg|gif|ttf|woff|woff2|eot|otf)$/,
                    use: [
                        'file-loader?name=' + (devMode ? '[path][name].[ext]' : '[contenthash].[ext]')
                    ]
                }
            ]
        },

        resolve: {
            extensions: [
                '*', '.js', '.vue', '.json', '.css', '.scss'
            ],
            alias: {
                // config: path.resolve(__dirname, './conf/app.' + envConf['dev-api'] + '.js')
            }
        },

        plugins: [
            // убедитесь что подключили плагин!
            new VueLoaderPlugin(),
            new MiniCssExtractPlugin({
                filename: 'style.css'
            }),
            new CleanWebpackPlugin(),
            new FixStyleOnlyEntriesPlugin(),
            new OptimizeCSSAssetsPlugin({
                cssProcessor: require('cssnano'),
                cssProcessorPluginOptions: {
                    preset: ['default', {
                        discardComments: { removeAll: true }
                    }]
                },
                canPrint: false
            })
        ]
    }
};
