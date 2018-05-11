import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { BoysignupComponent } from './boysignup.component';

describe('BoysignupComponent', () => {
  let component: BoysignupComponent;
  let fixture: ComponentFixture<BoysignupComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ BoysignupComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(BoysignupComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
