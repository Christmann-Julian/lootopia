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
      route("hunts/create", "./routes/dashboard/hunts/hunt-create.tsx"),
      route("hunts/:id/edit", "./routes/dashboard/hunts/hunt-edit.tsx"),
      route("hunts/:id/show", "./routes/dashboard/hunts/hunt-show.tsx"),
      route("rewards", "./routes/dashboard/rewards/reward-list.tsx"),
      route("rewards/:id/edit", "./routes/dashboard/rewards/reward-edit.tsx"),
      route("rewards/:id/show", "./routes/dashboard/rewards/reward-show.tsx"),
      route("settings", "./routes/dashboard/settings.tsx"),
      route("admin", "./routes/auth/admin-layout.tsx", [
        route("users", "./routes/dashboard/admin/users/user-list.tsx"),
        route("users/create", "./routes/dashboard/admin/users/user-create.tsx"),
        route("users/:id/edit", "./routes/dashboard/admin/users/user-edit.tsx"),
        route("users/:id/show", "./routes/dashboard/admin/users/user-show.tsx"),
        route("badges", "./routes/dashboard/admin/badges/badge-list.tsx"),
        route("badges/create", "./routes/dashboard/admin/badges/badge-create.tsx"),
        route("badges/:id/edit", "./routes/dashboard/admin/badges/badge-edit.tsx"),
        route("badges/:id/show", "./routes/dashboard/admin/badges/badge-show.tsx"),
        route("ranks", "./routes/dashboard/admin/ranks/rank-list.tsx"),
        route("ranks/create", "./routes/dashboard/admin/ranks/rank-create.tsx"),
        route("ranks/:id/edit", "./routes/dashboard/admin/ranks/rank-edit.tsx"),
        route("ranks/:id/show", "./routes/dashboard/admin/ranks/rank-show.tsx"),
        route("categories", "./routes/dashboard/admin/categories/category-list.tsx"),
        route("categories/create", "./routes/dashboard/admin/categories/category-create.tsx"),
        route("categories/:id/edit", "./routes/dashboard/admin/categories/category-edit.tsx"),
        route("categories/:id/show", "./routes/dashboard/admin/categories/category-show.tsx"),
        route("rarities", "./routes/dashboard/admin/rarities/rarity-list.tsx"),
        route("rarities/create", "./routes/dashboard/admin/rarities/rarity-create.tsx"),
        route("rarities/:id/edit", "./routes/dashboard/admin/rarities/rarity-edit.tsx"),
        route("rarities/:id/show", "./routes/dashboard/admin/rarities/rarity-show.tsx"),
      ]),
    ]),

    ...prefix("action", [route("change-password", "./routes/action/change-password.tsx")]),

    route("*", "./routes/not-found.tsx"),
  ]),
] satisfies RouteConfig;
