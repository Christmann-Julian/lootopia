import { useState, useEffect } from "react";
import "leaflet/dist/leaflet.css";
import "../assets/css/radar.css";
import { Unlock, AlertTriangle } from "lucide-react";
import { useTranslation } from "react-i18next";

type HuntDetails = {
  id: number;
  lat: number;
  lon: number;
  company: string;
  title: string;
  question: string;
  answer: string;
};

export default function ARView({
  hunt,
  arCoords,
  onExit,
  onSuccess,
}: {
  hunt: HuntDetails;
  arCoords: [number, number];
  onExit: () => void;
  onSuccess: () => void;
}) {
  const [answerInput, setAnswerInput] = useState("");
  const [error, setError] = useState("");
  const [isVerifying, setIsVerifying] = useState(false);
  const [boxFound, setBoxFound] = useState(false);

  const { t } = useTranslation();

  useEffect(() => {
    const handleIframeMessage = (event: MessageEvent) => {
      if (event.data === "BOX_CLICKED") {
        setBoxFound(true);
      }
    };
    window.addEventListener("message", handleIframeMessage);
    return () => window.removeEventListener("message", handleIframeMessage);
  }, []);

  const handleSubmit = () => {
    setIsVerifying(true);
    setError("");

    if (answerInput.trim().toLowerCase() === hunt.answer.trim().toLowerCase()) {
      onSuccess();
    } else {
      setError(t("radar.ar.incorrectAnswer"));
      setIsVerifying(false);
    }
  };

  return (
    <div className="ar-view-container">
      <button className="exit-ar" onClick={onExit}>
        {t("radar.ar.abort")}
      </button>

      <div className={`ar-search-hud ${boxFound ? "hidden" : ""}`}>
        <div className="crosshair"></div>
        <div className="search-text">{t("radar.ar.searchText")}</div>
      </div>

      <iframe
        className="ar-iframe"
        srcDoc={`
          <html>
            <head>
              <script src="https://aframe.io/releases/1.3.0/aframe.min.js"></script>
              <script src="https://raw.githack.com/AR-js-org/AR.js/master/aframe/build/aframe-ar-nft.js"></script>
              <script>
                AFRAME.registerComponent('hackable', {
                  init: function () {
                    this.el.addEventListener('click', function () {
                      const crystal = this.querySelector('.crystal');
                      const halo = this.querySelector('.halo');
                      const ring = this.querySelector('.ring');
                      
                      if (crystal) {
                        crystal.setAttribute('material', 'color', '#10b981');
                        crystal.setAttribute('material', 'emissive', '#10b981');
                      }
                      if (halo) halo.setAttribute('color', '#10b981');
                      if (ring) ring.setAttribute('color', '#10b981');
                      
                      window.parent.postMessage('BOX_CLICKED', '*');
                    });
                  }
                });
              </script>
            </head>
            <body style="margin: 0; overflow: hidden;">
              <a-scene embedded arjs="sourceType: webcam; debugUIEnabled: false;" cursor="rayOrigin: mouse">
                <a-entity 
                  hackable
                  gps-entity-place="latitude: ${arCoords[0]}; longitude: ${arCoords[1]};"
                  position="0 0 0" 
                  scale="1 1 1"
                  animation="property: position; dir: alternate; dur: 2000; easing: easeInOutSine; loop: true; to: 0 0.5 0"
                >
                  <a-octahedron class="crystal" radius="1" color="#d4af37" material="metalness: 0.8; roughness: 0.1; opacity: 0.95; emissive: #d4af37; emissiveIntensity: 0.4" animation="property: rotation; to: 0 360 0; loop: true; dur: 4000; easing: linear"></a-octahedron>
                  <a-icosahedron class="halo" radius="1.5" color="#00f2ff" wireframe="true" material="wireframeLinewidth: 2" animation="property: rotation; to: 360 360 360; loop: true; dur: 8000; easing: linear"></a-icosahedron>
                  <a-ring class="ring" radius-inner="1.8" radius-outer="2.2" color="#d4af37" rotation="-90 0 0" position="0 -1.5 0" material="opacity: 0.5; transparent: true" animation="property: scale; dir: alternate; dur: 1500; easing: easeInOutSine; loop: true; to: 1.2 1.2 1.2"></a-ring>
                </a-entity>
                <a-camera gps-camera rotation-reader></a-camera>
              </a-scene>
            </body>
          </html>
        `}
        allow="camera; geolocation"
      />

      <div className={`ar-overlay-ui ${boxFound ? "visible" : ""}`}>
        <div className="ar-overlay-header">
          <Unlock size={16} /> {t("radar.ar.anomalyDetected")}
        </div>
        <p className="ar-question">{hunt.question}</p>
        <input
          type="text"
          className="ar-input"
          placeholder={t("radar.ar.answerPlaceholder")}
          value={answerInput}
          onChange={(e) => setAnswerInput(e.target.value)}
        />
        {error && (
          <div className="ar-error">
            <AlertTriangle size={12} className="ar-error-icon" /> {error}
          </div>
        )}
        <button className="ar-submit" onClick={handleSubmit} disabled={isVerifying}>
          {isVerifying ? t("radar.ar.checking") : t("radar.ar.unlock")}
        </button>
      </div>
    </div>
  );
}
