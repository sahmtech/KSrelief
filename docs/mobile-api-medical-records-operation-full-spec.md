# KSrelief — Intra-Operative / During Surgery (Operation) Medical Record (Web ↔ Mobile 100%)

**Scope:** Stage code **`operation`** only (clinical phase **`intra_op`**).  
**Web UI:** Dedicated partial `_operation_stage_fields.blade.php` (green intra-op panel).  
**Parent:** [mobile-api-medical-records-full-spec.md](./mobile-api-medical-records-full-spec.md)  
**Version:** 1.0.0 — 2026-11-02

---

## 1. Summary

Operation is a **structured intra-op form** with:

- **Required scalars:** `operation_date`, `surgeon`, `implant_company_id`, `electrode_type_id`
- **Cascade:** electrode types depend on implant company
- **Composites:** `insertion_depth`, `audio_test`
- **Clinical selects:** `intra_op_findings`, insertion depth options (DB-backed with config fallback)
- **Create-only UX:** **Quick Fill** tab (default) + **Manual Entry** tab; **edit** shows manual fields only (no Quick Fill)
- **Templates:** user operation defaults + per-campaign per-company defaults (GET APIs)
- **Not in quick-fill/template payload:** `operation_date`, `side_of_surgery` — mobile must set these explicitly on create

---

## 2. Web layout (exact)

**File:** `resources/views/pages/patients/records/_operation_stage_fields.blade.php`

```
┌─ Header: "Workflow — Intra-Operative" (phase colors bg #B6D7A8, accent #6AA84F)
│
│  [Create only] Tabs: Manual Entry | Quick Fill (default active)
│
├─ Manual tab (always on edit; hidden panel on create until tab switch)
│  Card body, row g-3:
│    [col-md-6] Operation Date          — date *
│    [col-md-6] Surgeon                 — member_select (doctors) *
│    [col-md-6] Implant Company         — company select (colored) *
│    [col-md-6] Electrode Type          — cascade select *
│    [col-md-6] Insertion Approach      — select
│    [col-md-6] Side of surgery         — select
│  ── divider ──
│    Insertion depth composite (select + optional partial note)
│  Row:
│    [col-md-6] Time in surgery         — time
│    [col-md-6] Time out surgery        — time
│  ── divider ──
│    Audio test composite (KV metrics table; default keys Impedance, E cap; extra rows allowed)
│    Intra-op findings                   — clinical select (+ inline add on web)
│    Operation notes                     — textarea 4 rows
│
└─ Quick Fill tab (create only)
     Company cards → GET presets → Apply merges payload into form fields
```

**Shared record header (outside stage block):** `stage_id`, `record_date`, `notes` — see parent spec.

**Web template buttons** (load user / campaign defaults) exist in markup but are in a hidden container on create; mobile may expose the same via GET template endpoints when `schema.template_flags` allow.

---

## 3. API — load form metadata

### 3.1 Schema

```http
GET /api/v1/patients/{patientId}/records/schema?stage_id={operationStageId}
Authorization: Bearer …
```

When `stage_code === "operation"`, response includes:

- Everything in parent §5 (`fields`, `team_members`, `implant_companies`, `insertion_approaches`, `electrode_types` if `implant_company_id` query set, `template_flags`, …)
- **`operation_form`** — full UI catalog (below)

Optional on schema (same as today):

```http
GET …/schema?stage_id={id}&implant_company_id={companyId}
```

Preloads `electrode_types` for that company.

### 3.2 Dedicated meta

```http
GET /api/v1/patients/{patientId}/records/operation-form-meta
Authorization: Bearer …
```

```json
{ "data": { … } }
```

**Builder:** `App\Support\OperationFormMetaBuilder`

### 3.3 `operation_form` keys (reference)

| Key | Purpose |
|-----|---------|
| `stage_code` | `"operation"` |
| `phase` | `{ code: "intra_op", label, color, background }` |
| `title` | Localized workflow, stage name, intra-op phase label |
| `field_order` | Display order |
| `fields` | Same shape as `schema.fields` (types, required, labels) |
| `layout` | Rows with `column`, `widget`, optional `section: "divider"`, `depends_on` |
| `team_members.doctors` | Surgeon picklist (campaign members) |
| `implant_companies` | All active companies (`id`, `name`, `code`, `color`) |
| `quick_fill_companies` | Companies shown on Quick Fill step (supported implant companies, or all if empty) |
| `insertion_approaches` | `{ id, name }[]` |
| `select_options` | `side_of_surgery`, `insertion_depth`, `intra_op_findings` (value → label) |
| `composites` | Shapes, partial-insertion rules, legacy field names |
| `defaults_empty` | Empty/suggested create state (`operation_date` = today server date) |
| `widgets` | key → widget map |
| `create_ui` | Tabs, default tab `quick_fill`, edit = manual only |
| `quick_fill` | Endpoint path + apply rules |
| `electrode_types` | Cascade GET path |
| `templates` | User + campaign GET paths |
| `clinical_select` | Categories; inline add is **web-only** today |

Use **`schema.template_flags`** for `has_operation_defaults` / `has_campaign_operation_defaults`.

---

## 4. Field specification

Config: `config/patient_clinical.php` → `stage_fields.operation`

| Key | Web label (en) | Widget | Required | Stored in `fields_json` |
|-----|----------------|--------|----------|-------------------------|
| `operation_date` | Operation Date | date | **yes** | string `Y-m-d` |
| `surgeon` | Surgeon | member_select (`doctor`) | **yes** | **integer** member id |
| `implant_company_id` | Implant Company | company_select | **yes** | integer |
| `electrode_type_id` | Electrode Type | electrode_select | **yes** | integer |
| `insertion_approach_id` | Insertion Approach | insertion_approach_select | no | integer |
| `side_of_surgery` | Side of surgery | select | no | string code (`right`/`left`/`bilateral`) |
| `insertion_depth` | Insertion depth | composite | no | object §4.1 |
| `time_in_surgery` | Time in surgery | time | no | string `HH:MM` (24h) |
| `time_out_surgery` | Time out surgery | time | no | string `HH:MM` |
| `audio_test` | Audio test | composite | no | object §4.2 |
| `intra_op_findings` | Intra-op findings | clinical select | no | string option code |
| `operation_notes` | Operation notes | textarea | no | string |

### 4.1 `insertion_depth`

**Normalized shape** (server: `OperationFieldSupport::normalizeInsertionDepth`):

```json
{
  "selection": "full_insertion",
  "note": ""
}
```

- `selection`: option code from `select_options.insertion_depth` (e.g. `full_insertion`, `partial_insertion`, …).
- `note`: only meaningful when `selection === "partial_insertion"`; otherwise server clears `note`.
- **Legacy read:** old flat `insertion_depth_note` maps to `full_insertion` + note when loading web form.
- **Do not** send legacy keys in new mobile writes.

### 4.2 `audio_test`

**Normalized shape** (`OperationFieldSupport::normalizeAudioTest`):

```json
{
  "metrics": [
    { "key": "Impedance", "value": "…" },
    { "key": "E cap", "value": "…" }
  ]
}
```

- Default metric keys: **`Impedance`**, **`E cap`** (exact spelling/casing).
- Web allows **additional metric rows** (`allow-add-rows` on KV widget).
- **Legacy read:** `impedance_testing` string → `Impedance` metric when loading old records.

### 4.3 `surgeon` (member_select)

- Options: `operation_form.team_members.doctors` (or `schema.team_members.doctors`).
- Display: `name` + optional `specialty` (web: ` — specialty`).
- Value: **`members.id`**, not user id.

### 4.4 Implant company → electrode cascade

1. User selects `implant_company_id`.
2. Fetch:

```http
GET /api/v1/patients/{patientId}/records/electrode-types?implant_company_id={id}
```

3. Bind `electrode_type_id` to returned `data[]`.
4. Clear electrode when company changes.

Web also tints company select with `company.color` — use `implant_companies[].color` on mobile.

### 4.5 `side_of_surgery`

Options from `operation_form.select_options.side_of_surgery` (config `operation_side_of_surgery_options`).

---

## 5. Quick Fill (create only)

```http
GET /api/v1/patients/{patientId}/records/operation-quick-fill?implant_company_id={id}
Authorization: Bearer …
Permission: medical_record.create
```

**Response:**

```json
{
  "presets": [
    {
      "id": "user-default",
      "name": "My template",
      "source": "user_default",
      "company": { "id": "1", "name": "…", "color": "#…" },
      "summary": {
        "electrode": "…",
        "insertion_approach": "…",
        "insertion_depth": "…",
        "intra_op_findings": "…"
      },
      "payload": {
        "surgeon": "12",
        "implant_company_id": "1",
        "electrode_type_id": "3",
        "insertion_approach_id": "2",
        "insertion_depth": { "selection": "full_insertion", "note": "" },
        "time_in_surgery": "09:00",
        "time_out_surgery": "11:30",
        "audio_test": { "metrics": [ … ] },
        "intra_op_findings": "uneventful",
        "operation_notes": "",
        "_apply_labels": { "insertion_depth": "…", "intra_op_findings": "…" }
      }
    }
  ]
}
```

**Preset sources (current backend):**

| `source` | When |
|----------|------|
| `user_default` | User saved template for **same** `implant_company_id` |
| `campaign_default` | Campaign has defaults for company; merges user template base fields (surgeon, times, audio, notes) when present |

**Apply on mobile:**

1. Merge `preset.payload` into `fields` (deep-merge composites).
2. **Remove `_apply_labels`** before POST (UI hints only).
3. Set **`operation_date`** (and **`side_of_surgery`** if needed) — not included in normalized template/quick-fill payload.
4. Re-fetch electrode types if needed after company id applied.

---

## 6. Templates (manual tab / optional shortcuts)

### 6.1 User template

```http
GET /api/v1/patients/{patientId}/records/operation-defaults
```

```json
{ "has_defaults": true, "data": { … } }
```

`data` uses same field keys as §4; may include `_apply_labels`. Does **not** include `operation_date` / `side_of_surgery` in normalized template payload.

### 6.2 Campaign defaults

```http
GET /api/v1/patients/{patientId}/records/campaign-operation-defaults?implant_company_id={id}
```

Same response shape as user template.

**Save template:** web-only (`save_operation_defaults` on record POST). No mobile API yet.

---

## 7. Submit payload (create / update)

```http
POST /api/v1/patients/{patientId}/records
Content-Type: application/json
```

```json
{
  "stage_id": 5,
  "record_date": "2026-11-02",
  "notes": "Optional record-level notes",
  "fields": {
    "operation_date": "2026-11-02",
    "surgeon": 12,
    "implant_company_id": 1,
    "electrode_type_id": 3,
    "insertion_approach_id": 2,
    "side_of_surgery": "left",
    "insertion_depth": {
      "selection": "partial_insertion",
      "note": "Resistance at 360°"
    },
    "time_in_surgery": "09:15",
    "time_out_surgery": "11:45",
    "audio_test": {
      "metrics": [
        { "key": "Impedance", "value": "8 kΩ" },
        { "key": "E cap", "value": "0.4 nC" }
      ]
    },
    "intra_op_findings": "uneventful",
    "operation_notes": "Uneventful procedure."
  }
}
```

**Update:** `PUT …/records/{recordId}` — same shape; `stage_id` optional.

**Normalization:** Server normalizes `insertion_depth` and `audio_test` on save via `OperationFieldSupport` (same as web).

**Validation:** Laravel request rules follow stage field definitions; ensure required four fields on create.

---

## 8. Show / list / PDF

- **List / show:** `fields` object on `MedicalRecordResource`; hide empty composites using parent spec rules (`ClinicalCompositeFields::hasContent` for operation types).
- **Presentation helpers:** `OperationFieldSupport::presentInsertionDepth`, `presentAudioTest`, `presentIntraOpFindings`.
- **PDF:** `GET …/records/{recordId}/export-operation-pdf` — only for operation stage records.

---

## 9. Permissions & stage visibility

- Create: `medical_record.create` + patient access (quick-fill, defaults GET).
- View schema / meta / electrode-types: `medical_record.view` (viewAny).
- **Operation is visible** in stage dropdown (not hidden).

Resolve `stage_id` from `schema.stages` where `code === "operation"`.

---

## 10. Mobile implementation checklist

- [ ] Intra-op green styling from `operation_form.phase`.
- [ ] Create: Quick Fill default tab; Manual tab with full field grid matching `layout`.
- [ ] Edit: manual fields only (no Quick Fill).
- [ ] Surgeon picker → **member id** from doctors list.
- [ ] Company → electrode cascade via `electrode-types` API.
- [ ] Insertion depth: show note field only when `selection === partial_insertion`.
- [ ] Audio test: default two metrics; allow optional extra rows like web.
- [ ] Apply quick-fill/template: strip `_apply_labels`; always set `operation_date` on create.
- [ ] POST `fields` keys exactly as §4 (not `field_operation_date` at JSON root).
- [ ] Round-trip: create on web → show on mobile and vice versa.

---

## 11. Common mobile mistakes

| Mistake | Correct |
|---------|---------|
| Treating operation as flat text fields | Use **`operation_form`** / composites |
| User id in `surgeon` | **Member id** from campaign team |
| Skipping electrode fetch after company change | GET **`electrode-types?implant_company_id=`** |
| Sending `_apply_labels` in POST | Strip before save |
| Expecting quick-fill to set operation date | Set **`operation_date`** locally |
| Flat string for `insertion_depth` | Object `{ selection, note }` |
| Wrong audio metric keys | **`Impedance`**, **`E cap`** defaults |
| Using pre_op / anesthesia meta | **`operation_form`** / **`operation-form-meta`** |

---

## 12. Source files

| Piece | Path |
|-------|------|
| Field config | `config/patient_clinical.php` → `stage_fields.operation` |
| Web form | `resources/views/pages/patients/records/_operation_stage_fields.blade.php` |
| Composites | `resources/views/components/operation-*-input.blade.php` |
| Quick fill UI | `resources/views/pages/patients/partials/operation-quick-fill.blade.php`, `resources/js/operation-quick-fill.js` |
| Template JS | `resources/js/operation-template.js` |
| Normalization | `app/Support/OperationFieldSupport.php` |
| Quick fill service | `app/Services/OperationQuickFillService.php` |
| API meta | `app/Support/OperationFormMetaBuilder.php` |
| Routes | `routes/api.php` → `operation-form-meta`, `operation-quick-fill`, … |

---

**Related:** [Post op / Switch On — full form spec](./mobile-api-medical-records-post-operation-full-spec.md)

**End of Operation (Intra-Operative) medical record specification.**
