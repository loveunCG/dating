import { Component, OnInit, ViewEncapsulation, AfterViewInit, Injectable, ViewContainerRef } from '@angular/core';
import { ToastsManager } from 'ng2-toastr/ng2-toastr';

@Component({
    selector: ".m-grid__item.m-grid__item--fluid.m-wrapper",
    template: '<p></p>'
})

@Injectable()
export class NotifycomponentComponent implements OnInit {

    constructor(public toastr: ToastsManager, vcr: ViewContainerRef) {
        this.toastr.setRootViewContainerRef(vcr);
    }

    ngOnInit() {
    }

    showSuccess() {
        console.log(this.toastr);

        console.log('notifycomponent');

        this.toastr.success('from notify', 'Success!');
    }

}
