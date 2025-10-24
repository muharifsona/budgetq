import './bootstrap';
import Alpine from 'alpinejs';
import Sortable from 'sortablejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;

// buat helper untuk dipakai di komponen
window.makeSortable = (el, options={}) => new Sortable(el, options);
window.Chart = Chart;

Alpine.start();
