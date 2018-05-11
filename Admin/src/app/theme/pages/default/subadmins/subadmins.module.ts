import { Component, OnInit, ViewEncapsulation, AfterViewInit, NgModule, ComponentFactoryResolver, ViewChild, ViewContainerRef } from '@angular/core';
import { Helpers } from '../../../../helpers';
import { CommonModule } from '@angular/common';
import { LayoutModule } from '../../../layouts/layout.module';
import { DefaultComponent } from '../default.component';
import { FormsModule, Validator } from '@angular/forms';
import { Router, Routes, RouterModule } from "@angular/router";
import { AddsubadminComponent } from './addsubadmin/addsubadmin.component';
import { ListsubadminComponent } from './listsubadmin/listsubadmin.component';
import { EditsubadminComponent } from './editsubadmin/editsubadmin.component';

import { AlertService } from "../../../../auth/_services/alert.service";
import { AlertComponent } from "../../../../auth/_directives/alert.component";

const routes: Routes = [
    {
        "path": "",
        "component": DefaultComponent,
        "children": [
            {
                path: "",
                component: ListsubadminComponent
            },
            {
                path: 'add',
                component: AddsubadminComponent
            },
            {
                path: 'edit/:id',
                component: EditsubadminComponent
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
    declarations: [AddsubadminComponent, ListsubadminComponent, EditsubadminComponent]
})
export class SubadminsModule { }
