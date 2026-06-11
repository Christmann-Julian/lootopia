import { useEffect } from "react";
import { useMapEvents } from "react-leaflet";
import "leaflet/dist/leaflet.css";
import "../assets/css/radar.css";
import L from "leaflet";

export default function LocationTracker({
  onUpdate,
  treasurePos,
}: {
  onUpdate: (pos: L.LatLng, dist: number) => void;
  treasurePos: [number, number];
}) {
  const map = useMapEvents({
    locationfound(e) {
      const dist = e.latlng.distanceTo(L.latLng(treasurePos[0], treasurePos[1]));
      onUpdate(e.latlng, dist);
      map.flyTo(e.latlng, 17, { animate: true });
    },
    locationerror(e) {
      console.error("GPS Signal lost :", e.message);
    },
  });

  useEffect(() => {
    map.locate({
      setView: true,
      maxZoom: 18,
      enableHighAccuracy: true,
      watch: true,
    });
  }, [map]);

  return null;
}
