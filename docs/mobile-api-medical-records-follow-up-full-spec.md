# KSrelief — Follow Up Medical Record (Web ↔ Mobile 100%)

**Scope:** Stage code **`follow_up`** only (clinical phase **`follow_up`** — purple styling).  
**Web UI:** `_follow_up_stage_fields.blade.php` (four cards).  
**Parent:** [mobile-api-medical-records-full-spec.md](./mobile-api-medical-records-full-spec.md)  
**Version:** 1.0.0 — 2026-11-02

---

## 1. Summary

Follow-up stores **four top-level keys**:

| Key | UI |
|-----|-----|
| `clinical_assessment` | Wound + implant bed selects |
| `audiology_assessment` | Shared **hearing assessment workflow** + KV metrics table |
| `speech_assessment` | 3 selects + Cap/SIR text |
| `follow_up_notes` | Textarea |

**Templates:** user follow-up defaults (`GET follow-up-defaults`; save on web only).

**Hearing workflow** is the same engine as Pre-Op audiology (`HearingAssessmentSupport`, `config/hearing_assessment.php`). See also [Pre-Operation spec §6](./mobile-api-medical-records-pre-operation-full-spec.md) for layout shapes.

---

## 2. Web layout (exact)

**File:** `resources/views/pages/patients/records/_follow_up_stage_fields.blade.php`

```
┌─ Header: "Workflow — Follow Up" (bg #EDE9FE, accent #8B5CF6)
│  [Create only] "Fill Template" if has_follow_up_defaults
│
├─ Card: Clinical Assessment
│    [col-md-6] Wound        — select (follow_up_wound_options)
│    [col-md-6] Implant Bed  — select (follow_up_implant_bed_options)
│
├─ Card: Audiology Assessment
│    Hearing assessment workflow (multi-select types → panels)
│    KV metrics table (default keys + extra rows)
│
├─ Card: Speech Assessment
│    [col-md-4] Communication mood — select
│    [col-md-4] True word           — select
│    [col-md-4] Phrases             — select
│    [col-md-6] Cap                 — text
│    [col-md-6] SIR                 — text
│
└─ Card: Notes
     Textarea 5 rows
```

**Shared record header:** `stage_id`, `record_date`, `notes` — parent spec.

---

## 3. API — load form metadata

### 3.1 Schema

```http
GET /api/v1/patients/{patientId}/records/schema?stage_id={followUpStageId}
Authorization: Bearer …
```

When `stage_code === "follow_up"`, response includes **`follow_up_form`**.

### 3.2 Dedicated meta

```http
GET /api/v1/patients/{patientId}/records/follow-up-form-meta
```

```json
{ "data": { … } }
```

**Builder:** `App\Support\FollowUpFormMetaBuilder`

### 3.3 `follow_up_form` keys (reference)

| Key | Purpose |
|-----|---------|
| `stage_code` | `"follow_up"` |
| `phase` | Purple follow_up colors |
| `field_order` | Four keys in display order |
| `fields` | Same as `schema.fields` |
| `cards` | Card sequence + widgets |
| `select_options` | Clinical + speech select maps |
| `composites` | Shapes, hearing meta, metric keys |
| `defaults_empty` | Empty create state |
| `templates` | GET `follow-up-defaults` |
| `hearing_assessment_reference` | Points to pre-op hearing JSON rules |

Use **`schema.template_flags.has_follow_up_defaults`** for “Fill template”.

---

## 4. Field specification

Config: `config/patient_clinical.php` → `stage_fields.follow_up`

| Key | Type | Required | Stored in `fields_json` |
|-----|------|----------|-------------------------|
| `clinical_assessment` | `follow_up_clinical_assessment` | no | object §4.1 |
| `audiology_assessment` | `follow_up_audiology_assessment` | no | object §4.2 |
| `speech_assessment` | `follow_up_speech_assessment` | no | object §4.3 |
| `follow_up_notes` | `follow_up_notes` | no | string §4.4 |

All optional in config.

### 4.1 `clinical_assessment`

```json
{
  "wound": "clean",
  "implant_bed": null
}
```

**Options** (`follow_up_form.select_options.clinical_assessment`):

| Sub-field | Config key | Codes (examples) |
|-----------|------------|------------------|
| `wound` | `follow_up_wound_options` | `clean`, `infected`, `dehiscent` |
| `implant_bed` | `follow_up_implant_bed_options` | same |

**Legacy:** old records may store wound/implant_bed as metrics tables; server migrates on read (`FollowUpFieldSupport::migrateLegacyClinicalAssessment`). Mobile should send **flat select codes** only.

### 4.2 `audiology_assessment`

Combined object: **hearing workflow** + **metrics** (same `namePrefix` on web — one nested object in JSON).

**Normalized shape** (`FollowUpFieldSupport::normalizeAudiologyAssessment`):

```json
{
  "hearing_types": ["pta", "tympanometry"],
  "assessments": {
    "tympanometry": { "right": "type_a", "left": "type_b" },
    "pta": {
      "bc": { "250": "20", "500": "25" },
      "ac": { "250": "30", "500": "35" }
    }
  },
  "metrics": [
    { "key": "Impedance", "value": "…" },
    { "key": "Ecap", "value": "…" },
    { "key": "Magnet power", "value": "…" }
  ]
}
```

**Hearing types / assessments:** Use **`follow_up_form.composites.audiology_assessment.hearing_assessment`** (same types/frequencies/ear_options as `pre_operation_form.hearing_assessment`). Layout rules: [Pre-Operation spec §6](./mobile-api-medical-records-pre-operation-full-spec.md).

**Metrics (follow-up specific):**

- Default keys (exact spelling): **`Impedance`**, **`Ecap`**, **`Magnet power`**
- Extra metric rows allowed (web `allow-add-rows`).
- On save, server **strips** legacy metric keys: `Hearing level`, `SRT`, `SDS`.

**Empty defaults:** `hearing_types: []`, `assessments: {}`, metrics rows for the three default keys (empty values).

### 4.3 `speech_assessment`

```json
{
  "communication_mood": "verbal",
  "true_word": "yes",
  "phrases": "minimal_not_consistent",
  "cap": "72",
  "sir": "65"
}
```

**Select options** (`select_options.speech_assessment`):

| Field | Config |
|-------|--------|
| `communication_mood` | `follow_up_communication_mood_options` |
| `true_word` | `follow_up_true_word_options` (`minimal_not_consistent`, `yes`, `no`) |
| `phrases` | `follow_up_phrases_options` (same pattern) |

**Text:** `cap`, `sir` — free strings (web labels Cap / SIR).

### 4.4 `follow_up_notes`

Plain trimmed string. Web textarea 5 rows.

**Legacy read:** array with `lines[]` is joined with newlines on normalize — prefer string on mobile POST.

---

## 5. Templates

```http
GET /api/v1/patients/{patientId}/records/follow-up-defaults
Authorization: Bearer …
Permission: medical_record.create
```

```json
{
  "has_defaults": true,
  "data": {
    "clinical_assessment": { … },
    "audiology_assessment": { … },
    "speech_assessment": { … },
    "follow_up_notes": ""
  }
}
```

**Save template:** web-only (`save_follow_up_defaults` on record POST).

---

## 6. Submit payload (create / update)

```http
POST /api/v1/patients/{patientId}/records
Content-Type: application/json
```

```json
{
  "stage_id": 7,
  "record_date": "2026-11-02",
  "notes": "",
  "fields": {
    "clinical_assessment": {
      "wound": "clean",
      "implant_bed": "clean"
    },
    "audiology_assessment": {
      "hearing_types": ["pta"],
      "assessments": {
        "pta": {
          "bc": { "500": "15", "1000": "20" },
          "ac": { "500": "25", "1000": "30" }
        }
      },
      "metrics": [
        { "key": "Impedance", "value": "6 kΩ" },
        { "key": "Ecap", "value": "0.5 nC" },
        { "key": "Magnet power", "value": "2" }
      ]
    },
    "speech_assessment": {
      "communication_mood": "verbal",
      "true_word": "yes",
      "phrases": "no",
      "cap": "80",
      "sir": "70"
    },
    "follow_up_notes": "Routine follow-up visit."
  }
}
```

**Update:** `PUT …/records/{recordId}` — same shape.

**Normalization:** `MedicalRecordService` delegates to `FollowUpFieldSupport` for all four keys.

---

## 7. Show / list / brief

- Hide empty sections with `ClinicalCompositeFields::hasContent` (same composite types as registry).
- **Brief** (`PatientBriefService`): highlights all four keys for `follow_up` stage.

---

## 8. Permissions & visibility

- Follow-up is **visible** in stage dropdown.
- Schema / meta: `medical_record.view`; defaults GET: `medical_record.create`.

Resolve `stage_id` where `code === "follow_up"`.

---

## 9. Mobile implementation checklist

- [ ] Purple follow-up styling from `follow_up_form.phase`.
- [ ] Four cards in order matching `cards`.
- [ ] Clinical: two half-width selects (not nested under wrong key).
- [ ] Audiology: implement **full hearing workflow** + metrics (reuse Pre-Op hearing UI code where possible).
- [ ] Use metric keys **`Impedance`**, **`Ecap`**, **`Magnet power`** — not Post-Op’s `E cap` spelling.
- [ ] Speech: 3× col-4 selects + 2× col-6 text fields.
- [ ] Template from `follow-up-defaults` when flag set.
- [ ] POST under `fields.*` — not `field_clinical_assessment[...]` at root.
- [ ] Round-trip with web.

---

## 10. Common mobile mistakes

| Mistake | Correct |
|---------|---------|
| Flat `wound` at root | Under **`clinical_assessment`** |
| Reusing operation audio keys | Follow-up uses **`Ecap`**, **`Magnet power`** |
| Skipping hearing_types/assessments | Required for audiology **content** when user fills hearing panels |
| Pre-op speech option sets | Use **`follow_up_*_options`** from `follow_up_form` |
| Expecting `follow_up` in `pre_operation_form` | Use **`follow_up_form`** / **`follow-up-form-meta`** |
| Inventing required fields | All optional in config |

---

## 11. Source files

| Piece | Path |
|-------|------|
| Field config | `config/patient_clinical.php` → `stage_fields.follow_up` |
| Web form | `resources/views/pages/patients/records/_follow_up_stage_fields.blade.php` |
| Components | `resources/views/components/follow-up-*-input.blade.php`, `hearing-assessment-workflow-input.blade.php` |
| Template JS | `resources/js/follow-up-template.js` |
| Normalization | `app/Support/FollowUpFieldSupport.php`, `HearingAssessmentSupport.php` |
| Defaults | `app/Services/FollowUpFormDefaultService.php` |
| API meta | `app/Support/FollowUpFormMetaBuilder.php` |

---

**Related:** [Completed — full form spec](./mobile-api-medical-records-completed-full-spec.md)

**End of Follow Up medical record specification.**
