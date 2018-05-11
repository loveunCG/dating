import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { OpenimageComponent } from './openimage.component';

describe('OpenimageComponent', () => {
  let component: OpenimageComponent;
  let fixture: ComponentFixture<OpenimageComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ OpenimageComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(OpenimageComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
