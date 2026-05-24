<style>
.acct-shell { padding:24px 28px 38px; }
.acct-hero { display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap;background:linear-gradient(135deg,rgba(255,255,255,.96),rgba(221,234,245,.72) 48%,rgba(231,247,242,.86));border:1px solid rgba(58,110,165,.14);border-radius:20px;padding:24px 26px;box-shadow:0 18px 46px rgba(30,72,126,.10);position:relative;overflow:hidden; }
.acct-hero::before { content:'';position:absolute;left:0;right:0;top:0;height:3px;background:linear-gradient(90deg,#3A6EA5,#1BA784,#F2994A); }
.acct-kicker { font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#1BA784;margin-bottom:6px;display:flex;gap:6px;align-items:center; }
.acct-hero h1 { margin:0;color:#1C2B3A;font-size:28px;font-weight:800; }
.acct-hero p { margin:5px 0 0;color:#416C92;font-size:13.5px;max-width:720px; }
.acct-actions { display:flex;gap:10px;flex-wrap:wrap; }
.acct-btn { display:inline-flex;align-items:center;gap:8px;border:none;border-radius:12px;background:linear-gradient(135deg,#3A6EA5,#1BA784);color:#fff;text-decoration:none;padding:11px 16px;font-weight:800;font-size:13px;box-shadow:0 14px 34px rgba(27,167,132,.20);cursor:pointer; }
.acct-btn.secondary { background:#fff;color:#255F8C;border:1px solid #DDE8F4;box-shadow:none; }
.acct-submit-row { display:flex;gap:10px;flex-wrap:wrap; }
.acct-grid { display:grid;grid-template-columns:repeat(12,1fr);gap:16px;margin-top:18px; }
.acct-card { grid-column:span 12;background:rgba(255,255,255,.88);border:1px solid rgba(58,110,165,.14);border-radius:18px;padding:18px;box-shadow:0 16px 42px rgba(30,72,126,.09); }
.acct-card.span-3 { grid-column:span 3; }
.acct-card.span-4 { grid-column:span 4; }
.acct-card.span-5 { grid-column:span 5; }
.acct-card.span-6 { grid-column:span 6; }
.acct-card.span-7 { grid-column:span 7; }
.acct-card h2 { margin:0 0 14px;color:#1C2B3A;font-size:16px;font-weight:800; }
.acct-card-head { display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:14px; }
.acct-card-head h2 { margin:0 0 4px; }
.acct-card-head p { margin:0;color:#68839E;font-size:12.5px;font-weight:650; }
.acct-filter { display:flex;align-items:center;gap:8px;flex-wrap:wrap; }
.acct-filter select,.acct-filter input { border:1.5px solid #DDE8F4;border-radius:11px;padding:8px 10px;background:#fff;color:#1C2B3A;font:12.5px Inter,sans-serif;font-weight:750;outline:none; }
.acct-filter button { display:inline-flex;align-items:center;gap:6px;border:none;border-radius:11px;background:linear-gradient(135deg,#3A6EA5,#1BA784);color:#fff;padding:9px 12px;font-size:12px;font-weight:800;cursor:pointer; }
.acct-report-actions { display:flex;gap:8px;justify-content:flex-end;margin:-4px 0 14px;flex-wrap:wrap; }
.acct-report-actions a { display:inline-flex;align-items:center;gap:7px;text-decoration:none;border-radius:11px;border:1px solid #DDE8F4;background:#fff;color:#255F8C;padding:9px 12px;font-size:12px;font-weight:800; }
.acct-analytics-grid { display:grid;grid-template-columns:minmax(0,1.7fr) minmax(280px,.8fr);gap:16px;align-items:stretch; }
.acct-chart-box { height:360px;position:relative; }
.acct-top-panel { display:grid;gap:16px;align-content:start; }
.acct-stack { display:grid;gap:16px; }
.acct-mini-title { font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#416C92;margin-bottom:8px; }
.acct-metric { position:relative;overflow:hidden;min-height:122px; }
.acct-metric::after { content:'';position:absolute;right:-30px;bottom:-32px;width:100px;height:100px;border-radius:50%;background:rgba(27,167,132,.10); }
.acct-metric .label { font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#68839E; }
.acct-metric .value { margin-top:9px;font-size:24px;font-weight:800;color:#1C2B3A;position:relative;z-index:1; }
.acct-metric .hint { margin-top:5px;font-size:12px;color:#416C92;font-weight:650;position:relative;z-index:1; }
.acct-metric.income { border-top:3px solid #27AE60; }
.acct-metric.expense { border-top:3px solid #EB5757; }
.acct-metric.net { border-top:3px solid #3A6EA5; }
.acct-metric.debt { border-top:3px solid #F2994A; }
.acct-form { display:grid;grid-template-columns:repeat(12,1fr);gap:12px; }
.acct-field { grid-column:span 6; }
.acct-field.full { grid-column:span 12; }
.acct-field label { display:block;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#416C92;margin-bottom:7px; }
.acct-field input,.acct-field select,.acct-field textarea { width:100%;border:1.5px solid #DDE8F4;border-radius:12px;padding:10px 12px;font:13.5px Inter,sans-serif;color:#1C2B3A;background:#fff;outline:none; }
.acct-field textarea { min-height:88px;resize:vertical; }
.acct-field input:focus,.acct-field select:focus,.acct-field textarea:focus { border-color:#1BA784;box-shadow:0 0 0 4px rgba(27,167,132,.10); }
.acct-table-wrap { overflow:auto;border:1px solid #EEF4FA;border-radius:14px;max-height:520px; }
.acct-table { width:100%;border-collapse:collapse;min-width:760px;background:#fff; }
.acct-table th { background:#F4F8FC;color:#416C92;font-size:11px;text-transform:uppercase;letter-spacing:.06em;text-align:left;padding:12px;font-weight:800; }
.acct-table td { padding:12px;border-top:1px solid #EEF4FA;color:#1C2B3A;font-size:13px;vertical-align:top; }
.acct-table strong { font-weight:800; }
.acct-delete { border:none;background:#FFF1F0;color:#C0392B;border-radius:10px;padding:7px 10px;font-size:12px;font-weight:800;cursor:pointer; }
.acct-edit { display:inline-flex;align-items:center;justify-content:center;text-decoration:none;background:#E8F0FB;color:#255F8C;border-radius:10px;padding:7px 10px;font-size:12px;font-weight:800; }
.acct-row-actions { display:flex;gap:6px;align-items:center; }
.acct-row-actions form { margin:0; }
.acct-list { display:grid;gap:10px;max-height:520px;overflow:auto;padding-right:2px; }
.acct-list.compact { max-height:185px; }
.acct-list-row { display:flex;justify-content:space-between;gap:12px;align-items:flex-start;border:1px solid #EEF4FA;border-radius:13px;padding:12px;background:#fff; }
.acct-list-row b { color:#1C2B3A;font-size:13px; }
.acct-list-row span { color:#416C92;font-size:12px;font-weight:650; }
.acct-pill { display:inline-flex;align-items:center;border-radius:999px;padding:4px 9px;font-size:11px;font-weight:800;background:#E8F0FB;color:#3A6EA5; }
.acct-pill.green { background:#E8F7EE;color:#1E7E34; }
.acct-pill.red { background:#FFF1F0;color:#C0392B; }
.acct-pill.orange { background:#FFF8E1;color:#B9770E; }
.acct-alert { margin-top:16px;padding:12px 14px;border-radius:12px;font-size:13px;font-weight:750;background:#E8F7EE;color:#1E7E34;border:1px solid #BFE7CE;display:flex;gap:8px;align-items:center; }
.acct-progress { height:8px;background:#EDF4FA;border-radius:999px;overflow:hidden;margin-top:8px; }
.acct-progress > span { display:block;height:100%;background:linear-gradient(90deg,#3A6EA5,#1BA784);border-radius:999px; }
@media(max-width:1100px){ .acct-card.span-3,.acct-card.span-4,.acct-card.span-5,.acct-card.span-6,.acct-card.span-7 { grid-column:span 12; } }
@media(max-width:1100px){ .acct-analytics-grid { grid-template-columns:1fr; } }
@media(max-width:640px){ .acct-shell { padding:18px 14px 30px; } .acct-field { grid-column:span 12; } .acct-hero h1 { font-size:24px; } }
</style>
