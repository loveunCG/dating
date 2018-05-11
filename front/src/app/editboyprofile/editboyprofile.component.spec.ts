import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditboyprofileComponent } from './editboyprofile.component';

describe('EditboyprofileComponent', () => {
  let component: EditboyprofileComponent;
  let fixture: ComponentFixture<EditboyprofileComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditboyprofileComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditboyprofileComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
