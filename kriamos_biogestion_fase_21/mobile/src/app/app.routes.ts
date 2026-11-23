import { Routes } from '@angular/router';
import { LoginPage } from './pages/login/login.page';
import { DashboardPage } from './pages/dashboard/dashboard.page';
import { FormatosPage } from './pages/formatos/formatos.page';
import { AlertasPage } from './pages/alertas/alertas.page';
import { DocumentosPage } from './pages/documentos/documentos.page';
import { SincronizacionPage } from './pages/sincronizacion/sincronizacion.page';
import { IaPage } from './pages/ia/ia.page';

export const routes: Routes = [
  { path: '', redirectTo: 'dashboard', pathMatch: 'full' },
  { path: 'login', component: LoginPage },
  { path: 'dashboard', component: DashboardPage },
  { path: 'formatos', component: FormatosPage },
  { path: 'alertas', component: AlertasPage },
  { path: 'documentos', component: DocumentosPage },
  { path: 'sincronizacion', component: SincronizacionPage },
  { path: 'ia', component: IaPage }
];
