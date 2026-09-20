import { ComponentFixture, TestBed } from '@angular/core/testing';
import { Creationcompte } from './creationcompte';

describe('Creationcompte', () => {
  let component: Creationcompte;
  let fixture: ComponentFixture<Creationcompte>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Creationcompte],
    }).compileComponents();

    fixture = TestBed.createComponent(Creationcompte);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
