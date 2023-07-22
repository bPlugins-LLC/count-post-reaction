const { render } = require("react-dom");
import Settings from "./Components/Settings";

document.addEventListener("DOMContentLoaded", function () {
  const dom = document.getElementById(`postReactCountSettings`);

  if (dom) {
    render(<Settings />, dom);
  }
});
