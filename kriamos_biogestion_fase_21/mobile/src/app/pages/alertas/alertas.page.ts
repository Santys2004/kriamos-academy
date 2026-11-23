import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { IonicModule } from '@ionic/angular';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-alertas',
  standalone: true,
  imports: [CommonModule, IonicModule, RouterLink],
  templateUrl: './alertas.page.html'
})
export class AlertasPage implements OnInit {
  ngOnInit(): void {}
}
