import { useEffect } from "react";
import { useMapEvents } from "react-leaflet";
import L from "leaflet";

type Props = {
  onUpdate: (pos: L.LatLng, dist: number) => void;
  treasurePos: [number, number];
};

export function LocationTracker({ onUpdate, treasurePos }: Props) {
  const map = useMapEvents({
    locationfound(e) {
      const dist = e.latlng.distanceTo(L.latLng(treasurePos[0], treasurePos[1]));
      onUpdate(e.latlng, dist);
      map.flyTo(e.latlng, 17, { animate: true });
    },
    locationerror(e) {
      console.error("GPS Signal Lost:", e.message);
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
