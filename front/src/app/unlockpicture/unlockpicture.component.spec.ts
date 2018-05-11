import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { UnlockpictureComponent } from './unlockpicture.component';

describe('UnlockpictureComponent', () => {
  let component: UnlockpictureComponent;
  let fixture: ComponentFixture<UnlockpictureComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ UnlockpictureComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(UnlockpictureComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
