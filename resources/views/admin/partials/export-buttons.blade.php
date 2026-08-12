{{--
    $showPdf: set to false on any page that already has its own (better,
    khata-styled) PDF button - avoids two different "PDF" buttons doing two
    different things on the same page.
--}}
<div class="flex flex-wrap gap-2">
    <a href="{{ route('admin.exports.csv', ['type' => $type] + request()->all()) }}"
       class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition-colors duration-200">
        <i class="fas fa-file-csv mr-1"></i> CSV
    </a>
    <a href="{{ route('admin.exports.excel', ['type' => $type] + request()->all()) }}"
       class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200">
        <i class="fas fa-file-excel mr-1"></i> Excel
    </a>
    @if($showPdf ?? true)
    <a href="{{ route('admin.exports.pdf', ['type' => $type] + request()->all()) }}"
       class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors duration-200">
        <i class="fas fa-file-pdf mr-1"></i> PDF
    </a>
    @endif
</div>