import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { IonicModule } from '@ionic/angular';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-formatos',
  standalone: true,
  imports: [CommonModule, IonicModule, RouterLink],
  templateUrl: './formatos.page.html'
})
export class FormatosPage implements OnInit {
  ngOnInit(): void {}
}
