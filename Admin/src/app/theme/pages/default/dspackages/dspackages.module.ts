import { Component, OnInit, ViewEncapsulation, AfterViewInit, NgModule, ComponentFactoryResolver, ViewChild, ViewContainerRef } from '@angular/core';
import { CommonModule } from '@angular/common';

import { AddpackageComponent } from './addpackage/addpackage.component';
import { PackagelistComponent } from './packagelist/packagelist.component';
import { EditpackageComponent } from './editpackage/editpackage.component';

import { AlertService } from "../../../../auth/_services/alert.service";
import { AlertComponent } from "../../../../auth/_directives/alert.component";
import { TextMaskModule } from 'angular2-text-mask';
import { Helpers } from '../../../../helpers';
import { LayoutModule } from '../../../layouts/layout.module';
import { DefaultComponent } from '../default.component';
import { FormsModule, Validator } from '@angular/forms';
import { Router, Routes, RouterModule } from "@angular/router";

const routes: Routes = [
    {
        "path": "",
        "component": DefaultComponent,
        "children": [
            {
                path: "",
                component: PackagelistComponent
            },
            {
                path: 'add',
                component: AddpackageComponent
            },
            {
                path: 'edit/:id',
                component: EditpackageComponent
            }
        ]
    }
];

@NgModule({
    imports: [
        CommonModule, RouterModule.forChild(routes), LayoutModule, FormsModule, TextMaskModule
    ],
    declarations: [AddpackageComponent, PackagelistComponent, EditpackageComponent]
})
export class dsPackagesModule { }
