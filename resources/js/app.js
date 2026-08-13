import Alpine from 'alpinejs';
import JsBarcode from 'jsbarcode';
// import Livewire from 'livewire';

// ✅ Prevent multiple Alpine instances
window.Alpine = Alpine;
window.JsBarcode = JsBarcode;

// ✅ Start Alpine
Alpine.start();

// ✅ Livewire setup
// Livewire.start();

// DataTable configuration
document.addEventListener('DOMContentLoaded', function () {
    if (typeof $.fn.DataTable !== 'undefined') {
        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                zeroRecords: "No matching records found"
            }
        });
    }
});