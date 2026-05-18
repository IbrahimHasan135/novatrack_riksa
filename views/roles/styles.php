<style>
/* ─── ADMIN SHARED STYLES ─────────────────────── */

/* ── Shell & Card ── */
.admin-shell {
    padding: 24px 28px 36px;
}

.admin-hero,
.admin-card {
    background: rgba(255, 255, 255, .9);
    border: 1px solid rgba(58, 110, 165, .14);
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 18px 46px rgba(30, 72, 126, .10);
}

.admin-hero {
    margin-bottom: 18px;
}

.admin-kicker {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #1BA784;
}

.admin-hero h1,
.admin-card h1,
.admin-card h2 {
    margin: 4px 0;
    color: #1C2B3A;
    font-weight: 800;
}

.admin-hero p {
    color: #416C92;
    margin: 4px 0 0;
}

/* ── Grid ── */
.admin-grid {
    display: grid;
    grid-template-columns: minmax(280px, 380px) 1fr;
    gap: 18px;
}

.admin-card.wide {
    max-width: 760px;
}

/* ── Labels ── */
.admin-card label {
    display: block;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: #416C92;
    margin: 14px 0 8px;
}

/* ── Inputs (shared design: rounded, teal focus) ── */
.admin-card input[type=text],
.admin-card input[type=password],
.admin-card select,
.admin-card textarea {
    width: 100%;
    border: 1.5px solid #DDE8F4;
    border-radius: 12px;
    background: #F7FAFD;
    color: #1C2B3A;
    font: 14px Inter, sans-serif;
    padding: 11px 13px;
    outline: none;
    box-sizing: border-box;
    transition: border-color .2s, box-shadow .2s, background .2s;
}

/* Second block wins over Bootstrap form-control defaults */
.admin-card input[type=text],
.admin-card input[type=password],
.admin-card select,
.admin-card textarea {
    appearance: none;
    height: auto;
}

.admin-card input:focus,
.admin-card select:focus,
.admin-card textarea:focus {
    border-color: #1BA784;
    background: #fff;
    box-shadow: 0 0 0 4px rgba(27, 167, 132, .10);
}

.admin-card textarea {
    resize: vertical;
    min-height: 80px;
}

/* ── Submit / Action button ── */
.admin-card button {
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #3A6EA5, #1BA784);
    color: #fff;
    font-weight: 800;
    padding: 11px 20px;
    margin-top: 14px;
    cursor: pointer;
    font-size: 14px;
    font-family: Inter, sans-serif;
    transition: transform .15s, box-shadow .2s;
}

.admin-card button:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 22px rgba(27, 167, 132, .28);
}

/* ════════════════════════════════════════════
   PASSWORD VISIBILITY TOGGLE
   Shared design — login, roles, users
════════════════════════════════════════════ */

/* wrapper jadi anchor untuk tombol absolute */
.pw-wrap {
    position: relative;
}

/* tambah slot kanan untuk icon mata */
.pw-wrap input {
    padding-right: 50px !important;
}

/* tombol mata — desain pill dasar */
.pw-toggle {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255, 255, 255, .72);
    border: 1px solid #DDE8F4;
    border-radius: 8px;
    color: #8CA0B3;
    font-size: 15px;
    cursor: pointer;
    padding: 4px 6px;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition:
        color .18s ease,
        background .18s ease,
        border-color .18s ease,
        box-shadow .18s ease;
    -webkit-user-select: none;
    user-select: none;
}

.pw-toggle:hover {
    background: #fff;
    border-color: #3A6EA5;
    color: #3A6EA5;
}

/* ════════════════════════════════════════════
   CHECKBOX GRID
════════════════════════════════════════════ */
.check-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(155px, 1fr));
    gap: 8px;
}

.check-grid label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
    background: #F7FAFD;
    border: 1.5px solid #DDE8F4;
    border-radius: 10px;
    padding: 9px 11px;
    text-transform: none;
    letter-spacing: 0;
    font-size: 13px;
    font-weight: 500;
    color: #1C2B3A;
    cursor: pointer;
    transition: border-color .15s ease, background .15s ease, box-shadow .15s ease;
    user-select: none;
}

.check-grid label:hover {
    border-color: #8CBDE0;
    background: #EEF6FF;
}

.check-grid input[type=checkbox] {
    width: 16px;
    height: 16px;
    accent-color: #1BA784;
    cursor: pointer;
    flex-shrink: 0;
}

/* ════════════════════════════════════════════
   TABLE
════════════════════════════════════════════ */
.admin-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table th,
.admin-table td {
    padding: 11px;
    border-bottom: 1px solid #EEF4FA;
    text-align: left;
    font-size: 13px;
}

.admin-table th {
    font-size: 11px;
    text-transform: uppercase;
    color: #416C92;
}

.admin-table a,
.admin-table button {
    display: inline-flex;
    border: 1px solid #DDE8F4;
    background: #F7FAFD;
    border-radius: 9px;
    padding: 6px 9px;
    color: #255F8C;
    text-decoration: none;
    font-size: 12px;
    font-weight: 800;
    margin-right: 4px;
    cursor: pointer;
    font-family: Inter, sans-serif;
    transition: background .15s ease, border-color .15s ease;
}

.admin-table a:hover,
.admin-table button:hover {
    background: #EEF6FF;
    border-color: #8CBDE0;
}

.admin-table form {
    display: inline;
}

.admin-table button {
    color: #C0392B;
    margin-top: 0;
}

.admin-table button:hover {
    background: #FFF1F0;
    border-color: #FFCDC9;
    color: #C0392B;
}

/* ── Badge ── */
.tag {
    background: #E8F0FB;
    color: #3A6EA5;
    border-radius: 999px;
    padding: 2px 7px;
    font-size: 10px;
    font-weight: 800;
}

/* ── Back link ── */
.back-link {
    display: inline-flex;
    gap: 6px;
    align-items: center;
    margin-bottom: 14px;
    color: #255F8C;
    text-decoration: none;
    font-weight: 800;
    font-size: 13px;
}

/* ── Alert ── */
.admin-alert {
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 16px;
    padding: 10px 14px;
}

.admin-alert.danger {
    background: #FFF1F0;
    border: 1px solid #FFCDC9;
    color: #C0392B;
}

.admin-alert.success {
    background: #E8F7EE;
    border: 1px solid #9AE6B4;
    color: #1E7E34;
}

/* ── Responsive ── */
@media (max-width: 768px) {
    .admin-grid {
        grid-template-columns: 1fr;
    }

    .admin-card.wide {
        max-width: 100%;
    }
}
</style>
