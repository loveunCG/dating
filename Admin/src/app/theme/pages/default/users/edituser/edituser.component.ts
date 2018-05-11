import { Component, OnInit, AfterViewInit, ComponentFactoryResolver, ViewChild, ViewContainerRef, ViewEncapsulation, ElementRef, NgZone } from '@angular/core';
import { UserService } from "../../../../.././auth/_services/user.service";
import { ScriptLoaderService } from '../../../../.././_services/script-loader.service';
import { ReactiveFormsModule, FormsModule, FormGroup, FormControl, AbstractControl } from '@angular/forms';
import { Router, RouterLink, NavigationEnd } from "@angular/router";

import { AlertService } from "../../../../../auth/_services/alert.service";
import { AlertComponent } from "../../../../../auth/_directives/alert.component";

import { Helpers } from '../../../../../helpers';

import { AgmCoreModule, MapsAPILoader } from '@agm/core';
import { } from '@types/googlemaps';

@Component({
    selector: '.m-grid__item.m-grid__item--fluid.m-wrapper',
    templateUrl: "./edituser.component.html",
    encapsulation: ViewEncapsulation.None,
})
export class EdituserComponent implements OnInit {

    model: any = {};
    loading = false;
    mask: any[] = ['+', '6', '1', '-', '0', /[1-9]/, /\d/, /\d/, '-', /\d/, /\d/, /\d/, '-', /\d/, /\d/, /\d/];

    uploadpath = Helpers.logoFavPath + 'profile';
    uploadfavimg: any = {};
    profileboy = true;
    profilegirl = false;
    currentUrl = '';
    userid: any;
    usergirl = false;
    userboy = false;
    currentimages = [];
    newimages = false;

    searchControl: FormControl;
    locstate: FormControl;
    loclat: any;
    loclong: any;
    suburb = '';

    @ViewChild('alertAddUser', { read: ViewContainerRef }) alertAddUser: ViewContainerRef;
    @ViewChild("hsearch") public searchElementRef: ElementRef;

    constructor(private _userService: UserService,
        private _script: ScriptLoaderService,
        private _alertService: AlertService,
        private cfr: ComponentFactoryResolver,
        private _router: Router,
        private mapsAPILoader: MapsAPILoader,
        private ngZone: NgZone) {

        _router.events.subscribe((event: any) => {
            if (event instanceof NavigationEnd) {
                this.currentUrl = event.url;
                //console.log(this.currentUrl);
                let urldata = this.currentUrl.split("/");
                //console.log(urldata);
                let pid = urldata[3];
                this.userid = pid;
            }
        });
    }

    ngOnInit() {
        if (localStorage.getItem("admin") === null) {
            this._router.navigate(['/login']);
        }

        this.locstate = new FormControl('');
        this.searchControl = new FormControl('');

        Helpers.setLoading(true);
        this.loading = true;
        this._userService.getById(this.userid)
            .subscribe(
            data => {
                //console.log(data);
                Helpers.setLoading(false);
                this.loading = false;
                this.showAlert('alertAddUser');
                if (data.error == false) {
                    this.model = data.sdata;
                    if (this.model.gender == 'Female') {
                        this.usergirl = true;
                        this.model.regtype = 'Female';
                        this.searchControl.setValue(this.model.location);
                        //let ci = {};
                        //this.currentimages.push(this.uploadpath);
                        // let allcis = {};
                        // for(let img of this.model.images)
                        // {
                        //   console.log(img);
                        //     ci['filename']=img;
                        //     ci['url']=this.uploadpath;
                        //     this.currentimages.push(ci);
                        // }

                        //console.log(this.currentimages);
                        this.currentimages = this.model.profile_pic;
                        // let price = this.model.profile_pic[0].price.split('$');
                        // console.log(price);
                        // this.model.picprice = parseFloat(price[0]);
                        // this.model.islocked = this.model.profile_pic[0].lock;
                    } else {
                        this.userboy = true;
                        this.model.regtype = 'Male';
                        this.currentimages = this.model.profile_pic;
                    }

                }
                else {
                    this._alertService.error("Something went wrong");
                }


            },
            error => {
                Helpers.setLoading(false);
                this.loading = false;
                this.showAlert('alertAddUser');
                this._alertService.error("Something went wrong");
            });

    }

    ngAfterViewInit() {
        this._script.load('.m-grid__item.m-grid__item--fluid.m-wrapper',
            'assets/app/js/changeusertype.js');

        if (this.model.gender == 'Female') {
            setTimeout(() => {
                this.mapsAPILoader.load().then(() => {
                    //console.log('google places');
                    if (typeof (google) !== undefined) {
                        //console.log('google');
                    }
                    let autocomplete = new google.maps.places.Autocomplete(this.searchElementRef.nativeElement, {
                        types: ['(cities)']
                    });
                    autocomplete.setComponentRestrictions(
                        {
                            'country': ['au']
                        }
                    );
                    autocomplete.addListener("place_changed", () => {
                        this.ngZone.run(() => {
                            //get the place result
                            let place: google.maps.places.PlaceResult = autocomplete.getPlace();

                            //verify result
                            if (place.geometry === undefined || place.geometry === null) {
                                return;
                            }

                            //console.log(place);
                            var componentForm = {
                                street_number: 'short_name',
                                route: 'long_name',
                                locality: 'long_name',
                                administrative_area_level_1: 'long_name',
                                administrative_area_level_2: 'short_name',
                                country: 'long_name',
                                postal_code: 'short_name',
                            };
                            let route = '';
                            let nh = '';
                            let loc = '';
                            let state = '';
                            let st_number = '';
                            //let county = place.address_components[3].long_name ? place.address_components[3].long_name:'';

                            let addr = '';

                            for (var i = 0; i < place.address_components.length; i++) {


                                var addressType = place.address_components[i].types[0];
                                if (componentForm[addressType]) {
                                    var val = place.address_components[i][componentForm[addressType]];

                                    if (addressType == 'locality') {
                                        loc = val;
                                    }

                                    if (addressType == 'country') {

                                    }

                                    if (addressType == 'administrative_area_level_1') {
                                        state = val;
                                    }

                                    if (addressType == 'administrative_area_level_2') {
                                        nh = val;
                                    }

                                    if (addressType == 'street_number') {

                                        st_number = val + ' ';
                                    }

                                    if (addressType == 'route') {
                                        route = val + ' ';
                                    }
                                }
                            }

                            if (st_number) {
                                addr = addr.concat(st_number);
                            }

                            if (route) {
                                addr = addr.concat(route);
                            }
                            if (loc) {
                                this.suburb = loc;
                            }
                            if (nh) {
                                addr = addr.concat(nh);
                            }

                            this.loclat = place.geometry.location.lat();
                            this.loclong = place.geometry.location.lng();

                            // this.formControl['searchControl'].setValue(addr);
                            // this.myform.controls['locstate'].setValue(state);
                            // console.log('addr',addr);
                            this.searchControl.setValue(addr);
                            this.model.state = state;
                            this.model.location = addr;
                            this.model.lat = this.loclat;
                            this.model.long = this.loclong;
                            this.model.suburb = this.suburb;
                            // this.locstate.setValue(state);

                        });
                    });
                });
            }, 4000);
        }
    }

    addUser(form: any) {
        Helpers.setLoading(true);
        this.loading = true;
        let tuser = this.model.regtype;


        var keys = Object.keys(this.uploadfavimg);
        var len = keys.length;
        //console.log('upimgl:'+len);

        for (var key in this.uploadfavimg) {
            // skip loop if the property is from prototype
            // console.log(this.uploadfavimg[key]);
            this.uploadfavimg[key].price = this.model.picprice;
            this.uploadfavimg[key].enlock = this.model.islocked;
        }

        this.model.images = this.uploadfavimg;
        this.model.oldimages = this.currentimages;
        this._userService.update(this.model)
            .subscribe(
            data => {
                //console.log(data);
                Helpers.setLoading(false);
                this.loading = false;
                this.showAlert('alertAddUser');
                if (data.code == 1) {
                    this._alertService.success("User updated successfully");

                    this.model.regtype = tuser;
                    $(".img-ul-clear").eq(0).trigger('click');
                    //window.location.reload();
                    if (data.newimages == true) {
                        this.currentimages = data.imgsare;
                    }
                }
                if (data.code == 2) {
                    this._alertService.error("Email already registered, enter different email address");
                }
                if (data.code == 3) {
                    this._alertService.error("Something went wrong");
                }

            },
            error => {
                Helpers.setLoading(false);
                this.loading = false;
                this.showAlert('alertAddUser');
                this._alertService.error("Something went wrong");
            });
    }

    showAlert(target) {
        this[target].clear();
        let factory = this.cfr.resolveComponentFactory(AlertComponent);
        let ref = this[target].createComponent(factory);
        ref.changeDetectorRef.detectChanges();
    }

    onUploadFinishedFav(file: any) {
        console.log(file.file);

        this.uploadfavimg[file.file.name] = {};
        var currentimg: any = {};

        currentimg.data = JSON.stringify(file.src);
        currentimg.price = this.model.picprice + '$';
        currentimg.enlock = this.model.islocked;

        this.uploadfavimg[file.file.name] = currentimg;
    }

    onRemovedFav(file: any) {

        // do some stuff with the removed file.
        delete this.uploadfavimg[file.file.name];
        console.log(this.uploadfavimg);
    }

    onUploadStateChangedFav(state: boolean) {
        console.log(JSON.stringify(state));
    }

    changeType(val) {
        if (this.model.regtype == 'Male') {
            this.profileboy = true;
            this.profilegirl = false;
        }
        if (this.model.regtype == 'Female') {
            this.profileboy = false;
            this.profilegirl = true;
        }
    }

    removeimage(val) {
        //console.log('index:'+val);
        this.currentimages.splice(val, 1);
    }

}
