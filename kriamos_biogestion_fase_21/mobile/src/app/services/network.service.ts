import { Injectable } from '@angular/core';
import { Network } from '@capacitor/network';
import { BehaviorSubject } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class NetworkService {
  online$ = new BehaviorSubject<boolean>(true);

  async init(): Promise<void> {
    const status = await Network.getStatus();
    this.online$.next(status.connected);

    Network.addListener('networkStatusChange', status => {
      this.online$.next(status.connected);
    });
  }
}
