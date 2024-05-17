import { SelectControl, TextareaControl, FormToggle, PanelRow, Button, FormTokenField, __experimentalUnitControl as UnitControl } from "@wordpress/components";
import { useState, useEffect } from "react";
import { __ } from "@wordpress/i18n";

import { useWPOptionMutation, useWPOptionQuery } from "../../../../wp-utils";
import { BColor } from "../../../../wp-utils/v1/components";
import ReactTypes from "./ReactTypes";
import CustomReact from "./CustomReact";
import SimpleLoader from "../../../../wp-utils/components/Loader/SimpleLoader";

import design1 from "./../../assets/design-1.png";
import design2 from "./../../assets/design-2.png";

const Settings = () => {
  const { isLoading: dataSaving, saveData } = useWPOptionMutation("cprSettings", { type: "object" });
  const { data = {}, isLoading } = useWPOptionQuery("cprSettings");
  const { data: allPostTypes = [], isLoading: postLoading } = useWPOptionQuery("cprPostTypes");
  const [settings, setSettings] = useState(data || {});
  const { enabled, contentPosition, postTypes, enabledReacts, customReacts = [], beforeContent, afterContent, onlyUserCanReact = true, iconSize = "20px", activeBackground, design } = settings;

  useEffect(() => {
    if (data) {
      console.log(data);
      setSettings(data);
    }
  }, [isLoading]);

  // console.log({ enabledReacts, data, isLoading, allPostTypes });
  const handleChange = (data) => {
    setSettings({ ...settings, ...data });
  };

  const handleSaveData = () => {
    const customReacts = [...settings.customReacts];
    settings.customReacts?.map((item, index) => {
      if (item.svg?.includes("svg")) {
        customReacts[index].svg = btoa(item.svg);
      }
    });

    saveData({ ...settings, customReacts });
  };

  if (isLoading || postLoading) {
    return "Loading...";
  }

  return (
    <div>
      <>
        <div className="fit-content">
          <PanelRow>
            <label>{__("Enabled", "post-reaction")}</label>
            <FormToggle checked={enabled} onChange={() => handleChange({ enabled: !enabled })} />
          </PanelRow>
        </div>
        {enabled && (
          <>
            <div className="fit-content">
              <PanelRow>
                <label>{__("Only User Can React", "post-reaction")}</label>
                <FormToggle checked={onlyUserCanReact} onChange={() => handleChange({ onlyUserCanReact: !onlyUserCanReact })} />
              </PanelRow>
            </div>
            <div className="fit-content">
              <PanelRow>
                <label>{__("React Content Position", "post-reaction")}</label>
                <SelectControl
                  options={[
                    { label: __("Before Content", "post-reaction"), value: "before_content" },
                    { label: __("After Content", "post-reaction"), value: "after_content" },
                  ]}
                  value={contentPosition}
                  onChange={(contentPosition) => handleChange({ contentPosition })}
                />
              </PanelRow>
            </div>

            <div className="fit-content">
              <FormTokenField
                label="Post Types"
                value={postTypes}
                suggestions={allPostTypes}
                onChange={(postTypes) => {
                  if (postTypes.every((item) => allPostTypes.includes(item))) {
                    handleChange({ postTypes });
                  }
                }}
              />
            </div>
            <div className="fit-content">
              <ReactTypes customReacts={customReacts} handleChange={handleChange} enabledReacts={enabledReacts} />
            </div>
            <div className="fit-content">
              <PanelRow>
                <label>{__("Icon Size", "post-reaction")}</label>
                <UnitControl onChange={(iconSize) => handleChange({ iconSize })} value={iconSize} />
              </PanelRow>
            </div>
            <div className="fit-content">
              <PanelRow>
                <label>{__("Active Icon Background", "post-reaction")}</label>
                <BColor onChange={(activeBackground) => handleChange({ activeBackground })} value={activeBackground} />
              </PanelRow>
            </div>
            {/* <div className="fit-content"> */}
            <CustomReact customReacts={customReacts} handleChange={handleChange} />
            {/* </div> */}
            <div className="fit-content">
              <label>{__("Design", "post-reaction")}</label>
              <div className="designs">
                <img onClick={() => handleChange({ design: "design-1" })} src={design1} className={design === "design-1" ? "active" : ""} />
                <img onClick={() => handleChange({ design: "design-2" })} src={design2} className={design === "design-2" ? "active" : ""} />
              </div>
            </div>

            <div>
              <label>{__("Content Before React", "post-reaction")}</label>
              {/* <RichText tagName="p" allowedFormats={["core/bold", "core/italic"]} value={beforeContent} onChange={(beforeContent) => handleChange({ beforeContent })} /> */}
              <TextareaControl rows="2" value={beforeContent} onChange={(beforeContent) => handleChange({ beforeContent })} />
            </div>
            <div>
              <label>{__("Content After React", "post-reaction")}</label>
              <TextareaControl rows="2" value={afterContent} onChange={(afterContent) => handleChange({ afterContent })} />
            </div>
          </>
        )}
      </>

      {/* <div className="fit-content"> */}
      <div className="cprActionButton">
        {dataSaving && <SimpleLoader />}
        <Button disabled={dataSaving} variant="primary" onClick={handleSaveData}>
          {__("Save", "post-reaction")}
        </Button>
      </div>
      {/* </div> */}
    </div>
  );
};

export default Settings;
