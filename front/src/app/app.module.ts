import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule} from '@angular/platform-browser/animations';
import { NgModule } from '@angular/core';

import { HttpClientJsonpModule } from '@angular/common/http';
import { HttpClientModule} from '@angular/common/http';
import { AppComponent } from './app.component';
import { AppRoutingModule } from './app-routing.module';
import { FormControl, FormsModule, ReactiveFormsModule } from "@angular/forms";
import { NgHttpLoaderModule } from 'ng-http-loader/ng-http-loader.module';

import {ToastModule} from 'ng2-toastr/ng2-toastr';
// custom-option.ts
import {ToastOptions} from 'ng2-toastr';

// import { InlineSVGModule } from 'ng-inline-svg';

export class CustomOption extends ToastOptions {
  // you can override any options available
  newestOnTop = true;
  showCloseButton = true;
  positionClass = 'toast-bottom-right';
}

import {AgmCoreModule} from '@agm/core';

import { MultiselectDropdownModule } from 'angular-2-dropdown-multiselect';

// import * as firebase from 'firebase';
// import { AngularFireDatabaseModule, AngularFireDatabase } from 'angularfire2/database';
// import { AngularFireAuthModule } from 'angularfire2/auth';
// import { AngularFireAuth } from 'angularfire2/auth';
// import { AngularFireModule } from 'angularfire2';
import { environmentfb, environment } from '.././environments/environment'
// firebase.initializeApp(environmentfb.firebaseConfig);
// import { MessagingService } from "./services/messaging.service";
import { ChatService } from './services/socketmsg.service';
import { SocketIoModule, SocketIoConfig } from 'ng-socket-io';

const config: SocketIoConfig = { url: 'http://192.168.1.9:5400/', options: {} };
import {CdkTableModule} from '@angular/cdk/table';
import {
  MatButtonModule,
  MatCheckboxModule,
  MatDialogModule,
  MatGridListModule,
  MatListModule,
  MatMenuModule,
  MatRadioModule,
  MatSelectModule,
  MatTabsModule,
  MatToolbarModule,
  MatSlideToggleModule,
  MatPaginator,
  MatSort,
  MatTableDataSource,
  MatTableModule,
  MatPaginatorModule,
  MatInputModule

} from '@angular/material';

import { MatDatepickerModule, MatNativeDateModule } from '@angular/material';
import {MatSortModule} from '@angular/material/sort';
import { FooterComponent, HeaderComponent, SharedModule} from './shared';
import { HomeComponent } from './home/home.component';
import { SignupComponent } from './signup/signup.component';
import { UserService } from './services/index';
import { ScriptLoaderService } from './services/script-loader.service';
import { LoginComponent } from './login/login.component';
import { ForgotComponent } from './forgot/forgot.component';
import { BoysignupComponent } from './boysignup/boysignup.component';
// External packages
import { ImageUploadModule } from "angular2-image-upload";
import {NgxPaginationModule} from 'ngx-pagination';
import { TextMaskModule } from 'angular2-text-mask';
import { VerificationComponent } from './verification/verification.component';
import { SingleProfileComponent } from './single-profile/single-profile.component';
import { EditProfileComponent } from './edit-profile/edit-profile.component';
import { MessageLisitngComponent } from './message-lisitng/message-lisitng.component';
import { UploadvideoComponent } from './uploadvideo/uploadvideo.component';
import { MessageDetailComponent } from './message-detail/message-detail.component';
import { FaqComponent } from './faq/faq.component';
import { AboutUsComponent } from './about-us/about-us.component';
import { ContatcUsComponent } from './contatc-us/contatc-us.component';
import { AddtestinomialComponent } from './addtestinomial/addtestinomial.component';
import { EditboyprofileComponent } from './editboyprofile/editboyprofile.component';
import { SearchtestinomialComponent } from './searchtestinomial/searchtestinomial.component';
import { UnlockpictureComponent } from './unlockpicture/unlockpicture.component';
import { OpenimageComponent } from './openimage/openimage.component';
import { TabsModule } from 'ngx-bootstrap/tabs';

@NgModule({
  declarations: [
    AppComponent,
    HomeComponent,
    FooterComponent,
    HeaderComponent,
    SignupComponent,
    LoginComponent,
    ForgotComponent,
    BoysignupComponent,
    VerificationComponent,
    SingleProfileComponent,
    EditProfileComponent,
    MessageLisitngComponent,
    UploadvideoComponent,
    MessageDetailComponent,
    FaqComponent,
    AboutUsComponent,
    ContatcUsComponent,
    AddtestinomialComponent,
    EditboyprofileComponent,
    SearchtestinomialComponent,
    UnlockpictureComponent,
    OpenimageComponent
  ],
  imports: [
    BrowserModule,
    BrowserAnimationsModule,
    FormsModule,
    ReactiveFormsModule,
    TabsModule.forRoot(),
    AppRoutingModule,
    SharedModule,
    MatButtonModule,
    MatCheckboxModule,
    MatDialogModule,
    MatGridListModule,
    MatListModule,
    MatMenuModule,
    MatRadioModule,
    MatSelectModule,
    MatTabsModule,
    MatToolbarModule,
    MatSlideToggleModule,
    TextMaskModule,
    ImageUploadModule.forRoot(),
    NgxPaginationModule,
    SocketIoModule.forRoot(config),
    MatTableModule,
    MatSortModule,
    MatPaginatorModule,
    MatInputModule,
    ToastModule.forRoot(),
    AgmCoreModule.forRoot({apiKey: 'AIzaSyAnzd44RW1D2v1bkATEGaTu6wGpCjtWyZQ', libraries: ["places"]}),
    HttpClientModule,
    NgHttpLoaderModule,
    // AngularFireDatabaseModule,
    // AngularFireAuthModule,
    // AngularFireModule.initializeApp({
    //   apiKey: "AIzaSyA4bQnf-dOVr9DZHGadMXm6sQImK-huQCM",
    //   authDomain: "datingwebsite-a9bda.firebaseapp.com",
    //   databaseURL: "https://datingwebsite-a9bda.firebaseio.com",
    //   projectId: "datingwebsite-a9bda",
    //   storageBucket: "datingwebsite-a9bda.appspot.com",
    //   messagingSenderId: "676102531451"
    // }),
    MultiselectDropdownModule,
    MatDatepickerModule, MatNativeDateModule
  ],
  entryComponents: [
    UploadvideoComponent,
    AddtestinomialComponent,
    SearchtestinomialComponent,
    UnlockpictureComponent,
    OpenimageComponent
  ],
  exports:[
    MatButtonModule,
    MatCheckboxModule,
    MatDialogModule,
    MatGridListModule,
    MatListModule,
    MatMenuModule,
    MatRadioModule,
    MatSelectModule,
    MatTabsModule,
    MatToolbarModule,
    CdkTableModule
  ],
  providers: [UserService, ScriptLoaderService, ChatService, {provide: ToastOptions, useClass: CustomOption}],
  bootstrap: [AppComponent]
})
export class AppModule { }
