const { createRoot } = require("react-dom/client");
import Settings from "./Settings";

import "./style.scss";

document.addEventListener("DOMContentLoaded", function () {
  const dom = document.getElementById(`countPostReactSettings`);
  const root = createRoot(dom);

  root.render(<Settings />);
});
