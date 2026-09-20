import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ModifierInfos } from './modifier-infos';

describe('ModifierInfos', () => {
  let component: ModifierInfos;
  let fixture: ComponentFixture<ModifierInfos>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ModifierInfos],
    }).compileComponents();

    fixture = TestBed.createComponent(ModifierInfos);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
