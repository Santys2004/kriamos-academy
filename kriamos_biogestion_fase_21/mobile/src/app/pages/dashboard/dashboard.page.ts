import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { IonicModule } from '@ionic/angular';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, IonicModule, RouterLink],
  templateUrl: './dashboard.page.html'
})
export class DashboardPage implements OnInit {
  ngOnInit(): void {}
}
