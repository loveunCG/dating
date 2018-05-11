import { Injectable } from "@angular/core";
import { Http, Response } from "@angular/http";
import "rxjs/add/operator/map";
import { Helpers } from "../../helpers";

@Injectable()
export class AuthenticationService {

    constructor(private http: Http) {
    }

    login(email: string, password: string) {
        return this.http.post(Helpers.apipath + 'adminLogin', JSON.stringify({ email: email, password: password }))
            .map((response: Response) => {
                //console.log(response);
                // login successful if there's a jwt token in the response
                let user = response.json();
                //console.log(user.error);
                if (!user.error) {
                    // store user details and jwt token in local storage to keep user logged in between page refreshes
                    localStorage.setItem('admin', JSON.stringify(user));
                }
            });
    }

    updateToken(token: any, id: any) {
        return this.http.post(Helpers.apipath + 'updateatoken', JSON.stringify({ token: token, aid: id }))
            .map((response: Response) => {

            });
    }

    logout() {
        // remove user from local storage to log user out
        var userlocal = JSON.parse(localStorage.getItem("admin"));
        //console.log(userlocal);

        var adminid = userlocal.token;
        localStorage.removeItem('admin');
        this.http.get(Helpers.apipath + 'updatelogout/' + adminid).map((response: Response) => response.json());
    }
}
