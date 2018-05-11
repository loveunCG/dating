import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { MessageLisitngComponent } from './message-lisitng.component';

describe('MessageLisitngComponent', () => {
  let component: MessageLisitngComponent;
  let fixture: ComponentFixture<MessageLisitngComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ MessageLisitngComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(MessageLisitngComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
