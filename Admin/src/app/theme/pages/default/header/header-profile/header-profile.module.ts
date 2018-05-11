import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Routes, RouterModule } from '@angular/router';
import { HeaderProfileComponent } from './header-profile.component';
import { LayoutModule } from '../../../../layouts/layout.module';
import { DefaultComponent } from '../../default.component';

import { FormsModule, Validator } from '@angular/forms';

import { ImageUploadModule } from "angular2-image-upload";

const routes: Routes = [
    {
        "path": "",
        "component": DefaultComponent,
        "children": [
            {
                "path": "",
                "component": HeaderProfileComponent
            }
        ]
    }
];
@NgModule({
    imports: [
        CommonModule, RouterModule.forChild(routes), LayoutModule, FormsModule, ImageUploadModule.forRoot()
    ], exports: [
        RouterModule
    ], declarations: [
        HeaderProfileComponent
    ]
})
export class HeaderProfileModule {



}
