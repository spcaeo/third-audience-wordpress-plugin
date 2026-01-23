"use client";
import React, { useEffect } from "react";

declare global {
  interface Window {
    plugSDK: {
      init: (config: { app_id: string }) => void;
    };
  }
}

const Devrev = () => {
  useEffect(() => {
    if (window.plugSDK) {
      window.plugSDK.init({
        app_id:
          "DvRvStPZG9uOmNvcmU6ZHZydi11cy0xOmRldm8vMUVKeWFDTkdHRzpwbHVnX3NldHRpbmcvMV9ffHxfXzIwMjQtMTAtMjMgMDg6Mjg6MTIuMDM5MTExNDM3ICswMDAwIFVUQw==xlxendsDvRv",
      });
    } else {
      console.error("plugSDK is not defined");
    }
  }, []);
  return <div></div>;
};

export default Devrev;
