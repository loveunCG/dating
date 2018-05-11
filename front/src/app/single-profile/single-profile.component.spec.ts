import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { SingleProfileComponent } from './single-profile.component';

describe('SingleProfileComponent', () => {
  let component: SingleProfileComponent;
  let fixture: ComponentFixture<SingleProfileComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ SingleProfileComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SingleProfileComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
