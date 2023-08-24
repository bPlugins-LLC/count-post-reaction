import React from "react";
import { useWPOptionQuery } from "../../../../wp-utils";
import { FormTokenField } from "@wordpress/components";
import { __ } from "@wordpress/i18n";

const PostTypeCheckbox = ({ postTypes = [], handleChange }) => {
  const { data: allPostTypes = [], isLoading } = useWPOptionQuery("cprPostTypes");

  if (isLoading) {
    return "Loading...";
  }

  return (
    <div className="CPRPostCheckbox">
      <label>{__("Post Types", "post-reactions-counter")}</label>
      <div>
        <FormTokenField label="Post Types" value={postTypes} suggestions={allPostTypes} onChange={(postTypes) => handleChange({ postTypes })} />
      </div>
      <span>{__("dfg", "bpm")}</span>
    </div>
  );
};

export default PostTypeCheckbox;
