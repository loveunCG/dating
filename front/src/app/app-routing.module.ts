import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Routes } from '@angular/router';

import { HomeComponent } from './home/home.component';
import { SignupComponent } from './signup/signup.component';
import { LoginComponent } from './login/login.component';
import { ForgotComponent } from './forgot/forgot.component';
import { BoysignupComponent } from './boysignup/boysignup.component';
import { VerificationComponent } from './verification/verification.component';
import { SingleProfileComponent } from './single-profile/single-profile.component';
import { EditProfileComponent } from './edit-profile/edit-profile.component';
import { MessageLisitngComponent } from './message-lisitng/message-lisitng.component';
import { MessageDetailComponent } from './message-detail/message-detail.component';
import { FaqComponent } from './faq/faq.component';
import { AboutUsComponent } from './about-us/about-us.component';
import { ContatcUsComponent } from './contatc-us/contatc-us.component';
import { EditboyprofileComponent } from './editboyprofile/editboyprofile.component';

const routes: Routes = [
  { path: '', component: HomeComponent, pathMatch: 'full' },
  { path: 'home', component: HomeComponent },
  { path: 'girlsignup', component: SignupComponent },
  { path: 'boysignup', component: BoysignupComponent },
  { path: 'login', component: LoginComponent },
  { path: 'forgot', component: ForgotComponent },
  { path: 'verify', component: VerificationComponent },
  { path: 'girl/:id', component: SingleProfileComponent },
  { path: 'edit-girlprofile/:id', component: EditProfileComponent },
  { path: 'edit-boyprofile/:id', component: EditboyprofileComponent },
  { path: 'messagelist', component: MessageLisitngComponent },
  { path: 'messages/:id', component: MessageDetailComponent },
  { path: 'messages', component: MessageDetailComponent },
  { path: 'faq', component: FaqComponent },
  { path: 'about-us', component: AboutUsComponent },
  { path: 'contact-us', component: ContatcUsComponent }
];

@NgModule({
  imports: [
    CommonModule,
    RouterModule.forRoot(routes,{useHash:true})
  ],
  exports: [ RouterModule ],
  declarations: []
})

export class AppRoutingModule {
 }
