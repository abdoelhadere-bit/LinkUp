import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

if (!window.AlpineStarted) {
  window.AlpineStarted = true;
  Alpine.start();
}
