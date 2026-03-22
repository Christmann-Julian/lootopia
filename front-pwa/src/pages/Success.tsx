import {
  Trophy,
  ChevronRight,
  LayoutGrid,
  Ticket,
  ShieldCheck,
  Share2,
  Zap,
  Star,
  Compass,
  Activity,
  Cpu,
} from "lucide-react";
import "../assets/css/success.css";

type SuccessProps = {
  type?: "reward" | "email";
  rewardName?: string;
  brand?: string;
};

const Success: React.FC<SuccessProps> = ({
  type = "reward",
  rewardName = "-50% OFF MENU",
  brand = "BURGER KING",
}) => {
  return (
    <div className="success-wrapper">
      <div className="topo-overlay"></div>

      <div className="success-card">
        <div className="success-badge">
          <div className="badge-glow"></div>
          <div className="badge-hex">
            <div className="badge-icon">
              {type === "reward" ? <Trophy size={40} /> : <ShieldCheck size={40} />}
            </div>
          </div>
        </div>

        <h1 className="success-title">
          {type === "reward" ? "Mission Accomplished" : "System Verified"}
        </h1>

        <p className="success-msg">
          {type === "reward"
            ? "Strategic loot extraction successful. Data synced to neural profile."
            : "Neural link established. Your identity has been confirmed by the hub."}
        </p>

        {type === "reward" && (
          <div className="reward-info-box">
            <span className="reward-brand">{brand}</span>
            <h2 className="reward-name">{rewardName}</h2>
            <div style={{ marginTop: "0.75rem", display: "flex", gap: "10px" }}>
              <div
                style={{
                  display: "flex",
                  alignItems: "center",
                  gap: "4px",
                  fontSize: "0.6rem",
                  fontWeight: 800,
                  color: "rgba(255,255,255,0.4)",
                }}
              >
                <Star size={10} fill="var(--gold)" color="var(--gold)" />
                LÉGENDAIRE
              </div>
              <div
                style={{
                  display: "flex",
                  alignItems: "center",
                  gap: "4px",
                  fontSize: "0.6rem",
                  fontWeight: 800,
                  color: "rgba(255,255,255,0.4)",
                }}
              >
                <Zap size={10} color="var(--gold)" />
                PRÊT À L'EXTRACTION
              </div>
            </div>
          </div>
        )}

        <div className="btn-group">
          <button className="btn btn-primary">
            {type === "reward" ? <Ticket size={20} /> : <LayoutGrid size={20} />}
            <span>{type === "reward" ? "View Inventory" : "Go to Command Hub"}</span>
            <ChevronRight size={18} />
          </button>

          <button className="btn btn-secondary">
            {type === "reward" ? <Share2 size={20} /> : <Compass size={20} />}
            <span>{type === "reward" ? "Share Success" : "Back to Radar"}</span>
          </button>
        </div>

        <div className="hud-watermark">
          <span>TX_ID: AKHZRS</span>
          <span>SYNC_STAT: COMPLETED</span>
        </div>
      </div>

      <div
        style={{
          position: "fixed",
          bottom: "2rem",
          right: "2rem",
          opacity: 0.1,
          pointerEvents: "none",
        }}
      >
        <Activity size={100} color="var(--gold)" />
      </div>
      <div
        style={{
          position: "fixed",
          top: "2rem",
          left: "2rem",
          opacity: 0.1,
          pointerEvents: "none",
        }}
      >
        <Cpu size={100} color="var(--gold)" />
      </div>
    </div>
  );
};

export default Success;
