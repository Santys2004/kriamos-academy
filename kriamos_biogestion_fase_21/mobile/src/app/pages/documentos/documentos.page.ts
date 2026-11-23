import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { IonicModule } from '@ionic/angular';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-documentos',
  standalone: true,
  imports: [CommonModule, IonicModule, RouterLink],
  templateUrl: './documentos.page.html'
})
export class DocumentosPage implements OnInit {
  ngOnInit(): void {}
}
