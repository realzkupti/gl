@extends('tailadmin.layouts.app')

@section('title', 'งบทดลอง (แยกสาขา)')

@section('content')
<div class="p-4 md:p-6 2xl:p-10">
    <!-- Breadcrumb -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">
            งบทดลอง (แยกสาขา)
        </h2>

        <nav>
            <ol class="flex items-center gap-2">
                <li>
                    <a class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400" href="{{ route('tailadmin.dashboard') }}">Dashboard /</a>
                </li>
                <li class="font-medium text-brand-500">งบทดลอง</li>
            </ol>
        </nav>
    </div>

    <!-- Filters Section -->
    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">งวดบัญชี</label>
                <select id="period" class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:border-gray-700 dark:focus:border-brand-500">
                    @foreach($periods ?? [] as $p)
                        <option value="{{ $p->GLP_KEY }}" {{ ($selectedPeriodKey == $p->GLP_KEY) ? 'selected' : '' }}>
                            {{ $p->GLP_SEQUENCE }}/{{ $p->GLP_YEAR }} ({{ \Carbon\Carbon::parse($p->GLP_ST_DATE)->format('M Y') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">สาขา</label>
                <select id="branch" class="w-full rounded border border-gray-300 bg-transparent px-4 py-2.5 outline-none focus:border-brand-500 dark:border-gray-700 dark:focus:border-brand-500">
                    <option value="">ทุกสาขา</option>
                    @foreach($branches ?? [] as $b)
                        <option value="{{ $b->code }}">{{ $b->name ?? $b->code }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button id="btnLoad" class="w-full rounded bg-brand-500 px-6 py-2.5 text-white hover:bg-brand-600">
                    แสดงผล
                </button>
            </div>
        </div>

        <div class="mt-4 flex gap-3 print:hidden">
            <button onclick="window.print()" class="rounded border border-gray-300 px-4 py-2 text-sm hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800">
                <svg class="mr-2 inline-block h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                พิมพ์
            </button>

            <button onclick="exportExcel()" class="rounded border border-gray-300 px-4 py-2 text-sm hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800">
                <svg class="mr-2 inline-block h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Excel
            </button>

            <button onclick="exportPDF()" class="rounded border border-gray-300 px-4 py-2 text-sm hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800">
                <svg class="mr-2 inline-block h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Export PDF
            </button>
        </div>
    </div>

    <!-- Table Section -->
    <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table id="tb-branch" class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-800">
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-900 dark:text-white">เลขบัญชี</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-900 dark:text-white">ชื่อบัญชี</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-900 dark:text-white">สาขา</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">ยอดยกมา Dr</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">ยอดยกมา Cr</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">ยอดเคลื่อนไหว Dr</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">ยอดเคลื่อนไหว Cr</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">ยอดคงเหลือ Dr</th>
                        <th class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">ยอดคงเหลือ Cr</th>
                    </tr>
                </thead>
                <tbody id="branch-body">
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">กำลังโหลดข้อมูล...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        .print\:hidden { display: none !important; }
    }
    @page { size: A4 landscape; margin: 10mm; }

    /* Sticky Notes toolbar and note base styles */
    .sticky-note { position: fixed; width: 300px; z-index: 1000; }
    .sticky-note .sn-wrap { border-radius: 6px; box-shadow: 0 4px 16px rgba(0,0,0,0.15); overflow: hidden; }
    .sticky-note .sn-head { padding: .35rem .5rem; display:flex; align-items:center; justify-content:space-between; }
    .sticky-note .sn-head .sn-title { font-weight: 600; font-size: .85rem; }
    .sticky-note .sn-head .sn-ctrls { display:flex; gap:.4rem; }
    .sticky-note .sn-btn { font-size: .8rem; padding:.1rem .35rem; border:1px solid transparent; border-radius:4px; background: transparent; }
    .sticky-note .sn-body { padding: .4rem; position: relative; }
    .sticky-note textarea { width: 100%; min-height: 120px; height: 160px; resize: none; background: transparent; outline: none; border: none; font-family: inherit; }
    .sticky-note.min .sn-body { display:none; }
    .sticky-note .sn-resize { position:absolute; right:4px; bottom:4px; width:14px; height:14px; cursor: se-resize; }
    .sticky-toolbar { position: fixed; right: 16px; bottom: 16px; z-index: 1001; display:flex; gap:.5rem; }
    .sticky-toolbar .sn-add, .sticky-toolbar .sn-trash { padding:.35rem .6rem; border-radius:6px; box-shadow: 0 4px 16px rgba(0,0,0,0.15); border:1px solid #cbd5e1; background:#f8fafc; color:#0f172a; }
    body.dark .sticky-toolbar .sn-add, body.dark .sticky-toolbar .sn-trash { background:#1f2937; border-color:#4b5563; color:#f8fafc; }
    @media print { .sticky-note,.sticky-toolbar { display:none; } }
    .sticky-note .sn-head { cursor: move; }
</style>
@endpush

@push('scripts')
<script>
    function fmt(v){ v=Number(v||0); return v? v.toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2}): '' }

    async function loadBranch(){
        try{
            const p = document.getElementById('period').value;
            const b = document.getElementById('branch').value;

            const tbody = document.getElementById('branch-body');
            tbody.innerHTML = '<tr><td colspan="9" class="px-4 py-8 text-center text-gray-500">กำลังโหลดข้อมูล...</td></tr>';

            const res = await fetch(`/trial-balance-branch-data?period=${encodeURIComponent(p)}&branch=${encodeURIComponent(b)}`);
            const json = await res.json();
            const rows = json.data || [];

            let html = '';
            rows.forEach(r => {
                html += `<tr class="border-b border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800">
                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">${r.account_number||''}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">${r.account_name||''}</td>
                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">${r.branch_name||''}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right">${fmt(r.opening_debit)}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right">${fmt(r.opening_credit)}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right">${fmt(r.movement_debit)}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right">${fmt(r.movement_credit)}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right">${fmt(r.balance_debit)}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right">${fmt(r.balance_credit)}</td>
                </tr>`;
            });

            tbody.innerHTML = html || '<tr><td colspan="9" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">ไม่พบข้อมูล</td></tr>';
        }catch(e){
            document.getElementById('branch-body').innerHTML = '<tr><td colspan="9" class="px-4 py-8 text-center text-red-500">โหลดข้อมูลไม่สำเร็จ</td></tr>';
            console.error(e);
        }
    }

    function exportExcel() {
        const p = document.getElementById('period').value;
        const b = document.getElementById('branch').value;
        window.location.href = `/trial-balance-excel?period=${encodeURIComponent(p)}&branch=${encodeURIComponent(b)}`;
    }

    function exportPDF() {
        const p = document.getElementById('period').value;
        const b = document.getElementById('branch').value;
        window.open(`/trial-balance-pdf?period=${encodeURIComponent(p)}&branch=${encodeURIComponent(b)}`, '_blank');
    }

    document.getElementById('btnLoad').addEventListener('click', loadBranch);

    // Load on page ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadBranch);
    } else {
        loadBranch();
    }
</script>

<script>
(function(){
  // Per-company Sticky key
  var companyKey = @json(\App\Services\CompanyManager::getSelectedKey() ?? 'default');
  var pageKey = (location.pathname||'branch').replace(/\W+/g,'_');
  var keyBase = 'gl_sn2_';
  var keyItems = keyBase + 'items_' + companyKey + '_' + pageKey;
  var keyTrash = keyBase + 'trash_' + companyKey + '_' + pageKey;

  function load(){ try { return JSON.parse(localStorage.getItem(keyItems) || '[]'); } catch(e){ return []; } }
  function save(items){ try { localStorage.setItem(keyItems, JSON.stringify(items)); } catch(e){} }
  function loadTrash(){ try { return JSON.parse(localStorage.getItem(keyTrash) || '[]'); } catch(e){ return []; } }
  function saveTrash(items){ try { localStorage.setItem(keyTrash, JSON.stringify(items)); } catch(e){} }
  function uid(){ return 'n' + Date.now().toString(36) + Math.random().toString(36).slice(2,7); }

  function noteColors(c){
    var map = {
      yellow: { head:'#fff1a6', body:'#fffbe6', border:'#e5d17d', title:'#6b5d00' },
      blue:   { head:'#c7e1ff', body:'#eaf4ff', border:'#9fc5fb', title:'#0f172a' },
      green:  { head:'#cde9d7', body:'#eaf7f0', border:'#a8d8be', title:'#0f172a' },
      pink:   { head:'#ffd1dc', body:'#ffebf0', border:'#f5a3b5', title:'#0f172a' },
      purple: { head:'#e1d4ff', body:'#f3edff', border:'#c7b2ff', title:'#0f172a' },
      gray:   { head:'#e5e7eb', body:'#f3f4f6', border:'#d1d5db', title:'#0f172a' }
    }; return map[c] || map.yellow;
  }

  function renderToolbar(){
    var tb = document.querySelector('.sticky-toolbar');
    if (!tb){ tb = document.createElement('div'); tb.className='sticky-toolbar'; document.body.appendChild(tb); }
    tb.innerHTML = '<button type="button" class="sn-add">+ Note</button><button type="button" class="sn-trash">ถังขยะ</button>';
    tb.querySelector('.sn-add').addEventListener('click', function(){
      var items = load();
      items.push({ id: uid(), x: window.innerWidth-340, y: window.innerHeight-220, w: 300, h: 180, min: false, text: '', color: 'yellow' });
      save(items); renderAll();
    });
    tb.querySelector('.sn-trash').addEventListener('click', toggleTrashPanel);
  }

  function applyColors(note, color){
    var col = noteColors(color||'yellow');
    var head = note.querySelector('.sn-head');
    var body = note.querySelector('.sn-body');
    var res = note.querySelector('.sn-resize');
    var title = note.querySelector('.sn-title');
    if (head){ head.style.background = col.head; head.style.borderBottom = '1px solid '+col.border; }
    if (body){ body.style.background = col.body; body.style.borderTop = '1px solid '+col.border; }
    if (res){ res.style.borderColor = col.border; }
    if (title){ title.style.color = col.title; }
  }

  function bindDrag(note, item){
    var head = note.querySelector('.sn-head');
    var dragging=false, sx=0, sy=0, ox=0, oy=0;
    head.addEventListener('mousedown', function(ev){ dragging=true; sx=ev.clientX; sy=ev.clientY; var r=note.getBoundingClientRect(); ox=r.left; oy=r.top; ev.preventDefault(); });
    document.addEventListener('mousemove', function(ev){ if(!dragging) return; var nx=ox+(ev.clientX-sx), ny=oy+(ev.clientY-sy); note.style.left=nx+'px'; note.style.top=ny+'px'; });
    document.addEventListener('mouseup', function(){ if(!dragging) return; dragging=false; var items=load(); var it=items.find(i=>i.id===item.id); if(it){ var r=note.getBoundingClientRect(); it.x=r.left; it.y=r.top; save(items);} });
  }

  function bindResize(note, item){
    var handle = note.querySelector('.sn-resize');
    var resizing=false, sx=0, sy=0, sw=0, sh=0;
    handle.addEventListener('mousedown', function(ev){ resizing=true; sx=ev.clientX; sy=ev.clientY; var r=note.getBoundingClientRect(); sw=r.width; sh=r.height; ev.preventDefault(); });
    document.addEventListener('mousemove', function(ev){ if(!resizing) return; var nw=Math.max(220, sw+(ev.clientX-sx)); var nh=Math.max(120, sh+(ev.clientY-sy)); note.style.width=nw+'px'; note.querySelector('textarea').style.height=(nh-64)+'px'; });
    document.addEventListener('mouseup', function(){ if(!resizing) return; resizing=false; var items=load(); var it=items.find(i=>i.id===item.id); if(it){ var r=note.getBoundingClientRect(); it.w=r.width; it.h=r.height; save(items);} });
  }

  function renderNote(item){
    var note = document.createElement('div'); note.className='sticky-note';
    note.style.left = (item.x|| (window.innerWidth-340)) + 'px';
    note.style.top = (item.y || (window.innerHeight-220)) + 'px';
    note.style.width = (item.w || 300) + 'px';
    var c = item.color || 'yellow'; var col = noteColors(c);
    note.innerHTML = '\
      <div class="sn-wrap">\
        <div class="sn-head" style="background:'+col.head+'; border-bottom:1px solid '+col.border+';">\
          <div class="sn-title">Note — ' + (companyKey||'default') + '</div>\
          <div class="sn-ctrls">\
            <select class="sn-color sn-btn" title="สี">\
              <option value="yellow"'+(c==='yellow'?' selected':'')+'>เหลือง</option>\
              <option value="blue"'+(c==='blue'?' selected':'')+'>น้ำเงิน</option>\
              <option value="green"'+(c==='green'?' selected':'')+'>เขียว</option>\
              <option value="pink"'+(c==='pink'?' selected':'')+'>ชมพู</option>\
              <option value="purple"'+(c==='purple'?' selected':'')+'>ม่วง</option>\
              <option value="gray"'+(c==='gray'?' selected':'')+'>เทา</option>\
            </select>\
            <button type="button" class="sn-btn sn-min">_</button>\
            <button type="button" class="sn-btn sn-del">×</button>\
          </div>\
        </div>\
        <div class="sn-body" style="background:'+col.body+'; border-top:1px solid '+col.border+';">\
          <textarea class="sn-text" placeholder="จดโน้ตสำหรับบริษัทนี้... (auto-save)"></textarea>\
          <div class="sn-resize" style="border-color:'+col.border+';"></div>\
        </div>\
      </div>';
    document.body.appendChild(note);
    if (item.min) note.classList.add('min');
    var txt = note.querySelector('.sn-text'); txt.value = item.text || '';
    note.querySelector('.sn-min').addEventListener('click', function(){ note.classList.toggle('min'); var items=load(); var it=items.find(i=>i.id===item.id); if(it){ it.min = note.classList.contains('min'); save(items);} });
    note.querySelector('.sn-del').addEventListener('click', function(){ var items=load(); var it=items.find(i=>i.id===item.id); var r=note.getBoundingClientRect(); var trash=loadTrash(); var tItem=Object.assign({}, it||item, { text: txt.value, color: (note.querySelector('.sn-color').value||'yellow'), x:r.left, y:r.top, w:r.width, h:r.height }); trash.unshift(tItem); saveTrash(trash); save(items.filter(i=>i.id!==item.id)); note.remove(); renderTrashBadge(); });
    note.querySelector('.sn-color').addEventListener('change', function(){ var color=this.value; var items=load(); var it=items.find(i=>i.id===item.id); if(it){ it.color=color; save(items);} applyColors(note,color); });
    txt.addEventListener('input', function(){ var items=load(); var it=items.find(i=>i.id===item.id); if(it){ it.text=this.value; save(items);} });
    applyColors(note, c); bindDrag(note,item); bindResize(note,item);
  }

  function renderAll(){
    document.querySelectorAll('.sticky-note').forEach(n => n.remove());
    renderToolbar();
    var items = load(); if (!items.length){ items=[{ id: uid(), x: window.innerWidth-340, y: window.innerHeight-220, w: 300, h: 180, min:false, text:'', color:'yellow' }]; save(items); }
    items.forEach(renderNote);
  }

  function renderTrashBadge(){ var tb=document.querySelector('.sticky-toolbar'); if(!tb) return; var btn=tb.querySelector('.sn-trash'); if(!btn) return; var c=(loadTrash().length)||0; btn.textContent='ถังขยะ'+(c?(' ('+c+')'):''); }
  function toggleTrashPanel(){
    var panel = document.getElementById('sn-trash-panel');
    if (panel){ panel.remove(); return; }
    panel = document.createElement('div'); panel.id='sn-trash-panel'; panel.style.position='fixed'; panel.style.right='16px'; panel.style.bottom='60px'; panel.style.zIndex='1002'; panel.style.width='320px'; panel.style.background='#ffffff'; panel.style.border='1px solid #e5e7eb'; panel.style.borderRadius='6px'; panel.style.boxShadow='0 4px 16px rgba(0,0,0,.15)';
    panel.innerHTML='<div style="padding:.5rem; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;"><strong>ถังขยะ</strong><button type="button" id="sn-trash-close" class="px-2 py-1 border rounded text-sm">ปิด</button></div><div id="sn-trash-list" style="max-height:240px; overflow:auto;"></div><div style="padding:.5rem; border-top:1px solid #e5e7eb; text-align:right;"><button type="button" id="sn-trash-empty" class="px-2 py-1 border rounded text-sm">ลบถาวรทั้งหมด</button></div>';
    document.body.appendChild(panel);
    document.getElementById('sn-trash-close').onclick=function(){ panel.remove(); };
    document.getElementById('sn-trash-empty').onclick=function(){ saveTrash([]); renderTrashBadge(); panel.remove(); };
    var list = document.getElementById('sn-trash-list'); var trash=loadTrash(); if(!trash.length){ list.innerHTML='<div style="padding:.5rem; color:#6b7280;">ถังขยะว่างเปล่า</div>'; return; }
    trash.forEach(function(it){ var row=document.createElement('div'); row.style.padding='.5rem'; row.style.borderBottom='1px solid #e5e7eb'; row.dataset.id=it.id; var preview=(it.text||'').split('\n')[0].slice(0,40); row.innerHTML='<div style="display:flex; justify-content:space-between; align-items:center; gap:.5rem;"><div style="flex:1 1 auto; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">'+(preview||'(ไม่มีข้อความ)')+'</div><div style="flex:0 0 auto; display:flex; gap:.25rem;"><button type="button" class="px-2 py-1 border rounded text-sm sn-restore">กู้คืน</button><button type="button" class="px-2 py-1 border rounded text-sm sn-destroy">ลบถาวร</button></div></div>'; list.appendChild(row); row.querySelector('.sn-restore').onclick=function(){ var id=row.dataset.id; var t=loadTrash(); var idx=t.findIndex(x=>x.id===id); if(idx>=0){ var found=t.splice(idx,1)[0]; if(!found.color) found.color='yellow'; saveTrash(t); var items=load(); items.unshift(found); save(items); renderAll(); toggleTrashPanel(); } }; row.querySelector('.sn-destroy').onclick=function(){ var id=row.dataset.id; var t=loadTrash(); var idx=t.findIndex(x=>x.id===id); if(idx>=0){ t.splice(idx,1); saveTrash(t); renderTrashBadge(); row.remove(); if(!loadTrash().length) toggleTrashPanel(); } }; });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function(){ renderAll(); renderTrashBadge(); }); else { renderAll(); renderTrashBadge(); }
})();
</script>
@endpush
@endsection
