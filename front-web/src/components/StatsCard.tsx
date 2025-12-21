import React from "react";
import "../assets/css/ui/stats-card.css";

type StatsCardProps = {
  icon: React.ReactNode;
  cardTitle: string;
  cardValue: string;
  cardDescription: string;
  classDescription?: string;
};

const StatsCard: React.FC<StatsCardProps> = ({
  icon,
  cardTitle,
  cardValue,
  cardDescription,
  classDescription,
}) => {
  return (
    <div className="card">
      <div className="card-header">
        <div className="card-title">{cardTitle}</div>
        {icon}
      </div>
      <div className="card-content">
        <div className="card-value">{cardValue}</div>
        <div className={`card-description ${classDescription || ""}`}>
          {cardDescription}
        </div>
      </div>
    </div>
  );
};

export default StatsCard;