import { Injectable } from '@angular/core';
import { StorageService } from './storage.service';
import { ApiService } from './api.service';

export interface OfflineItem {
  id: string;
  endpoint: string;
  payload: any;
  estado: 'pendiente' | 'sincronizado' | 'error';
  createdAt: string;
  error?: string;
}

@Injectable({ providedIn: 'root' })
export class SyncService {
  private queueKey = 'kriamos_mobile_sync_queue';

  constructor(private storage: StorageService, private api: ApiService) {}

  async add(endpoint: string, payload: any): Promise<void> {
    const queue = await this.queue();
    queue.push({
      id: crypto.randomUUID(),
      endpoint,
      payload,
      estado: 'pendiente',
      createdAt: new Date().toISOString()
    });
    await this.storage.set(this.queueKey, queue);
  }

  async queue(): Promise<OfflineItem[]> {
    return await this.storage.get<OfflineItem[]>(this.queueKey) || [];
  }

  async pendingCount(): Promise<number> {
    const queue = await this.queue();
    return queue.filter(item => item.estado === 'pendiente' || item.estado === 'error').length;
  }

  async syncAll(): Promise<{ enviados: number; errores: number }> {
    const queue = await this.queue();
    let enviados = 0;
    let errores = 0;

    for (const item of queue) {
      if (item.estado === 'sincronizado') continue;

      try {
        await this.api.post(item.endpoint, item.payload);
        item.estado = 'sincronizado';
        enviados++;
      } catch (error: any) {
        item.estado = 'error';
        item.error = error.message || 'Error sincronizando';
        errores++;
      }
    }

    await this.storage.set(this.queueKey, queue);
    return { enviados, errores };
  }
}
