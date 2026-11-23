import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { IonicModule } from '@ionic/angular';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-sincronizacion',
  standalone: true,
  imports: [CommonModule, IonicModule, RouterLink],
  templateUrl: './sincronizacion.page.html'
})
export class SincronizacionPage implements OnInit {
  ngOnInit(): void {}
}
