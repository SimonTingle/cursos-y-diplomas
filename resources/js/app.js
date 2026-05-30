import './bootstrap';

import Alpine from 'alpinejs';
import calendarApp from './calendar';

window.Alpine = Alpine;

Alpine.data('calendarApp', calendarApp);

Alpine.start();
