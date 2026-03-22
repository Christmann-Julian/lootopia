type Props = {
  treasurePos: [number, number];
  onExitArMode: () => void;
};

export const ARView = ({ treasurePos, onExitArMode }: Props) => (
  <div className="ar-view-container">
    <style>{`
          .ar-view-container { width: 100vw; height: 100vh; background: #000; position: fixed; inset: 0; z-index: 9999; }
          .exit-ar { position: absolute; top: 2rem; left: 1.5rem; z-index: 10000; background: #ef4444; color: white; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; box-shadow: 0 0 20px rgba(239, 68, 68, 0.4); }
        `}</style>
    <button className="exit-ar" onClick={onExitArMode}>
      Abort Extraction
    </button>
    <iframe
      srcDoc={`
            <html>
              <head>
                <script src="https://aframe.io/releases/1.3.0/aframe.min.js"></script>
                <script src="https://raw.githack.com/AR-js-org/AR.js/master/aframe/build/aframe-ar-nft.js"></script>
              </head>
              <body style="margin: 0; overflow: hidden;">
                <a-scene embedded arjs="sourceType: webcam; debugUIEnabled: false;">
                  <a-box 
                    gps-entity-place="latitude: ${treasurePos[0]}; longitude: ${treasurePos[1]};" 
                    scale="10 10 10" 
                    material="color: #d4af37; metalness: 0.8; roughness: 0.2; opacity: 0.9;">
                  </a-box>
                  <a-camera gps-camera rotation-reader></a-camera>
                </a-scene>
              </body>
            </html>
          `}
      style={{ width: "100%", height: "100%", border: "none" }}
      allow="camera; geolocation"
    />
  </div>
);
