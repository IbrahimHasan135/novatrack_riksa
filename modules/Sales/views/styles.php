<style>
.sales-shell { padding:24px 28px 38px; }
.sales-hero { display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap;background:linear-gradient(135deg,rgba(255,255,255,.96),rgba(221,234,245,.74) 48%,rgba(231,247,242,.88));border:1px solid rgba(58,110,165,.14);border-radius:20px;padding:24px 26px;box-shadow:0 18px 46px rgba(30,72,126,.10);position:relative;overflow:hidden; }
.sales-hero::before { content:'';position:absolute;left:0;right:0;top:0;height:3px;background:linear-gradient(90deg,#3A6EA5,#1BA784,#F2994A); }
.sales-kicker { font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#1BA784;margin-bottom:6px;display:flex;gap:6px;align-items:center; }
.sales-hero h1 { margin:0;color:#1C2B3A;font-size:28px;font-weight:800; }
.sales-hero p { margin:5px 0 0;color:#416C92;font-size:13.5px;max-width:760px; }
.sales-actions { display:flex;gap:10px;flex-wrap:wrap; }
.sales-btn { display:inline-flex;align-items:center;gap:8px;border:none;border-radius:12px;background:linear-gradient(135deg,#3A6EA5,#1BA784);color:#fff;text-decoration:none;padding:11px 16px;font-weight:800;font-size:13px;box-shadow:0 14px 34px rgba(27,167,132,.20);cursor:pointer; }
.sales-btn.secondary { background:#fff;color:#255F8C;border:1px solid #DDE8F4;box-shadow:none; }
.sales-grid { display:grid;grid-template-columns:repeat(12,1fr);gap:16px;margin-top:18px; }
.sales-card { grid-column:span 12;background:rgba(255,255,255,.88);border:1px solid rgba(58,110,165,.14);border-radius:18px;padding:18px;box-shadow:0 16px 42px rgba(30,72,126,.09); }
.sales-card.span-3 { grid-column:span 3; }
.sales-card.span-4 { grid-column:span 4; }
.sales-card.span-5 { grid-column:span 5; }
.sales-card.span-6 { grid-column:span 6; }
.sales-card.span-7 { grid-column:span 7; }
.sales-card h2 { margin:0 0 14px;color:#1C2B3A;font-size:16px;font-weight:800; }
.sales-metric { min-height:118px;position:relative;overflow:hidden;border-top:3px solid #3A6EA5; }
.sales-metric.green { border-top-color:#27AE60; }
.sales-metric.orange { border-top-color:#F2994A; }
.sales-metric.red { border-top-color:#EB5757; }
.sales-metric .label { font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#68839E; }
.sales-metric .value { margin-top:9px;font-size:24px;font-weight:800;color:#1C2B3A; }
.sales-metric .hint { margin-top:5px;font-size:12px;color:#416C92;font-weight:650; }
.sales-form { display:grid;grid-template-columns:repeat(12,1fr);gap:12px; }
.sales-field { grid-column:span 6; }
.sales-field.full { grid-column:span 12; }
.sales-field label { display:block;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#416C92;margin-bottom:7px; }
.sales-field input,.sales-field select,.sales-field textarea { width:100%;border:1.5px solid #DDE8F4;border-radius:12px;padding:10px 12px;font:13.5px Inter,sans-serif;color:#1C2B3A;background:#fff;outline:none; }
.sales-field textarea { min-height:88px;resize:vertical; }
.sales-field input:focus,.sales-field select:focus,.sales-field textarea:focus { border-color:#1BA784;box-shadow:0 0 0 4px rgba(27,167,132,.10); }
.sales-table-wrap { overflow:auto;border:1px solid #EEF4FA;border-radius:14px;max-height:540px; }
.sales-table { width:100%;border-collapse:collapse;min-width:860px;background:#fff; }
.sales-table th { background:#F4F8FC;color:#416C92;font-size:11px;text-transform:uppercase;letter-spacing:.06em;text-align:left;padding:12px;font-weight:800; }
.sales-table td { padding:12px;border-top:1px solid #EEF4FA;color:#1C2B3A;font-size:13px;vertical-align:top; }
.sales-table strong { font-weight:800; }
.sales-pill { display:inline-flex;align-items:center;border-radius:999px;padding:4px 9px;font-size:11px;font-weight:800;background:#E8F0FB;color:#3A6EA5; }
.sales-pill.green { background:#E8F7EE;color:#1E7E34; }
.sales-pill.red { background:#FFF1F0;color:#C0392B; }
.sales-pill.orange { background:#FFF8E1;color:#B9770E; }
.sales-list { display:grid;gap:10px;max-height:520px;overflow:auto;padding-right:2px; }
.sales-list-row { display:flex;justify-content:space-between;gap:12px;align-items:flex-start;border:1px solid #EEF4FA;border-radius:13px;padding:12px;background:#fff; }
.sales-list-row b { color:#1C2B3A;font-size:13px; }
.sales-list-row span { color:#416C92;font-size:12px;font-weight:650; }
.sales-progress { height:8px;background:#EDF4FA;border-radius:999px;overflow:hidden;margin-top:8px; }
.sales-progress span { display:block;height:100%;background:linear-gradient(90deg,#3A6EA5,#1BA784);border-radius:999px; }
.sales-alert { margin-top:16px;padding:12px 14px;border-radius:12px;font-size:13px;font-weight:750;background:#E8F7EE;color:#1E7E34;border:1px solid #BFE7CE;display:flex;gap:8px;align-items:center; }
.sales-row-actions { display:flex;gap:6px;align-items:center; }
.sales-row-actions form { margin:0; }
.sales-edit,.sales-delete { display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border:none;border-radius:10px;padding:7px 10px;font-size:12px;font-weight:800;cursor:pointer; }
.sales-edit { background:#E8F0FB;color:#255F8C; }
.sales-delete { background:#FFF1F0;color:#C0392B; }
@media(max-width:1100px){ .sales-card.span-3,.sales-card.span-4,.sales-card.span-5,.sales-card.span-6,.sales-card.span-7 { grid-column:span 12; } }
@media(max-width:640px){ .sales-shell { padding:18px 14px 30px; } .sales-field { grid-column:span 12; } .sales-hero h1 { font-size:24px; } }
</style>
