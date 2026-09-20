import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ChangerMdp } from './changer-mdp';

describe('ChangerMdp', () => {
  let component: ChangerMdp;
  let fixture: ComponentFixture<ChangerMdp>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ChangerMdp],
    }).compileComponents();

    fixture = TestBed.createComponent(ChangerMdp);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
