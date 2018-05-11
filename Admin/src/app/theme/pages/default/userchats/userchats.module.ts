import { Component, OnInit, ViewEncapsulation, AfterViewInit, NgModule, ComponentFactoryResolver, ViewChild, ViewContainerRef } from '@angular/core';
import { Helpers } from '../../../../helpers';
import { CommonModule } from '@angular/common';
import { LayoutModule } from '../../../layouts/layout.module';
import { DefaultComponent } from '../default.component';
import { ChatlistComponent } from './chatlist/chatlist.component';
import { ChatviewComponent } from './chatview/chatview.component';

import { FormsModule, Validator } from '@angular/forms';
import { Router, Routes, RouterModule } from "@angular/router";
import { AlertService } from "../../../../auth/_services/alert.service";
import { AlertComponent } from "../../../../auth/_directives/alert.component";

const routes: Routes = [
    {
        "path": "",
        "component": DefaultComponent,
        "children": [
            {
                path: "",
                component: ChatlistComponent
            },
            {
                path: 'view/:id',
                component: ChatviewComponent
            }
        ]
    }
];

@NgModule({
    imports: [
        CommonModule, RouterModule.forChild(routes), LayoutModule, FormsModule
    ], exports: [
        RouterModule
    ],
    declarations: [ChatlistComponent, ChatviewComponent]
})
export class UserchatsModule { }
