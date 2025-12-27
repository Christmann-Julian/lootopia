import { type RouteConfig, index, prefix, route } from "@react-router/dev/routes";

export default [
  ...prefix(":locale?", [
    route(undefined, "./routes/auth/guest-layout.tsx", [
      index("./routes/auth/login.tsx"),
      route("register", "./routes/auth/register.tsx"),
      route("register-success", "./routes/auth/register-success.tsx"),
      route("forgot-password", "./routes/auth/forgot-password.tsx"),
      route("reset-password", "./routes/auth/reset-password.tsx"),
    ]),

    route("dashboard", "./routes/auth/auth-layout.tsx", [
      index("./routes/dashboard/dashboard.tsx"),
    ]),
  ]),
] satisfies RouteConfig;
