# KSrelief — Post op / Switch On Medical Record (Web ↔ Mobile 100%)

**Scope:** Stage code **`post_operation`** only (clinical phase **`post_op`**).  
**Display name (EN):** “Post op / switch on” — same label in workflow dropdown and form header.  
**Web UI:** `_post_operation_stage_fields.blade.php` (blue post-op cards).  
**Parent:** [mobile-api-medical-records-full-spec.md](./mobile-api-medical-records-full-spec.md)  
**Version:** 1.0.0 — 2026-11-02

---

## 1. Summary

This stage is **four stored field keys** (not flat scalars):

| Key | UI |
|-----|-----|
| `physician_assessment` | 4 clinical selects (2×2 grid) |
| `clinical_aud` | KV metrics table |
| `counselling` | Yes/no checkbox |
| `post_op_notes` | Textarea |

**Templates:** user **post-operation defaults** (GET only on API; save on web).

**Important — name vs fields:** The stage is *named* “Post op / switch on”, but **`switch_on`, `activation_date`, etc. are not on this web form.** They belong to a separate **`activation`** stage that is **hidden** from the medical record create/edit dropdown (`record_form_hidden_stage_codes`). Mobile must mirror the **four keys above**, not invent switch-on fields unless product later exposes the activation stage.

---

## 2. Web layout (exact)

**File:** `resources/views/pages/patients/records/_post_operation_stage_fields.blade.php`

```
┌─ Header: "Workflow — Post op / switch on" (bg #9FC5E8, accent #3C78D8)
│  [Create only] "Fill Template" if user has saved defaults
│
├─ Card: Physician Assessment
│    [col-md-6] Wound           — inline clinical select (post_op_wound)
│    [col-md-6] Implant Bed     — post_op_implant_bed
│    [col-md-6] Facial Nerve    — post_op_facial_nerve
│    [col-md-6] Post Op X-Ray   — post_op_xray
│    (Web: optional inline "add option" per select — API add not exposed)
│
├─ Card: Clinical Audiology (C. AUD)
│    KV metrics: default keys Impedance, E cap, magnet power; extra rows allowed
│
├─ Card (compact): Counselling — checkbox (hidden input "no" + checkbox value "yes")
│
└─ Card: Notes
     Textarea 5 rows, placeholder "Enter notes…"
```

**Shared record header:** `stage_id`, `record_date`, `notes` — parent spec.

**Web-only on save:** `save_post_operation_defaults=1` stores current values as user template.

---

## 3. API — load form metadata

### 3.1 Schema

```http
GET /api/v1/patients/{patientId}/records/schema?stage_id={postOperationStageId}
Authorization: Bearer …
```

When `stage_code === "post_operation"`, response includes **`post_operation_form`**.

### 3.2 Dedicated meta

```http
GET /api/v1/patients/{patientId}/records/post-operation-form-meta
```

```json
{ "data": { … } }
```

**Builder:** `App\Support\PostOperationFormMetaBuilder`

### 3.3 `post_operation_form` keys (reference)

| Key | Purpose |
|-----|---------|
| `stage_code` | `"post_operation"` |
| `display_name` | Localized stage name from `patient_stages` |
| `phase` | `{ code: "post_op", label, color, background }` |
| `title` | Workflow + `workflow.post_op.title` |
| `field_order` | `physician_assessment`, `clinical_aud`, `counselling`, `post_op_notes` |
| `fields` | Same as `schema.fields` (types, labels) |
| `cards` | Card order + header label keys + widgets |
| `select_options.physician_assessment` | `wound`, `implant_bed`, `facial_nerve`, `post_op_xray` → value/label maps |
| `composites` | Shapes + metric keys for `clinical_aud` |
| `counselling` | yes/no widget hints, default `no` |
| `post_op_notes` | textarea rows + placeholder key |
| `defaults_empty` | Empty form state |
| `templates` | GET `post-operation-defaults` |
| `clinical_select` | Categories for physician fields |
| `activation_stage_note` | Explains hidden `activation` / switch-on config |

Use **`schema.template_flags.has_post_operation_defaults`** before offering “Fill template”.

---

## 4. Field specification

Config: `config/patient_clinical.php` → `stage_fields.post_operation`

| Key | Type | Required (config) | Stored in `fields_json` |
|-----|------|-------------------|-------------------------|
| `physician_assessment` | `post_op_physician_assessment` | no | object §4.1 |
| `clinical_aud` | `post_op_clinical_aud` | no | object §4.2 |
| `counselling` | `yes_no` | no | string `"yes"` \| `"no"` |
| `post_op_notes` | `post_op_notes` | no | string |

All four are **optional** on web (no `required: true` in config).

### 4.1 `physician_assessment`

**Normalized shape** (`PostOperationFieldSupport::normalizePhysicianAssessment`):

```json
{
  "wound": "clean",
  "implant_bed": null,
  "facial_nerve": "intact",
  "post_op_xray": "full_insertion"
}
```

**Select options** (config fallback + DB clinical select options):

| Sub-field | Category | Example codes |
|-----------|----------|----------------|
| `wound` | `post_op_wound` | `clean`, `swelling`, `hematoma` |
| `implant_bed` | `post_op_implant_bed` | same pattern |
| `facial_nerve` | `post_op_facial_nerve` | `intact`, `partial_weakness`, `complete_weakness` |
| `post_op_xray` | `post_op_xray` | `full_insertion`, `partial_insertion` |

Use **`post_operation_form.select_options.physician_assessment`** for picklists.

### 4.2 `clinical_aud`

**Normalized shape** (`PostOperationFieldSupport::normalizeClinicalAud`):

```json
{
  "metrics": [
    { "key": "Impedance", "value": "…" },
    { "key": "E cap", "value": "…" },
    { "key": "magnet power", "value": "…" }
  ]
}
```

- Default metric keys (exact): **`Impedance`**, **`E cap`**, **`magnet power`** (lowercase “magnet power”).
- Extra metric rows allowed (same as web `kv-metrics-input`).
- Profile reference: `config/patient_clinical.clinical_aud_profiles.post_operation`.

### 4.3 `counselling`

- Web: checkbox; unchecked → stored **`no`** (hidden field sends `no`).
- Server normalizes `yes_no` fields to **`yes`** or **`no`** (`normalizeCounselling` on save).
- Default empty form: **`no`** (`defaults_empty`).

### 4.4 `post_op_notes`

Free text; trimmed string. Empty string when blank.

---

## 5. Templates

```http
GET /api/v1/patients/{patientId}/records/post-operation-defaults
Authorization: Bearer …
Permission: medical_record.create
```

```json
{
  "has_defaults": true,
  "data": {
    "physician_assessment": { … },
    "clinical_aud": { "metrics": [ … ] },
    "counselling": "no",
    "post_op_notes": ""
  }
}
```

Apply all keys into `fields` before submit. **Save template:** web-only (`save_post_operation_defaults` on POST record).

---

## 6. Submit payload (create / update)

```http
POST /api/v1/patients/{patientId}/records
Content-Type: application/json
```

```json
{
  "stage_id": 6,
  "record_date": "2026-11-02",
  "notes": "Optional record-level notes",
  "fields": {
    "physician_assessment": {
      "wound": "clean",
      "implant_bed": "clean",
      "facial_nerve": "intact",
      "post_op_xray": "full_insertion"
    },
    "clinical_aud": {
      "metrics": [
        { "key": "Impedance", "value": "7 kΩ" },
        { "key": "E cap", "value": "0.3 nC" },
        { "key": "magnet power", "value": "3" }
      ]
    },
    "counselling": "yes",
    "post_op_notes": "Patient counselled on device care."
  }
}
```

**Update:** `PUT …/records/{recordId}` — same shape.

**Normalization:** Server normalizes composites and `counselling` via `MedicalRecordService` + `PostOperationFieldSupport`.

---

## 7. Show / list / brief

- **List / show:** `fields` on `MedicalRecordResource`.
- **Hide empty blocks:** `ClinicalCompositeFields::hasContent` — physician assessment needs any sub-field filled; clinical aud needs any metric value; counselling counts as content when filled (yes/no); notes when non-empty text.
- **Brief highlights** (`PatientBriefService`): all four keys for `post_operation`.

---

## 8. Hidden `activation` stage (do not mix into mobile form)

Config still defines **`stage_fields.activation`** with:

`coordinator`, `activation_date`, `switch_on`, `switch_on_note`, `activation_result`, `comments`

These are **not rendered** on web medical records (stage hidden). Existing production data may use **`post_operation`** records only for post-op + switch-on workflow naming.

If activation is enabled later, treat it as a **separate stage** with its own spec — do not add switch-on fields to the post-op card UI today.

---

## 9. Permissions & visibility

- **Post op / switch on** is visible in stage dropdown (not in `record_form_hidden_stage_codes`).
- Schema / meta: `medical_record.view`; defaults GET: `medical_record.create`.

Resolve `stage_id` where `code === "post_operation"`.

---

## 10. Mobile implementation checklist

- [ ] Post-op blue styling from `post_operation_form.phase`.
- [ ] Four cards in `cards` order (counselling = compact card, no section title on web).
- [ ] Physician assessment: 2-column grid, four clinical selects.
- [ ] Clinical aud: three default metrics + optional extra rows.
- [ ] Counselling: boolean UI → store **`yes`** / **`no`** strings.
- [ ] Notes: multiline ~5 rows.
- [ ] Load template from `post-operation-defaults` when flag true.
- [ ] POST nested `fields` keys — not `field_physician_assessment[...]` at root.
- [ ] **Do not** add switch-on text fields unless activation stage is product-approved.
- [ ] Round-trip with web create/edit.

---

## 11. Common mobile mistakes

| Mistake | Correct |
|---------|---------|
| Expecting `switch_on` on this stage | Only **`post_operation`** four keys on web |
| Flat top-level wound/implant_bed keys | Nested under **`physician_assessment`** |
| Wrong aud keys | **`Impedance`**, **`E cap`**, **`magnet power`** |
| Boolean JSON for counselling without normalization | Send **`"yes"`** / **`"no"`** |
| Using operation/pre-op meta | **`post_operation_form`** / **`post-operation-form-meta`** |
| Treating all fields as required | **All optional** in config |

---

## 12. Source files

| Piece | Path |
|-------|------|
| Field config | `config/patient_clinical.php` → `stage_fields.post_operation` |
| Web form | `resources/views/pages/patients/records/_post_operation_stage_fields.blade.php` |
| Components | `resources/views/components/post-op-*-input.blade.php`, `yes-no-input.blade.php` |
| Template JS | `resources/js/post-operation-template.js` |
| Normalization | `app/Support/PostOperationFieldSupport.php` |
| Defaults service | `app/Services/PostOperationFormDefaultService.php` |
| API meta | `app/Support/PostOperationFormMetaBuilder.php` |
| API defaults | `GET …/post-operation-defaults` |

---

**Related:** [Follow Up — full form spec](./mobile-api-medical-records-follow-up-full-spec.md)

**End of Post op / Switch On medical record specification.**
