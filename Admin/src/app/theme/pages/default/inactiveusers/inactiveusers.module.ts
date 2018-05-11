import { Component, OnInit, ViewEncapsulation, AfterViewInit, NgModule, ComponentFactoryResolver, ViewChild, ViewContainerRef } from '@angular/core';
import { Helpers } from '../../../../helpers';
import { LayoutModule } from '../../../layouts/layout.module';
import { DefaultComponent } from '../default.component';
import { CommonModule } from '@angular/common';
import { AlertService } from "../../../../auth/_services/alert.service";
import { AlertComponent } from "../../../../auth/_directives/alert.component";
import { Router, Routes, RouterModule } from "@angular/router";
import { InactivelistComponent } from './inactivelist/inactivelist.component';

const routes: Routes = [
    {
        "path": "",
        "component": DefaultComponent,
        "children": [
            {
                path: "",
                component: InactivelistComponent
            }
        ]
    }
];


@NgModule({
    imports: [
        CommonModule, RouterModule.forChild(routes), LayoutModule
    ], exports: [
        RouterModule
    ],
    declarations: [InactivelistComponent]
})
export class InactiveusersModule { }
