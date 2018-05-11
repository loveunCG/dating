import {
  Component,
  OnInit,
  AfterViewInit,
  ViewChild,
  ElementRef,
  NgZone
} from '@angular/core';
import {
  ReactiveFormsModule,
  FormsModule,
  FormGroup,
  FormControl,
  Validators,
  FormBuilder,
  AbstractControl
} from '@angular/forms';
import {
  Router,
  ActivatedRoute,
  Params,
  NavigationEnd
} from '@angular/router';
import {
  MatDialog
} from '@angular/material';
import {
  SignupComponent
} from '../signup/signup.component';
import {
  LoginComponent
} from '../login/login.component';
import {
  BoysignupComponent
} from '../boysignup/boysignup.component';
import {
  UserService
} from '../services/index';
import {
  environment
} from '../../environments/environment';
import {
  PaginationInstance
} from '../../../node_modules/ngx-pagination/dist/ngx-pagination.module';
import {
  trigger,
  state,
  style,
  animate,
  transition
} from '@angular/animations';
import {
  UnlockpictureComponent
} from '../unlockpicture/unlockpicture.component';
import {
  OpenimageComponent
} from '../openimage/openimage.component';
import {
  IMultiSelectOption,
  IMultiSelectTexts,
  IMultiSelectSettings
} from 'angular-2-dropdown-multiselect';
import {
  HttpClientModule
} from '@angular/common/http';

declare let jquery: any;
declare let $: any;

import {
  AgmCoreModule,
  MapsAPILoader
} from '@agm/core';
import {
  ToastsManager
} from 'ng2-toastr/ng2-toastr';
import {} from '@types/googlemaps';

@Component({
  selector: 'app-home',
  templateUrl: './home.component.html',
  styleUrls: ['./home.component.css'],
  animations: [
    trigger('heroState', [
      transition('inactive => active', animate('100ms ease-in')),
      transition('active => inactive', animate('100ms ease-out')),
      transition('inactive => void', [
        animate(400, style({
          transform: 'translateY(-100%) scale(1)'
        }))
      ]),
      transition('active => void', [
        animate(400, style({
          transform: 'translateY(0) scale(0)'
        }))
      ])
    ])
  ],
  /*entryComponents: [
    SignupComponent,
  	LoginComponent,
    BoysignupComponent
  ]*/
})
export class HomeComponent implements OnInit {

  profiledata: any = [];
  test: number = 4;
  imageurl = environment.imageurl;
  innerWidth: any;
  highlight: any = {};
  profilepics: any;
  price: any;
  highlightcomments: number;
  recentist: any;
  selstate = 'South Australia';
  miles = [];
  model: any = {};
  heightvals = [];
  weightvals = [];
  loclat = 0;
  loclong = 0;
  noprofile = false;

  myform: FormGroup;
  searchControl: FormControl;
  name: FormControl;
  height: FormControl;
  weight: FormControl;
  radius: FormControl;
  selsuburb: FormControl;
  userid = '';
  searching = true;
  imgprice: any;
  actopenimg: any;
  imgtimeout: any;
  tempip: any;
  mySettings: IMultiSelectSettings = {
    enableSearch: false,
    checkedStyle: 'checkboxes',
    buttonClasses: 'btn-multiselect',
    dynamicTitleMaxItems: 2,
    displayAllSelectedText: true,
    closeOnSelect: false,
    maxHeight: '400px',
    containerClasses: 'search-field'
  };
  sublist: IMultiSelectOption[];
  sublitText: IMultiSelectTexts = {
    defaultTitle: 'Select suburbs',
    allSelected: 'All selected',
  };

  @ViewChild("hsearch")
  public searchElementRef: ElementRef;
  addresscomponents: any;
  public filter: string = '';
  public maxSize: number = 5;
  public directionLinks: boolean = true;
  public autoHide: boolean = false;
  public config: PaginationInstance = {
    id: 'advanced',
    itemsPerPage: 12,
    currentPage: 1
  };
  public labels: any = {
    previousLabel: 'Previous',
    nextLabel: 'Next',
    screenReaderPaginationLabel: 'Pagination',
    screenReaderPageLabel: 'page',
    screenReaderCurrentLabel: `You're on page`
  };

  ietrue: boolean = false;
  heights = [];
  constructor(private route: ActivatedRoute,
    private router: Router,
    public dialog: MatDialog,
    public userService: UserService,
    private mapsAPILoader: MapsAPILoader,
    private ngZone: NgZone,
    public toastr: ToastsManager) {
    if (navigator.userAgent.indexOf(".NET4.0E") != -1 || navigator.userAgent.indexOf(".NET4.0C") != -1) {
      this.ietrue = true;
    }
  }

  ngOnInit() {
    for (let hes = 123; hes < 244; hes++) {
      this.heights.push(hes);
    }



    this.userService.checkvisitor().subscribe(
      data => {
        //console.log(data);
        if (data.ip) {
          this.tempip = data.ip;
          this.userService.addvisitor(data.ip).subscribe(
            data => {
              if (!data.error) {
                console.log('added');
              }
            }, error => {

            }
          );
        }
      }, error => {

      }
    );
    this.userService.getsetting().subscribe(
      data => {
        this.imgprice = data.sdata.imgprice;
      }, error => {

      }
    );
    this.searchControl = new FormControl('');
    this.height = new FormControl('');
    this.weight = new FormControl('');
    this.radius = new FormControl('');
    this.name = new FormControl('');
    this.selsuburb = new FormControl('');

    this.myform = new FormGroup({
      searchControl: this.searchControl,
      radius: this.radius,
      height: this.height,
      weight: this.weight,
      name: this.name,
      selsuburb: this.selsuburb
    });

    this.getprofilelisting();
    this.gethighlightprofile();
    this.recentlisting();
    this.userService.getsuburbs(this.selstate)
      .subscribe(
        data => {
          if (data.error) {
            //this.toastr.error('Unable to fetch suburb at this time, try refreshing the page or choose another state.');
          } else {
            this.sublist = [];
            this.myform.controls['selsuburb'].setValue([]);
            this.sublitText = {
              defaultTitle: 'Select suburbs',
              allSelected: 'All selected',
            };
            this.sublist = data.data;

            //console.log(this.sublist);
          }
        },
        error => {
          //this.toastr.error('Unable to fetch suburb at this time, try refreshing the page or choose another state.');
        });

    this.innerWidth = window.innerWidth;
    if (innerWidth <= 991) {
      this.test = 2;
    }
    if (innerWidth >= 992) {
      this.test = 3;
    }
    if (innerWidth >= 1200) {
      this.test = 4;
    }


    if (innerWidth < 767) {
      this.test = 2;
    }

    // for(let i=200;i>9;i-10){
    //   this.miles.push(i);
    //
    // }
    let i = 200;
    do {
      this.miles.push(i);
      i = i - 10;
    } while (i > 9);

    //console.log(this.miles);

    let sq = "'";
    let dq = '"';

    for (let hf = 4; hf < 9; hf++) {
      let ch = hf + sq;
      this.heightvals.push(ch);
    }

    let stwv = 30;
    do {
      this.weightvals.push(stwv);
      stwv = stwv + 10;
    } while (stwv < 101);

  }

  ngAfterViewInit() {

    this.mapsAPILoader.load().then(() => {
      //console.log('google places');
      if (typeof (google) !== undefined) {
        //console.log('google');
      }

      setTimeout(() => {

      }, 0);
      let autocomplete = new google.maps.places.Autocomplete(this.searchElementRef.nativeElement, {
        types: ['(cities)']
      });
      autocomplete.setComponentRestrictions({
        'country': ['au']
      });
      autocomplete.addListener("place_changed", () => {
        this.ngZone.run(() => {
          //get the place result
          let place: google.maps.places.PlaceResult = autocomplete.getPlace();

          //verify result
          if (place.geometry === undefined || place.geometry === null) {
            return;
          }

          //console.log(place);
          let componentForm = {
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

          for (let i = 0; i < place.address_components.length; i++) {


            let addressType = place.address_components[i].types[0];
            if (componentForm[addressType]) {
              let val = place.address_components[i][componentForm[addressType]];

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

          // console.log('selstate', this.selstate);
          // console.log('state', state);

          if (state !== this.selstate) {
            this.loclat = 0;
            this.loclong = 0;

            this.myform.controls['searchControl'].setValue('');

            this.toastr.error('Please select location within selected state');
          } else {
            if (st_number) {
              addr = addr.concat(st_number);
            }

            if (route) {
              addr = addr.concat(route);
            }

            if (nh) {
              addr = addr.concat(nh);
            }

            this.loclat = place.geometry.location.lat();
            this.loclong = place.geometry.location.lng();

            this.myform.controls['searchControl'].setValue(place.name);
          }

        });
      });
    });

  }

  onsubChange(event) {
    //console.log(event);
  }

  changestate(val) {
    this.searching = true;
    //console.log(this.selstate);
    this.selstate = val;
    this.myform.controls['searchControl'].setValue('');
    this.userService.getsuburbs(this.selstate)
      .subscribe(
        data => {
          if (data.error) {
            this.sublist = [];
            this.myform.controls['selsuburb'].setValue([]);
          } else {
            this.sublist = [];
            this.myform.controls['selsuburb'].setValue([]);
            this.sublist = data.data;
          }
        },
        error => {
        }, () => {
          this.getprofilelisting();
        });

  }
  onPageChange(number: number) {
    this.config.currentPage = number;
    $('html, body').animate({
        scrollTop: $(".profile-listing").offset().top
      },
      600);
  }

  recentlisting() {
    this.userService.recentlisting()
      .subscribe(
        data => {
          if (data.error) {} else {
            this.recentist = data.data;
            console.log(this.recentist);
          }
        },
        error => {});

  }
  gethighlightprofile() {
    this.userService.gethighlight()
      .subscribe(
        data => {
          if (data.error) {} else {
            this.highlight = data.data;
            console.log('--------------', this.highlight);
          }
        },
        error => {});
  }
  clearfilters() {
    this.searching = true;
    this.myform.reset();
    this.myform.controls['weight'].setValue('');
    this.myform.controls['height'].setValue('');
    this.myform.controls['radius'].setValue('');
    this.loclat = 0;
    this.loclong = 0;
    this.getprofilelisting();
  }
  unlockimg(pos, type, username) {

    clearTimeout(this.imgtimeout);

    let price = this.imgprice;
    let unlockid = 0;
    let name = username;

    if (type == 1) {
      unlockid = this.profiledata[pos].id;

    } else if (type == 2) {
      //price = this.recentist[pos].profile_pic[0].price;
      unlockid = this.recentist[pos].id;

    } else {
      //price = this.highlight.profile_pic[pos].price;
      unlockid = this.highlight.id;

    }

    if (localStorage.getItem("currentUser") === null) {
      this.toastr.error('To Unlock Photo please Register and Login.');
    } else {
      let balance = 0;
      let currentUser = JSON.parse(localStorage.getItem("currentUser"));
      let user = currentUser;
      this.userid = user.id;
      this.userService.checkWallet(this.userid)
        .subscribe(
          data => {
            if (data.error) {

            } else {
              balance = data.stat.amount;
              if (balance > price) {
                const dialogRef = this.dialog.open(UnlockpictureComponent, {
                  data: {
                    uid: this.userid,
                    amount: price,
                    unlockid: unlockid,
                    username: name
                  },
                  height: '250px',
                  width: '30%'
                });
                dialogRef.afterClosed().subscribe(result => {
                  //console.log(result);
                  if (result == 1) {
                    let openimg = '';
                    if (type == 1) {
                      this.profiledata[pos].profile_pic[0].lock = false;
                      openimg = this.imageurl + this.profiledata[pos].profile_pic[0].image;
                      setTimeout(() => {
                        this.profiledata[pos].profile_pic[0].lock = true
                      }, 30000);
                    } else if (type == 2) {
                      this.recentist[pos].profile_pic[0].lock = false;
                      openimg = this.imageurl + this.recentist[pos].profile_pic[0].image;
                      setTimeout(() => {
                        this.recentist[pos].profile_pic[0].lock = true
                      }, 30000);
                    } else {
                      this.highlight.profile_pic[pos].lock = false;
                      openimg = this.imageurl + this.highlight.profile_pic[pos].image;
                      setTimeout(() => {
                        this.highlight.profile_pic[pos].lock = true
                      }, 30000);
                    }
                    this.actopenimg = openimg;
                    //console.log('imgafter',openimg);
                    // const dialogRef = this.dialog.open(OpenimageComponent,{
                    // 	data: {image:openimg},
                    // 		height: '100%',width:'40%'
                    // 	});
                    $('#imgmodel').show();
                    this.imgtimeout = setTimeout(() => {
                      $('#imgmodel').hide();
                      this.actopenimg = '';
                    }, 30000);
                  }

                });

              } else {
                this.toastr.error('Not enough balance in wallet, add money to wallet by choosing a package');
              }
            }
          },
          error => {
            this.toastr.error('Something went wrong');
          });


    }

  }

  closeimg() {
    $('#imgmodel').hide();
  }

  getprofilelisting() {
    this.noprofile = false;
    this.searching = true;
    let state = this.selstate;
    let name = this.myform.value.name;
    let weight = this.myform.value.weight;
    let height = this.myform.value.height;
    let lat = 0;
    let long = 0;
    if (this.myform.value.searchControl != '') {
      lat = this.loclat;
      long = this.loclong;
    }

    let radius = this.myform.value.radius;
    let sub = this.myform.value.searchControl;
    //console.log('sub',sub);

    this.userService.getlisting(state, sub, name, weight, height, lat, long, radius)
      .subscribe(
        data => {
          if (data.error) {
            this.searching = false;
            this.profiledata = {};
            //console.log(this.profiledata);
            this.profilepics = {};

            this.noprofile = true;

          } else {
            this.searching = false;
            this.noprofile = false;
            this.profiledata = data.data;
            //console.log(this.profiledata);
            this.profilepics = this.profiledata.profile_pic;
            this.price = this.profiledata[0].profile_pic[0].price;
          }
        },
        error => {
          this.searching = false;
          this.profiledata = {};
          //console.log(this.profiledata);
          this.profilepics = {};

          this.noprofile = true;
        });
  }

  onResize(event: any) {
    const element = event.target.innerWidth;
    if (element <= 991) {
      this.test = 2;
    }

    if (element >= 992) {
      this.test = 3;
    }

    if (element >= 1200) {
      this.test = 4;
    }

    if (element < 767) {
      this.test = 1;
    }
  }
}
