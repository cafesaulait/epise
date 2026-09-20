import { ComponentFixture, TestBed } from '@angular/core/testing';
import { Don } from './don';

describe('Don', () => {
  let component: Don;
  let fixture: ComponentFixture<Don>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Don],
    }).compileComponents();

    fixture = TestBed.createComponent(Don);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
