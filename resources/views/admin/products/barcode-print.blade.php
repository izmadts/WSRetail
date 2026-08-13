<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Barcode Labels</title>
    @vite(['resources/js/app.js'])
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 10mm; }
        .toolbar { margin-bottom: 10px; }
        .toolbar button {
            padding: 6px 14px; border-radius: 6px; border: 1px solid #ccc;
            background: #fff; cursor: pointer; font-size: 13px;
        }
        .sheet {
            display: grid;
            grid-template-columns: repeat({{ (int) $posSettings->barcode_columns_per_row }}, 1fr);
            gap: 2mm;
        }
        .label {
            width: {{ $posSettings->barcode_label_width_mm }}mm;
            height: {{ $posSettings->barcode_label_height_mm }}mm;
            border: 1px dashed #ccc;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 1mm;
            text-align: center;
        }
        .label .name { font-size: 9px; line-height: 1.1; margin-bottom: 1px; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .label .price { font-size: 9px; font-weight: bold; margin-top: 1px; }
        .label svg { max-width: 100%; }
        @media print {
            .toolbar { display: none; }
            body { margin: 0; padding: 5mm; }
            .label { border: none; }
        }
    </style>
</head>
<body>
    <div class="toolbar"><button type="button" onclick="window.print()">Print</button></div>

    <div class="sheet">
        @foreach($labels as $product)
        <div class="label">
            @if($posSettings->barcode_show_name)
            <div class="name">{{ $product->name }}</div>
            @endif
            <svg class="barcode-svg" data-value="{{ $product->barcode ?: $product->code }}"></svg>
            @if($posSettings->barcode_show_price)
            <div class="price">Rs. {{ number_format($product->sale_price, 2) }}</div>
            @endif
        </div>
        @endforeach
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var format = @json($posSettings->barcode_format);
            document.querySelectorAll('.barcode-svg').forEach(function (el) {
                var value = el.dataset.value;
                try {
                    JsBarcode(el, value, { format: format, width: 1.3, height: 28, fontSize: 9, margin: 2 });
                } catch (e) {
                    // Not every product code is valid under EAN13/CODE39's
                    // stricter input rules (e.g. non-numeric for EAN13) -
                    // CODE128 accepts arbitrary text, so it's the safe
                    // fallback rather than leaving the label blank.
                    try {
                        JsBarcode(el, value, { format: 'CODE128', width: 1.3, height: 28, fontSize: 9, margin: 2 });
                    } catch (e2) {
                        el.outerHTML = '<p style="font-size:9px;color:#c00;">Invalid code: ' + value + '</p>';
                    }
                }
            });
        });
    </script>
</body>
</html>
