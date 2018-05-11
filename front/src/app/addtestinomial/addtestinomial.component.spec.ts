import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddtestinomialComponent } from './addtestinomial.component';

describe('AddtestinomialComponent', () => {
  let component: AddtestinomialComponent;
  let fixture: ComponentFixture<AddtestinomialComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddtestinomialComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddtestinomialComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
