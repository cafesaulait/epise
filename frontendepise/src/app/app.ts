import { Component, OnInit, signal } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { Header } from './header/header';
import { Footer } from './footer/footer';
import { AuthService } from './core/services/auth.service';

@Component({
  imports: [RouterOutlet, Header, Footer],
  selector: 'app-root',
  styleUrl: './app.scss',
  templateUrl: './app.html',
})
export class App implements OnInit {
  protected readonly title = signal('frontendepise');

  constructor(private auth: AuthService) {}

  ngOnInit(): void {
    this.auth.chargerSession().subscribe();
  }
}