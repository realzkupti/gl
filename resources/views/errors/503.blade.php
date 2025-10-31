<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบกำลังปรับปรุง | Maintenance</title>
    <link rel="icon" href="/favicon.ico">
    <style>
        :root { color-scheme: light dark; }
        html, body { height: 100%; }
        body { margin:0; font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Helvetica Neue, Arial, "Apple Color Emoji", "Segoe UI Emoji"; }
        .wrap { min-height:100%; display:flex; align-items:center; justify-content:center; background: #f8fafc; }
        .card { width:100%; max-width: 680px; background:#fff; border-radius:16px; padding:32px; box-shadow: 0 10px 25px rgba(2,6,23,.08); border:1px solid #e5e7eb; }
        .badge { display:inline-flex; align-items:center; gap:8px; padding:6px 10px; border-radius:9999px; background:#eef2ff; color:#3730a3; font-weight:700; font-size:12px; letter-spacing:.02em; }
        .title { margin:16px 0 8px; font-size: clamp(22px, 3.2vw, 30px); font-weight:800; color:#0f172a; }
        .desc { color:#475569; line-height:1.6; }
        .grid { display:grid; grid-template-columns: 1fr; gap:16px; margin-top:24px; }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:12px 16px; border-radius:12px; font-weight:600; text-decoration:none; border:1px solid transparent; transition: all .15s ease; }
        .btn-primary { background:#5b6cff; color:#fff; }
        .btn-primary:hover { filter:brightness(0.95); }
        .btn-ghost { background:#f1f5f9; color:#0f172a; border-color:#e2e8f0; }
        .btn-ghost:hover { background:#e2e8f0; }
        .footer { margin-top:16px; color:#64748b; font-size:12px; text-align:center; }
        .illustration { display:flex; align-items:center; justify-content:center; width:80px; height:80px; border-radius:16px; background: linear-gradient(135deg, #6366f1, #22c55e); color:#fff; box-shadow: 0 8px 20px rgba(99,102,241,.35); }
        .row { display:flex; gap:16px; align-items:center; }
        .grow { flex:1; }
        @media (prefers-color-scheme: dark) {
            .wrap { background:#0b1220; }
            .card { background:#0f172a; border-color:#1f2937; box-shadow: 0 10px 25px rgba(0,0,0,.35); }
            .badge { background:#1e293b; color:#93c5fd; }
            .title { color:#e5e7eb; }
            .desc { color:#94a3b8; }
            .btn-ghost { background:#111827; color:#e5e7eb; border-color:#1f2937; }
            .btn-ghost:hover { background:#0b1220; }
            .footer { color:#94a3b8; }
        }
    </style>
    <meta http-equiv="refresh" content="600">
    <meta name="robots" content="noindex">
    @php($retryAfter = (int) (headers_list() && function_exists('http_response_code') ? 0 : 0))
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="row">
                <div class="illustration" aria-hidden="true">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/>
                        <path d="M12 12v7"/>
                        <path d="M9 19h6"/>
                        <path d="M8 8V6a4 4 0 1 1 8 0v2"/>
                    </svg>
                </div>
                <div class="grow">
                    <div class="badge">ระบบกำลังปรับปรุง</div>
                    <h1 class="title">เรากำลังอัปเดตระบบให้ดียิ่งขึ้น</h1>
                    <p class="desc">ขออภัยในความไม่สะดวก ขณะนี้ระบบอยู่ระหว่างการบำรุงรักษาชั่วคราวเพื่อเพิ่มประสิทธิภาพและคุณสมบัติใหม่ ๆ กรุณาลองใหม่อีกครั้งภายหลัง</p>
                </div>
            </div>

            <div class="grid">
                <a class="btn btn-primary" href="javascript:location.reload()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.13-3.36L23 10"></path><path d="M20.49 15a9 9 0 0 1-14.13 3.36L1 14"></path></svg>
                    โหลดหน้าใหม่
                </a>
                @auth
                <a class="btn btn-ghost" href="{{ route('tailadmin.dashboard') }}">กลับไปหน้าแดชบอร์ด</a>
                @endauth
            </div>

            <div class="footer">หากคุณเป็นผู้ดูแลระบบ: รันคำสั่ง “php artisan up” เพื่อเปิดระบบอีกครั้ง</div>
        </div>
    </div>
</body>
</html>

