{{--
    Shared "khata" (ledger book) PDF letterhead - logo, company name, report
    title, generated-on timestamp, and a repeating page-number footer. Used
    by every ledger/statement PDF (Customer Ledger, Supplier Ledger, Account
    Ledger, Day Book, Trial Balance, Receivable, Payable, Profit & Loss) so
    they all share one consistent look.

    dompdf-safe CSS only: no flexbox/grid (dompdf doesn't support them), a
    table for the header layout instead, position:fixed with a negative
    top/bottom offset matching @page's margin for the repeating header/footer
    (the standard dompdf technique for content that repeats on every page),
    and CSS counter(page)/counter(pages) for page numbers (dompdf supports
    CSS paged-media counters natively).

    Props: title (string) - the report name, e.g. "Customer Ledger Statement"
    Slot: the report's own content (normally a table.ledger).
--}}
@props(['title'])
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page {
        margin: 135px 28px 65px 28px;
    }
    body {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 11px;
        color: #1a1a1a;
    }
    .khata-header {
        position: fixed;
        top: -120px;
        left: 0;
        right: 0;
        height: 110px;
    }
    .khata-header table { width: 100%; border-collapse: collapse; }
    .khata-header td { vertical-align: middle; }
    .khata-logo-cell { width: 64px; }
    .khata-logo-cell img { max-width: 60px; max-height: 60px; }
    .khata-company { font-size: 19px; font-weight: bold; color: #16326b; }
    .khata-tagline { font-size: 9px; color: #777; }
    .khata-title-block { text-align: right; }
    .khata-title { font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; color: #16326b; }
    .khata-generated { font-size: 9px; color: #777; margin-top: 2px; }
    .khata-meta { font-size: 10px; color: #333; margin-top: 8px; }
    .khata-meta strong { color: #111; }
    .khata-rule { border-top: 2px solid #16326b; margin-top: 8px; }

    .khata-footer {
        position: fixed;
        bottom: -55px;
        left: 0;
        right: 0;
        height: 40px;
        font-size: 9px;
        color: #777;
        border-top: 1px solid #ccc;
        padding-top: 5px;
    }
    .khata-footer table { width: 100%; }
    .khata-footer .page-num:after {
        content: "Page " counter(page) " of " counter(pages);
    }

    table.ledger { width: 100%; border-collapse: collapse; margin-top: 4px; }
    table.ledger th, table.ledger td {
        border: 1px solid #999;
        padding: 4px 6px;
        font-size: 10px;
    }
    table.ledger thead th {
        background: #e8ecf7;
        text-align: left;
        font-weight: bold;
        color: #16326b;
    }
    table.ledger td.num, table.ledger th.num { text-align: right; }
    table.ledger tr.opening-row td { background: #f7f7f0; font-weight: bold; }
    table.ledger tr.closing-row td { background: #f7f7f0; font-weight: bold; border-top: 2px solid #16326b; }
    table.ledger tr.total-row td { background: #eef1fa; font-weight: bold; }
</style>
</head>
<body>
    <div class="khata-header">
        <table>
            <tr>
                @if($logo = \App\Helpers\PdfHelper::companyLogoDataUri())
                <td class="khata-logo-cell"><img src="{{ $logo }}"></td>
                @endif
                <td>
                    <div class="khata-company">{{ \App\Helpers\PdfHelper::companyName() }}</div>
                </td>
                <td class="khata-title-block">
                    <div class="khata-title">{{ $title }}</div>
                    <div class="khata-generated">Generated: {{ now()->format('d-M-Y h:i A') }}</div>
                </td>
            </tr>
        </table>
        <div class="khata-rule"></div>
        {{ $meta ?? '' }}
    </div>

    <div class="khata-footer">
        <table><tr>
            <td>{{ \App\Helpers\PdfHelper::companyName() }} - system-generated statement, no signature required.</td>
            <td style="text-align:right;" class="page-num"></td>
        </tr></table>
    </div>

    {{ $slot }}
</body>
</html>
