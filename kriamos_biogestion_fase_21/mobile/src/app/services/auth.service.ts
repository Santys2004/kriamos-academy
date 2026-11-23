import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { StorageService } from './storage.service';
import { environment } from './environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class AuthService {
  private tokenKey = 'kriamos_mobile_token';

  constructor(private http: HttpClient, private storage: StorageService) {}

  async login(email: string, password: string): Promise<any> {
    const response = await firstValueFrom(
      this.http.post<any>(environment.apiBaseUrl + environment.mobileApiVersion + '/login', { email, password })
    );

    if (response.ok && response.token) {
      await this.storage.set(this.tokenKey, response.token);
    }

    return response;
  }

  async token(): Promise<string | null> {
    return this.storage.get<string>(this.tokenKey);
  }

  async logout(): Promise<void> {
    await this.storage.remove(this.tokenKey);
  }
}
