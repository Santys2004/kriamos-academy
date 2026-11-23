import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from './environment';
import { AuthService } from './auth.service';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class ApiService {
  constructor(private http: HttpClient, private auth: AuthService) {}

  async get<T>(path: string): Promise<T> {
    const headers = await this.headers();
    return firstValueFrom(this.http.get<T>(environment.apiBaseUrl + path, { headers }));
  }

  async post<T>(path: string, payload: any): Promise<T> {
    const headers = await this.headers();
    return firstValueFrom(this.http.post<T>(environment.apiBaseUrl + path, payload, { headers }));
  }

  private async headers(): Promise<HttpHeaders> {
    const token = await this.auth.token();
    let headers = new HttpHeaders({ 'Content-Type': 'application/json' });

    if (token) {
      headers = headers.set('Authorization', 'Bearer ' + token);
    }

    return headers;
  }
}
