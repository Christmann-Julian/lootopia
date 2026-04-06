import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";
import Login from "./pages/Login";
import Register from "./pages/Register";
import Home from "./pages/Home";
import Profile from "./pages/Profile";
import Reward from "./pages/Reward";
import Radar from "./pages/Radar";
import TreasureHunt from "./pages/TreasureHunt";
import Success from "./pages/Success";

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Login />} />
        <Route path="/register" element={<Register />} />
        <Route path="/home" element={<Home />} />
        <Route path="/profile" element={<Profile />} />
        <Route path="/rewards" element={<Reward />} />
        <Route path="/treasure-hunts" element={<TreasureHunt />} />
        <Route path="/radar" element={<Radar />} />
        <Route path="/success" element={<Success />} />
        <Route path="*" element={<Navigate to="/" />} />
      </Routes>
    </BrowserRouter>
  );
}
