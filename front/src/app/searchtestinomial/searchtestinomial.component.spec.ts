import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { SearchtestinomialComponent } from './searchtestinomial.component';

describe('SearchtestinomialComponent', () => {
  let component: SearchtestinomialComponent;
  let fixture: ComponentFixture<SearchtestinomialComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ SearchtestinomialComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SearchtestinomialComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
