const path = require("path");
const { VueLoaderPlugin } = require("vue-loader");

module.exports = {
  entry: "./src/main.js",
  output: {
    filename: "bundle.js",
    path: path.resolve(__dirname, "public"),
  },
  module: {
    rules: [
      {
        test: /\.vue$/,
        loader: "vue-loader"
      },
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: "babel-loader"
        }
      },
      {
        test: /\.css$/,
        use: ["style-loader", "css-loader"]
      }
    ]
  },
  resolve: {
    alias: {
      vue: "vue/dist/vue.esm-bundler.js"
    },
    extensions: [".js", ".vue", ".json"]
  },
  devServer: {
    static: './public',
    port: 5173,
    hot: true,
    host: '0.0.0.0',
    watchFiles: ['src/**/*'],
    allowedHosts: 'all',
  client: {
    webSocketURL: 'ws://localhost:5173/ws',
  }
  },
  plugins: [
    new VueLoaderPlugin()
  ]
};
