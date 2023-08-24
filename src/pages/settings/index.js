const { render } = require("react-dom");
import Settings from "./Settings";

import "./style.scss";

document.addEventListener("DOMContentLoaded", function () {
  const dom = document.getElementById(`countPostReactSettings`);

  if (dom) {
    render(<Settings />, dom);
  }
});
