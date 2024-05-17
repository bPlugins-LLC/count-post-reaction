import { Button, FormToggle, PanelBody, PanelRow, TextControl, TextareaControl } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import React, { useEffect } from "react";

// import SimpleLoader from "./../../../../wp-utils/components/Loader/SimpleLoader";

const CustomReact = ({ customReacts, handleChange }) => {
  const handleCustomReactChange = (index, key, value) => {
    const newCustomReacts = [...customReacts];
    newCustomReacts[index][key] = value;
    handleChange({ customReacts: newCustomReacts });
  };

  const handleAddNewReact = () => {
    const newCustomReacts = [...customReacts];
    newCustomReacts.push({ enabled: true, name: "new react" });
    handleChange({ customReacts: newCustomReacts });
  };

  function decodeIcon(string) {
    try {
      // Attempt to decode the string
      const decodedString = atob(string);
      // Encode the decoded string back to Base64
      const reencodedString = btoa(decodedString);
      // If the reencoded string matches the original, then it's Base64 encoded
      if (string === reencodedString) {
        return decodedString;
      }
      return string;
    } catch (error) {
      return string;
    }
  }

  useEffect(() => {
    customReacts.map((item, index) => {
      if (item.svg?.length > 30 && !item.svg?.includes(".")) handleCustomReactChange(index, "svg", decodeIcon(item["svg"]));
    });
  }, []);

  return (
    <div>
      <label>{__("Custom React", "post-reaction")}</label>
      {customReacts.map((item, index) => (
        <>
          <PanelBody title={item.name || __("Custom React", "post-reaction")} initialOpen={false}>
            <PanelRow>
              <label>{__("Enabled", "post-reaction")}</label>
              <FormToggle checked={item.enabled} onChange={() => handleCustomReactChange(index, "enabled", !item.enabled)} />
            </PanelRow>
            <TextControl label={__("React Name", "post-reaction")} value={item?.name} onChange={(name) => handleCustomReactChange(index, "name", name)} />
            <TextControl label={__("Unique React ID", "post-reaction")} value={item?.id} onChange={(id) => handleCustomReactChange(index, "id", id)} />
            <TextareaControl label={__("SVG Icon", "post-reaction")} value={item?.svg} onChange={(svg) => handleCustomReactChange(index, "svg", svg)} />
            <div className="cprActionButton">
              {" "}
              <Button
                variant="danger"
                className="button button-danger"
                onClick={() => {
                  const newCustomReacts = [...customReacts];
                  newCustomReacts.splice(index, 1);
                  handleChange({ customReacts: newCustomReacts });
                }}
              >
                {__("Delete", "post-reaction")}
              </Button>
            </div>
          </PanelBody>
        </>
      ))}
      <div className="cprActionButton">
        <Button variant="primary" onClick={handleAddNewReact}>
          {__("Add New React", "post-reaction")}
        </Button>
      </div>
    </div>
  );
};

export default CustomReact;
