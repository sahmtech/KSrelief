<?php

namespace App\Support;

/**
 * Default OPERATIVE NOTE PDF template (matches the approved export layout).
 */
final class OperativeNotePdfTemplateCatalog
{
    /**
     * @return array{
     *     defaults: array<string, string>,
     *     narrative_paragraphs: list<string>,
     *     post_op_orders: list<string>,
     *     formatting: array<string, float|int|bool>
     * }
     */
    public static function defaults(): array
    {
        return [
            'defaults' => [
                'preop_diagnosis' => 'profound SNHL',
                'postop_diagnosis' => 'profound SNHL',
                'facial_nerve_monitor' => 'used',
                'local_anesthesia' => '1:100000',
                'bed_status' => 'drilled',
            ],
            'narrative_paragraphs' => [
                'Patient was brought to the operating room and placed in a supine position. A surgical safety checklist was completed. General anesthesia was induced and the patient endotracheally intubated. Head rest donut was placed and the head was turned to the opposite side. Marking for the incision in postauricular area 1 cm above mastoid tip. The patient was then prepped and draped in a sterile fashion. Next, the position of the external auditory canal was marked and the ear was taped forward. The post-auricular incision and posterior skin flap was infiltrated with local anesthesia {{local_anesthesia}}.',
                'Post-auricular skin incision was carried down to the level of temporalis fascia. Mucoperiosteal flap elevated. The posterior wall of the ear canal and root of zygoma localized as landmarks for drilling. The flap was placed in self-retaining retractor. Cutting bur is used for cortical mastoidectomy by identifying the lateral semicircular canal and opening the antrum to localize the short process of the incus.',
                'Tight pocket technique used for stimulator-receiver unit 1 cm away from mastoid edge, the bed ({{bed_status}}). The templet of the implant is used to confirm the location',
                'At this stage microscope is introduced to the surgical field to complete the drilling of the facial recess with diamond bur, the location of the facial nerve identified, and middle ear approached with respect to the landmarks (fossa incudis, chorda tympani, mastoid segment of facial nerve). The pyramid and stapedial tendon used as landmark to identify the area of the round window. The overhanging niche drilled at low speed and suction was avoided at this stage.',
                'The implant package opened in sterile field, the processor was then fit into the well. The electrode was inserted {{insertion_depth}} into the scala tympani to appropriate depth and sealed with fascia. The electrode lead was coiled in the mastoid cavity, which was then packed with Gelfoam. The Palva flap was released and secured in place using interrupted sutures. The wound was closed in layers using absorbable suture. Dressing applied with steri-strip.',
                'Audiometric testing done intraoperatively {{audio_metrics}}',
                'General anesthetic was reversed, patient extubated, and transferred to the PACU in stable condition.',
            ],
            'post_op_orders' => [
                'Keep NPO till fully awake',
                'Cont. IVF as per anesthesia',
                'X ray of temporal bone (modified stenvers view )',
                'Paracetamol 15 mg/kg .......... PO Q6H PRN X 7 days',
                'Ibuprofen 10 mg /kg ..........PO Q8hr PRN X 7 days',
                'Cefuroxime 15 mg/kg .......... PO BID X 7 days',
                'Dexamethasone 4 mg IV Q8H X 3 doses',
                'Fucidine ointment local application TID X 7 days',
                'Keep dry ear',
            ],
            'formatting' => [
                'page_margin_mm' => 10,
                'border_width_pt' => 2.5,
                'page_padding_bottom_mm' => 22,
                'body_font_size_pt' => 11,
                'title_font_size_pt' => 16,
                'show_logo' => true,
            ],
        ];
    }

    /**
     * @return list<array{token: string, label: string, description: string}>
     */
    public static function availableTokens(): array
    {
        return [
            [
                'token' => '{{local_anesthesia}}',
                'label' => 'Local anesthesia',
                'description' => 'From template defaults (e.g. 1:100000)',
            ],
            [
                'token' => '{{bed_status}}',
                'label' => 'Bed status',
                'description' => 'From template defaults (e.g. drilled)',
            ],
            [
                'token' => '{{insertion_depth}}',
                'label' => 'Insertion depth',
                'description' => 'From the operation medical record',
            ],
            [
                'token' => '{{audio_metrics}}',
                'label' => 'Audio test metrics',
                'description' => 'From the operation medical record (Impedance, E cap, …)',
            ],
        ];
    }
}
