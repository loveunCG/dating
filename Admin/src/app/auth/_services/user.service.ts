import { Injectable } from "@angular/core";
import { Headers, Http, RequestOptions, Response } from "@angular/http";
import { Helpers } from "../../helpers";

import { User } from "../_models/index";

@Injectable()
export class UserService {
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
        return this.http.get(Helpers.apipath + 'getuser/' + id).map((response: Response) => response.json());
    }

    getsubadmin(id: any) {
        return this.http.get(Helpers.apipath + 'getsubadmin/' + id).map((response: Response) => response.json());
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

    getNotifications(adminid: number, lastlogin: any) {
        return this.http.post(Helpers.apipath + 'getadminnotify', JSON.stringify({ id: adminid, lastlogin: lastlogin })).map((response: Response) => response.json());
    }

    updateProfile(userdata: any) {
        return this.http.post(Helpers.apipath + 'updateProfile', userdata).map((response: Response) => response.json());
    }

    updatesubprofile(userdata: any) {
        return this.http.post(Helpers.apipath + 'subprofileupdate', userdata).map((response: Response) => response.json());
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

    addtowallet(amount: any, userid: any) {
        return this.http.post(Helpers.apipath + 'addtowallet', { amount: amount, id: userid }).map((response: Response) => response.json());
    }

    createsubadmin(userdata: any) {
        return this.http.post(Helpers.apipath + 'addsubadmin', userdata).map((response: Response) => response.json());
    }

    update(user: any) {
        return this.http.post(Helpers.apipath + 'updateuser', user).map((response: Response) => response.json());
    }

    updatesubadmin(user: any) {
        return this.http.post(Helpers.apipath + 'updatesubadmin', user).map((response: Response) => response.json());
    }

    delete(id: number) {
        return this.http.delete('/api/users/' + id, this.jwt()).map((response: Response) => response.json());
    }

    adminearnings() {
        return this.http.get(Helpers.apipath + 'getadminearning').map((response: Response) => response.json());
    }

    visitors() {
        return this.http.get(Helpers.apipath + 'totalvisitors').map((response: Response) => response.json());
    }

    getAllUsers() {
        return this.http.get(Helpers.apipath + 'getallusers').map((response: Response) => response.json());
    }

    getGirlUsers() {
        return this.http.get(Helpers.apipath + 'getgirlusers').map((response: Response) => response.json());
    }

    chatmsgs(cid: any, start: any) {
        return this.http.get(Helpers.apipath + 'admincmsgs/' + cid + '/' + start).map((response: Response) => response.json());
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
