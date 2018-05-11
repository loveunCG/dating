import { NgModule } from '@angular/core';
import { ThemeComponent } from './theme.component';
import { Routes, RouterModule } from '@angular/router';
import { AuthGuard } from "../auth/_guards/auth.guard";

const routes: Routes = [
    {
        "path": "",
        "component": ThemeComponent,
        "canActivate": [AuthGuard],
        "children": [
            {
                "path": "users",
                "loadChildren": ".\/pages\/default\/users\/users.module#UsersComponent"
            },
            {
                "path": "profile-list",
                "loadChildren": ".\/pages\/default\/profilelist\/profilelist.module#ProfilelistModule"
            },
            {
                "path": "inactive-users",
                "loadChildren": ".\/pages\/default\/inactiveusers\/inactiveusers.module#InactiveusersModule"
            },
            {
                "path": "users/add",
                "loadChildren": ".\/pages\/default\/users\/users.module#UsersComponent"
            },
            {
                "path": "users/edit/:id",
                "loadChildren": ".\/pages\/default\/users\/users.module#UsersComponent"
            },
            {
                "path": "sub-admins",
                "loadChildren": ".\/pages\/default\/subadmins\/subadmins.module#SubadminsModule"
            },
            {
                "path": "add-to-wallet",
                "loadChildren": ".\/pages\/default\/addtowallet\/addtowallet.module#AddtowalletModule"
            },
            {
                "path": "sub-admins/add",
                "loadChildren": ".\/pages\/default\/subadmins\/subadmins.module#SubadminsModule"
            },
            {
                "path": "sub-admins/edit/:id",
                "loadChildren": ".\/pages\/default\/subadmins\/subadmins.module#SubadminsModule"
            },
            {
                "path": "user-chats",
                "loadChildren": ".\/pages\/default\/userchats\/userchats.module#UserchatsModule"
            },
            {
                "path": "user-chats/view/:id",
                "loadChildren": ".\/pages\/default\/userchats\/userchats.module#UserchatsModule"
            },
            {
                "path": "transactions",
                "loadChildren": ".\/pages\/default\/transactions\/transactions.module#TransactionsModule"
            },
            {
                "path": "transactions/:id",
                "loadChildren": ".\/pages\/default\/transactions\/transactions.module#TransactionsModule"
            },
            {
                "path": "earnings",
                "loadChildren": ".\/pages\/default\/earnings\/earnings.module#EarningsModule"
            },
            {
                "path": "cms-pages",
                "loadChildren": ".\/pages\/default\/cmspages\/cmspages.module#CmspagesModule"
            },
            {
                "path": "cms-pages/add",
                "loadChildren": ".\/pages\/default\/cmspages\/cmspages.module#CmspagesModule"
            },
            {
                "path": "cms-pages/edit/:id",
                "loadChildren": ".\/pages\/default\/cmspages\/cmspages.module#CmspagesModule"
            },
            {
                "path": "packages",
                "loadChildren": ".\/pages\/default\/dspackages\/dspackages.module#dsPackagesModule"
            },
            {
                "path": "packages/add",
                "loadChildren": ".\/pages\/default\/dspackages\/dspackages.module#dsPackagesModule"
            },
            {
                "path": "packages/edit/:id",
                "loadChildren": ".\/pages\/default\/dspackages\/dspackages.module#dsPackagesModule"
            },

            {
                "path": "index",
                "loadChildren": ".\/pages\/default\/index\/index.module#IndexModule"
            },
            {
                "path": "header\/actions",
                "loadChildren": ".\/pages\/default\/header\/header-actions\/header-actions.module#HeaderActionsModule"
            },
            {
                "path": "header\/profile",
                "loadChildren": ".\/pages\/default\/header\/header-profile\/header-profile.module#HeaderProfileModule"
            },
            {
                "path": "404",
                "loadChildren": ".\/pages\/default\/not-found\/not-found.module#NotFoundModule"
            },
            {
                "path": "",
                "redirectTo": "index",
                "pathMatch": "full"
            }
        ]
    },

    {
        "path": "**",
        "redirectTo": "404",
        "pathMatch": "full"
    }
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class ThemeRoutingModule { }
