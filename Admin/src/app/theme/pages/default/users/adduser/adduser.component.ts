import { Component, OnInit, AfterViewInit, ComponentFactoryResolver, ViewChild, ViewContainerRef, ViewEncapsulation, ElementRef, NgZone } from '@angular/core';
import { UserService } from "../../../../.././auth/_services/user.service";
import { ScriptLoaderService } from '../../../../.././_services/script-loader.service';
import { ReactiveFormsModule, FormsModule, FormGroup, FormControl, AbstractControl } from '@angular/forms';
import { Router, RouterLink } from "@angular/router";

import { AlertService } from "../../../../../auth/_services/alert.service";
import { AlertComponent } from "../../../../../auth/_directives/alert.component";

import { Helpers } from '../../../../../helpers';

import { AgmCoreModule, MapsAPILoader } from '@agm/core';
import { } from '@types/googlemaps';

@Component({
    selector: '.m-grid__item.m-grid__item--fluid.m-wrapper',
    templateUrl: "./adduser.component.html",
    encapsulation: ViewEncapsulation.None,
})
export class AdduserComponent implements OnInit {

    model: any = {};
    loading = false;
    mask: any[] = ['+', '6', '1', '-', '0', /[1-9]/, /\d/, /\d/, '-', /\d/, /\d/, /\d/, '-', /\d/, /\d/, /\d/];

    uploadpath = Helpers.logoFavPath + 'profile';
    uploadfavimg: any = {};
    profileboy = false;
    profilegirl = true;

    myform: FormGroup;
    searchControl: FormControl;
    locstate: FormControl;
    loclat: any;
    loclong: any;
    suburb = '';

    @ViewChild('alertAddUser', { read: ViewContainerRef }) alertAddUser: ViewContainerRef;
    @ViewChild("hsearch") public searchElementRef: ElementRef;

    addresscomponents: any;

    constructor(private _userService: UserService,
        private _script: ScriptLoaderService,
        private _alertService: AlertService,
        private cfr: ComponentFactoryResolver,
        private _router: Router,
        private mapsAPILoader: MapsAPILoader,
        private ngZone: NgZone) {
    }

    ngOnInit() {
        if (localStorage.getItem("admin") === null) {
            this._router.navigate(['/login']);
        }
        this.model.status = 1;
        this.model.regtype = 'Female';
        // this._userService.getuserlist()
        //   .subscribe(
        //     data => {
        //       //console.log(data);
        //
        //     },
        //     error => {
        //
        //     });
        this.locstate = new FormControl('');
        this.searchControl = new FormControl('');

    }

    ngAfterViewInit() {
        this._script.load('.m-grid__item.m-grid__item--fluid.m-wrapper',
            'assets/app/js/changeusertype.js');

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
                    this.model.locstate = state;
                    this.model.searchControl = addr;
                    this.model.lat = this.loclat;
                    this.model.long = this.loclong;
                    this.model.suburb = this.suburb;
                    // this.locstate.setValue(state);

                });
            });
        });
    }

    addUser(form: any) {
        Helpers.setLoading(true);
        this.loading = true;
        for (var key in this.uploadfavimg) {
            // skip loop if the property is from prototype
            // console.log(this.uploadfavimg[key]);
            this.uploadfavimg[key].price = this.model.picprice;
            this.uploadfavimg[key].enlock = this.model.islocked;
        }
        let tuser = this.model.regtype;
        this.model.images = this.uploadfavimg;
        this.model.adminadd = true;
        if (this.model.regtype == 'boy') {
            this.model.regtype = 'Male';
        }
        if (this.model.regtype == 'Girl') {
            this.model.regtype = 'Female';
        }

        this._userService.create(this.model)
            .subscribe(
            data => {
                //console.log(data);
                Helpers.setLoading(false);
                this.loading = false;
                this.showAlert('alertAddUser');
                if (data.code == 1) {
                    this._alertService.success("User created successfully");
                    form.resetForm();
                    this.model.regtype = tuser;
                    $(".img-ul-clear").eq(0).trigger('click');
                }
                if (data.code == 2) {
                    this._alertService.error("Something went wrong");
                }
                if (data.code == 3) {
                    this._alertService.error("Email already registered, enter different email address");
                }

            },
            error => {
                Helpers.setLoading(false);
                this.loading = false;
                this.showAlert('alertAddUser');
                this._alertService.error("Somthing went wrong");
            });
    }

    showAlert(target) {
        this[target].clear();
        let factory = this.cfr.resolveComponentFactory(AlertComponent);
        let ref = this[target].createComponent(factory);
        ref.changeDetectorRef.detectChanges();
    }

    onUploadFinishedFav(file: any) {
        // console.log(file.file);
        this.uploadfavimg[file.file.name] = {};
        var currentimg: any = {};

        currentimg.data = JSON.stringify(file.src);
        currentimg.price = this.model.picprice + '$';
        currentimg.enlock = this.model.islocked;
        //console.log(currentimg);
        this.uploadfavimg[file.file.name] = currentimg;
        // console.log(this.uploadfavimg);
    }

    onRemovedFav(file: any) {

        // do some stuff with the removed file.
        delete this.uploadfavimg[file.file.name];
        // console.log(this.uploadfavimg);
    }

    onUploadStateChangedFav(state: boolean) {
        // console.log(JSON.stringify(state));
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

    changelocked(val) {
        // console.log('locked val:');
        // console.log(val);
    }

}
