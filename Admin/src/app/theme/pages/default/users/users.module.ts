import { Component, OnInit, ViewEncapsulation, AfterViewInit, NgModule, ComponentFactoryResolver, ViewChild, ViewContainerRef, ElementRef, NgZone } from '@angular/core';
import { Helpers } from '../../../../helpers';
import { CommonModule } from '@angular/common';
import { LayoutModule } from '../../../layouts/layout.module';
import { DefaultComponent } from '../default.component';
import { ReactiveFormsModule, FormsModule, FormGroup, FormControl, Validators, FormBuilder, AbstractControl } from '@angular/forms';
import { AgmCoreModule, MapsAPILoader } from '@agm/core';
import { } from '@types/googlemaps';

import { Router, Routes, RouterModule } from "@angular/router";
import { UserlistComponent } from './userlist/userlist.component';
import { AdduserComponent } from './adduser/adduser.component';

import { AlertService } from "../../../../auth/_services/alert.service";
import { AlertComponent } from "../../../../auth/_directives/alert.component";
import { TextMaskModule } from 'angular2-text-mask';
import { ImageUploadModule } from "angular2-image-upload";
import { EdituserComponent } from './edituser/edituser.component';
import { Socket, SocketIoModule } from 'ng-socket-io';

const routes: Routes = [
    {
        "path": "",
        "component": DefaultComponent,
        "children": [
            {
                path: "",
                component: UserlistComponent
            },
            {
                path: 'add',
                component: AdduserComponent
            },
            {
                path: 'edit/:id',
                component: EdituserComponent
            }
        ]
    }
];

@NgModule({
    imports: [
        CommonModule, RouterModule.forChild(routes), LayoutModule, FormsModule, ReactiveFormsModule, TextMaskModule, ImageUploadModule.forRoot(), SocketIoModule, AgmCoreModule.forRoot({ apiKey: 'AIzaSyAnzd44RW1D2v1bkATEGaTu6wGpCjtWyZQ', libraries: ["places"] }),
    ], exports: [
        RouterModule,
    ],
    declarations: [UserlistComponent, AdduserComponent, EdituserComponent],
    providers: []
})

export class UsersComponent {

    constructor() { }

    ngOnInit() {
    }

}
