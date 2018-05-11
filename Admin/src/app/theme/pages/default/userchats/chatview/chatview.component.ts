import { Component, OnInit, AfterViewInit } from '@angular/core';
import { UserService } from "../../../../.././auth/_services/user.service";
import { ScriptLoaderService } from '../../../../.././_services/script-loader.service';
import { FormsModule, Validator, NgModel } from '@angular/forms';
import { Router, RouterLink, NavigationEnd } from "@angular/router";

@Component({
    selector: '.m-grid__item.m-grid__item--fluid.m-wrapper',
    templateUrl: "./chatview.component.html",
    styles: ['.m-messenger__message--in{text-align: left;} .m-messenger__message--in .m-messenger__message-body{float: left;} .m-messenger__message--out{text-align: right;} .m-messenger__message--out .m-messenger__message-body{float: right;}']
})
export class ChatviewComponent implements OnInit {

    currentUrl = '';
    cid: any;
    start = 0;
    messages: any = [];
    showmore: boolean = false;
    fromcu: any;
    tocu: any;

    constructor(private _userService: UserService, private _script: ScriptLoaderService, private _router: Router) {
        _router.events.subscribe((event: any) => {
            if (event instanceof NavigationEnd) {
                this.currentUrl = event.url;
                //console.log(this.currentUrl);
                let urldata = this.currentUrl.split("/");
                console.log(urldata);
                let pid = urldata[3];
                console.log('tlist', pid);
                this.cid = pid;
            }
        });
    }

    ngOnInit() {
        this.getmsgs();
    }

    getmsgs() {
        this._userService.chatmsgs(this.cid, this.start).subscribe(
            data => {
                if (!data.error) {
                    for (var i = 0; i < data.data.msgs.length; i++) {
                        this.messages.push(data.data.msgs[i]);
                    }
                    this.fromcu = data.data.fromcu;
                    this.tocu = data.data.tocu;
                    this.showmore = data.data.loadmore;
                }
            }, error => {

            }
        );
    }

    loadmore() {
        this.start++;
        this.getmsgs();
    }

}
