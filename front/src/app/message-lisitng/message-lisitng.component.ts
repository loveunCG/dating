import { Component, OnInit, ViewChild } from '@angular/core';
import { Router, ActivatedRoute,NavigationEnd, Params } from '@angular/router';

import {MatPaginator, MatSort, MatTableDataSource} from '@angular/material';
import {MatSortModule} from '@angular/material/sort';
import {MatDatepickerModule} from '@angular/material/datepicker';

import {Observable} from 'rxjs/Observable';
import {merge} from 'rxjs/observable/merge';
import {of as observableOf} from 'rxjs/observable/of';
import {catchError} from 'rxjs/operators/catchError';
import {map} from 'rxjs/operators/map';
import {startWith} from 'rxjs/operators/startWith';
import {switchMap} from 'rxjs/operators/switchMap';

import { HttpClient } from '@angular/common/http';

import {Http,Headers,RequestOptions,Response} from '@angular/http';

import { environment } from '../../environments/environment';

@Component({
  selector: 'app-message-lisitng',
  templateUrl: './message-lisitng.component.html',
  styleUrls: ['./message-lisitng.component.css']
})
export class MessageLisitngComponent implements OnInit {
  displayedColumns = ['cdate', 'tousername', 'lastmsg'];

  transactiondata : GetTransactionData | null;

  dataSource = new MatTableDataSource();

  resultsLength = 0;
  isLoadingResults = true;
  isRateLimitReached = false;

  fromuserid:any;

  @ViewChild(MatPaginator) paginator: MatPaginator;
  @ViewChild(MatSort) sort: MatSort;

  constructor(public http:HttpClient,private route: ActivatedRoute,private router: Router) {
    if (localStorage.getItem("currentUser") === null) {
        this.router.navigate(['login']);
    }

    var currentUser = JSON.parse(localStorage.getItem("currentUser"));
    var user = currentUser;
    this.fromuserid = user.id;

  }

  ngAfterViewInit() {
    // this.dataSource.paginator = this.paginator;
    // this.dataSource.sort = this.sort;
  }

  ngOnInit() {
    this.loadTable();
  }

  click(toid){
    this.router.navigate(['/messages/'+toid]);
  }

  loadTable(){
    // Transation Log starts
    console.log('before');
    this.transactiondata = new GetTransactionData(this.http);
    console.log('after');
    // If the user changes the sort order, reset back to the first page.
    // this.sort.sortChange.subscribe(() => this.paginator.pageIndex = 0);

    this.getLoadTable();
    //Transation Log ends
  }

  getLoadTable(){
    merge(this.paginator.page)
      .pipe(
        startWith({}),
        switchMap(() => {
          this.isLoadingResults = true;

          return this.transactiondata!.getData(
            this.sort.active, this.sort.direction, this.paginator.pageIndex, this.paginator.pageSize, this.fromuserid);
        }),
        map(data => {
          // Flip flag to show that loading has finished.
          this.isLoadingResults = false;
          this.isRateLimitReached = false;
          this.resultsLength = data.total_count;
          console.log('resultsLength sec',this.resultsLength);
          return data.items;
        }),
        catchError(() => {
          this.isLoadingResults = false;
          // Catch if the API has reached its rate limit. Return empty data.
          this.isRateLimitReached = true;
          return observableOf([]);
        })
      ).subscribe(data => this.dataSource.data = data);
  }

}

export interface ApiReturn {
  items: TableForm[];
  total_count: number;
}

export interface TableForm {
  cdate: string;
  tousername: string;
  lastmsg: string;
}

export class GetTransactionData {
  constructor(private http: HttpClient) {}

  getData(sort: string, order: string, page: number, perpage:any, userid:any): Observable<ApiReturn> {
    //const href = 'https://api.github.com/search/issues';
    const requestUrl = environment.apiUrl+'getchatmlist/'+userid+'/'+page+'/'+10;

    return this.http.get<ApiReturn>(requestUrl);
  }
}
