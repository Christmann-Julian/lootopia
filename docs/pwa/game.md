# Gameplay - Géolocalisation et Réalité Augmentée

Ce document décrit le déroulement complet d'une chasse au trésor, de l'arrivée sur la page radar jusqu'à la validation de la récompense.

---

## Vue d'ensemble

```
/radar/:huntId
      |
      v
  Vérification doublon + participation enregistrée
      |
      v
  Carte Leaflet + GPS en temps réel
      |
      | (distance < 50m)
      v
  Vue AR (iframe A-Frame + AR.js GPS)
      |
      | (objet 3D cliqué)
      v
  Question / Réponse
      |
      | (bonne réponse)
      v
  POST /api/me/rewards/:huntId/claim
      |
      v
  Écran de succès -> redirect /rewards
```

---

## Initialisation de la chasse (`Radar`)

Au montage, deux appels sont effectués en parallèle :

```ts
const [huntRes, rewardsRes] = await Promise.all([
  api.get(`/api/hunts/${huntId}`),
  api.get("/api/me/rewards"),
]);
```

**Vérification du doublon :**

```ts
const hasAlreadyClaimed = rewardsRes.data.data.some(
  (reward) => reward.huntId === Number(huntId)
);
```

Si la récompense est déjà dans l'inventaire, un écran d'avertissement s'affiche et l'utilisateur est redirigé vers `/rewards` après 3 secondes. Aucune erreur n'est remontée à l'API.

**Enregistrement de la participation :**

Si la chasse n'est pas encore réclamée, `POST /api/me/hunts/{huntId}/participate` est appelé automatiquement à l'arrivée sur la page. Les erreurs sont silencieuses (console uniquement) - une participation ratée n'empêche pas de jouer.

---

## États de la page Radar

La page Radar est un automate d'états qui se remplace entièrement selon la progression :

| Condition | Écran affiché |
|---|---|
| `isLoading` | Écran de chargement |
| `alreadyClaimed` | Message "déjà réclamé" + redirect /rewards (3s) |
| `!huntDetails` | Message "cible introuvable" |
| `claimSuccess` | Écran de succès + redirect /rewards (3s) |
| `arMode` | Composant `ARView` |
| défaut | Carte radar + HUD |

---

## Géolocalisation (`LocationTracker`)

Composant Leaflet invisible qui gère le suivi GPS continu.

### Configuration

```ts
map.locate({
  setView: true,
  maxZoom: 18,
  enableHighAccuracy: true,
  watch: true,           // suivi continu, pas de lecture unique
});
```

`watch: true` correspond à `navigator.geolocation.watchPosition` - la position est mise à jour automatiquement quand l'appareil se déplace.

### Mise à jour de position

À chaque événement `locationfound` :

```ts
const dist = e.latlng.distanceTo(L.latLng(treasurePos[0], treasurePos[1]));
onUpdate(e.latlng, dist);
map.flyTo(e.latlng, 17, { animate: true });
```

Leaflet's `distanceTo` retourne la distance en mètres selon la formule de Haversine. La carte se recentre automatiquement sur la position du joueur à chaque mise à jour (zoom 17).

Les erreurs GPS (`locationerror`) sont logées en console uniquement - aucun feedback utilisateur.

### Détection de proximité

```ts
const DETECTION_RADIUS = 50; // mètres

isNear = dist < DETECTION_RADIUS;
```

La distance est arrondie à l'entier (`Math.round(dist)`) pour l'affichage dans le HUD.

---

## Carte Leaflet

| Élément | Configuration |
|---|---|
| TileLayer | CartoDB Dark (`dark_all`) - thème sombre |
| Zoom initial | 16 (vue de quartier) |
| Cercle trésor | Rayon 50m, couleur or `#d4af37`, remplissage 20%, bordure pointillée |
| Cercle joueur | Rayon 8m, couleur cyan `#00f2ff`, solide |

Le cercle du trésor matérialise visuellement la zone de détection. Le joueur doit entrer dedans pour débloquer l'AR.

Le bouton AR en bas de l'écran est stylé `active` si `isNear`, mais reste cliquable uniquement si `effectiveIsNear` est vrai. Sans proximité, le clic est ignoré silencieusement.

---

## Vue AR (`ARView`)

### Technologies

| Composant | Rôle |
|---|---|
| A-Frame 1.3.0 | Moteur de scène 3D WebXR |
| AR.js (aframe-ar-nft) | Superposition AR GPS sur flux caméra |
| iframe `srcDoc` | Isolation du contexte WebXR/caméra |
| `postMessage` | Communication iframe -> parent |

### Isolation via iframe

La vue AR est rendue dans un `<iframe srcDoc="...">` plutôt que directement dans React. Cette approche est nécessaire car A-Frame + AR.js requièrent un accès direct à la caméra via getUserMedia et des APIs WebXR qui ne s'intègrent pas facilement dans une app React classique.

```html
<iframe
  srcDoc={`<html>...</html>`}
  allow="camera; geolocation"
/>
```

Les permissions `camera` et `geolocation` sont explicitement accordées à l'iframe.

### Scène A-Frame

L'objet 3D est un assemblage de primitives animées :

| Primitive | Classe CSS | Rôle |
|---|---|---|
| `a-octahedron` | `.crystal` | Corps principal, couleur or `#d4af37` |
| `a-icosahedron` | `.halo` | Cage wireframe cyan `#00f2ff`, rotation continue |
| `a-ring` | `.ring` | Anneau au sol, animation de pulsation |

L'entité flotte (animation position Y de 0 à 0.5, loop) et tourne sur elle-même.

### Placement GPS de l'objet

```html
<a-entity
  gps-entity-place="latitude: ${arCoords[0]}; longitude: ${arCoords[1]};"
  ...
>
```

`arCoords` correspond normalement aux coordonnées exactes de la chasse (`hunt.lat`, `hunt.lon`).

### Détection de clic sur l'objet 3D

Un composant A-Frame personnalisé `hackable` est enregistré dans l'iframe :

```js
AFRAME.registerComponent('hackable', {
  init: function () {
    this.el.addEventListener('click', function () {
      // Changement de couleur (crystal -> vert)
      window.parent.postMessage('BOX_CLICKED', '*');
    });
  }
});
```

Le clic sur l'objet change sa couleur en vert (`#10b981`) et envoie un message au parent via `postMessage`. Le parent écoute :

```ts
window.addEventListener("message", (event) => {
  if (event.data === "BOX_CLICKED") {
    setBoxFound(true);
  }
});
```

> Note : `postMessage` est utilisé avec l'origine `'*'` (wildcard). Aucune vérification d'origine n'est effectuée côté parent.

### Overlay de question

Quand `boxFound` devient `true`, un overlay s'affiche par-dessus l'iframe avec :
- La question de la chasse (`hunt.question`)
- Un champ de saisie libre
- Un bouton de validation

### Validation de la réponse

```ts
answerInput.trim().toLowerCase() === hunt.answer.trim().toLowerCase()
```

La comparaison est insensible à la casse et aux espaces en début/fin. Pas de tentatives limitées - le joueur peut essayer autant de fois qu'il veut.

En cas d'erreur, le message `t("radar.ar.incorrectAnswer")` s'affiche sous le champ.

---

## Claim de la récompense

Quand la bonne réponse est saisie, `onSuccess()` est appelé, ce qui déclenche :

```ts
await api.post(`/api/me/rewards/${huntId}/claim`);
setClaimSuccess(true);
setTimeout(() => navigate("/rewards"), 3000);
```

En cas d'erreur API : `alert("Network error or treasure already claimed.")` + retour à la vue carte (`arMode = false`). C'est le seul endroit de la PWA utilisant `alert()` natif.

---

## Dev Tools

Un bouton discret (`<Bug>`) dans le coin de l'écran ouvre un panneau de développement :

| Option | Effet |
|---|---|
| Forcer Distance = 0m | `devForceNear = true` - bypass la détection GPS |
| Pop l'anomalie devant moi | `devSpawnInFront = true` - place l'objet AR à `[userPos.lat + 0.00008, userPos.lng]` (~8.9m au nord) |

`effectiveIsNear = devForceNear ? true : isNear` - la valeur réelle est ignorée si le mode forçage est actif.

Ces outils permettent de tester le flow complet sans se déplacer physiquement sur le lieu de la chasse.