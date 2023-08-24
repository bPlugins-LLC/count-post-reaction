const { render } = require("react-dom");
import Settings from "./pages/settings/Settings";

document.addEventListener("DOMContentLoaded", function () {
  const dom = document.getElementById(`countPostReactSettings`);

  if (dom) {
    render(<Settings />, dom);
  }
});
