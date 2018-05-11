import { Component,OnInit } from '@angular/core';
import { UserService } from '../../services/index';
import { Router,ActivatedRoute ,Params,NavigationEnd} from '@angular/router';
@Component({
  selector: 'layout-footer',
  templateUrl: './footer.component.html',
  styleUrls: ['./footer.component.css']
})
export class FooterComponent implements OnInit {
	setting:any={};
  cmspages:any={};
  currentUrl='';

  footerclass = '';
  constructor(private route: ActivatedRoute,
			  private router: Router,
        		public userService:UserService) 
  {

   router.events.subscribe((event: any) => {
          if (event instanceof NavigationEnd ) {
            this.currentUrl=event.url;
            // this.girlid=this.currentUrl.replace('/edit-girlprofile/','');
            
            if(this.currentUrl == '/'){
              this.footerclass = 'homeclass';
            } else{
              this.footerclass = 'normalfooter';
            }
          }
        });
  }

   ngOnInit() {
   	this.getsetting();
     this.getcmspages();
   }

   getcmspages(){
     this.userService.getcmspages()
    .subscribe(
      data =>{
        if(data.error){}
        else
        {
         this.cmspages=data;
        // console.log(this.cmspages);
        }
      },
      error =>{});
   }

   getsetting(){
   	this.userService.getsetting()
    .subscribe(
      data =>{
        if(data.error){}
        else
        {
         this.setting=data.sdata;
        // console.log(this.setting);
        }
      },
      error =>{});
   }
}
