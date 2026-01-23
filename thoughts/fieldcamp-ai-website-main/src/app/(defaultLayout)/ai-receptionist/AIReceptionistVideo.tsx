import React from 'react';

export default function AIReceptionistVideo() {
  return (
    <div className="relative aspect-video bg-white rounded-2xl overflow-hidden">
      <div style={{ position: "relative", paddingBottom: "56.25%", height: 0 }}>
        <iframe
          src="https://www.loom.com/embed/26c2ae73ff5242f0b3611658a4d59787?sid=10bb3792-480f-451a-a3a1-debdd4c2b99"
          frameBorder="0"
          allowFullScreen
          style={{ position: "absolute", top: 0, left: 0, width: "100%", height: "100%" }}
        />
      </div>
    </div>
  );
}