import './bootstrap';

import Alpine from 'alpinejs';

import { initShell } from './ui/shell';
import { toast } from './ui/toast';
import { fetchJson } from './ui/fetch';

window.Alpine = Alpine;

Alpine.start();



// Expose minimal API for later AJAX modules
window.App = {
  toast,
  fetchJson,
  initShell
};

document.addEventListener('DOMContentLoaded', () => {
  initShell();
});