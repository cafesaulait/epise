import { ComponentFixture, TestBed } from '@angular/core/testing';
import { MesDons } from './mes-dons';

describe('MesDons', () => {
  let component: MesDons;
  let fixture: ComponentFixture<MesDons>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [MesDons],
    }).compileComponents();

    fixture = TestBed.createComponent(MesDons);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
