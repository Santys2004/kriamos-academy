import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { IonicModule } from '@ionic/angular';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-ia',
  standalone: true,
  imports: [CommonModule, IonicModule, RouterLink],
  templateUrl: './ia.page.html'
})
export class IaPage implements OnInit {
  ngOnInit(): void {}
}
