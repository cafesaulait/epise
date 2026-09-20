import { ComponentFixture, TestBed } from '@angular/core/testing';
import { PolitiqueConfidentialites } from './politique-confidentialites';

describe('PolitiqueConfidentialites', () => {
  let component: PolitiqueConfidentialites;
  let fixture: ComponentFixture<PolitiqueConfidentialites>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [PolitiqueConfidentialites],
    }).compileComponents();

    fixture = TestBed.createComponent(PolitiqueConfidentialites);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
