<?php
namespace App\Service\Lookup;

use App\Exception\DuplicateRecordException;
use App\Exception\ModelNotFoundException;
use App\Exception\RecordNotFoundException;
use App\Model\IdNameModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\UniqueConstraintViolationException;

class LookupService
{

    private array $modelMap = [
        'age' => \App\Model\Lookup\Age::class,
        'annotation_abbreviation' => \App\Model\Lookup\AnnotationAbbreviation::class,
        'annotation_accentuation' => \App\Model\Lookup\AnnotationAccentuation::class,
        'annotation_accronym' => \App\Model\Lookup\AnnotationAccronym::class,
        'annotation_aspect_content' => \App\Model\Lookup\AnnotationAspectContent::class,
        'annotation_aspect_context' => \App\Model\Lookup\AnnotationAspectContext::class,
        'annotation_aspect_form' => \App\Model\Lookup\AnnotationAspectForm::class,
        'annotation_bigraphism_comments' => \App\Model\Lookup\AnnotationBigraphismComments::class,
        'annotation_bigraphism_domain' => \App\Model\Lookup\AnnotationBigraphismDomain::class,
        'annotation_bigraphism_formulaicity' => \App\Model\Lookup\AnnotationBigraphismFormulaicity::class,
        'annotation_bigraphism_rank' => \App\Model\Lookup\AnnotationBigraphismRank::class,
        'annotation_bigraphism_type' => \App\Model\Lookup\AnnotationBigraphismType::class,
        'annotation_case_content' => \App\Model\Lookup\AnnotationCaseContent::class,
        'annotation_case_context' => \App\Model\Lookup\AnnotationCaseContext::class,
        'annotation_case_form' => \App\Model\Lookup\AnnotationCaseForm::class,
        'annotation_clitic_content' => \App\Model\Lookup\AnnotationCliticContent::class,
        'annotation_clitic_context' => \App\Model\Lookup\AnnotationCliticContext::class,
        'annotation_clitic_form' => \App\Model\Lookup\AnnotationCliticForm::class,
        'annotation_codeswitching_comments' => \App\Model\Lookup\AnnotationCodeswitchingComments::class,
        'annotation_codeswitching_domain' => \App\Model\Lookup\AnnotationCodeswitchingDomain::class,
        'annotation_codeswitching_formulaicity' => \App\Model\Lookup\AnnotationCodeswitchingFormulaicity::class,
        'annotation_codeswitching_rank' => \App\Model\Lookup\AnnotationCodeswitchingRank::class,
        'annotation_codeswitching_type' => \App\Model\Lookup\AnnotationCodeswitchingType::class,
        'annotation_coherence_content' => \App\Model\Lookup\AnnotationCoherenceContent::class,
        'annotation_coherence_context' => \App\Model\Lookup\AnnotationCoherenceContext::class,
        'annotation_coherence_form' => \App\Model\Lookup\AnnotationCoherenceForm::class,
        'annotation_complementation_content' => \App\Model\Lookup\AnnotationComplementationContent::class,
        'annotation_complementation_context' => \App\Model\Lookup\AnnotationComplementationContext::class,
        'annotation_complementation_form' => \App\Model\Lookup\AnnotationComplementationForm::class,
        'annotation_connectivity' => \App\Model\Lookup\AnnotationConnectivity::class,
        'annotation_correction' => \App\Model\Lookup\AnnotationCorrection::class,
        'annotation_curvature' => \App\Model\Lookup\AnnotationCurvature::class,
        'annotation_degree_of_formality' => \App\Model\Lookup\AnnotationDegreeOfFormality::class,
        'annotation_deletion' => \App\Model\Lookup\AnnotationDeletion::class,
        'annotation_expansion' => \App\Model\Lookup\AnnotationExpansion::class,
        'annotation_formulaicity_lexis' => \App\Model\Lookup\AnnotationFormulaicityLexis::class,
        'annotation_formulaicity_morphology' => \App\Model\Lookup\AnnotationFormulaicityMorphology::class,
        'annotation_formulaicity_orthography' => \App\Model\Lookup\AnnotationFormulaicityOrthography::class,
        'annotation_identifier_lexis' => \App\Model\Lookup\AnnotationIdentifierLexis::class,
        'annotation_identifier_morphology' => \App\Model\Lookup\AnnotationIdentifierMorphology::class,
        'annotation_identifier_orthography' => \App\Model\Lookup\AnnotationIdentifierOrthography::class,
        'annotation_insertion' => \App\Model\Lookup\AnnotationInsertion::class,
        'annotation_lineation' => \App\Model\Lookup\AnnotationLineation::class,
        'annotation_modality_content' => \App\Model\Lookup\AnnotationModalityContent::class,
        'annotation_modality_context' => \App\Model\Lookup\AnnotationModalityContext::class,
        'annotation_modality_form' => \App\Model\Lookup\AnnotationModalityForm::class,
        'annotation_orientation' => \App\Model\Lookup\AnnotationOrientation::class,
        'annotation_other_comments' => \App\Model\Lookup\AnnotationOtherComments::class,
        'annotation_other_domain' => \App\Model\Lookup\AnnotationOtherDomain::class,
        'annotation_other_formulaicity' => \App\Model\Lookup\AnnotationOtherFormulaicity::class,
        'annotation_other_rank' => \App\Model\Lookup\AnnotationOtherRank::class,
        'annotation_other_type' => \App\Model\Lookup\AnnotationOtherType::class,
        'annotation_position_in_text' => \App\Model\Lookup\AnnotationPositionInText::class,
        'annotation_position_in_word_lexis' => \App\Model\Lookup\AnnotationPositionInWordLexis::class,
        'annotation_position_in_word_morphology' => \App\Model\Lookup\AnnotationPositionInWordMorphology::class,
        'annotation_position_in_word_orthography' => \App\Model\Lookup\AnnotationPositionInWordOrthography::class,
        'annotation_prescription_lexis' => \App\Model\Lookup\AnnotationPrescriptionLexis::class,
        'annotation_prescription_morphology' => \App\Model\Lookup\AnnotationPrescriptionMorphology::class,
        'annotation_prescription_orthography' => \App\Model\Lookup\AnnotationPrescriptionOrthography::class,
        'annotation_proscription_lexis' => \App\Model\Lookup\AnnotationProscriptionLexis::class,
        'annotation_proscription_morphology' => \App\Model\Lookup\AnnotationProscriptionMorphology::class,
        'annotation_proscription_orthography' => \App\Model\Lookup\AnnotationProscriptionOrthography::class,
        'annotation_punctuation' => \App\Model\Lookup\AnnotationPunctuation::class,
        'annotation_regularity' => \App\Model\Lookup\AnnotationRegularity::class,
        'annotation_relativisation_content' => \App\Model\Lookup\AnnotationRelativisationContent::class,
        'annotation_relativisation_context' => \App\Model\Lookup\AnnotationRelativisationContext::class,
        'annotation_relativisation_form' => \App\Model\Lookup\AnnotationRelativisationForm::class,
        'annotation_script_type' => \App\Model\Lookup\AnnotationScriptType::class,
        'annotation_slope' => \App\Model\Lookup\AnnotationSlope::class,
        'annotation_standard_form_lexis' => \App\Model\Lookup\AnnotationStandardFormLexis::class,
        'annotation_standard_form_morphology' => \App\Model\Lookup\AnnotationStandardFormMorphology::class,
        'annotation_standard_form_orthography' => \App\Model\Lookup\AnnotationStandardFormOrthography::class,
        'annotation_subordination_content' => \App\Model\Lookup\AnnotationSubordinationContent::class,
        'annotation_subordination_context' => \App\Model\Lookup\AnnotationSubordinationContext::class,
        'annotation_subordination_form' => \App\Model\Lookup\AnnotationSubordinationForm::class,
        'annotation_subtype_lexis' => \App\Model\Lookup\AnnotationSubtypeLexis::class,
        'annotation_subtype_morphology' => \App\Model\Lookup\AnnotationSubtypeMorphology::class,
        'annotation_subtype_orthography' => \App\Model\Lookup\AnnotationSubtypeOrthography::class,
        'annotation_symbol' => \App\Model\Lookup\AnnotationSymbol::class,
        'annotation_type_formulaicity' => \App\Model\Lookup\AnnotationTypeFormulaicity::class,
        'annotation_type_lexis' => \App\Model\Lookup\AnnotationTypeLexis::class,
        'annotation_type_morphology' => \App\Model\Lookup\AnnotationTypeMorphology::class,
        'annotation_type_orthography' => \App\Model\Lookup\AnnotationTypeOrthography::class,
        'annotation_type_reconstruction' => \App\Model\Lookup\AnnotationTypeReconstruction::class,
        'annotation_vacat' => \App\Model\Lookup\AnnotationVacat::class,
        'annotation_weight' => \App\Model\Lookup\AnnotationWeight::class,
        'annotation_word_class' => \App\Model\Lookup\AnnotationWordClass::class,
        'annotation_word_splitting' => \App\Model\Lookup\AnnotationWordSplitting::class,
        'annotation_wordclass_lexis' => \App\Model\Lookup\AnnotationWordclassLexis::class,
        'annotation_wordclass_morphology' => \App\Model\Lookup\AnnotationWordclassMorphology::class,
        'annotation_wordclass_orthography' => \App\Model\Lookup\AnnotationWordclassOrthography::class,
        'archive' => \App\Model\Lookup\Archive::class,
        'attestation_hypertype' => \App\Model\Lookup\AttestationHypertype::class,
        'collaborator' => \App\Model\Collaborator::class,
        'communicative_goal_subtype' => \App\Model\Lookup\CommunicativeGoalSubtype::class,
        'communicative_goal_type' => \App\Model\Lookup\CommunicativeGoalType::class,
        'domicile' => \App\Model\Lookup\Domicile::class,
        'drawing' => \App\Model\Lookup\Drawing::class,
        'education' => \App\Model\Lookup\Education::class,
        'era' => \App\Model\Lookup\Era::class,
        'form' => \App\Model\Lookup\Form::class,
        'gender' => \App\Model\Lookup\Gender::class,
        'generic_agentive_role' => \App\Model\Lookup\GenericAgentiveRole::class,
        'generic_text_structure_components' => \App\Model\Lookup\GenericTextStructureComponents::class,
        'generic_text_structure_part' => \App\Model\Lookup\GenericTextStructurePart::class,
        'graph_type' => \App\Model\Lookup\GraphType::class,
        'honorific_epithet' => \App\Model\Lookup\HonorificEpithet::class,
        'keyword' => \App\Model\Keyword::class,
        'language' => \App\Model\Lookup\Language::class,
        'layout_text_structure_part' => \App\Model\Lookup\LayoutTextStructurePart::class,
        'level_category_category' => \App\Model\Lookup\LevelCategoryCategory::class,
        'level_category_hypercategory' => \App\Model\Lookup\LevelCategoryHypercategory::class,
        'level_category_subcategory' => \App\Model\Lookup\LevelCategorySubcategory::class,
        'location' => \App\Model\Location::class,
        'location_type' => \App\Model\Lookup\LocationType::class,
        'margin_filler' => \App\Model\Lookup\MarginFiller::class,
        'margin_writing' => \App\Model\Lookup\MarginWriting::class,
        'material' => \App\Model\Lookup\Material::class,
        'preservation_state' => \App\Model\Lookup\PreservationState::class,
        'preservation_status_h' => \App\Model\Lookup\PreservationStatusH::class,
        'preservation_status_w' => \App\Model\Lookup\PreservationStatusW::class,
        'production_stage' => \App\Model\Lookup\ProductionStage::class,
        'project' => \App\Model\Project::class,
        'revision_status' => \App\Model\Lookup\RevisionStatus::class,
        'role' => \App\Model\Lookup\Role::class,
        'script' => \App\Model\Lookup\Script::class,
        'social_distance' => \App\Model\Lookup\SocialDistance::class,
        'social_rank' => \App\Model\Lookup\SocialRank::class,
        'text_format' => \App\Model\Lookup\TextFormat::class,
        'text_structure_alignment' => \App\Model\Lookup\TextStructureAlignment::class,
        'text_structure_annotation_subtype' => \App\Model\Lookup\TextStructureAnnotationSubtype::class,
        'text_structure_annotation_type' => \App\Model\Lookup\TextStructureAnnotationType::class,
        'text_structure_attached_to' => \App\Model\Lookup\TextStructureAttachedTo::class,
        'text_structure_attachment_type' => \App\Model\Lookup\TextStructureAttachmentType::class,
        'text_structure_indentation' => \App\Model\Lookup\TextStructureIndentation::class,
        'text_structure_information_status' => \App\Model\Lookup\TextStructureInformationStatus::class,
        'text_structure_lectional_signs' => \App\Model\Lookup\TextStructureLectionalSigns::class,
        'text_structure_lineation' => \App\Model\Lookup\TextStructureLineation::class,
        'text_structure_orientation' => \App\Model\Lookup\TextStructureOrientation::class,
        'text_structure_pagination' => \App\Model\Lookup\TextStructurePagination::class,
        'text_structure_separation' => \App\Model\Lookup\TextStructureSeparation::class,
        'text_structure_spacing' => \App\Model\Lookup\TextStructureSpacing::class,
        'text_structure_speech_act' => \App\Model\Lookup\TextStructureSpeechAct::class,
        'text_structure_standard_form' => \App\Model\Lookup\TextStructureStandardForm::class,
        'text_subtype' => \App\Model\Lookup\TextSubtype::class,
        'text_type' => \App\Model\Lookup\TextType::class,
        'writing_direction' => \App\Model\Lookup\WritingDirection::class,
    ];

    private ?string $modelName = null;

    /** @var $model class-string<Model> */
    private ?string $model = null;

    private LookupUriTranslator $uriTranslator;

    public static function factory(string $modelName) {
        return new LookupService($modelName);
    }

    public function __construct(string $modelName) {
        if (!$this->isModelSupported($modelName)) {
            throw new ModelNotFoundException($modelName);
        }

        $this->modelName = $modelName;
        $this->model = $this->resolveModelClass($modelName);
        $this->uriTranslator = new LookupUriTranslator($modelName);
    }

    public function getInfo(): array {
        return [
            'id' => 'lookup_service_' . $this->modelName,
            'capabilities' => ['lookup', 'create', 'update', 'delete'],
            'schema' => [
                'data' => [
                    '$schema' => 'http://json-schema.org/draft-07/schema#',
                    'type' => 'object',
                    'properties' => [
                        'name' => ['type' => 'string', 'minLength' => 1],
                    ],
                    'required' => ['name'],
                ],
            ],
        ];
    }

    private function isModelSupported(string $modelName): bool {
        return array_key_exists($modelName, $this->modelMap);
    }

    private function resolveModelClass(string $modelName): string {
        return $this->modelMap[$modelName];
    }

    public function lookup(string $query, int $limit = 20): array {
        /** @var Builder $queryBuilder */
        $queryBuilder = $this->model::query();
        $results = $queryBuilder
            ->where('name', 'LIKE', '%' . $query . '%')
            ->orderBy('name', 'ASC')
            ->limit($limit)
            ->get();

        $data = $results->map(function (IdNameModel $item) {
            return [
                'id' => $this->uriTranslator->toUri($item->getId()),
                'name' => $item->name,
            ];
        })->toArray();

        return $data;
    }

    public function create(array $data): array {
        $data = $this->filterData($data);
        try {
            $item = $this->model::create($data);
        } catch (UniqueConstraintViolationException) {
            throw new DuplicateRecordException($this->modelName, $data['name'] ?? '');
        }

        return $this->serialize($item);
    }

    public function update(int $id, array $data): array {
        $item = $this->findOrFail($id);
        $data = $this->filterData($data);
        try {
            $item->update($data);
        } catch (UniqueConstraintViolationException) {
            throw new DuplicateRecordException($this->modelName, $data['name'] ?? '');
        }

        return $this->serialize($item);
    }

    public function delete(int $id): void {
        $item = $this->findOrFail($id);
        $item->delete();
    }

    private function findOrFail(int $id): IdNameModel {
        $item = $this->model::find($id);
        if ($item === null) {
            throw new RecordNotFoundException($this->modelName, (string) $id);
        }
        return $item;
    }

    private function filterData(array $data): array {
        /** @var IdNameModel $instance */
        $instance = new $this->model();
        $fillable = array_diff($instance->getFillable(), [$instance->getKeyName()]);
        return array_intersect_key($data, array_flip($fillable));
    }

    private function serialize(IdNameModel $item): array {
        return [
            'id' => $this->uriTranslator->toUri($item->getId()),
            'name' => $item->name,
        ];
    }



}