# KSrelief — Anesthesia Medical Record (Web ↔ Mobile 100%)

**Scope:** Stage code **`anesthesia`** only.  
**Web UI:** Generic clinical panel in `records/create` when stage = Anesthesia (no dedicated Blade partial).  
**Parent:** [mobile-api-medical-records-full-spec.md](./mobile-api-medical-records-full-spec.md)  
**Version:** 1.0.0 — 2026-11-02

---

## 1. Summary

Anesthesia is a **simple flat form** (6 scalar fields). Unlike Pre-Op, there are **no composites**, **no templates**, and **no defaults API**. Mobile must mirror the generic web renderer in `_stage_fields.blade.php` (the `@else` branch).

---

## 2. Web layout (exact)

**File:** `resources/views/pages/patients/records/_stage_fields.blade.php` (when `stageCode !== pre_operation|operation|follow_up|post_operation`)

```
┌─ Card (phase pre_op colors: bg #FFF2CC, accent #FFD966)
│  Header: "Workflow — Anesthesia" + badge "Pre-Op" phase label
│  Row (Bootstrap g-3):
│    [col-md-6] Attending Doctor     — member_select (doctors)
│    [col-md-6] Anesthesia           — text  (field key: anesthesia_type)
│    [col-md-6] NPO Time             — text
│    [col-md-6] ASA Score            — text
│    [col-md-6] Weight               — number (step 0.01, min 0)
│    [col-12]   Anesthesia Notes     — textarea, 3 rows
└─
```

**Not on anesthesia stage (web):**

- No “Load template” / save defaults buttons  
- No admission file upload (`admission_attachments` only when `stageCode === 'admission'`)  
- No screening prefill from `patient.screening_data` (only `pre_operation` gets that in the generic loop)

**Shared record header (outside stage block):** `stage_id`, `record_date`, `notes` — see parent spec.

---

## 3. API — load form metadata

### 3.1 Schema

```http
GET /api/v1/patients/{patientId}/records/schema?stage_id={anesthesiaStageId}
Authorization: Bearer …
```

When `stage_code === "anesthesia"`, response includes **`anesthesia_form`**.

### 3.2 Dedicated meta

```http
GET /api/v1/patients/{patientId}/records/anesthesia-form-meta
```

```json
{ "data": { … } }
```

**Builder:** `App\Support\AnesthesiaFormMetaBuilder` (includes campaign **team_members.doctors** for attending doctor picklist).

### 3.3 `anesthesia_form` payload (reference)

| Key | Purpose |
|-----|---------|
| `stage_code` | `"anesthesia"` |
| `phase` | `{ code, label, color, background }` — pre_op styling |
| `title` | Localized workflow + stage names |
| `field_order` | Display order of keys |
| `fields` | Same as `schema.fields` (labels, types, required flags) |
| `layout` | Column width hints (`half` / `full`) + widget types |
| `team_members` | `doctors[]`, `specialists[]`, `coordinators[]` |
| `defaults_empty` | Empty form state |
| `widgets` | key → widget map |
| `templates.supported` | `false` |

---

## 4. Field specification

Config: `config/patient_clinical.php` → `stage_fields.anesthesia`

| Key | Web label (en) | Widget | Required | Stored in `fields_json` |
|-----|----------------|--------|----------|-------------------------|
| `attending_doctor` | Attending Doctor | `member_select` (`member_role: doctor`) | no | **integer** campaign member id |
| `anesthesia_type` | Anesthesia | text | no | string |
| `npo_time` | NPO Time | text | no | string |
| `asa_score` | ASA Score | text | no | string |
| `weight` | Weight | number (step 0.01, min 0) | no | number or string (as submitted) |
| `anesthesia_notes` | Anesthesia Notes | textarea (full width) | no | string |

### 4.1 `attending_doctor` (member_select)

- Options: **`anesthesia_form.team_members.doctors`** (active campaign assignments).  
- Web shows: `full_name` + optional ` — specialty`.  
- Empty list: web shows disabled option “No campaign members” — mobile should show empty state, not invent users.  
- **Not** `users.id` — always **`members.id`**.

### 4.2 Plain text fields

Free text; no server-side enum validation. Trimming only via normal form handling.

### 4.3 `weight`

HTML `type="number"`, `step="0.01"`, `min="0"`. Optional UX: suggest `patient.weight_kg` as placeholder only — **web does not auto-fill** from patient on create.

---

## 5. Submit payload (create / update)

```http
POST /api/v1/patients/{patientId}/records
Content-Type: application/json
```

```json
{
  "stage_id": 3,
  "record_date": "2026-11-02",
  "notes": "Optional record-level notes",
  "fields": {
    "attending_doctor": 15,
    "anesthesia_type": "General anesthesia",
    "npo_time": "8 hours",
    "asa_score": "II",
    "weight": 72.5,
    "anesthesia_notes": "Plan reviewed with team."
  }
}
```

**Update:** `PUT …/records/{recordId}` — same shape; `stage_id` optional.

**Normalization:** No special composite normalizers — values stored as sent (except standard JSON types).

---

## 6. Show / list / brief

- **List:** `GET …/records` — each item includes `stage.code === "anesthesia"` and `fields` object.  
- **Show:** Render all non-empty fields; web uses `ClinicalCompositeFields::hasContent` — for scalars, “filled” means non-empty string / non-null id.  
- **Brief priorities** (`PatientBriefService`): for anesthesia stage highlights `npo_time`, `asa_score`, `anesthesia_type`.

---

## 7. Permissions & stage visibility

- Create: `medical_record.create` + patient access.  
- View: `medical_record.view`.  
- **Anesthesia is visible** in create/edit stage dropdown (not in `record_form_hidden_stage_codes`).

Resolve `stage_id` from `schema.stages` where `code === "anesthesia"`.

---

## 8. Mobile implementation checklist

- [ ] One pre_op-styled card titled “Workflow — Anesthesia”.  
- [ ] Two-column grid for rows 1–5; full width for notes.  
- [ ] Attending doctor picker bound to **member id** from `team_members.doctors`.  
- [ ] Weight input numeric with decimal step ≥ 0.01.  
- [ ] Do **not** use Pre-Op composite widgets or `pre_operation_form`.  
- [ ] Do **not** expect template or defaults endpoints.  
- [ ] POST `fields` keys exactly as table in §4 (not `field_attending_doctor` at root).  
- [ ] Verify round-trip: create on web → show on mobile and vice versa.

---

## 9. Common mobile mistakes

| Mistake | Correct |
|---------|---------|
| Treating `anesthesia_type` as select | **Text** input on web |
| User id in `attending_doctor` | **Member id** from campaign team |
| Expecting nested `physician_assessment` | Flat keys only |
| Using pre_op API meta | Use **`anesthesia_form`** / `anesthesia-form-meta` |
| Required validation on all fields | **All optional** in config |

---

## 10. Source files

| Piece | Path |
|-------|------|
| Field config | `config/patient_clinical.php` → `stage_fields.anesthesia` |
| Web renderer | `resources/views/pages/patients/records/_stage_fields.blade.php` (@else branch) |
| API meta | `app/Support/AnesthesiaFormMetaBuilder.php` |
| Save | `MedicalRecordService::buildFieldsJson` |
| Demo seed data | `database/seeders/PatientWorkflowSeeder.php` (sample anesthesia record) |

---

**Related:** [Operation (Intra-Operative) — full form spec](./mobile-api-medical-records-operation-full-spec.md)

**End of Anesthesia medical record specification.**
