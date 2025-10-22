@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <h1 class="text-2xl font-semibold mb-4">Trial Balance (Plain)</h1>

    @if($selectedPeriod)
        <p class="mb-2 text-sm text-gray-600">
            Showing balances for period: {{ $selectedPeriod->GLP_SEQUENCE }}/{{ $selectedPeriod->GLP_YEAR }}
            ({{ \Carbon\Carbon::parse($selectedPeriod->GLP_ST_DATE)->format('M j, Y') }} - {{ \Carbon\Carbon::parse($selectedPeriod->GLP_EN_DATE)->format('M j, Y') }})
        </p>
    @endif

    <form method="get" class="mb-4 flex items-end space-x-2">
        <div>
            <label>Period</label>
            <select name="period" class="border rounded px-2 py-1">
                @foreach($periods ?? [] as $p)
                    <option value="{{ $p->GLP_KEY }}" {{ ($selectedPeriod && $selectedPeriod->GLP_KEY == $p->GLP_KEY) ? 'selected' : '' }}>
                        {{ $p->GLP_SEQUENCE }}/{{ $p->GLP_YEAR }} ({{ \Carbon\Carbon::parse($p->GLP_ST_DATE)->format('M Y') }})
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded">Load</button>
        </div>
    </form>

    <!-- DataTables (striped rows) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <style>
        /* numeric columns: fixed width so DR/CR and balances align (no wrap) */
    .col-num { box-sizing: border-box; width:140px; min-width:120px; max-width:160px; text-align:right; white-space:nowrap; }
        /* text columns: allow wrapping so table expands by content (main table) */
        .col-text { min-width:120px; max-width:9999px; word-break:break-word; white-space:normal; }
        /* main table uses automatic layout so columns expand based on content; modal/detail tables keep fixed layout */
        #tb-trial { table-layout: auto; }
        #detail-table, #entries-table { table-layout: fixed; }
        /* keep type column in detail modal truncated to avoid overlapping numbers */
        #detail-table th.col-type, #detail-table td.col-type { max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        /* Fixed width for DR and CR columns in modal tables to ensure equal width */
        #detail-table th:nth-child(4), #detail-table td:nth-child(4),
        #detail-table th:nth-child(5), #detail-table td:nth-child(5),
        #entries-table th:nth-child(3), #entries-table td:nth-child(3),
        #entries-table th:nth-child(4), #entries-table td:nth-child(4) {
            width: 120px;
            min-width: 100px;
            max-width: 140px;
            text-align: right;
            white-space: nowrap;
        }
    </style>

    <style>
        /* small account column for Entries modal */
        .col-account { width:100px; min-width:80px; max-width:120px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    </style>

    <div class="overflow-auto">
        <table id="tb-trial" class="display stripe" style="width:100%">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2 py-1 col-text">Account</th>
                    <th class="border px-2 py-1 col-text">Name</th>
                    <th class="border px-2 py-1 col-num">Opening Dr</th>
                    <th class="border px-2 py-1 col-num">Opening Cr</th>
                    <th class="border px-2 py-1 col-num">Movement Dr</th>
                    <th class="border px-2 py-1 col-num">Movement Cr</th>
                    <th class="border px-2 py-1 text-center">Detail</th>
                    <th class="border px-2 py-1 col-num">Balance Dr</th>
                    <th class="border px-2 py-1 col-num">Balance Cr</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows ?? [] as $r)
                    <tr>
                        <td class="border px-2 py-1 col-text" title="{{ $r['account_number'] }}">{{ $r['account_number'] }}</td>
                        <td class="border px-2 py-1 col-text" title="{{ $r['account_name'] }}">{{ $r['account_name'] }}</td>
                        <td class="border px-2 py-1 col-num">{{ number_format($r['opening_debit'],2) }}</td>
                        <td class="border px-2 py-1 col-num">{{ number_format($r['opening_credit'],2) }}</td>
                        <td class="border px-2 py-1 col-num">{{ number_format($r['movement_debit'],2) }}</td>
                        <td class="border px-2 py-1 col-num">{{ number_format($r['movement_credit'],2) }}</td>
                        <td class="border px-2 py-1 text-center"><button class="detail-btn bg-gray-200 px-2 py-1 rounded" data-account="{{ $r['account_number'] }}" data-account-name="{{ $r['account_name'] }}">Details</button></td>
                        <td class="border px-2 py-1 col-num">{{ number_format($r['balance_debit'],2) }}</td>
                        <td class="border px-2 py-1 col-num">{{ number_format($r['balance_credit'],2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div id="detail-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5);">
    <div style="background:#fff; padding:1rem; width:95%; max-width:1200px; margin:0 auto; border-radius:6px; max-height:90vh; overflow:auto;">
            <div class="flex justify-between items-center mb-2">
                <div style="white-space:nowrap; display:flex; align-items:center; gap:1rem;">
                    <h3 id="detail-title" class="text-lg font-semibold" style="margin:0;">Detail</h3>
                    <div id="detail-subheader" class="text-sm text-gray-600" style="display:inline-block;">Account: <span id="detail-account"></span> — <span id="detail-account-name"></span> | Branch: <span id="detail-branch"></span></div>
                </div>
                <button id="detail-close" class="px-2 py-1 bg-gray-200 rounded">Close</button>
            </div>
            <div>
                <style>
                    /* prevent wrapping in modal tables */
                    #detail-table th, #detail-table td, #entries-table th, #entries-table td { white-space: nowrap; }
                    /* larger account text for detail header and entries links */
                    #detail-account { font-weight: 700; font-size: 1.05rem; }
                    .entries-account { font-weight:700; color: #1f2937; text-decoration: underline; cursor: pointer; }
                    /* constrain Type column to avoid overlapping numbers; show ellipsis */
                    th.col-type, td.col-type { max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
                    /* Fixed width for DR and CR columns in modal tables to ensure equal width */
                    #detail-table th:nth-child(4), #detail-table td:nth-child(4),
                    #detail-table th:nth-child(5), #detail-table td:nth-child(5),
                    #entries-table th:nth-child(3), #entries-table td:nth-child(3),
                    #entries-table th:nth-child(4), #entries-table td:nth-child(4) {
                        width: 120px;
                        min-width: 100px;
                        max-width: 140px;
                        text-align: right;
                        white-space: nowrap;
                    }
                </style>
                <table id="detail-table" class="min-w-full display stripe" style="table; width:100%;">
                    <thead>
                        <tr>
                            <th class="border px-2 py-1">Date</th>
                            <th class="border px-2 py-1">Ref</th>
                            <th class="border px-2 py-1 col-type">Type</th>
                            <th class="border px-2 py-1 text-right">Dr</th>
                            <th class="border px-2 py-1 text-right">Cr</th>
                            <th class="border px-2 py-1">Remark</th>
                            <th class="border px-2 py-1">Branch</th>
                        </tr>
                    </thead>
                    <tbody id="detail-body"></tbody>
                    <tfoot>
                        <tr>
                            <!-- colspan = total columns (7) - 2 (DR/CR) = 5 -->
                            <td class="border px-2 py-1 font-semibold" colspan="3">SubTotal</td>
                            <td class="border px-2 py-1 font-semibold text-right" id="detail-sub-dr" style="text-align:right;"></td>
                            <td class="border px-2 py-1 font-semibold text-right" id="detail-sub-cr" style="text-align:right;"></td>
                            <td class="border px-2 py-1 font-semibold" colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Nested entries modal -->
    <div id="entries-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5);">
    <div style="background:#fff; padding:1rem; width:95%; max-width:1200px; margin:0 auto; border-radius:6px; max-height:90vh; overflow:auto;">
            <div class="flex justify-between items-center mb-2">
                <h3 id="entries-title" class="text-lg font-semibold">Entries</h3>
                <button id="entries-close" class="px-2 py-1 bg-gray-200 rounded">Close</button>
            </div>
                <div>
                <table id="entries-table" class="min-w-full display stripe" style="table-layout:fixed; width:100%;">
                    <thead>
                        <tr>
                            <th class="border px-2 py-1 col-account">Account</th>
                            <th class="border px-2 py-1">Name</th>
                            <th class="border px-2 py-1 text-right">Dr</th>
                            <th class="border px-2 py-1 text-right">Cr</th>
                            <th class="border px-2 py-1">Source</th>
                        </tr>
                    </thead>
                    <tbody id="entries-body"></tbody>
                    <tfoot>
                        <tr>
                            <!-- colspan = total columns (5) - 2 (DR/CR) = 3 -->
                                <td class="border px-2 py-1 font-semibold" colspan="2">Total</td>
                                <td class="border px-2 py-1 font-semibold text-right" id="entries-sub-dr" style="text-align:right;"></td>
                                <td class="border px-2 py-1 font-semibold text-right" id="entries-sub-cr" style="text-align:right;"></td>
                                <td class="border px-2 py-1 font-semibold" ></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function(){
            var mainTable = $('#tb-trial').DataTable({
                paging: true,
                searching: true,
                info: true,
                autoWidth: false,
                columnDefs: [
                    { orderable: false, targets: 6 }, // Detail column not orderable (index starts at 0)
                    { targets: [2,3,4,5,7,8], className: 'col-num', width: '140px' },
                    { targets: [0,1], className: 'col-text' }
                ]
            });

            // helper to load detail for an account and populate the detail modal
            function performDetailLoad(acc, acctName) {
                var period = $('select[name="period"]').val();
                $('#detail-modal').css('display','flex');

                if ($.fn.DataTable.isDataTable('#detail-table')) {
                    try { $('#detail-table').DataTable().destroy(); } catch(e) {}
                }
                $('#detail-body').html('Loading...');
                $('#detail-sub-dr').text('');
                $('#detail-sub-cr').text('');

                // set header
                $('#detail-account').text(acc);
                $('#detail-account-name').text(acctName || '');
                $('#detail-branch').text('');

                $.getJSON('/trial-balance-detail', { account: acc, period: period }, function(res){
                    var rows = res.data || [];
                    var html = '';
                    var sumDr = 0.0, sumCr = 0.0;
                    rows.forEach(function(r){
                        var dr = r.DR ? Number(r.DR) : 0;
                        var cr = r.CR ? Number(r.CR) : 0;
                        sumDr += dr; sumCr += cr;
                        // add title attributes for tooltips on truncated fields
                        var docRef = r.doc_ref || '';
                        var docType = r.doc_type || '';
                        var sourceText = r.source || '';
                        var branchText = r.branch_code || '';
                        html += '<tr>'+
                            '<td class="border px-2 py-1">'+ r.doc_date +'</td>'+
                    '<td class="border px-2 py-1"><a href="#" class="doc-link" title="'+ docRef.replace(/"/g,'&quot;') +'" data-doc_key="'+ r.doc_key +'" data-doc_ref="'+ docRef +'">'+ docRef +'</a></td>'+
                    '<td class="border px-2 py-1 col-type" title="'+ docType.replace(/"/g,'&quot;') +'">'+ (docType) +'</td>'+
                    '<td class="border px-2 py-1 text-right">'+ (dr ? dr.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) : '') +'</td>'+
                    '<td class="border px-2 py-1 text-right">'+ (cr ? cr.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) : '') +'</td>'+
                    '<td class="border px-2 py-1" title="'+ sourceText.replace(/"/g,'&quot;') +'">'+ sourceText +'</td>'+
                    '<td class="border px-2 py-1" title="'+ branchText.replace(/"/g,'&quot;') +'">'+ branchText +'</td>'+
                '</tr>';
                    });

                    $('#detail-body').html(html);
                    if (rows.length > 0) {
                        var branches = rows.map(function(x){ return x.branch_code; }).filter(function(v,i,a){ return v && a.indexOf(v)===i; });
                        $('#detail-branch').text(branches.join(', '));
                    }
                    $('#detail-sub-dr').text(sumDr.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}));
                    // $('#detail-sub-dr').addClass('text-right');
                    $('#detail-sub-cr').text(sumCr.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}));
                    // $('#detail-sub-cr').addClass('text-right');

                    $('#detail-table').DataTable({ paging: false, searching: false, info: false, order: [[0, 'asc']] });
                });
            }

            // click handler for main Details buttons
            $(document).on('click', '.detail-btn', function(e){
                var acc = $(this).data('account');
                var acctName = $(this).data('account-name') || $(this).data('accountName') || '';
                performDetailLoad(acc, acctName);
            });

            // single delegated handler for doc links (outside AJAX success) — avoids duplicate bindings
            $(document).off('click', '.doc-link').on('click', '.doc-link', function(ev){
                ev.preventDefault();
                var docKey = $(this).data('doc_key');
                var docRef = $(this).data('doc_ref') || docKey;
                $('#entries-body').html('Loading...');
                $('#entries-sub-dr').text('');
                $('#entries-sub-cr').text('');
                $('#entries-modal').css('display','flex');

                // destroy entries table if exists
                if ($.fn.DataTable.isDataTable('#entries-table')) {
                    try { $('#entries-table').DataTable().destroy(); } catch(e) {}
                }

                var sumEntriesDr = 0.0, sumEntriesCr = 0.0;
                $.getJSON('/trial-balance-entries', { doc_key: docKey }, function(res){
                    var rows = res.data || [];
                    var html = '';
                    rows.forEach(function(rr){
                        var edr = rr.DR ? Number(rr.DR) : 0;
                        var ecr = rr.CR ? Number(rr.CR) : 0;
                        sumEntriesDr += edr; sumEntriesCr += ecr;
                        var acctCode = rr.account_code || '';
                        var acctName = rr.account_name || '';
                        var docRemark = rr.doc_remark || '';
                        var docType = rr.doc_type || '';
                        html += '<tr>'+
                                '<td class="border px-2 py-1 col-account"><a href="#" class="entries-account" title="'+ acctCode.replace(/"/g,'&quot;') +'" data-account="'+ acctCode +'" data-account_name="'+ acctName +'">'+ acctCode +'</a></td>'+
                                '<td class="border px-2 py-1" title="'+ acctName.replace(/"/g,'&quot;') +'">'+ acctName +'</td>'+
                                '<td class="border px-2 py-1 text-right">'+ (edr ? edr.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) : '') +'</td>'+
                                '<td class="border px-2 py-1 text-right">'+ (ecr ? ecr.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) : '') +'</td>'+
                                '<td class="border px-2 py-1" title="'+ docRemark.replace(/"/g,'&quot;') +'">'+ (docRemark) +'</td>'+
                            '</tr>';
                    });
                    $('#entries-body').html(html);
                    // update entries subtotals
                    $('#entries-sub-dr').text(sumEntriesDr.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}));
                    $('#entries-sub-cr').text(sumEntriesCr.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}));

                    // set title with doc ref and doc type if available
                    var docType = (rows.length && rows[0].doc_type) ? rows[0].doc_type : '';
                    $('#entries-title').text('Entries — ' + docRef + (docType ? ' ('+docType+')' : ''));

                    $('#entries-table').DataTable({ paging: false, searching: false, info: false, order: [[0, 'asc']] });
                });
            });

            // delegated click: when clicking an account in entries, re-open detail modal for that account
            $(document).off('click', '.entries-account').on('click', '.entries-account', function(ev){
                ev.preventDefault();
                var acct = $(this).data('account');
                var acctName = $(this).data('account_name') || '';
                // close entries modal and load detail for the clicked account
                if ($.fn.DataTable.isDataTable('#entries-table')) { try { $('#entries-table').DataTable().destroy(); } catch(e) {} }
                $('#entries-modal').css('display','none');
                performDetailLoad(acct, acctName);
            });

            $('#detail-close').on('click', function(){
                // destroy detail table if exists and clear body
                if ($.fn.DataTable.isDataTable('#detail-table')) {
                    try { $('#detail-table').DataTable().destroy(); } catch(e) {}
                }
                $('#detail-body').html('');
                $('#detail-sub-dr').text('');
                $('#detail-sub-cr').text('');
                $('#detail-modal').css('display','none');
            });
            $('#entries-close').on('click', function(){
                if ($.fn.DataTable.isDataTable('#entries-table')) {
                    try { $('#entries-table').DataTable().destroy(); } catch(e) {}
                }
                $('#entries-body').html('');
                $('#entries-sub-dr').text('');
                $('#entries-sub-cr').text('');
                $('#entries-modal').css('display','none');
            });
        });
    </script>

    <p class="mt-4 text-sm text-gray-600">This is a plain server-rendered page (no Livewire) — useful for debugging DB queries and avoiding Livewire runtime dispatch issues.</p>
</div>
@endsection
