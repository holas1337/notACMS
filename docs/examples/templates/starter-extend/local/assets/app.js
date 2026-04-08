// local/assets/app.js
//
// "Extend styles" boilerplate — adds CSS overrides on top of the original.
//
// local.scss imports only the original SCSS variables (not the full rules),
// so it can use $color-primary etc. The original CSS rules are loaded
// separately by the 'app' importmap entrypoint — no double-loading.

import "./styles/local.scss";
// import "./my-feature.js";  // uncomment to add your own JavaScript
