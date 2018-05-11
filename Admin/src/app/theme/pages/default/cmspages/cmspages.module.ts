import { Component, OnInit, ViewEncapsulation, AfterViewInit, NgModule, ComponentFactoryResolver, ViewChild, ViewContainerRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AlertService } from "../../../../auth/_services/alert.service";
import { AlertComponent } from "../../../../auth/_directives/alert.component";
import { TextMaskModule } from 'angular2-text-mask';
import { ImageUploadModule } from "angular2-image-upload";

import { Helpers } from '../../../../helpers';

import { LayoutModule } from '../../../layouts/layout.module';
import { DefaultComponent } from '../default.component';
import { FormsModule, Validator } from '@angular/forms';
import { Router, Routes, RouterModule } from "@angular/router";

import { ListcmsComponent } from './listcms/listcms.component';
import { AddcmsComponent } from './addcms/addcms.component';
import { EditcmsComponent } from './editcms/editcms.component';


const routes: Routes = [
    {
        "path": "",
        "component": DefaultComponent,
        "children": [
            {
                path: "",
                component: ListcmsComponent
            },
            {
                path: 'add',
                component: AddcmsComponent
            },
            {
                path: 'edit/:id',
                component: EditcmsComponent
            }
        ]
    }
];

@NgModule({
    imports: [
        CommonModule, RouterModule.forChild(routes), LayoutModule, FormsModule, TextMaskModule, ImageUploadModule.forRoot()
    ], exports: [
        RouterModule
    ],
    declarations: [ListcmsComponent, AddcmsComponent, EditcmsComponent]
})
export class CmspagesModule { }
