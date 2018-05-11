import { Component, OnInit, ViewEncapsulation, AfterViewInit } from '@angular/core';
import { Helpers } from '../../../helpers';
import { Router, RouterLink, NavigationEnd } from "@angular/router";

declare let mLayout: any;
@Component({
    selector: "app-aside-nav",
    templateUrl: "./aside-nav.component.html",
    encapsulation: ViewEncapsulation.None,
})
export class AsideNavComponent implements OnInit, AfterViewInit {
    subadmin: any;
    prev: any;

    constructor(private _router: Router) {
        if (localStorage.getItem("admin") !== null) {
            var user = JSON.parse(localStorage.getItem("admin"));
            //console.log(user);

            this.subadmin = user.subadmin;
            this.prev = user.prev;
        }

    }
    ngOnInit() {

    }
    ngAfterViewInit() {

        mLayout.initAside();
        let menu = mLayout.getAsideMenu(); let item = $(menu).find('a[href="' + window.location.pathname + '"]').parent('.m-menu__item'); (<any>$(menu).data('menu')).setActiveItem(item);
    }

}
