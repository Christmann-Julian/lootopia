import { useState, useEffect } from "react";
import { MapContainer, TileLayer, Circle } from "react-leaflet";
import { useNavigate, useParams } from "react-router-dom";
import "leaflet/dist/leaflet.css";
import "../assets/css/radar.css";
import L from "leaflet";
import { Camera, Crosshair, CheckCircle2, ShieldAlert, Bug } from "lucide-react";
import Navbar from "../components/Navbar";
import LocationTracker from "../components/LocationTracker";
import ARView from "../components/ARView";
import { api } from "../services/auth";
import { useTranslation } from "react-i18next";

const DETECTION_RADIUS = 50;

type HuntDetails = {
  id: number;
  lat: number;
  lon: number;
  company: string;
  title: string;
  question: string;
  answer: string;
};

const Radar = () => {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { huntId } = useParams();

  const [huntDetails, setHuntDetails] = useState<HuntDetails | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [alreadyClaimed, setAlreadyClaimed] = useState(false);

  const [userPos, setUserPos] = useState<L.LatLng | null>(null);
  const [distance, setDistance] = useState<number | null>(null);
  const [isNear, setIsNear] = useState(false);

  const [arMode, setArMode] = useState(false);
  const [claimSuccess, setClaimSuccess] = useState(false);

  const [showDevMenu, setShowDevMenu] = useState(false);
  const [devForceNear, setDevForceNear] = useState(false);
  const [devSpawnInFront, setDevSpawnInFront] = useState(false);

  useEffect(() => {
    const initHunt = async () => {
      try {
        setIsLoading(true);
        const [huntRes, rewardsRes] = await Promise.all([
          api.get(`/api/hunts/${huntId}`),
          api.get("/api/me/rewards"),
        ]);

        const hasAlreadyClaimed = rewardsRes.data.data.some(
          (reward: { huntId: number }) => reward.huntId === Number(huntId)
        );

        if (hasAlreadyClaimed) {
          setAlreadyClaimed(true);
          setTimeout(() => navigate("/rewards"), 3000);
          return;
        }

        setHuntDetails(huntRes.data);

        try {
          await api.post(`/api/me/hunts/${huntId}/participate`);
        } catch (participateError) {
          console.error("Participation error :", participateError);
        }
      } catch (error) {
        console.error("Error :", error);
      } finally {
        setIsLoading(false);
      }
    };

    if (huntId) initHunt();
    else setIsLoading(false);
  }, [huntId, navigate]);

  const handleLocationUpdate = (pos: L.LatLng, dist: number) => {
    setUserPos(pos);
    setDistance(Math.round(dist));
    setIsNear(dist < DETECTION_RADIUS);
  };

  const handleRewardClaim = async () => {
    try {
      await api.post(`/api/me/rewards/${huntId}/claim`);
      setClaimSuccess(true);
      setTimeout(() => navigate("/rewards"), 3000);
    } catch (error) {
      console.error("Claim reward error :", error);
      alert("Network error or treasure already claimed.");
      setArMode(false);
    }
  };

  const effectiveIsNear = devForceNear ? true : isNear;
  const TREASURE_POS: [number, number] = huntDetails ? [huntDetails.lat, huntDetails.lon] : [0, 0];

  const arCoords: [number, number] =
    devSpawnInFront && userPos ? [userPos.lat + 0.00008, userPos.lng] : TREASURE_POS;

  if (isLoading) {
    return (
      <div className="radar-wrapper radar-status-screen">
        <div className="status-text-loading">{t("radar.connection")}</div>
      </div>
    );
  }

  if (alreadyClaimed) {
    return (
      <div className="radar-wrapper radar-status-screen">
        <ShieldAlert size={80} color="#d4af37" className="status-icon" />
        <h1 className="status-title">{t("radar.reward.alreadyClaimed.title")}</h1>
        <p className="status-subtitle">{t("radar.reward.alreadyClaimed.message")}</p>
      </div>
    );
  }

  if (!huntDetails) {
    return (
      <div className="radar-wrapper radar-status-screen">
        <div className="status-text-error">{t("radar.targetNotFound")}</div>
        <Navbar activeItem="treasure-hunts" />
      </div>
    );
  }

  if (claimSuccess) {
    return (
      <div className="radar-wrapper radar-status-screen">
        <CheckCircle2 size={80} color="#10b981" className="status-icon" />
        <h1 className="status-title large">{t("radar.reward.success.title")}</h1>
        <p className="status-subtitle">{t("radar.reward.success.message")}</p>
      </div>
    );
  }

  if (arMode) {
    return (
      <ARView
        hunt={huntDetails}
        arCoords={arCoords}
        onExit={() => setArMode(false)}
        onSuccess={handleRewardClaim}
      />
    );
  }

  return (
    <div className="radar-wrapper">
      <button className="dev-toggle-btn" onClick={() => setShowDevMenu(!showDevMenu)}>
        <Bug size={20} />
      </button>

      {showDevMenu && (
        <div className="dev-panel">
          <div className="dev-panel-title">DEV TOOLS</div>
          <label className="dev-panel-label">
            <input
              type="checkbox"
              checked={devForceNear}
              onChange={(e) => setDevForceNear(e.target.checked)}
            />
            Forcer Distance = 0m
          </label>
          <label className="dev-panel-label">
            <input
              type="checkbox"
              checked={devSpawnInFront}
              onChange={(e) => setDevSpawnInFront(e.target.checked)}
            />
            Pop l'anomalie devant moi
          </label>
        </div>
      )}

      <div className="topo-overlay"></div>
      <div className="leaflet-vignette"></div>
      <div className="scan-line"></div>

      <MapContainer center={TREASURE_POS} zoom={16} zoomControl={false}>
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
          <span className="stat-label">{t("radar.target")}</span>
          <span className="stat-value font-mono uppercase">{huntDetails.company}</span>
        </div>
        <div className="hud-column">
          <div className="hud-box">
            <span className="stat-label">{t("radar.targetDistance")}</span>
            <span className={`stat-value font-mono ${effectiveIsNear ? "success" : "danger"}`}>
              {devForceNear
                ? "OVERRIDE"
                : distance !== null
                  ? `${distance} ${t("radar.meters")}`
                  : t("radar.targetSearch")}
            </span>
          </div>
        </div>
      </div>

      <div className="hud-bottom-actions">
        {!effectiveIsNear && distance !== null && (
          <div className="hud-box hud-box-alert">
            <span className="stat-label alert-label">{t("radar.alert.title")}</span>
            <span className="alert-desc">{t("radar.alert.message")}</span>
          </div>
        )}

        <button
          className={`ar-btn ${effectiveIsNear ? "active" : ""}`}
          onClick={() => effectiveIsNear && setArMode(true)}
        >
          <span className="ar-label">
            {effectiveIsNear ? t("radar.targetReady") : t("radar.targetLocked")}
          </span>
          {effectiveIsNear ? <Camera size={36} /> : <Crosshair size={36} />}
        </button>

        <div className="sync-indicator font-bold flex items-center gap-2">
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
