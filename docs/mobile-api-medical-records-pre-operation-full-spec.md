# KSrelief — Pre-Operation Medical Record (Web ↔ Mobile 100%)

**Scope:** Stage code **`pre_operation`** only — medical record create/edit/show.  
**Web UI:** `/patients/{patientId}/records/create?stage_id={preOpStageId}`  
**Parent doc:** [mobile-api-medical-records-full-spec.md](./mobile-api-medical-records-full-spec.md)  
**Version:** 1.0.0 — 2026-11-02

---

## 1. Golden rules (Flutter / Cursor)

1. Pre-op is **not** four generic fields. It is **four composite objects** in `fields_json`:
   - `physician_assessment`
   - `imaging_findings`
   - `audiology_decision`
   - `speech_assessment`

2. Schema `fields.*.type` values (`pre_op_physician_assessment`, `imaging_findings`, …) describe **widgets**, not flat inputs. Build UI from **`pre_operation_form`** (API §3), mirroring web Blade components.

3. Submit with **`fields: { … }`** (see §8). Server normalizes via `PreOperationFieldSupport` / `ScreeningFieldSupport` / `HearingAssessmentSupport` — same as web `field_*` POST.

4. **Do not** map pre-op to screening patient fields on save — screening lives on `patients.screening_data`; pre-op record is separate. Use screening only for **initial prefill** on create (§7).

---

## 2. Web layout (exact structure to clone)

Web file: `resources/views/pages/patients/records/_pre_operation_stage_fields.blade.php`

```
┌─ Header: "Workflow — Pre-Op" + [Load template] (create only, if user has defaults)
├─ Card 1: Physician assessment
│    ├─ general_condition (select + add option)
│    ├─ pre_op_request (select + add option)
│    ├─ clinical_decision (select + add option)
│    ├─ imaging_findings (tree CT/MRI × right/left) — inside same card
│    └─ notes (textarea)
├─ Card 2: Audiology decision
│    ├─ status (select)
│    ├─ decision (select)
│    ├─ Hearing assessment workflow (multi-select types → dynamic panels)
│    ├─ KV metrics table (fixed keys, no add row)
│    └─ audiology_link (url)
├─ Card 3: Speech assessment
│    ├─ communication_mood, iq, cognitive_function, true_word, phrases, assessment, speech_decision (selects)
│    ├─ expectations_post_ci (select by DB id + optional add on web)
│    ├─ cap, sir (text)
│    └─ notes (textarea)
└─ (Outside stage block on create page) record_date, stage_id, general notes
```

**Phase styling:** `config/patient_clinical.phases.pre_op` — background `#FFF2CC`, accent `#FFD966`.

---

## 3. API — load form metadata (required)

### 3.1 Schema (when stage is pre-op)

```http
GET /api/v1/patients/{patientId}/records/schema?stage_id={preOperationStageId}
Authorization: Bearer …
```

When `stage_code === "pre_operation"`, response includes **`pre_operation_form`** — full catalog (options, trees, hearing types, empty defaults).

Also includes generic keys: `stage_id`, `stages[]`, `fields` (4 entries with composite types), `template_flags.has_pre_operation_defaults`.

### 3.2 Dedicated meta (same payload)

```http
GET /api/v1/patients/{patientId}/records/pre-operation-form-meta
```

```json
{ "data": { …same as pre_operation_form… } }
```

**Implementation:** `App\Support\PreOperationFormMetaBuilder` (merged DB + config options like web `PreOperationFieldSupport::selectOptions`).

### 3.3 User template (apply defaults)

```http
GET /api/v1/patients/{patientId}/records/pre-operation-defaults
```

```json
{
  "has_defaults": true,
  "data": {
    "physician_assessment": { … },
    "imaging_findings": { … },
    "audiology_decision": { … },
    "speech_assessment": { … }
  }
}
```

Merge `data` into your form state (web: `applyPreOperationTemplate` in `resources/js/pre-operation-template.js`).

**Save template:** web-only (`save_pre_operation_defaults` on POST) — not on mobile API yet.

---

## 4. Schema `fields` vs `pre_operation_form`

| Key in `fields` | `type` (widget) | Detail in `pre_operation_form` |
|-----------------|-----------------|--------------------------------|
| `physician_assessment` | `pre_op_physician_assessment` | `select_options.physician_assessment` |
| `imaging_findings` | `imaging_findings` | `imaging_findings.trees` + modalities/ears |
| `audiology_decision` | `pre_op_audiology_decision` | `select_options.audiology_decision`, `hearing_assessment`, `audiology_metrics_default_keys` |
| `speech_assessment` | `pre_op_speech_assessment` | `select_options.speech_assessment`, `expectation_post_ci_options` |

---

## 5. Field-by-field specification

### 5.1 `physician_assessment`

**Storage type:** object  
**Web names:** `field_physician_assessment[…]`  
**API names:** `fields.physician_assessment.…`

| Sub-field | UI | Value type | Options category (API map) |
|-----------|-----|------------|----------------------------|
| `general_condition` | Select | string code | `pre_op_general_condition` → config `medical_history_general_condition_options` |
| `pre_op_request` | Select | string code | `pre_op_pre_op_request` |
| `clinical_decision` | Select | string code | `pre_op_clinical_decision` |
| `notes` | Textarea | string | — |

**Option codes (config defaults — labels in API `select_options`):**

`general_condition`: `medically_free`, `need_clearance`  
`pre_op_request`: `farther_investigation`, `need_ct_tb`, `need_x_ray`, `need_rmi`  
`clinical_decision`: `accepted`, `rejected`, `postponed_further_test`

**Legacy prefill:** If record empty, web merges `patient.screening_data.medical_history` into physician fields (`resolvePhysicianAssessmentForForm`).

---

### 5.2 `imaging_findings` (inside physician card on web)

**Storage type:** object `{ right, left }` per ear:

```json
{
  "right": {
    "ct": ["path.to.leaf.key"],
    "mri": [],
    "ct_notes": "",
    "mri_notes": "",
    "ct_drive_link": "",
    "mri_drive_link": ""
  },
  "left": { … }
}
```

- **`ct` / `mri`:** array of **leaf path strings** from `config/imaging_findings_tree.php` (e.g. `cochlea.apical`), not numeric IDs. Legacy numeric IDs are migrated server-side.
- **UI:** CT/MRI tabs × Right/Left ear; expandable tree from `pre_operation_form.imaging_findings.trees`.
- **Legacy prefill:** `patient.screening_data.imaging_findings` when record field empty.

Web add-option for CT/MRI nodes: POST `imaging-options/ct|mri` — **not on mobile API**; mobile uses tree keys only.

---

### 5.3 `audiology_decision`

**Storage type:** object — merge of metrics, status, decision, link, **and** hearing workflow:

| Sub-field | UI | Notes |
|-----------|-----|--------|
| `status` | Select | Codes: `complete`, `required_more_assessment`, `not_done`. Accept alias `required_more` / `assessment` → normalized to `required_more_assessment` |
| `decision` | Select | See `pre_op_audiology_decision_options` in config |
| `metrics` | Fixed 3-row KV | Keys **exactly:** `HA usage`, `Duration of HA Usage`, `Deafness age` — no extra rows on web |
| `audiology_link` | URL input | |
| `hearing_types` | Multi-select | List of keys from `hearing_assessment.types` |
| `assessments` | Nested object | Per selected type — see §6 |

**Decision codes (examples):** `accepted_need_more_test`, `postponed_further_exam`, `rejected`, `accepted_left`, `accepted_right`

**Legacy prefill:** `screening.clinical_aud`, `screening.audiology_link`, `screening.deafness_age` → audiology block when record empty.

---

### 5.4 `speech_assessment`

**Storage type:** object

| Sub-field | UI | Options category |
|-----------|-----|------------------|
| `communication_mood` | Select | `pre_op_speech_communication_mood` |
| `iq` | Select | `pre_op_speech_iq` (`average`, `below_average`) |
| `cognitive_function` | Select | `pre_op_speech_cognitive_function` (`wnl`, `deficit`) |
| `true_word` | Select | `pre_op_speech_true_word` |
| `phrases` | Select | `pre_op_speech_phrases` |
| `assessment` | Select | `pre_op_speech_assessment` (`complete`, `required_more_assessment`, `not_done_yet`) |
| `speech_decision` | Select | `pre_op_speech_decision` |
| `expectations_post_ci` | Select | **DB id as string** from `expectation_post_ci_options` |
| `cap` | Text | |
| `sir` | Text | |
| `notes` | Textarea | |

**Legacy prefill:** `screening.clinical_speech` maps old assessment enum to `assessment`; `expectations_post_ci` from screening checklist.

---

## 6. Hearing assessment JSON (`audiology_decision` nested)

Config: `config/hearing_assessment.php` — full meta in `pre_operation_form.hearing_assessment`.

**Top level (inside `fields.audiology_decision`):**

```json
{
  "status": "complete",
  "decision": "accepted_left",
  "metrics": [
    { "key": "HA usage", "value": "Yes" },
    { "key": "Duration of HA Usage", "value": "2y" },
    { "key": "Deafness age", "value": "3" }
  ],
  "audiology_link": "https://…",
  "hearing_types": ["pta", "tympanometry"],
  "assessments": {
    "tympanometry": { "right": "type_a", "left": "type_b" },
    "pta": {
      "bc": { "250": "…", "500": "…" },
      "ac": { "250": "…", "500": "…" }
    }
  }
}
```

**Layouts:**

| `layout` | `assessments[type]` shape |
|----------|---------------------------|
| `ear_options` | `{ "right": "<code>", "left": "<code>" }` |
| `bc_ac_table` | `{ "bc": { freq: value }, "ac": { freq: value } }` — freqs from `frequencies` |
| `speech_table` | speech audiometry grid (see web partial) |
| `ff_table` | aided FF grid |

Only include keys under `assessments` for types listed in `hearing_types`.

---

## 7. Create / edit flow (mobile)

1. Resolve `pre_operation` **`stage_id`** from `schema.stages` (code `pre_operation`).
2. `GET schema?stage_id=` → read `pre_operation_form` + `template_flags`.
3. Initialize form state from `pre_operation_form.defaults_empty`.
4. **Optional:** If creating, merge patient `screening_data` using rules in `pre_operation_form.legacy_prefill_sources` (mirror web `_pre_operation_stage_fields.blade.php` PHP).
5. **Optional:** If `has_pre_operation_defaults`, `GET pre-operation-defaults` and merge.
6. User edits → build `fields` object (§8).
7. `POST` or `PUT` with `stage_id`, `record_date`, `notes`, `fields`.

**Edit:** `GET …/records/{id}` → `data.fields` is source of truth; use schema/meta for labels/options.

**Show:** Render like web `show.blade.php` — hide sections with no content (`PreOperationFieldSupport::has*Content` logic).

---

## 8. Submit payload (canonical)

```http
POST /api/v1/patients/20/records
Content-Type: application/json
```

```json
{
  "stage_id": 4,
  "record_date": "2026-11-02",
  "notes": "Optional record-level notes",
  "fields": {
    "physician_assessment": {
      "general_condition": "medically_free",
      "pre_op_request": "need_ct_tb",
      "clinical_decision": "accepted",
      "notes": ""
    },
    "imaging_findings": {
      "right": {
        "ct": ["some.leaf.key"],
        "mri": [],
        "ct_notes": "",
        "mri_notes": "",
        "ct_drive_link": "",
        "mri_drive_link": ""
      },
      "left": {
        "ct": [],
        "mri": [],
        "ct_notes": "",
        "mri_notes": "",
        "ct_drive_link": "",
        "mri_drive_link": ""
      }
    },
    "audiology_decision": {
      "status": "complete",
      "decision": "accepted_left",
      "metrics": [
        { "key": "HA usage", "value": "" },
        { "key": "Duration of HA Usage", "value": "" },
        { "key": "Deafness age", "value": "4" }
      ],
      "audiology_link": "",
      "hearing_types": [],
      "assessments": {}
    },
    "speech_assessment": {
      "communication_mood": "verbal",
      "iq": "average",
      "cognitive_function": "wnl",
      "true_word": "yes",
      "phrases": "minimal_not_consistent",
      "cap": "",
      "sir": "",
      "expectations_post_ci": "12",
      "assessment": "complete",
      "speech_decision": "accepted",
      "notes": ""
    }
  }
}
```

**201** → `data.fields` should match normalized storage.

---

## 9. Common mobile bugs (pre-op)

| Bug | Fix |
|-----|-----|
| Treating `physician_assessment` as string | Must be **object** with 4 keys |
| Missing `iq`, `cognitive_function`, `speech_decision` | Required on web — include in speech object |
| Using `clinical_aud` type for pre-op audiology | Pre-op uses **`pre_op_audiology_decision`** shape (§5.3 + §6), not screening `clinical_aud` |
| Imaging as flat text | Use **tree leaf paths** per ear/modality |
| Expectations as free text | Value is **option id** from `expectation_post_ci_options` |
| Options hardcoded in app | Load from **`pre_operation_form.select_options`** (includes DB custom options) |
| Ignoring hearing workflow | Persist `hearing_types` + `assessments` inside `audiology_decision` |

---

## 10. Web ↔ API parity checklist (pre-op)

- [ ] Four cards/sections in same order as web
- [ ] All select fields use API option maps (not guessed enums)
- [ ] Imaging tree paths match `imaging_findings_tree` leaves
- [ ] Audiology metrics keys exactly three strings (§5.3)
- [ ] Hearing assessment types/panels match `hearing_assessment.types`
- [ ] Speech includes Cap/SIR + expectations id
- [ ] Template load from `pre-operation-defaults`
- [ ] POST `fields` round-trips with web-created record on same patient
- [ ] Show view hides empty composites like web

---

## 11. Source reference (backend)

| Piece | Location |
|-------|----------|
| Web pre-op UI | `resources/views/pages/patients/records/_pre_operation_stage_fields.blade.php` |
| Physician component | `resources/views/components/pre-op-physician-assessment-input.blade.php` |
| Audiology component | `resources/views/components/pre-op-audiology-decision-input.blade.php` |
| Speech component | `resources/views/components/pre-op-speech-assessment-input.blade.php` |
| Imaging tree | `resources/views/components/imaging-findings-input.blade.php` |
| Normalize/save | `App\Support\PreOperationFieldSupport`, `MedicalRecordService::buildFieldsJson` |
| API meta | `App\Support\PreOperationFormMetaBuilder` |
| Template JS | `resources/js/pre-operation-template.js` |

---

**Next stages (separate specs):** `operation`, `follow_up`, `post_operation`, …

**End of Pre-Operation medical record specification.**
