import { Component,ViewContainerRef } from '@angular/core';
import { ToastsManager } from 'ng2-toastr/ng2-toastr';

import { Spinkit } from 'ng-http-loader/spinkits';
import { PendingInterceptorService } from 'ng-http-loader/services/pending-interceptor.service';

import { SimpleNotificationsModule } from 'angular2-notifications';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {
  public spinkit = Spinkit;
  title = 'app';
  constructor(public toastr: ToastsManager, vcr: ViewContainerRef, pendingInterceptorService: PendingInterceptorService) {
    pendingInterceptorService.pendingRequestsStatus.subscribe(pending => {
            if (!pending) {
                console.log('No tracked http requests pending anymore');
            }
    });
    this.toastr.setRootViewContainerRef(vcr);
  }
}
