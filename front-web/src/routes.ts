import { type RouteConfig, index, prefix, route } from "@react-router/dev/routes";

export default [
    ...prefix(":locale?", [
        index("./routes/auth/login.tsx"),
        route("register", "./routes/auth/register.tsx"),
        route("register-success", "./routes/auth/register-success.tsx"),
        ...prefix("dashboard", [
            index("./routes/dashboard/dashboard.tsx"),
            // route("profile", "./routes/dashboard/profile.tsx"),
        ]),
    ]),
] satisfies RouteConfig;