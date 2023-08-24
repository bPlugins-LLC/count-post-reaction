import { CheckboxControl } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import React, { useState } from "react";

const ReactTypes = ({ enabledReacts = [], handleChange }) => {
  const [reactTypes] = useState(["like", "love", "wow", "angry"]);

  return (
    <div className="CPRPostCheckbox">
      <label>{__("Enable Reacts", "count-post-react")}: </label>
      <div className="cprReacts">
        {reactTypes?.map((react) => {
          return <CheckboxControl key={react} label={react} checked={enabledReacts?.includes(react)} onChange={(checked) => (checked ? handleChange({ enabledReacts: [...enabledReacts, react] }) : handleChange({ enabledReacts: enabledReacts.filter((item) => item != react) }))} />;
        })}
      </div>
    </div>
  );
};

export default ReactTypes;
