import { Button, FormToggle, PanelBody, PanelRow, TextControl, TextareaControl } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import React from "react";

// import SimpleLoader from "./../../../../wp-utils/components/Loader/SimpleLoader";

const CustomReact = ({ customReacts, handleChange }) => {
  const handleCustomReactChange = (index, key, value) => {
    const newCustomReacts = [...customReacts];
    newCustomReacts[index][key] = value;

    console.log(newCustomReacts);
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

  return (
    <div>
      <label>{__("Custom React", "count-post-react")}</label>
      {customReacts.map((item, index) => (
        <>
          <PanelBody title={item.name || __("Custom React", "count-post-react")} initialOpen={false}>
            <PanelRow>
              <label>{__("Enabled", "count-post-react")}</label>
              <FormToggle checked={item.enabled} onChange={() => handleCustomReactChange(index, "enabled", !item.enabled)} />
            </PanelRow>
            <TextControl label={__("React Name", "count-post-react")} value={item?.name} onChange={(name) => handleCustomReactChange(index, "name", name)} />
            <TextControl label={__("Unique React ID", "count-post-react")} value={item?.id} onChange={(id) => handleCustomReactChange(index, "id", id)} />
            <TextareaControl label={__("SVG Icon", "count-post-react")} value={decodeIcon(item?.svg)} onChange={(svg) => handleCustomReactChange(index, "svg", svg)} />
          </PanelBody>
        </>
      ))}
      <div className="cprActionButton">
        <Button variant="primary" onClick={handleAddNewReact}>
          {__("Add New React", "count-post-react")}
        </Button>
      </div>
    </div>
  );
};

export default CustomReact;
