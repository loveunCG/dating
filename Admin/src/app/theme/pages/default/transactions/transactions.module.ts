import { Component, OnInit, ViewEncapsulation, AfterViewInit, NgModule, ComponentFactoryResolver, ViewChild, ViewContainerRef } from '@angular/core';
import { Helpers } from '../../../../helpers';
import { CommonModule } from '@angular/common';
import { LayoutModule } from '../../../layouts/layout.module';
import { DefaultComponent } from '../default.component';
import { FormsModule, Validator } from '@angular/forms';
import { Router, Routes, RouterModule } from "@angular/router";
import { TransactionlistComponent } from './transactionlist/transactionlist.component';

const routes: Routes = [
    {
        "path": "",
        "component": DefaultComponent,
        "children": [
            {
                path: "",
                component: TransactionlistComponent
            },
            {
                path: ":id",
                component: TransactionlistComponent
            }
        ]
    }
];

@NgModule({
    imports: [
        CommonModule, RouterModule.forChild(routes), LayoutModule, FormsModule
    ],
    declarations: [TransactionlistComponent]
})
export class TransactionsModule { }
