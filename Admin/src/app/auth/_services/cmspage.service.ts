import { Injectable } from "@angular/core";
import { Headers, Http, RequestOptions, Response } from "@angular/http";
import { Helpers } from "../../helpers";

import { User } from "../_models/index";

@Injectable()
export class CmspageService {
    constructor(private http: Http) {
    }

    verify() {
        return this.http.get('/api/verify', this.jwt()).map((response: Response) => response.json());
    }

    forgotPassword(email: string) {
        return this.http.post(Helpers.apipath + 'forgotPassAdmin', JSON.stringify({ email })).map((response: Response) => response.json());
    }

    getAll() {
        return this.http.get('/api/users', this.jwt()).map((response: Response) => response.json());
    }

    getById(id: number) {
        return this.http.get(Helpers.apipath + 'getcmspage/' + id).map((response: Response) => response.json());
    }

    getuserlist(start: any = 0, limit: any = 10) {
        //return this.http.get('/api/userslist/').map((response: Response) => response.json());
    }

    getSiteInfo() {
        return this.http.get(Helpers.apipath + 'siteInfo').map((response: Response) => response.json());
    }

    getAdmininfo(adminid: number) {
        return this.http.get(Helpers.apipath + 'adminInfo/' + adminid).map((response: Response) => response.json());
    }

    updateProfile(userdata: any) {
        return this.http.post(Helpers.apipath + 'updateProfile', userdata).map((response: Response) => response.json());
    }

    updateGenSets(userdata: any) {
        return this.http.post(Helpers.apipath + 'updateGenSets', userdata).map((response: Response) => response.json());
    }

    updateFooter(userdata: any) {
        return this.http.post(Helpers.apipath + 'updatefooter', userdata).map((response: Response) => response.json());
    }

    create(userdata: any) {
        return this.http.post(Helpers.apipath + 'register', userdata).map((response: Response) => response.json());
    }

    update(user: any) {
        return this.http.post(Helpers.apipath + 'updatecmspage', user).map((response: Response) => response.json());
    }

    delete(id: number) {
        return this.http.delete('/api/users/' + id, this.jwt()).map((response: Response) => response.json());
    }

    // private helper methods

    private jwt() {
        // create authorization header with jwt token
        let currentUser = JSON.parse(localStorage.getItem('admin'));
        if (currentUser && currentUser.token) {
            let headers = new Headers({ 'Authorization': 'Bearer ' + currentUser.token });
            return new RequestOptions({ headers: headers });
        }
    }
}
