import { useState } from "react";
import { MapContainer, TileLayer, Circle } from "react-leaflet";
import "leaflet/dist/leaflet.css";
import "../assets/css/radar.css";
import L from "leaflet";
import { Camera, Crosshair } from "lucide-react";
import Navbar from "../components/Navbar";
import { LocationTracker } from "../components/LocationTracker";
import { ARView } from "../components/ARView";

const TREASURE_POS: [number, number] = [48.836485, 2.352331];
const DETECTION_RADIUS = 50; // Mètres pour débloquer l'AR

const Radar = () => {
  const [userPos, setUserPos] = useState<L.LatLng | null>(null);
  const [distance, setDistance] = useState<number | null>(null);
  const [isNear, setIsNear] = useState(false);
  const [arMode, setArMode] = useState(false);

  const handleLocationUpdate = (pos: L.LatLng, dist: number) => {
    setUserPos(pos);
    setDistance(Math.round(dist));
    setIsNear(dist < DETECTION_RADIUS);
  };

  const handleExitArMode = () => {
    setArMode(false);
  };

  if (arMode) {
    return <ARView treasurePos={TREASURE_POS} onExitArMode={handleExitArMode} />;
  }

  return (
    <div className="radar-wrapper">
      <style>{`
        .ar-btn {
          width: 80px; height: 80px;
          background: ${isNear ? "linear-gradient(135deg, #d4af37 0%, #fef08a 100%)" : "rgba(15, 23, 42, 0.9)"};
          border: 2px solid ${isNear ? "#d4af37" : "rgba(255,255,255,0.1)"};
          border-radius: 50%; color: ${isNear ? "#0f172a" : "rgba(255,255,255,0.2)"};
          display: flex; align-items: center; justify-content: center;
          box-shadow: ${isNear ? "0 0 40px rgba(212, 175, 55, 0.4)" : "none"};
          cursor: ${isNear ? "pointer" : "not-allowed"};
          transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
          position: relative;
        }

        .ar-btn:hover { transform: ${isNear ? "scale(1.1) rotate(5deg)" : "none"}; }
        .ar-btn:active { transform: scale(0.95); }

        .ar-label {
          position: absolute; top: -25px; font-size: 0.6rem; font-weight: 900;
          color: ${isNear ? "#d4af37" : "rgba(255,255,255,0.2)"}; letter-spacing: 0.2em; text-transform: uppercase;
          white-space: nowrap;
        }
      `}</style>

      <div className="topo-overlay"></div>
      <div className="leaflet-vignette"></div>
      <div className="scan-line"></div>

      <MapContainer center={TREASURE_POS} zoom={15} zoomControl={false}>
        <TileLayer
          url="https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png"
          attribution="&copy; CARTO"
        />

        <LocationTracker onUpdate={handleLocationUpdate} treasurePos={TREASURE_POS} />

        <Circle
          center={TREASURE_POS}
          radius={DETECTION_RADIUS}
          pathOptions={{
            color: "#d4af37",
            fillColor: "#d4af37",
            fillOpacity: 0.2,
            dashArray: "5, 10",
            weight: 2,
          }}
        />

        {userPos && (
          <Circle
            center={userPos}
            radius={8}
            pathOptions={{ color: "#00f2ff", fillColor: "#00f2ff", fillOpacity: 1, weight: 4 }}
          />
        )}
      </MapContainer>

      <div className="hud-top">
        <div className="hud-box">
          <span className="stat-label">TARGET_NAME</span>
          <span className="stat-value font-mono">WHOPPER_GOLD_01</span>
        </div>

        <div className="hud-column">
          <div className="hud-box">
            <span className="stat-label">TARGET_DISTANCE</span>
            <span className={`stat-value font-mono ${isNear ? "success" : "danger"}`}>
              {distance !== null ? `${distance} METERS` : "SEARCHING..."}
            </span>
          </div>
        </div>
      </div>

      <div className="hud-bottom-actions">
        {!isNear && distance !== null && (
          <div
            className="hud-box"
            style={{ background: "rgba(239, 68, 68, 0.1)", borderColor: "rgba(239, 68, 68, 0.3)" }}
          >
            <span className="stat-label" style={{ color: "#ef4444" }}>
              ALERT: TOO FAR
            </span>
            <span style={{ fontSize: "0.65rem", display: "block" }}>
              Get within 50m to extract.
            </span>
          </div>
        )}

        <button className="ar-btn" onClick={() => isNear && setArMode(true)}>
          <span className="ar-label">{isNear ? "READY FOR EXTRACTION" : "LOCKED"}</span>
          {isNear ? <Camera size={36} /> : <Crosshair size={36} />}
        </button>

        <div
          className="font-bold text-gold flex items-center gap-2"
          style={{ color: "rgba(212, 175, 55, 0.5)" }}
        >
          <div
            className={`w-2 h-2 rounded-full ${userPos ? "bg-green-500 animate-pulse" : "bg-red-500"}`}
          ></div>
          {userPos ? "SYNC_ACTIVE // GPS_LOCKED" : "INITIALIZING NEURAL_LINK..."}
        </div>
      </div>

      <Navbar activeItem="treasure-hunts" />
    </div>
  );
};

export default Radar;
