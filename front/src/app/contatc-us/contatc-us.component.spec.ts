import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ContatcUsComponent } from './contatc-us.component';

describe('ContatcUsComponent', () => {
  let component: ContatcUsComponent;
  let fixture: ComponentFixture<ContatcUsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ContatcUsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ContatcUsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
