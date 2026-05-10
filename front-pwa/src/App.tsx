import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";
import Login from "./pages/Login";
import Register from "./pages/Register";
import Home from "./pages/Home";
import Profile from "./pages/Profile";
import Reward from "./pages/Reward";
import Radar from "./pages/Radar";
import TreasureHunt from "./pages/TreasureHunt";
import Success from "./pages/Success";
import AuthGuard from "./components/AuthGuard";
import GuestGuard from "./components/GuestGuard";

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route
          path="/"
          element={
            <GuestGuard>
              <Login />
            </GuestGuard>
          }
        />
        <Route
          path="/register"
          element={
            <GuestGuard>
              <Register />
            </GuestGuard>
          }
        />
        <Route
          path="/home"
          element={
            <AuthGuard>
              <Home />
            </AuthGuard>
          }
        />
        <Route
          path="/profile"
          element={
            <AuthGuard>
              <Profile />
            </AuthGuard>
          }
        />
        <Route
          path="/rewards"
          element={
            <AuthGuard>
              <Reward />
            </AuthGuard>
          }
        />
        <Route
          path="/treasure-hunts"
          element={
            <AuthGuard>
              <TreasureHunt />
            </AuthGuard>
          }
        />
        <Route
          path="/radar/:huntId"
          element={
            <AuthGuard>
              <Radar />
            </AuthGuard>
          }
        />
        <Route
          path="/success"
          element={
            <AuthGuard>
              <Success />
            </AuthGuard>
          }
        />
        <Route path="*" element={<Navigate to="/" />} />
      </Routes>
    </BrowserRouter>
  );
}
