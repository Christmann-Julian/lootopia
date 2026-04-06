import { type RouteConfig, index, prefix, route } from "@react-router/dev/routes";

export default [
  index("routes/redirect.tsx"),
  ...prefix(":lang", [
    route(undefined, "./routes/auth/guest-layout.tsx", [
      index("./routes/auth/login.tsx"),
      route("register", "./routes/auth/register.tsx"),
      route("register-success", "./routes/auth/register-success.tsx"),
      route("forgot-password", "./routes/auth/forgot-password.tsx"),
      route("reset-password", "./routes/auth/reset-password.tsx"),
    ]),

    route("dashboard", "./routes/auth/auth-layout.tsx", [
      index("./routes/dashboard/dashboard.tsx"),
      route("hunts", "./routes/dashboard/hunts/hunt-list.tsx"),
      route("hunts/:id/show", "./routes/dashboard/hunts/hunt-show.tsx"),
      route("rewards", "./routes/dashboard/rewards/reward-list.tsx"),
      route("rewards/:id/show", "./routes/dashboard/rewards/reward-show.tsx"),
      route("settings", "./routes/dashboard/settings.tsx"),
      route("admin", "./routes/auth/admin-layout.tsx", [
        route("users", "./routes/dashboard/admin/users/user-list.tsx"),
        route("users/create", "./routes/dashboard/admin/users/user-create.tsx"),
        route("users/:id/edit", "./routes/dashboard/admin/users/user-edit.tsx"),
        route("users/:id/show", "./routes/dashboard/admin/users/user-show.tsx"),
        route("badges", "./routes/dashboard/admin/badges/badge-list.tsx"),
        route("badges/:id/show", "./routes/dashboard/admin/badges/badge-show.tsx"),
        route("ranks", "./routes/dashboard/admin/ranks/rank-list.tsx"),
        route("ranks/:id/show", "./routes/dashboard/admin/ranks/rank-show.tsx"),
        route("categories", "./routes/dashboard/admin/categories/category-list.tsx"),
        route("categories/:id/show", "./routes/dashboard/admin/categories/category-show.tsx"),
        route("rarities", "./routes/dashboard/admin/rarities/rarity-list.tsx"),
        route("rarities/:id/show", "./routes/dashboard/admin/rarities/rarity-show.tsx"),
      ]),
    ]),

    ...prefix("action", [route("change-password", "./routes/action/change-password.tsx")]),

    route("*", "./routes/not-found.tsx"),
  ]),
] satisfies RouteConfig;
