import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { ThemeComponent } from './theme/theme.component';
import { LayoutModule } from './theme/layouts/layout.module';
import { BrowserAnimationsModule } from "@angular/platform-browser/animations";
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { ScriptLoaderService } from "./_services/script-loader.service";

import { ThemeRoutingModule } from "./theme/theme-routing.module";
import { AuthModule } from "./auth/auth.module";
import { CmspageService } from "./auth/_services/cmspage.service"

import { ImageUploadModule } from "angular2-image-upload";

import { ToastModule } from 'ng2-toastr/ng2-toastr';
import * as firebase from 'firebase';

import { AngularFireAuthModule } from 'angularfire2/auth';
import { AngularFireAuth } from 'angularfire2/auth';
import { AngularFireModule } from 'angularfire2';
import { environmentfb } from '.././environments/environment'
firebase.initializeApp(environmentfb.firebaseConfig);
// import { AngularFireDatabaseModule, AngularFireDatabase } from 'angularfire2/database';
import { MessagingService } from "./_services/messaging.service";

@NgModule({
    declarations: [
        ThemeComponent,
        AppComponent,
    ],
    imports: [
        LayoutModule,
        BrowserModule,
        BrowserAnimationsModule,
        AppRoutingModule,
        ThemeRoutingModule,
        AuthModule,
        FormsModule,
        ReactiveFormsModule,
        ImageUploadModule.forRoot(),
        ToastModule.forRoot(),
        // AngularFireDatabaseModule,
        AngularFireAuthModule,
        AngularFireModule.initializeApp({
            apiKey: "AIzaSyA4bQnf-dOVr9DZHGadMXm6sQImK-huQCM",
            authDomain: "datingwebsite-a9bda.firebaseapp.com",
            databaseURL: "https://datingwebsite-a9bda.firebaseio.com",
            projectId: "datingwebsite-a9bda",
            storageBucket: "datingwebsite-a9bda.appspot.com",
            messagingSenderId: "676102531451"
        })
    ],
    providers: [ScriptLoaderService, CmspageService],
    bootstrap: [AppComponent]
})
export class AppModule { }
