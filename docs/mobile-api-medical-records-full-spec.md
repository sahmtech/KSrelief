# KSrelief — Mobile Medical Records (100% Web Parity)

**Audience:** Flutter team + Cursor agent implementing the mobile app  
**Backend:** Laravel 11, Sanctum Bearer auth, prefix `/api/v1`  
**Web reference:** `https://{host}/patients/{patientId}/records/create` (e.g. patient `20`)  
**Version:** 1.0.0 — 2026-11-02  
**Related:** [mobile-api-patients-full-spec.md](./mobile-api-patients-full-spec.md) (patient CRUD, permissions, campaign scope)

---

## 0. Instructions for Cursor / Flutter (read first)

1. **Do not invent fields or endpoints.** Every screen under web **Medical Records** maps to the REST routes in §3. Dynamic UI is driven by **`GET …/records/schema`**, not hard-coded forms.
2. **Payload shape:** Send clinical data in **`fields`** (object keyed exactly like `fields_json` in DB). Do **not** send `field_surgeon` unless you mirror web form names; prefer nested `fields.surgeon`.
3. **After stage change:** Re-fetch **`schema?stage_id=`** (same as web `stage-fields` AJAX).
4. **Show / list:** Use `MedicalRecordResource` (`fields` = stored JSON). Hide empty composites with the same rule as web: `ClinicalCompositeFields::hasContent($key, $value, $definition)`.
5. **Permissions:** Create/update/delete require Spatie permissions (§2). Default `doctor` role is **view-only** for records.
6. **Parity gaps (web-only today):** §11 — mobile should not stub different behavior; either call web-equivalent API when added or hide actions until backend exposes them.

---

## 1. Web UX map (what you must replicate)

| Web route | Purpose | Mobile screen |
|-----------|---------|---------------|
| `GET /patients/{p}/records` | List all records for patient (date desc) | Records tab / list |
| `GET /patients/{p}/records/create?stage_id=` | New record form | Create record |
| `POST /patients/{p}/records` | Save new record | `POST …/records` |
| `GET /patients/{p}/records/{r}` | Read-only detail + stage fields | Record detail |
| `GET /patients/{p}/records/{r}/edit` | Edit form (same fields as create) | Edit record |
| `PUT /patients/{p}/records/{r}` | Update | `PUT …/records/{r}` |
| `DELETE /patients/{p}/records/{r}` | Delete | `DELETE …/records/{r}` |
| `GET …/records/{r}/export-operation-pdf` | Operative note PDF | Download PDF (operation stage only) |
| `GET …/records/stage-fields?stage_id=` | **HTML** field block | **Use API `schema` instead** |

### 1.1 Create / edit form (web `_form.blade.php`)

Fixed header (always visible):

| Control | Name | Required | Default |
|---------|------|----------|---------|
| Stage | `stage_id` | yes | Patient `current_stage_id`, or query `stage_id` on create |
| Record date | `record_date` | yes | Today (`Y-m-d`) |
| General notes | `notes` | no | textarea, max 2000 |

Below header: **stage-specific dynamic fields** (from schema).  
Bottom on web: optional **admission attachments** (`admission_attachments[]` files) on create/update.

**Stages hidden from create/edit dropdown** (still exist in workflow):  
`admission`, `activation`, `rehab_education` — config `patient_clinical.record_form_hidden_stage_codes`.  
If editing an existing record whose stage is hidden, that stage remains selectable (web merges it into the list).

### 1.2 Operation stage — extra web actions (buttons)

When `stage_code === 'operation'`:

| Web control | Effect |
|-------------|--------|
| Save | Normal store/update |
| Save + export PDF | `export_pdf=1` → redirect to PDF download |
| Save as default | `save_operation_defaults=1` → saves user operation template (web only POST) |
| Load user template | GET `operation-defaults` |
| Load campaign defaults | GET `campaign-operation-defaults?implant_company_id=` |
| Quick fill presets | GET `operation-quick-fill?implant_company_id=` |
| Implant company → electrode cascade | GET `electrode-types?implant_company_id=` |

Same pattern for **`follow_up`**, **`pre_operation`**, **`post_operation`** with their `*-defaults` GET endpoints and “save as default” on web (POST to dedicated web routes).

### 1.3 Show page (web `show.blade.php`)

- Sidebar: date, stage badge, submitter, created_at.
- Main: only fields with **content** (composite-aware).
- Operation: **Export PDF** button if `medical_record.view`.
- **Edit** if `medical_record.update`.

---

## 2. Auth, permissions, campaign scope

All routes: **`Authorization: Bearer {token}`**, `Accept: application/json` (except PDF/binary).

| Permission | Ability |
|------------|---------|
| `medical_record.view` | List, show, schema (viewAny), electrode-types, PDF |
| `medical_record.create` | Create, schema (create), defaults GET, quick-fill |
| `medical_record.update` | Update |
| `medical_record.delete` | Delete |

Policy (`MedicalRecordPolicy`): always requires **`patient.view`** on the patient + record belongs to patient.

**Campaign scope:** Same as patients API — user must access patient’s campaign (`PatientAccessService` on API controllers).

**Default roles:** `doctor` → view only. Coordinators/managers get create/update/delete via Spatie.

---

## 3. REST API — complete route table

Base: **`/api/v1/patients/{patientId}/records`**

| Method | Path | Name | Web equivalent |
|--------|------|------|----------------|
| GET | `/` | index | records index |
| GET | `/schema` | schema | stage-fields (JSON) |
| POST | `/` | store | store |
| GET | `/{recordId}` | show | show |
| PUT | `/{recordId}` | update | update |
| DELETE | `/{recordId}` | destroy | destroy |
| GET | `/electrode-types?implant_company_id=` | electrode-types | same |
| GET | `/follow-up-defaults` | follow-up-defaults | same |
| GET | `/operation-defaults` | operation-defaults | same |
| GET | `/pre-operation-defaults` | pre-operation-defaults | same |
| GET | `/post-operation-defaults` | post-operation-defaults | same |
| GET | `/campaign-operation-defaults?implant_company_id=` | campaign-operation-defaults | same |
| GET | `/operation-quick-fill?implant_company_id=` | operation-quick-fill | same |
| GET | `/{recordId}/export-operation-pdf` | export-operation-pdf | same |

**Route order note:** Static segments (`schema`, `electrode-types`, `*-defaults`) are registered **before** `{record}` — always use these paths literally.

---

## 4. Core flows (implementation order)

### 4.1 List records

```http
GET /api/v1/patients/20/records
```

Response:

```json
{
  "data": [
    {
      "id": 15,
      "patient_id": 20,
      "record_date": "2026-08-05",
      "stage": { "id": 5, "name": "Operation", "code": "operation", "color": "#…" },
      "specialty": null,
      "fields": { "surgeon": 3, "operation_date": "2026-08-05", "…": "…" },
      "notes": "General note",
      "submitted_by": { "id": 1, "name": "Admin" },
      "created_at": "…",
      "updated_at": "…"
    }
  ]
}
```

Sort: **`record_date` desc** (same as `MedicalRecordService::getPatientRecords`).

### 4.2 Build create form (critical)

**Step A — load schema**

```http
GET /api/v1/patients/20/records/schema?stage_id=5
GET /api/v1/patients/20/records/schema?stage_id=5&implant_company_id=2
```

Query params:

| Param | Purpose |
|-------|---------|
| `stage_id` | Selected stage (optional; default = patient `current_stage`, else first visible stage) |
| `implant_company_id` | Pre-load `electrode_types` in schema response (operation stage) |

**Step B — render** each key in `fields` by `type` (§7).  
**Step C — on stage dropdown change:** repeat Step A.

**Step C2 — operation:** on `implant_company_id` change, either re-call schema with `implant_company_id` or:

```http
GET /api/v1/patients/20/records/electrode-types?implant_company_id=2
```

**Step D — optional templates before save:**

| Stage | GET |
|-------|-----|
| follow_up | `/follow-up-defaults` |
| operation | `/operation-defaults`, `/campaign-operation-defaults?implant_company_id=`, `/operation-quick-fill?implant_company_id=` |
| pre_operation | `/pre-operation-defaults` |
| post_operation | `/post-operation-defaults` |

Response pattern:

```json
{ "has_defaults": true, "data": { … } }
```

Merge `data` into form `fields` (keys match `fields_json` keys). Operation/campaign templates may include **`_apply_labels`** metadata via `OperationFieldSupport::payloadWithApplyLabels` — use labels for UI only, strip keys starting with `_` before submit if present.

### 4.3 Create record

```http
POST /api/v1/patients/20/records
Content-Type: application/json
```

```json
{
  "stage_id": 5,
  "record_date": "2026-08-05",
  "specialty_id": null,
  "notes": "Optional general notes",
  "fields": {
    "operation_date": "2026-08-05",
    "surgeon": 12,
    "implant_company_id": 2,
    "electrode_type_id": 7,
    "insertion_approach_id": 1,
    "side_of_surgery": "left",
    "insertion_depth": { "selection": "full_insertion", "note": "" },
    "audio_test": { "metrics": [{ "key": "Impedance", "value": "…" }, { "key": "E cap", "value": "…" }] },
    "intra_op_findings": "uneventful",
    "operation_notes": "…"
  }
}
```

**201** → `{ "message", "data": MedicalRecordResource }`.

**Multipart (optional):** same as web admission files:

- `admission_attachments[]` files (max 10MB each, pdf/images/office) — requires `multipart/form-data`; JSON fields can be sent as `fields` JSON string only if you implement server-side decode (web uses separate fields). **Recommended:** upload attachments via `POST /api/v1/patients/{id}/attachments` after save if multipart is hard on mobile.

**Push:** Server sends `medical_record_created` push to campaign doctors on create.

### 4.4 Show record

```http
GET /api/v1/patients/20/records/15
```

Use `data.fields` + `data.stage.code` to pick definitions from cached schema or re-fetch schema for that stage for labels.

**Display rule:** Match web — skip fields where `hasContent` is false.

### 4.5 Update record

```http
PUT /api/v1/patients/20/records/15
```

Same body as create; `stage_id` optional (keeps existing if omitted). **`record_date` required.**

### 4.6 Delete

```http
DELETE /api/v1/patients/20/records/15
```

### 4.7 Operation PDF

```http
GET /api/v1/patients/20/records/15/export-operation-pdf
```

Binary PDF; only valid when record’s stage code is **`operation`**.

---

## 5. Schema response (actual backend shape)

```json
{
  "stage_id": 5,
  "stage_code": "operation",
  "stages": [
    { "id": 4, "name": "Pre Operation", "code": "pre_operation" }
  ],
  "fields": {
    "operation_date": {
      "type": "date",
      "phase": "intra_op",
      "label": "Operation Date",
      "required": true
    },
    "surgeon": {
      "type": "member_select",
      "member_role": "doctor",
      "phase": "intra_op",
      "label": "Surgeon",
      "required": true
    },
    "side_of_surgery": {
      "type": "select",
      "options": { "right": "Right", "left": "Left", "bilateral": "Bilateral" },
      "options_key": "operation_side_of_surgery_options",
      "label": "Side of surgery",
      "required": false
    }
  },
  "team_members": {
    "doctors": [{ "id": 12, "name": "Dr. …" }],
    "specialists": [],
    "coordinators": []
  },
  "implant_companies": [{ "id": 1, "name": "…", "code": "…" }],
  "insertion_approaches": [{ "id": 1, "name": "…" }],
  "electrode_types": [],
  "template_flags": {
    "has_follow_up_defaults": true,
    "has_operation_defaults": false,
    "has_pre_operation_defaults": true,
    "has_post_operation_defaults": false,
    "has_campaign_operation_defaults": true
  }
}
```

**`member_select`:** Filter `team_members` by definition’s `member_role`:

| `member_role` | API key |
|---------------|---------|
| `doctor` | `team_members.doctors` |
| `specialist` | `team_members.specialists` |
| `coordinator` | `team_members.coordinators` |

Stored value: **numeric member id** (not user id).

**`company_select` / `electrode_select`:** Store **`implant_company_id`** / **`electrode_type_id`** integers.

---

## 6. Stage catalog (`config/patient_clinical.php`)

### 6.1 Visible on create/edit dropdown

All **`stage_fields`** keys **except** hidden: `admission`, `activation`, `rehab_education`.

| `stage_code` | Phase (typical) | Field keys |
|--------------|-----------------|------------|
| `pre_operation` | pre_op | `physician_assessment`, `imaging_findings`, `audiology_decision`, `speech_assessment` |
| `anesthesia` | pre_op | `attending_doctor`, `anesthesia_type`, `npo_time`, `asa_score`, `weight`, `anesthesia_notes` |
| `operation` | intra_op | `operation_date`, `surgeon`, `implant_company_id`, `electrode_type_id`, `insertion_approach_id`, `side_of_surgery`, `insertion_depth`, `time_in_surgery`, `time_out_surgery`, `audio_test`, `intra_op_findings`, `operation_notes` |
| `follow_up` | follow_up | `clinical_assessment`, `audiology_assessment`, `speech_assessment`, `follow_up_notes` |
| `post_operation` | post_op | `physician_assessment`, `clinical_aud`, `counselling`, `post_op_notes` |

### 6.2 Hidden from dropdown (legacy / workflow-only)

| `stage_code` | Field keys |
|--------------|------------|
| `admission` | `coordinator`, `admission_notes`, `initial_assessment` |
| `activation` | `coordinator`, `activation_date`, `switch_on`, `switch_on_note`, `activation_result`, `comments` |
| `rehab_education` | `specialist`, `session_date`, `post_op_audio_education`, `post_op_speech_education`, `education_notes`, `rehab_plan`, `outcome` |

Records for these stages can still exist in DB; coordinators may add via web if stage is forced — mobile should **not** offer them in create unless product opens them later.

### 6.3 Operation required fields (validation on web UI)

From config: `operation_date`, `surgeon`, `implant_company_id`, `electrode_type_id` marked **`required: true`**. Enforce on mobile before submit.

---

## 7. Field types → mobile widget + JSON storage

Source of truth for normalization: `MedicalRecordService::buildFieldsJson` + `normalizeFieldValue`.

| type | Widget | Stored in `fields_json[key]` |
|------|--------|------------------------------|
| `text`, `textarea`, `url`, `number`, `date`, `time` | Native inputs | string/number as submitted |
| `select`, `yes_no` | Picker | string option key |
| `member_select` | Picker | **member id** (int) |
| `company_select` | Picker | **implant company id** |
| `electrode_select` | Picker (depends on company) | **electrode type id** |
| `insertion_approach_select` | Picker | **insertion approach id** |
| `clinical_aud` | Metrics list + optional status | `{ "metrics": [{"key","value"}], "status": "…" }` |
| `clinical_speech` | Screening speech UI | `{ "assessment": "…", "notes": "…" }` (see `ClinicalCompositeFields::normalizeSpeech`) |
| `clinical_speech_followup` | Cap/SIR grid | metrics array |
| `imaging_findings` | Per-ear CT/MRI trees | `{ "right": { "ct": [], "mri": [], "ct_notes", "mri_notes", "ct_drive_link", "mri_drive_link" }, "left": { … } }` — CT/MRI entries are **option id strings** |
| `medical_history_screening` | Two lists | `{ "pre_op_request", "general_condition" }` |
| `expandable_checklist` | Multi select | list of selected codes/ids |
| `pre_op_physician_assessment` | Card | `{ "general_condition", "pre_op_request", "clinical_decision", "notes" }` |
| `pre_op_audiology_decision` | Card + hearing tables | `{ "status", "decision", "metrics", "audiology_link", … }` — may include **`HearingAssessmentSupport`** nested keys when used |
| `pre_op_speech_assessment` | Card | `{ "communication_mood", "true_word", "phrases", "decision", "expectations", … }` |
| `follow_up_clinical_assessment` | Wound / implant bed | `{ "wound", "implant_bed" }` |
| `follow_up_audiology_assessment` | Metrics + hearing | `{ "metrics": […], … }` |
| `follow_up_speech_assessment` | Mood / words / Cap / SIR | `{ "communication_mood", "true_word", "phrases", "cap", "sir" }` |
| `follow_up_notes` | Structured notes | string or `{ "notes": "…" }` normalized to string |
| `post_op_physician_assessment` | Card | wound, implant_bed, facial_nerve, post_op_xray, notes |
| `post_op_clinical_aud` | Post-op audiology | `{ "metrics": […] }` |
| `post_op_notes` | Text | string |
| `operation_insertion_depth` | Radio + note | `{ "selection": "full_insertion"|"partial_insertion", "note": "…" }` |
| `operation_audio_test` | Metrics | `{ "metrics": [{ "key": "Impedance"|"E cap", "value" }] }` |
| `operation_intra_op_findings` | Select | string code e.g. `uneventful` |

**Option labels:** Resolved server-side in schema (`options`, `options_key` → config `patient_clinical.*_options`). Use **keys** in JSON payloads, labels for display only.

**Clinical aud profiles** (`config/patient_clinical.clinical_aud_profiles`):

| Profile | Default metric keys |
|---------|---------------------|
| `screening` | Hearing level |
| `post_operation` | Impedance, E cap, magnet power |
| `follow_up` | Impedance, E cap, Aided Hearing level, Data logging, Hearing age, Magnet |

---

## 8. Example payloads by stage (minimal)

### 8.1 `pre_operation`

```json
{
  "stage_id": 4,
  "record_date": "2026-08-01",
  "fields": {
    "physician_assessment": {
      "general_condition": "medically_free",
      "pre_op_request": "need_ct_tb",
      "clinical_decision": "accepted",
      "notes": ""
    },
    "imaging_findings": {
      "right": { "ct": ["12"], "mri": [], "ct_notes": "", "mri_notes": "", "ct_drive_link": "", "mri_drive_link": "" },
      "left": { "ct": [], "mri": [], "ct_notes": "", "mri_notes": "", "ct_drive_link": "", "mri_drive_link": "" }
    },
    "audiology_decision": { "status": "complete", "decision": "accepted_left", "metrics": [], "audiology_link": "https://…" },
    "speech_assessment": { "communication_mood": "verbal", "true_word": "yes", "phrases": "minimal_not_consistent", "decision": "accepted", "cap": "", "sir": "" }
  }
}
```

### 8.2 `follow_up`

```json
{
  "fields": {
    "clinical_assessment": { "wound": "clean", "implant_bed": "clean" },
    "audiology_assessment": { "metrics": [{ "key": "Impedance", "value": "5" }] },
    "speech_assessment": { "communication_mood": "verbal", "true_word": "yes", "phrases": "yes", "cap": "80", "sir": "70" },
    "follow_up_notes": "Patient doing well"
  }
}
```

### 8.3 `post_operation`

```json
{
  "fields": {
    "physician_assessment": { "wound": "clean", "implant_bed": "clean", "facial_nerve": "intact", "post_op_xray": "full_insertion", "notes": "" },
    "clinical_aud": { "metrics": [{ "key": "Impedance", "value": "…" }] },
    "counselling": "yes",
    "post_op_notes": "…"
  }
}
```

---

## 9. Common Flutter mistakes (fix these)

| Symptom | Cause | Fix |
|---------|-------|-----|
| Empty `fields_json` after save | Sent `field_*` flat keys without `fields` object | Use **`fields: { key: value }`** |
| Composites saved as strings | Bypassed server normalization | Send **objects**; backend normalizes (2026-11-02 fix applies same rules as web) |
| 404 on routes | Old deploy / route cache | `php artisan route:clear && route:cache` |
| Doctor cannot create | Missing permission | Use coordinator account or grant `medical_record.create` |
| Wrong surgeon list | Used users instead of members | Use **`team_members.doctors`** ids |
| Electrodes empty | Company not selected | Pass `implant_company_id` to schema or electrode-types |
| Stage change UI stale | Cached first schema | Re-fetch **schema** on every `stage_id` change |
| Campaign defaults wrong shape | Old API bug | Use latest backend; `campaign-operation-defaults` returns **`templateForForm`** shape |

---

## 10. Screening vs medical record

**Screening** lives on **`patients.screening_data`**, not `medical_records`.  
Patient create/update uses keys `screening_{key}` — see patients spec §13.  
Pre-op **record** reuses some screening field **types** (e.g. `imaging_findings`) inside stage `pre_operation`.

**Clinical profile** on patient show/brief merges screening + latest records per phase — read-only context for mobile patient header.

---

## 11. Web ↔ API parity matrix (2026-11-02)

| Feature | Web | API `/api/v1` |
|---------|-----|----------------|
| List / show / create / update / delete | ✅ | ✅ |
| Dynamic schema | HTML `stage-fields` | ✅ `GET schema` |
| Electrode cascade | ✅ | ✅ |
| User templates (GET) | ✅ | ✅ |
| Campaign operation defaults (GET) | ✅ | ✅ (aligned with web `templateForForm`) |
| Operation quick-fill | ✅ | ✅ |
| Save user/campaign defaults (POST) | ✅ | ❌ not exposed — hide “save as template” on mobile or add API later |
| Add CT/MRI/expectation/clinical option (POST) | ✅ | ❌ not exposed — use existing options from schema only |
| Save + PDF in one submit | ✅ `export_pdf` | ❌ — save then `GET export-operation-pdf` |
| Admission attachments on record form | ✅ multipart | ⚠️ same Request supports files; prefer attachments API |
| Push on create | ✅ | ✅ |

---

## 12. Source files (for backend / debugging)

| Area | Path |
|------|------|
| Web controller | `app/Http/Controllers/MedicalRecordController.php` |
| API controller | `app/Http/Controllers/Api/V1/MedicalRecordController.php` |
| Service | `app/Services/MedicalRecordService.php` |
| Requests | `app/Http/Requests/Workflow/StoreMedicalRecordRequest.php`, `UpdateMedicalRecordRequest.php` |
| Field config | `config/patient_clinical.php` |
| Registry | `app/Support/PatientClinicalFieldRegistry.php` |
| Resource | `app/Http/Resources/MedicalRecordResource.php` |
| Web views | `resources/views/pages/patients/records/*` |
| Web JS | `resources/views/pages/patients/records/_form.blade.php`, `resources/js/operation-quick-fill.js` |

---

## 13. Acceptance checklist (100% parity)

- [ ] Create: stage list matches web (hidden stages excluded).
- [ ] Create: default stage = patient current stage; date = today.
- [ ] Stage change reloads all dynamic fields from **schema**.
- [ ] Operation: company → electrodes cascade + quick-fill + defaults buttons behave like web.
- [ ] Pre/op/post/follow-up: load template from respective GET defaults when user taps “Apply template”.
- [ ] Submit sends **`fields`** object; response `data.fields` round-trips.
- [ ] Show hides empty fields like web.
- [ ] Edit pre-fills from `GET …/records/{id}` + schema for labels.
- [ ] PDF export for operation records only.
- [ ] Permissions enforced (no create UI for pure doctor role unless granted).

---

**End of medical records mobile specification.**  
Production base URL example: `https://phplaravel-1451551-6496195.cloudwaysapps.com/api/v1/patients/{id}/records/…`
