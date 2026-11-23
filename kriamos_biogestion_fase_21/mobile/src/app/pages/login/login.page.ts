import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { IonicModule, AlertController } from '@ionic/angular';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [CommonModule, IonicModule, FormsModule],
  templateUrl: './login.page.html'
})
export class LoginPage {
  email = '';
  password = '';
  loading = false;

  constructor(private auth: AuthService, private router: Router, private alert: AlertController) {}

  async login(): Promise<void> {
    this.loading = true;
    try {
      const response = await this.auth.login(this.email, this.password);
      if (response.ok) {
        await this.router.navigateByUrl('/dashboard');
      } else {
        await this.showAlert(response.message || 'No fue posible iniciar sesión.');
      }
    } catch (error: any) {
      await this.showAlert(error.message || 'Error de conexión.');
    }
    this.loading = false;
  }

  private async showAlert(message: string): Promise<void> {
    const alert = await this.alert.create({
      header: 'Ingreso no permitido',
      message,
      buttons: ['Aceptar']
    });
    await alert.present();
  }
}
