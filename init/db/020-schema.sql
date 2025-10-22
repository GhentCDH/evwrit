\connect evwrit

CREATE TABLE IF NOT EXISTS archive (
	archive_id integer NOT NULL,
	name varchar NULL,
	CONSTRAINT archive_pk PRIMARY KEY (archive_id)
);CREATE TABLE IF NOT EXISTS collaborator (
	collaborator_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT collaborator_pk PRIMARY KEY (collaborator_id)
);

CREATE UNIQUE INDEX collaborator_name_index ON public.collaborator (name);CREATE TABLE IF NOT EXISTS drawing (
	drawing_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT drawing_pk PRIMARY KEY (drawing_id)
);

CREATE UNIQUE INDEX drawing_name_index ON public.drawing (name);CREATE TABLE IF NOT EXISTS era (
	era_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT era_pk PRIMARY KEY (era_id)
);

CREATE UNIQUE INDEX era_name_index ON public.era (name);CREATE TABLE IF NOT EXISTS form (
	form_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT form_pk PRIMARY KEY (form_id)
);

CREATE UNIQUE INDEX form_name_index ON public.form (name);CREATE TABLE IF NOT EXISTS gender (
	gender_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT gender_pk PRIMARY KEY (gender_id)
);

CREATE UNIQUE INDEX gender_name_index ON public.gender (name);CREATE TABLE IF NOT EXISTS keyword (
	keyword_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT keyword_pk PRIMARY KEY (keyword_id)
);

CREATE UNIQUE INDEX keyword_name_index ON public.keyword (name);CREATE TABLE IF NOT EXISTS language (
	language_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT language_pk PRIMARY KEY (language_id)
);

CREATE UNIQUE INDEX language_name_index ON public.language (name);CREATE TABLE IF NOT EXISTS location_type (
	location_type_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT location_type_pk PRIMARY KEY (location_type_id)
);

CREATE UNIQUE INDEX location_type_name_index ON public.location_type (name);CREATE TABLE IF NOT EXISTS margin_filler (
	margin_filler_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT margin_filler_pk PRIMARY KEY (margin_filler_id)
);

CREATE UNIQUE INDEX margin_filler_name_index ON public.margin_filler (name);CREATE TABLE IF NOT EXISTS margin_writing (
	margin_writing_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT margin_writing_pk PRIMARY KEY (margin_writing_id)
);

CREATE UNIQUE INDEX margin_writing_name_index ON public.margin_writing (name);CREATE TABLE IF NOT EXISTS material (
	material_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT material_pk PRIMARY KEY (material_id)
);

CREATE UNIQUE INDEX material_name_index ON public.material (name);CREATE TABLE IF NOT EXISTS preservation_state (
	preservation_state_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT preservation_state_pk PRIMARY KEY (preservation_state_id)
);

CREATE UNIQUE INDEX preservation_state_name_index ON public.preservation_state (name);CREATE TABLE IF NOT EXISTS preservation_status_h (
	preservation_status_h_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT preservation_status_h_pk PRIMARY KEY (preservation_status_h_id)
);

CREATE UNIQUE INDEX preservation_status_h_name_index ON public.preservation_status_h (name);CREATE TABLE IF NOT EXISTS preservation_status_w (
	preservation_status_w_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT preservation_status_w_pk PRIMARY KEY (preservation_status_w_id)
);

CREATE UNIQUE INDEX preservation_status_w_name_index ON public.preservation_status_w (name);CREATE TABLE IF NOT EXISTS project (
	project_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT project_pk PRIMARY KEY (project_id)
);

CREATE UNIQUE INDEX project_name_index ON public.project (name);CREATE TABLE IF NOT EXISTS region (
	region_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT region_pk PRIMARY KEY (region_id)
);

CREATE UNIQUE INDEX region_name_index ON public.region (name);CREATE TABLE IF NOT EXISTS revision_status (
	revision_status_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT revision_status_pk PRIMARY KEY (revision_status_id)
);

CREATE UNIQUE INDEX revision_status_name_index ON public.revision_status (name);CREATE TABLE IF NOT EXISTS script (
	script_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT script_pk PRIMARY KEY (script_id)
);

CREATE UNIQUE INDEX script_name_index ON public.script (name);CREATE TABLE IF NOT EXISTS social_distance (
	social_distance_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT social_distance_pk PRIMARY KEY (social_distance_id)
);

CREATE UNIQUE INDEX social_distance_name_index ON public.social_distance (name);CREATE TABLE IF NOT EXISTS text_format (
	text_format_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT text_format_pk PRIMARY KEY (text_format_id)
);

CREATE UNIQUE INDEX text_format_name_index ON public.text_format (name);CREATE TABLE IF NOT EXISTS url (
	url_id serial NOT NULL,
	title varchar NULL,
	url varchar NOT NULL,
	CONSTRAINT url_pk PRIMARY KEY (url_id)
);CREATE TABLE IF NOT EXISTS writing_direction (
	writing_direction_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT writing_direction_pk PRIMARY KEY (writing_direction_id)
);

CREATE UNIQUE INDEX writing_direction_name_index ON public.writing_direction (name);CREATE TABLE IF NOT EXISTS ancient_person (
	ancient_person_id integer NOT NULL,
	tm_id integer NULL,
	name varchar NULL,
	alias varchar NULL,
	patronymic varchar NULL,
	gender_id integer NULL,
	CONSTRAINT ancient_person_pk PRIMARY KEY (ancient_person_id)
);

ALTER TABLE public.ancient_person ADD CONSTRAINT gender_id_fk FOREIGN KEY (gender_id) REFERENCES public.gender(gender_id);CREATE TABLE IF NOT EXISTS image (
	image_id serial NOT NULL,
	filename varchar NOT NULL,
	width real NULL,
	height real NULL,
	margin_top real NULL,
	margin_bottom real NULL,
	margin_left real NULL,
	margin_right real NULL,
	line_height real NULL,
	interlinear_space real NULL,
	kollemata varchar NULL,
	kollemata_cm varchar NULL,
	px_per_cm real NULL,
	orientation varchar NULL,
	is_incomplete bool NOT NULL,
	is_measurable bool NULL,
	publish bool NOT NULL,
	source varchar NULL,
	copyright varchar NULL,
	comment text NULL,
	status text NULL,
	CONSTRAINT image_pk PRIMARY KEY (image_id)
);CREATE TABLE IF NOT EXISTS location (
	location_id integer NOT NULL,
	name varchar NULL,
	tm_id integer NULL,
	default_location_type_id integer NULL,
	CONSTRAINT location_pk PRIMARY KEY (location_id)
);

ALTER TABLE public.location ADD CONSTRAINT default_location_type_id_fk FOREIGN KEY (default_location_type_id) REFERENCES public.location_type(location_type_id);CREATE TABLE IF NOT EXISTS location__region (
	location__region_id serial NOT NULL,
	location_id integer NOT NULL,
	region_id integer NOT NULL,
	CONSTRAINT location__region_pk PRIMARY KEY (location__region_id)
);

ALTER TABLE public.location__region ADD CONSTRAINT location_id_fk FOREIGN KEY (location_id) REFERENCES public.location(location_id);
ALTER TABLE public.location__region ADD CONSTRAINT region_id_fk FOREIGN KEY (region_id) REFERENCES public.region(region_id);
CREATE UNIQUE INDEX location__region_foreign_keys_index ON public.location__region (location_id,region_id);CREATE TABLE IF NOT EXISTS text (
	text_id integer NOT NULL,
	tm_id integer NULL,
	archive_id integer NULL,
	title varchar(5000) NOT NULL,
	text text NOT NULL,
	text_lemmas text NOT NULL,
	text_scrubbed text NOT NULL,
	text_edited text NOT NULL,
	comment text NULL,
	note text NULL,
	remark text NULL,
	no_known_translation bool NOT NULL,
	archeological_context text NULL,
	summary text NULL,
	content text NULL,
	inventory text NULL,
	apparatus text NOT NULL,
	year_begin integer NULL,
	year_end integer NULL,
	date_uncertain bool NOT NULL,
	era_id integer NULL,
	margin_top real NULL,
	margin_bottom real NULL,
	margin_left real NULL,
	margin_right real NULL,
	count_lines_auto integer NULL,
	count_words integer NULL,
	lines_min integer NULL,
	lines_max integer NULL,
	columns_min integer NULL,
	columns_max integer NULL,
	width real NULL,
	preservation_status_w_id integer NULL,
	height real NULL,
	preservation_status_h_id integer NULL,
	letters_per_line_auto real NULL,
	letters_per_line_min integer NULL,
	letters_per_line_max integer NULL,
	is_recto bool NULL,
	is_verso bool NULL,
	is_transversa_charta bool NULL,
	interlinear_space real NULL,
	revision_status_id integer NULL,
	text_format_id integer NULL,
	text_format_uncertain bool NOT NULL,
	kollesis integer NULL,
	kollesis_dir varchar NULL,
	kollesis_uncertain bool NOT NULL,
	tomos_synkollesimos bool NULL,
	tomos_synkollesimos_uncertain bool NOT NULL,
	dimension varchar NULL,
	is_measurable bool NULL,
	image_status varchar NULL,
	materiality_status varchar NULL,
	writing_direction_uncertain bool NOT NULL,
	recto_verso_uncertain bool NOT NULL,
	created timestamp NULL,
	created_by varchar NULL,
	modified timestamp NULL,
	modified_by varchar NULL,
	revision_status varchar NULL,
	arabic_relative real NULL,
	greek_relative real NULL,
	latin_relative real NULL,
	coptic_relative real NULL,
	arabic_absolute integer NULL,
	greek_absolute integer NULL,
	latin_absolute integer NULL,
	coptic_absolute integer NULL,
	drawing_id integer NULL,
	margin_filler_id integer NULL,
	margin_writing_id integer NULL,
	CONSTRAINT text_pk PRIMARY KEY (text_id)
);

ALTER TABLE public.text ADD CONSTRAINT era_id_fk FOREIGN KEY (era_id) REFERENCES public.era(era_id);
ALTER TABLE public.text ADD CONSTRAINT preservation_status_w_id_fk FOREIGN KEY (preservation_status_w_id) REFERENCES public.preservation_status_w(preservation_status_w_id);
ALTER TABLE public.text ADD CONSTRAINT preservation_status_h_id_fk FOREIGN KEY (preservation_status_h_id) REFERENCES public.preservation_status_h(preservation_status_h_id);
ALTER TABLE public.text ADD CONSTRAINT revision_status_id_fk FOREIGN KEY (revision_status_id) REFERENCES public.revision_status(revision_status_id);
ALTER TABLE public.text ADD CONSTRAINT text_format_id_fk FOREIGN KEY (text_format_id) REFERENCES public.text_format(text_format_id);
ALTER TABLE public.text ADD CONSTRAINT drawing_id_fk FOREIGN KEY (drawing_id) REFERENCES public.drawing(drawing_id);
ALTER TABLE public.text ADD CONSTRAINT margin_filler_id_fk FOREIGN KEY (margin_filler_id) REFERENCES public.margin_filler(margin_filler_id);
ALTER TABLE public.text ADD CONSTRAINT margin_writing_id_fk FOREIGN KEY (margin_writing_id) REFERENCES public.margin_writing(margin_writing_id);CREATE TABLE IF NOT EXISTS text_translation (
	text_translation_id serial NOT NULL,
	text_id integer NOT NULL,
	text varchar NOT NULL,
	iso_language_id varchar NOT NULL,
	CONSTRAINT text_translation_pk PRIMARY KEY (text_translation_id)
);

CREATE UNIQUE INDEX text_translation_TextId_index ON public.text_translation (text_id);CREATE TABLE IF NOT EXISTS text__collaborator (
	text__collaborator_id serial NOT NULL,
	text_id integer NOT NULL,
	collaborator_id integer NOT NULL,
	CONSTRAINT text__collaborator_pk PRIMARY KEY (text__collaborator_id)
);

ALTER TABLE public.text__collaborator ADD CONSTRAINT text_id_fk FOREIGN KEY (text_id) REFERENCES public.text(text_id);
ALTER TABLE public.text__collaborator ADD CONSTRAINT collaborator_id_fk FOREIGN KEY (collaborator_id) REFERENCES public.collaborator(collaborator_id);
CREATE UNIQUE INDEX text__collaborator_foreign_keys_index ON public.text__collaborator (text_id,collaborator_id);CREATE TABLE IF NOT EXISTS text__form (
	text__form_id serial NOT NULL,
	text_id integer NOT NULL,
	form_id integer NOT NULL,
	CONSTRAINT text__form_pk PRIMARY KEY (text__form_id)
);

ALTER TABLE public.text__form ADD CONSTRAINT text_id_fk FOREIGN KEY (text_id) REFERENCES public.text(text_id);
ALTER TABLE public.text__form ADD CONSTRAINT form_id_fk FOREIGN KEY (form_id) REFERENCES public.form(form_id);
CREATE UNIQUE INDEX text__form_foreign_keys_index ON public.text__form (text_id,form_id);CREATE TABLE IF NOT EXISTS text__image (
	text__image_id serial NOT NULL,
	text_id integer NOT NULL,
	image_id integer NOT NULL,
	CONSTRAINT text__image_pk PRIMARY KEY (text__image_id)
);

ALTER TABLE public.text__image ADD CONSTRAINT text_id_fk FOREIGN KEY (text_id) REFERENCES public.text(text_id);
ALTER TABLE public.text__image ADD CONSTRAINT image_id_fk FOREIGN KEY (image_id) REFERENCES public.image(image_id);
CREATE UNIQUE INDEX text__image_foreign_keys_index ON public.text__image (text_id,image_id);CREATE TABLE IF NOT EXISTS text__keyword (
	text__keyword_id serial NOT NULL,
	text_id integer NOT NULL,
	keyword_id integer NOT NULL,
	CONSTRAINT text__keyword_pk PRIMARY KEY (text__keyword_id)
);

ALTER TABLE public.text__keyword ADD CONSTRAINT text_id_fk FOREIGN KEY (text_id) REFERENCES public.text(text_id);
ALTER TABLE public.text__keyword ADD CONSTRAINT keyword_id_fk FOREIGN KEY (keyword_id) REFERENCES public.keyword(keyword_id);
CREATE UNIQUE INDEX text__keyword_foreign_keys_index ON public.text__keyword (text_id,keyword_id);CREATE TABLE IF NOT EXISTS text__language (
	text__language_id serial NOT NULL,
	text_id integer NOT NULL,
	language_id integer NOT NULL,
	CONSTRAINT text__language_pk PRIMARY KEY (text__language_id)
);

ALTER TABLE public.text__language ADD CONSTRAINT text_id_fk FOREIGN KEY (text_id) REFERENCES public.text(text_id);
ALTER TABLE public.text__language ADD CONSTRAINT language_id_fk FOREIGN KEY (language_id) REFERENCES public.language(language_id);
CREATE UNIQUE INDEX text__language_foreign_keys_index ON public.text__language (text_id,language_id);CREATE TABLE IF NOT EXISTS text__location (
	text__location_id serial NOT NULL,
	text_id integer NOT NULL,
	location_id integer NOT NULL,
	is_written bool NOT NULL,
	is_found bool NOT NULL,
	is_uncertain bool NOT NULL,
	CONSTRAINT text__location_pk PRIMARY KEY (text__location_id)
);

ALTER TABLE public.text__location ADD CONSTRAINT text_id_fk FOREIGN KEY (text_id) REFERENCES public.text(text_id);
ALTER TABLE public.text__location ADD CONSTRAINT location_id_fk FOREIGN KEY (location_id) REFERENCES public.location(location_id);
CREATE UNIQUE INDEX text__location_foreign_keys_index ON public.text__location (text_id,location_id);CREATE TABLE IF NOT EXISTS text__material (
	text__material_id serial NOT NULL,
	text_id integer NOT NULL,
	material_id integer NOT NULL,
	CONSTRAINT text__material_pk PRIMARY KEY (text__material_id)
);

ALTER TABLE public.text__material ADD CONSTRAINT text_id_fk FOREIGN KEY (text_id) REFERENCES public.text(text_id);
ALTER TABLE public.text__material ADD CONSTRAINT material_id_fk FOREIGN KEY (material_id) REFERENCES public.material(material_id);
CREATE UNIQUE INDEX text__material_foreign_keys_index ON public.text__material (text_id,material_id);CREATE TABLE IF NOT EXISTS text__preservation_state (
	text__preservation_state_id serial NOT NULL,
	text_id integer NOT NULL,
	preservation_state_id integer NOT NULL,
	CONSTRAINT text__preservation_state_pk PRIMARY KEY (text__preservation_state_id)
);

ALTER TABLE public.text__preservation_state ADD CONSTRAINT text_id_fk FOREIGN KEY (text_id) REFERENCES public.text(text_id);
ALTER TABLE public.text__preservation_state ADD CONSTRAINT preservation_state_id_fk FOREIGN KEY (preservation_state_id) REFERENCES public.preservation_state(preservation_state_id);
CREATE UNIQUE INDEX text__preservation_state_foreign_keys_index ON public.text__preservation_state (text_id,preservation_state_id);CREATE TABLE IF NOT EXISTS text__project (
	text__project_id serial NOT NULL,
	text_id integer NOT NULL,
	project_id integer NOT NULL,
	CONSTRAINT text__project_pk PRIMARY KEY (text__project_id)
);

ALTER TABLE public.text__project ADD CONSTRAINT project_id_fk FOREIGN KEY (project_id) REFERENCES public.project(project_id);
CREATE UNIQUE INDEX text__project_foreign_keys_index ON public.text__project (text_id,project_id);CREATE TABLE IF NOT EXISTS text__script (
	text__script_id serial NOT NULL,
	text_id integer NOT NULL,
	script_id integer NOT NULL,
	CONSTRAINT text__script_pk PRIMARY KEY (text__script_id)
);

ALTER TABLE public.text__script ADD CONSTRAINT text_id_fk FOREIGN KEY (text_id) REFERENCES public.text(text_id);
ALTER TABLE public.text__script ADD CONSTRAINT script_id_fk FOREIGN KEY (script_id) REFERENCES public.script(script_id);
CREATE UNIQUE INDEX text__script_foreign_keys_index ON public.text__script (text_id,script_id);CREATE TABLE IF NOT EXISTS text__social_distance (
	text__social_distance_id serial NOT NULL,
	text_id integer NOT NULL,
	social_distance_id integer NOT NULL,
	CONSTRAINT text__social_distance_pk PRIMARY KEY (text__social_distance_id)
);

ALTER TABLE public.text__social_distance ADD CONSTRAINT text_id_fk FOREIGN KEY (text_id) REFERENCES public.text(text_id);
ALTER TABLE public.text__social_distance ADD CONSTRAINT social_distance_id_fk FOREIGN KEY (social_distance_id) REFERENCES public.social_distance(social_distance_id);
CREATE UNIQUE INDEX text__social_distance_foreign_keys_index ON public.text__social_distance (text_id,social_distance_id);CREATE TABLE IF NOT EXISTS text__url (
	text__url_id serial NOT NULL,
	text_id integer NOT NULL,
	url_id integer NOT NULL,
	CONSTRAINT text__url_pk PRIMARY KEY (text__url_id)
);

ALTER TABLE public.text__url ADD CONSTRAINT text_id_fk FOREIGN KEY (text_id) REFERENCES public.text(text_id);
ALTER TABLE public.text__url ADD CONSTRAINT url_id_fk FOREIGN KEY (url_id) REFERENCES public.url(url_id);
CREATE UNIQUE INDEX text__url_foreign_keys_index ON public.text__url (text_id,url_id);CREATE TABLE IF NOT EXISTS text__writing_direction (
	text__writing_direction_id serial NOT NULL,
	text_id integer NOT NULL,
	writing_direction_id integer NOT NULL,
	CONSTRAINT text__writing_direction_pk PRIMARY KEY (text__writing_direction_id)
);

ALTER TABLE public.text__writing_direction ADD CONSTRAINT text_id_fk FOREIGN KEY (text_id) REFERENCES public.text(text_id);
ALTER TABLE public.text__writing_direction ADD CONSTRAINT writing_direction_id_fk FOREIGN KEY (writing_direction_id) REFERENCES public.writing_direction(writing_direction_id);
CREATE UNIQUE INDEX text__writing_direction_foreign_keys_index ON public.text__writing_direction (text_id,writing_direction_id);CREATE TABLE IF NOT EXISTS communicative_goal_subtype (
	communicative_goal_subtype_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT communicative_goal_subtype_pk PRIMARY KEY (communicative_goal_subtype_id)
);

CREATE UNIQUE INDEX communicative_goal_subtype_name_index ON public.communicative_goal_subtype (name);CREATE TABLE IF NOT EXISTS communicative_goal_type (
	communicative_goal_type_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT communicative_goal_type_pk PRIMARY KEY (communicative_goal_type_id)
);

CREATE UNIQUE INDEX communicative_goal_type_name_index ON public.communicative_goal_type (name);CREATE TABLE IF NOT EXISTS generic_agentive_role (
	generic_agentive_role_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT generic_agentive_role_pk PRIMARY KEY (generic_agentive_role_id)
);

CREATE UNIQUE INDEX generic_agentive_role_name_index ON public.generic_agentive_role (name);CREATE TABLE IF NOT EXISTS level (
	level_id integer NOT NULL,
	text_id integer NOT NULL,
	number integer NOT NULL,
	attested_in_text varchar NULL,
	CONSTRAINT level_pk PRIMARY KEY (level_id)
);

ALTER TABLE public.level ADD CONSTRAINT text_id_fk FOREIGN KEY (text_id) REFERENCES public.text(text_id);CREATE TABLE IF NOT EXISTS level_category_category (
	level_category_category_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT level_category_category_pk PRIMARY KEY (level_category_category_id)
);

CREATE UNIQUE INDEX level_category_category_name_index ON public.level_category_category (name);CREATE TABLE IF NOT EXISTS level_category_hypercategory (
	level_category_hypercategory_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT level_category_hypercategory_pk PRIMARY KEY (level_category_hypercategory_id)
);

CREATE UNIQUE INDEX level_category_hypercategory_name_index ON public.level_category_hypercategory (name);CREATE TABLE IF NOT EXISTS level_category_subcategory (
	level_category_subcategory_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT level_category_subcategory_pk PRIMARY KEY (level_category_subcategory_id)
);

CREATE UNIQUE INDEX level_category_subcategory_name_index ON public.level_category_subcategory (name);CREATE TABLE IF NOT EXISTS agentive_role (
	agentive_role_id integer NOT NULL,
	name varchar NOT NULL,
	generic_name varchar NOT NULL,
	generic_agentive_role_id integer NULL,
	CONSTRAINT agentive_role_pk PRIMARY KEY (agentive_role_id)
);

ALTER TABLE public.agentive_role ADD CONSTRAINT generic_agentive_role_id_fk FOREIGN KEY (generic_agentive_role_id) REFERENCES public.generic_agentive_role(generic_agentive_role_id);CREATE TABLE IF NOT EXISTS communicative_goal (
	communicative_goal_id integer NOT NULL,
	type varchar NOT NULL,
	communicative_goal_type_id integer NOT NULL,
	subtype varchar NULL,
	communicative_goal_subtype_id integer NULL,
	CONSTRAINT communicative_goal_pk PRIMARY KEY (communicative_goal_id)
);

ALTER TABLE public.communicative_goal ADD CONSTRAINT communicative_goal_type_id_fk FOREIGN KEY (communicative_goal_type_id) REFERENCES public.communicative_goal_type(communicative_goal_type_id);
ALTER TABLE public.communicative_goal ADD CONSTRAINT communicative_goal_subtype_id_fk FOREIGN KEY (communicative_goal_subtype_id) REFERENCES public.communicative_goal_subtype(communicative_goal_subtype_id);CREATE TABLE IF NOT EXISTS greek_latin (
	greek_latin_id integer NOT NULL,
	label varchar NOT NULL,
	sublabel varchar NULL,
	english varchar NOT NULL,
	CONSTRAINT greek_latin_pk PRIMARY KEY (greek_latin_id)
);CREATE TABLE IF NOT EXISTS level_category (
	level_category_id integer NOT NULL,
	category varchar NOT NULL,
	level_category_category_id integer NULL,
	subcategory varchar NULL,
	level_category_subcategory_id integer NULL,
	hypercategory varchar NULL,
	level_category_hypercategory_id integer NULL,
	CONSTRAINT level_category_pk PRIMARY KEY (level_category_id)
);

ALTER TABLE public.level_category ADD CONSTRAINT level_category_category_id_fk FOREIGN KEY (level_category_category_id) REFERENCES public.level_category_category(level_category_category_id);
ALTER TABLE public.level_category ADD CONSTRAINT level_category_subcategory_id_fk FOREIGN KEY (level_category_subcategory_id) REFERENCES public.level_category_subcategory(level_category_subcategory_id);
ALTER TABLE public.level_category ADD CONSTRAINT level_category_hypercategory_id_fk FOREIGN KEY (level_category_hypercategory_id) REFERENCES public.level_category_hypercategory(level_category_hypercategory_id);CREATE TABLE IF NOT EXISTS object (
	object_id integer NOT NULL,
	name varchar NOT NULL,
	CONSTRAINT object_pk PRIMARY KEY (object_id)
);CREATE TABLE IF NOT EXISTS production_stage (
	production_stage_id integer NOT NULL,
	name varchar NOT NULL,
	CONSTRAINT production_stage_pk PRIMARY KEY (production_stage_id)
);CREATE TABLE IF NOT EXISTS level__agentive_role (
	level__agentive_role_id serial NOT NULL,
	agentive_role_id integer NOT NULL,
	level_id integer NOT NULL,
	CONSTRAINT level__agentive_role_pk PRIMARY KEY (level__agentive_role_id)
);

ALTER TABLE public.level__agentive_role ADD CONSTRAINT level_id_fk FOREIGN KEY (level_id) REFERENCES public.level(level_id);
ALTER TABLE public.level__agentive_role ADD CONSTRAINT agentive_role_id_fk FOREIGN KEY (agentive_role_id) REFERENCES public.agentive_role(agentive_role_id);CREATE TABLE IF NOT EXISTS level__communicative_goal (
	level__communicative_goal_id serial NOT NULL,
	communicative_goal_id integer NOT NULL,
	level_id integer NOT NULL,
	CONSTRAINT level__communicative_goal_pk PRIMARY KEY (level__communicative_goal_id)
);

ALTER TABLE public.level__communicative_goal ADD CONSTRAINT level_id_fk FOREIGN KEY (level_id) REFERENCES public.level(level_id);
ALTER TABLE public.level__communicative_goal ADD CONSTRAINT communicative_goal_id_fk FOREIGN KEY (communicative_goal_id) REFERENCES public.communicative_goal(communicative_goal_id);CREATE TABLE IF NOT EXISTS level__greek_latin (
	level__greek_latin_id serial NOT NULL,
	greek_latin_id integer NOT NULL,
	level_id integer NOT NULL,
	CONSTRAINT level__greek_latin_pk PRIMARY KEY (level__greek_latin_id)
);

ALTER TABLE public.level__greek_latin ADD CONSTRAINT level_id_fk FOREIGN KEY (level_id) REFERENCES public.level(level_id);
ALTER TABLE public.level__greek_latin ADD CONSTRAINT greek_latin_id_fk FOREIGN KEY (greek_latin_id) REFERENCES public.greek_latin(greek_latin_id);CREATE TABLE IF NOT EXISTS level__level_category (
	level__level_category_id serial NOT NULL,
	level_category_id integer NOT NULL,
	level_id integer NOT NULL,
	CONSTRAINT level__level_category_pk PRIMARY KEY (level__level_category_id)
);

ALTER TABLE public.level__level_category ADD CONSTRAINT level_id_fk FOREIGN KEY (level_id) REFERENCES public.level(level_id);
ALTER TABLE public.level__level_category ADD CONSTRAINT level_category_id_fk FOREIGN KEY (level_category_id) REFERENCES public.level_category(level_category_id);CREATE TABLE IF NOT EXISTS level__object (
	level__object_id serial NOT NULL,
	object_id integer NOT NULL,
	level_id integer NOT NULL,
	amount varchar NULL,
	action varchar NULL,
	CONSTRAINT level__object_pk PRIMARY KEY (level__object_id)
);

ALTER TABLE public.level__object ADD CONSTRAINT level_id_fk FOREIGN KEY (level_id) REFERENCES public.level(level_id);
ALTER TABLE public.level__object ADD CONSTRAINT object_id_fk FOREIGN KEY (object_id) REFERENCES public.object(object_id);CREATE TABLE IF NOT EXISTS level__production_stage (
	level__production_stage_id serial NOT NULL,
	production_stage_id integer NOT NULL,
	level_id integer NOT NULL,
	is_uncertain bool NOT NULL,
	CONSTRAINT level__production_stage_pk PRIMARY KEY (level__production_stage_id)
);

ALTER TABLE public.level__production_stage ADD CONSTRAINT level_id_fk FOREIGN KEY (level_id) REFERENCES public.level(level_id);
ALTER TABLE public.level__production_stage ADD CONSTRAINT production_stage_id_fk FOREIGN KEY (production_stage_id) REFERENCES public.production_stage(production_stage_id);CREATE TABLE IF NOT EXISTS age (
	age_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT age_pk PRIMARY KEY (age_id)
);

CREATE UNIQUE INDEX age_name_index ON public.age (name);CREATE TABLE IF NOT EXISTS attestation_hypertype (
	attestation_hypertype_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT attestation_hypertype_pk PRIMARY KEY (attestation_hypertype_id)
);

CREATE UNIQUE INDEX attestation_hypertype_name_index ON public.attestation_hypertype (name);CREATE TABLE IF NOT EXISTS domicile (
	domicile_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT domicile_pk PRIMARY KEY (domicile_id)
);

CREATE UNIQUE INDEX domicile_name_index ON public.domicile (name);CREATE TABLE IF NOT EXISTS education (
	education_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT education_pk PRIMARY KEY (education_id)
);

CREATE UNIQUE INDEX education_name_index ON public.education (name);CREATE TABLE IF NOT EXISTS graph_type (
	graph_type_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT graph_type_pk PRIMARY KEY (graph_type_id)
);

CREATE UNIQUE INDEX graph_type_name_index ON public.graph_type (name);CREATE TABLE IF NOT EXISTS honorific_epithet (
	honorific_epithet_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT honorific_epithet_pk PRIMARY KEY (honorific_epithet_id)
);

CREATE UNIQUE INDEX honorific_epithet_name_index ON public.honorific_epithet (name);CREATE TABLE IF NOT EXISTS occupation (
	occupation_id serial NOT NULL,
	name_gr varchar NOT NULL,
	name_en varchar NOT NULL,
	CONSTRAINT occupation_pk PRIMARY KEY (occupation_id)
);

CREATE UNIQUE INDEX occupation_label_index ON public.occupation (name_gr);CREATE TABLE IF NOT EXISTS role (
	role_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT role_pk PRIMARY KEY (role_id)
);

CREATE UNIQUE INDEX role_name_index ON public.role (name);CREATE TABLE IF NOT EXISTS social_rank (
	social_rank_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT social_rank_pk PRIMARY KEY (social_rank_id)
);

CREATE UNIQUE INDEX social_rank_name_index ON public.social_rank (name);CREATE TABLE IF NOT EXISTS attestation (
	attestation_id integer NOT NULL,
	level_id integer NOT NULL,
	ancient_person_id integer NOT NULL,
	attestation_hypertype_id integer NULL,
	education_id integer NULL,
	domicile_id integer NULL,
	location_type_id integer NULL,
	graph_type_id integer NULL,
	age_id integer NULL,
	remark_social_background text NULL,
	comment text NULL,
	occupation_is_overt bool NULL,
	occupation_is_former bool NULL,
	occupation_is_collective bool NULL,
	CONSTRAINT attestation_pk PRIMARY KEY (attestation_id)
);

ALTER TABLE public.attestation ADD CONSTRAINT level_id_fk FOREIGN KEY (level_id) REFERENCES public.level(level_id);
ALTER TABLE public.attestation ADD CONSTRAINT ancient_person_id_fk FOREIGN KEY (ancient_person_id) REFERENCES public.ancient_person(ancient_person_id);
ALTER TABLE public.attestation ADD CONSTRAINT attestation_hypertype_id_fk FOREIGN KEY (attestation_hypertype_id) REFERENCES public.attestation_hypertype(attestation_hypertype_id);
ALTER TABLE public.attestation ADD CONSTRAINT education_id_fk FOREIGN KEY (education_id) REFERENCES public.education(education_id);
ALTER TABLE public.attestation ADD CONSTRAINT domicile_id_fk FOREIGN KEY (domicile_id) REFERENCES public.domicile(domicile_id);
ALTER TABLE public.attestation ADD CONSTRAINT location_type_id_fk FOREIGN KEY (location_type_id) REFERENCES public.location_type(location_type_id);
ALTER TABLE public.attestation ADD CONSTRAINT graph_type_id_fk FOREIGN KEY (graph_type_id) REFERENCES public.graph_type(graph_type_id);
ALTER TABLE public.attestation ADD CONSTRAINT age_id_fk FOREIGN KEY (age_id) REFERENCES public.age(age_id);CREATE TABLE IF NOT EXISTS attestation__honorific_epithet (
	attestation__honorific_epithet_id serial NOT NULL,
	attestation_id integer NOT NULL,
	honorific_epithet_id integer NOT NULL,
	CONSTRAINT attestation__honorific_epithet_pk PRIMARY KEY (attestation__honorific_epithet_id)
);

ALTER TABLE public.attestation__honorific_epithet ADD CONSTRAINT attestation_id_fk FOREIGN KEY (attestation_id) REFERENCES public.attestation(attestation_id);
ALTER TABLE public.attestation__honorific_epithet ADD CONSTRAINT honorific_epithet_id_fk FOREIGN KEY (honorific_epithet_id) REFERENCES public.honorific_epithet(honorific_epithet_id);
CREATE UNIQUE INDEX attestation__honorific_epithet_foreign_keys_index ON public.attestation__honorific_epithet (attestation_id,honorific_epithet_id);CREATE TABLE IF NOT EXISTS attestation__occupation (
	attestation__occupation_id serial NOT NULL,
	attestation_id integer NOT NULL,
	occupation_id integer NOT NULL,
	CONSTRAINT attestation__occupation_pk PRIMARY KEY (attestation__occupation_id)
);

ALTER TABLE public.attestation__occupation ADD CONSTRAINT attestation_id_fk FOREIGN KEY (attestation_id) REFERENCES public.attestation(attestation_id);
ALTER TABLE public.attestation__occupation ADD CONSTRAINT occupation_id_fk FOREIGN KEY (occupation_id) REFERENCES public.occupation(occupation_id);
CREATE UNIQUE INDEX attestation__occupation_foreign_keys_index ON public.attestation__occupation (attestation_id,occupation_id);CREATE TABLE IF NOT EXISTS attestation__role (
	attestation__role_id serial NOT NULL,
	attestation_id integer NOT NULL,
	role_id integer NOT NULL,
	CONSTRAINT attestation__role_pk PRIMARY KEY (attestation__role_id)
);

ALTER TABLE public.attestation__role ADD CONSTRAINT attestation_id_fk FOREIGN KEY (attestation_id) REFERENCES public.attestation(attestation_id);
ALTER TABLE public.attestation__role ADD CONSTRAINT role_id_fk FOREIGN KEY (role_id) REFERENCES public.role(role_id);
CREATE UNIQUE INDEX attestation__role_foreign_keys_index ON public.attestation__role (attestation_id,role_id);CREATE TABLE IF NOT EXISTS attestation__social_rank (
	attestation__social_rank_id serial NOT NULL,
	attestation_id integer NOT NULL,
	social_rank_id integer NOT NULL,
	CONSTRAINT attestation__social_rank_pk PRIMARY KEY (attestation__social_rank_id)
);

ALTER TABLE public.attestation__social_rank ADD CONSTRAINT attestation_id_fk FOREIGN KEY (attestation_id) REFERENCES public.attestation(attestation_id);
ALTER TABLE public.attestation__social_rank ADD CONSTRAINT social_rank_id_fk FOREIGN KEY (social_rank_id) REFERENCES public.social_rank(social_rank_id);
CREATE UNIQUE INDEX attestation__social_rank_foreign_keys_index ON public.attestation__social_rank (attestation_id,social_rank_id);CREATE TABLE IF NOT EXISTS annotation_abbreviation (
	annotation_abbreviation_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_abbreviation_pk PRIMARY KEY (annotation_abbreviation_id)
);

CREATE UNIQUE INDEX annotation_abbreviation_name_index ON public.annotation_abbreviation (name);CREATE TABLE IF NOT EXISTS annotation_accentuation (
	annotation_accentuation_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_accentuation_pk PRIMARY KEY (annotation_accentuation_id)
);

CREATE UNIQUE INDEX annotation_accentuation_name_index ON public.annotation_accentuation (name);CREATE TABLE IF NOT EXISTS annotation_accronym (
	annotation_accronym_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_accronym_pk PRIMARY KEY (annotation_accronym_id)
);

CREATE UNIQUE INDEX annotation_accronym_name_index ON public.annotation_accronym (name);CREATE TABLE IF NOT EXISTS annotation_aspect_content (
	annotation_aspect_content_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_aspect_content_pk PRIMARY KEY (annotation_aspect_content_id)
);

CREATE UNIQUE INDEX annotation_aspect_content_name_index ON public.annotation_aspect_content (name);CREATE TABLE IF NOT EXISTS annotation_aspect_context (
	annotation_aspect_context_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_aspect_context_pk PRIMARY KEY (annotation_aspect_context_id)
);

CREATE UNIQUE INDEX annotation_aspect_context_name_index ON public.annotation_aspect_context (name);CREATE TABLE IF NOT EXISTS annotation_aspect_form (
	annotation_aspect_form_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_aspect_form_pk PRIMARY KEY (annotation_aspect_form_id)
);

CREATE UNIQUE INDEX annotation_aspect_form_name_index ON public.annotation_aspect_form (name);CREATE TABLE IF NOT EXISTS annotation_bigraphism_comments (
	annotation_bigraphism_comments_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_bigraphism_comments_pk PRIMARY KEY (annotation_bigraphism_comments_id)
);

CREATE UNIQUE INDEX annotation_bigraphism_comments_name_index ON public.annotation_bigraphism_comments (name);CREATE TABLE IF NOT EXISTS annotation_bigraphism_domain (
	annotation_bigraphism_domain_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_bigraphism_domain_pk PRIMARY KEY (annotation_bigraphism_domain_id)
);

CREATE UNIQUE INDEX annotation_bigraphism_domain_name_index ON public.annotation_bigraphism_domain (name);CREATE TABLE IF NOT EXISTS annotation_bigraphism_formulaicity (
	annotation_bigraphism_formulaicity_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_bigraphism_formulaicity_pk PRIMARY KEY (annotation_bigraphism_formulaicity_id)
);

CREATE UNIQUE INDEX annotation_bigraphism_formulaicity_name_index ON public.annotation_bigraphism_formulaicity (name);CREATE TABLE IF NOT EXISTS annotation_bigraphism_rank (
	annotation_bigraphism_rank_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_bigraphism_rank_pk PRIMARY KEY (annotation_bigraphism_rank_id)
);

CREATE UNIQUE INDEX annotation_bigraphism_rank_name_index ON public.annotation_bigraphism_rank (name);CREATE TABLE IF NOT EXISTS annotation_bigraphism_type (
	annotation_bigraphism_type_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_bigraphism_type_pk PRIMARY KEY (annotation_bigraphism_type_id)
);

CREATE UNIQUE INDEX annotation_bigraphism_type_name_index ON public.annotation_bigraphism_type (name);CREATE TABLE IF NOT EXISTS annotation_case_content (
	annotation_case_content_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_case_content_pk PRIMARY KEY (annotation_case_content_id)
);

CREATE UNIQUE INDEX annotation_case_content_name_index ON public.annotation_case_content (name);CREATE TABLE IF NOT EXISTS annotation_case_context (
	annotation_case_context_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_case_context_pk PRIMARY KEY (annotation_case_context_id)
);

CREATE UNIQUE INDEX annotation_case_context_name_index ON public.annotation_case_context (name);CREATE TABLE IF NOT EXISTS annotation_case_form (
	annotation_case_form_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_case_form_pk PRIMARY KEY (annotation_case_form_id)
);

CREATE UNIQUE INDEX annotation_case_form_name_index ON public.annotation_case_form (name);CREATE TABLE IF NOT EXISTS annotation_clitic_content (
	annotation_clitic_content_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_clitic_content_pk PRIMARY KEY (annotation_clitic_content_id)
);

CREATE UNIQUE INDEX annotation_clitic_content_name_index ON public.annotation_clitic_content (name);CREATE TABLE IF NOT EXISTS annotation_clitic_context (
	annotation_clitic_context_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_clitic_context_pk PRIMARY KEY (annotation_clitic_context_id)
);

CREATE UNIQUE INDEX annotation_clitic_context_name_index ON public.annotation_clitic_context (name);CREATE TABLE IF NOT EXISTS annotation_clitic_form (
	annotation_clitic_form_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_clitic_form_pk PRIMARY KEY (annotation_clitic_form_id)
);

CREATE UNIQUE INDEX annotation_clitic_form_name_index ON public.annotation_clitic_form (name);CREATE TABLE IF NOT EXISTS annotation_codeswitching_comments (
	annotation_codeswitching_comments_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_codeswitching_comments_pk PRIMARY KEY (annotation_codeswitching_comments_id)
);

CREATE UNIQUE INDEX annotation_codeswitching_comments_name_index ON public.annotation_codeswitching_comments (name);CREATE TABLE IF NOT EXISTS annotation_codeswitching_domain (
	annotation_codeswitching_domain_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_codeswitching_domain_pk PRIMARY KEY (annotation_codeswitching_domain_id)
);

CREATE UNIQUE INDEX annotation_codeswitching_domain_name_index ON public.annotation_codeswitching_domain (name);CREATE TABLE IF NOT EXISTS annotation_codeswitching_formulaicity (
	annotation_codeswitching_formulaicity_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_codeswitching_formulaicity_pk PRIMARY KEY (annotation_codeswitching_formulaicity_id)
);

CREATE UNIQUE INDEX annotation_codeswitching_formulaicity_name_index ON public.annotation_codeswitching_formulaicity (name);CREATE TABLE IF NOT EXISTS annotation_codeswitching_rank (
	annotation_codeswitching_rank_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_codeswitching_rank_pk PRIMARY KEY (annotation_codeswitching_rank_id)
);

CREATE UNIQUE INDEX annotation_codeswitching_rank_name_index ON public.annotation_codeswitching_rank (name);CREATE TABLE IF NOT EXISTS annotation_codeswitching_type (
	annotation_codeswitching_type_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_codeswitching_type_pk PRIMARY KEY (annotation_codeswitching_type_id)
);

CREATE UNIQUE INDEX annotation_codeswitching_type_name_index ON public.annotation_codeswitching_type (name);CREATE TABLE IF NOT EXISTS annotation_coherence_content (
	annotation_coherence_content_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_coherence_content_pk PRIMARY KEY (annotation_coherence_content_id)
);

CREATE UNIQUE INDEX annotation_coherence_content_name_index ON public.annotation_coherence_content (name);CREATE TABLE IF NOT EXISTS annotation_coherence_context (
	annotation_coherence_context_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_coherence_context_pk PRIMARY KEY (annotation_coherence_context_id)
);

CREATE UNIQUE INDEX annotation_coherence_context_name_index ON public.annotation_coherence_context (name);CREATE TABLE IF NOT EXISTS annotation_coherence_form (
	annotation_coherence_form_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_coherence_form_pk PRIMARY KEY (annotation_coherence_form_id)
);

CREATE UNIQUE INDEX annotation_coherence_form_name_index ON public.annotation_coherence_form (name);CREATE TABLE IF NOT EXISTS annotation_complementation_content (
	annotation_complementation_content_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_complementation_content_pk PRIMARY KEY (annotation_complementation_content_id)
);

CREATE UNIQUE INDEX annotation_complementation_content_name_index ON public.annotation_complementation_content (name);CREATE TABLE IF NOT EXISTS annotation_complementation_context (
	annotation_complementation_context_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_complementation_context_pk PRIMARY KEY (annotation_complementation_context_id)
);

CREATE UNIQUE INDEX annotation_complementation_context_name_index ON public.annotation_complementation_context (name);CREATE TABLE IF NOT EXISTS annotation_complementation_form (
	annotation_complementation_form_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_complementation_form_pk PRIMARY KEY (annotation_complementation_form_id)
);

CREATE UNIQUE INDEX annotation_complementation_form_name_index ON public.annotation_complementation_form (name);CREATE TABLE IF NOT EXISTS annotation_connectivity (
	annotation_connectivity_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_connectivity_pk PRIMARY KEY (annotation_connectivity_id)
);

CREATE UNIQUE INDEX annotation_connectivity_name_index ON public.annotation_connectivity (name);CREATE TABLE IF NOT EXISTS annotation_correction (
	annotation_correction_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_correction_pk PRIMARY KEY (annotation_correction_id)
);

CREATE UNIQUE INDEX annotation_correction_name_index ON public.annotation_correction (name);CREATE TABLE IF NOT EXISTS annotation_curvature (
	annotation_curvature_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_curvature_pk PRIMARY KEY (annotation_curvature_id)
);

CREATE UNIQUE INDEX annotation_curvature_name_index ON public.annotation_curvature (name);CREATE TABLE IF NOT EXISTS annotation_degree_of_formality (
	annotation_degree_of_formality_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_degree_of_formality_pk PRIMARY KEY (annotation_degree_of_formality_id)
);

CREATE UNIQUE INDEX annotation_degree_of_formality_name_index ON public.annotation_degree_of_formality (name);CREATE TABLE IF NOT EXISTS annotation_deletion (
	annotation_deletion_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_deletion_pk PRIMARY KEY (annotation_deletion_id)
);

CREATE UNIQUE INDEX annotation_deletion_name_index ON public.annotation_deletion (name);CREATE TABLE IF NOT EXISTS annotation_expansion (
	annotation_expansion_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_expansion_pk PRIMARY KEY (annotation_expansion_id)
);

CREATE UNIQUE INDEX annotation_expansion_name_index ON public.annotation_expansion (name);CREATE TABLE IF NOT EXISTS annotation_formulaicity_lexis (
	annotation_formulaicity_lexis_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_formulaicity_lexis_pk PRIMARY KEY (annotation_formulaicity_lexis_id)
);

CREATE UNIQUE INDEX annotation_formulaicity_lexis_name_index ON public.annotation_formulaicity_lexis (name);CREATE TABLE IF NOT EXISTS annotation_formulaicity_morphology (
	annotation_formulaicity_morphology_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_formulaicity_morphology_pk PRIMARY KEY (annotation_formulaicity_morphology_id)
);

CREATE UNIQUE INDEX annotation_formulaicity_morphology_name_index ON public.annotation_formulaicity_morphology (name);CREATE TABLE IF NOT EXISTS annotation_formulaicity_orthography (
	annotation_formulaicity_orthography_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_formulaicity_orthography_pk PRIMARY KEY (annotation_formulaicity_orthography_id)
);

CREATE UNIQUE INDEX annotation_formulaicity_orthography_name_index ON public.annotation_formulaicity_orthography (name);CREATE TABLE IF NOT EXISTS annotation_identifier_lexis (
	annotation_identifier_lexis_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_identifier_lexis_pk PRIMARY KEY (annotation_identifier_lexis_id)
);

CREATE UNIQUE INDEX annotation_identifier_lexis_name_index ON public.annotation_identifier_lexis (name);CREATE TABLE IF NOT EXISTS annotation_identifier_morphology (
	annotation_identifier_morphology_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_identifier_morphology_pk PRIMARY KEY (annotation_identifier_morphology_id)
);

CREATE UNIQUE INDEX annotation_identifier_morphology_name_index ON public.annotation_identifier_morphology (name);CREATE TABLE IF NOT EXISTS annotation_identifier_orthography (
	annotation_identifier_orthography_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_identifier_orthography_pk PRIMARY KEY (annotation_identifier_orthography_id)
);

CREATE UNIQUE INDEX annotation_identifier_orthography_name_index ON public.annotation_identifier_orthography (name);CREATE TABLE IF NOT EXISTS annotation_insertion (
	annotation_insertion_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_insertion_pk PRIMARY KEY (annotation_insertion_id)
);

CREATE UNIQUE INDEX annotation_insertion_name_index ON public.annotation_insertion (name);CREATE TABLE IF NOT EXISTS annotation_lineation (
	annotation_lineation_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_lineation_pk PRIMARY KEY (annotation_lineation_id)
);

CREATE UNIQUE INDEX annotation_lineation_name_index ON public.annotation_lineation (name);CREATE TABLE IF NOT EXISTS annotation_modality_content (
	annotation_modality_content_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_modality_content_pk PRIMARY KEY (annotation_modality_content_id)
);

CREATE UNIQUE INDEX annotation_modality_content_name_index ON public.annotation_modality_content (name);CREATE TABLE IF NOT EXISTS annotation_modality_context (
	annotation_modality_context_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_modality_context_pk PRIMARY KEY (annotation_modality_context_id)
);

CREATE UNIQUE INDEX annotation_modality_context_name_index ON public.annotation_modality_context (name);CREATE TABLE IF NOT EXISTS annotation_modality_form (
	annotation_modality_form_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_modality_form_pk PRIMARY KEY (annotation_modality_form_id)
);

CREATE UNIQUE INDEX annotation_modality_form_name_index ON public.annotation_modality_form (name);CREATE TABLE IF NOT EXISTS annotation_orientation (
	annotation_orientation_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_orientation_pk PRIMARY KEY (annotation_orientation_id)
);

CREATE UNIQUE INDEX annotation_orientation_name_index ON public.annotation_orientation (name);CREATE TABLE IF NOT EXISTS annotation_other_comments (
	annotation_other_comments_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_other_comments_pk PRIMARY KEY (annotation_other_comments_id)
);

CREATE UNIQUE INDEX annotation_other_comments_name_index ON public.annotation_other_comments (name);CREATE TABLE IF NOT EXISTS annotation_other_domain (
	annotation_other_domain_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_other_domain_pk PRIMARY KEY (annotation_other_domain_id)
);

CREATE UNIQUE INDEX annotation_other_domain_name_index ON public.annotation_other_domain (name);CREATE TABLE IF NOT EXISTS annotation_other_formulaicity (
	annotation_other_formulaicity_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_other_formulaicity_pk PRIMARY KEY (annotation_other_formulaicity_id)
);

CREATE UNIQUE INDEX annotation_other_formulaicity_name_index ON public.annotation_other_formulaicity (name);CREATE TABLE IF NOT EXISTS annotation_other_rank (
	annotation_other_rank_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_other_rank_pk PRIMARY KEY (annotation_other_rank_id)
);

CREATE UNIQUE INDEX annotation_other_rank_name_index ON public.annotation_other_rank (name);CREATE TABLE IF NOT EXISTS annotation_other_type (
	annotation_other_type_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_other_type_pk PRIMARY KEY (annotation_other_type_id)
);

CREATE UNIQUE INDEX annotation_other_type_name_index ON public.annotation_other_type (name);CREATE TABLE IF NOT EXISTS annotation_position_in_text (
	annotation_position_in_text_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_position_in_text_pk PRIMARY KEY (annotation_position_in_text_id)
);

CREATE UNIQUE INDEX annotation_position_in_text_name_index ON public.annotation_position_in_text (name);CREATE TABLE IF NOT EXISTS annotation_position_in_word_lexis (
	annotation_position_in_word_lexis_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_position_in_word_lexis_pk PRIMARY KEY (annotation_position_in_word_lexis_id)
);

CREATE UNIQUE INDEX annotation_position_in_word_lexis_name_index ON public.annotation_position_in_word_lexis (name);CREATE TABLE IF NOT EXISTS annotation_position_in_word_morphology (
	annotation_position_in_word_morphology_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_position_in_word_morphology_pk PRIMARY KEY (annotation_position_in_word_morphology_id)
);

CREATE UNIQUE INDEX annotation_position_in_word_morphology_name_index ON public.annotation_position_in_word_morphology (name);CREATE TABLE IF NOT EXISTS annotation_position_in_word_orthography (
	annotation_position_in_word_orthography_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_position_in_word_orthography_pk PRIMARY KEY (annotation_position_in_word_orthography_id)
);

CREATE UNIQUE INDEX annotation_position_in_word_orthography_name_index ON public.annotation_position_in_word_orthography (name);CREATE TABLE IF NOT EXISTS annotation_prescription_lexis (
	annotation_prescription_lexis_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_prescription_lexis_pk PRIMARY KEY (annotation_prescription_lexis_id)
);

CREATE UNIQUE INDEX annotation_prescription_lexis_name_index ON public.annotation_prescription_lexis (name);CREATE TABLE IF NOT EXISTS annotation_prescription_morphology (
	annotation_prescription_morphology_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_prescription_morphology_pk PRIMARY KEY (annotation_prescription_morphology_id)
);

CREATE UNIQUE INDEX annotation_prescription_morphology_name_index ON public.annotation_prescription_morphology (name);CREATE TABLE IF NOT EXISTS annotation_prescription_orthography (
	annotation_prescription_orthography_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_prescription_orthography_pk PRIMARY KEY (annotation_prescription_orthography_id)
);

CREATE UNIQUE INDEX annotation_prescription_orthography_name_index ON public.annotation_prescription_orthography (name);CREATE TABLE IF NOT EXISTS annotation_proscription_lexis (
	annotation_proscription_lexis_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_proscription_lexis_pk PRIMARY KEY (annotation_proscription_lexis_id)
);

CREATE UNIQUE INDEX annotation_proscription_lexis_name_index ON public.annotation_proscription_lexis (name);CREATE TABLE IF NOT EXISTS annotation_proscription_morphology (
	annotation_proscription_morphology_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_proscription_morphology_pk PRIMARY KEY (annotation_proscription_morphology_id)
);

CREATE UNIQUE INDEX annotation_proscription_morphology_name_index ON public.annotation_proscription_morphology (name);CREATE TABLE IF NOT EXISTS annotation_proscription_orthography (
	annotation_proscription_orthography_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_proscription_orthography_pk PRIMARY KEY (annotation_proscription_orthography_id)
);

CREATE UNIQUE INDEX annotation_proscription_orthography_name_index ON public.annotation_proscription_orthography (name);CREATE TABLE IF NOT EXISTS annotation_punctuation (
	annotation_punctuation_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_punctuation_pk PRIMARY KEY (annotation_punctuation_id)
);

CREATE UNIQUE INDEX annotation_punctuation_name_index ON public.annotation_punctuation (name);CREATE TABLE IF NOT EXISTS annotation_regularity (
	annotation_regularity_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_regularity_pk PRIMARY KEY (annotation_regularity_id)
);

CREATE UNIQUE INDEX annotation_regularity_name_index ON public.annotation_regularity (name);CREATE TABLE IF NOT EXISTS annotation_relativisation_content (
	annotation_relativisation_content_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_relativisation_content_pk PRIMARY KEY (annotation_relativisation_content_id)
);

CREATE UNIQUE INDEX annotation_relativisation_content_name_index ON public.annotation_relativisation_content (name);CREATE TABLE IF NOT EXISTS annotation_relativisation_context (
	annotation_relativisation_context_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_relativisation_context_pk PRIMARY KEY (annotation_relativisation_context_id)
);

CREATE UNIQUE INDEX annotation_relativisation_context_name_index ON public.annotation_relativisation_context (name);CREATE TABLE IF NOT EXISTS annotation_relativisation_form (
	annotation_relativisation_form_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_relativisation_form_pk PRIMARY KEY (annotation_relativisation_form_id)
);

CREATE UNIQUE INDEX annotation_relativisation_form_name_index ON public.annotation_relativisation_form (name);CREATE TABLE IF NOT EXISTS annotation_script_type (
	annotation_script_type_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_script_type_pk PRIMARY KEY (annotation_script_type_id)
);

CREATE UNIQUE INDEX annotation_script_type_name_index ON public.annotation_script_type (name);CREATE TABLE IF NOT EXISTS annotation_slope (
	annotation_slope_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_slope_pk PRIMARY KEY (annotation_slope_id)
);

CREATE UNIQUE INDEX annotation_slope_name_index ON public.annotation_slope (name);CREATE TABLE IF NOT EXISTS annotation_standard_form_lexis (
	annotation_standard_form_lexis_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_standard_form_lexis_pk PRIMARY KEY (annotation_standard_form_lexis_id)
);

CREATE UNIQUE INDEX annotation_standard_form_lexis_name_index ON public.annotation_standard_form_lexis (name);CREATE TABLE IF NOT EXISTS annotation_standard_form_morphology (
	annotation_standard_form_morphology_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_standard_form_morphology_pk PRIMARY KEY (annotation_standard_form_morphology_id)
);

CREATE UNIQUE INDEX annotation_standard_form_morphology_name_index ON public.annotation_standard_form_morphology (name);CREATE TABLE IF NOT EXISTS annotation_standard_form_orthography (
	annotation_standard_form_orthography_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_standard_form_orthography_pk PRIMARY KEY (annotation_standard_form_orthography_id)
);

CREATE UNIQUE INDEX annotation_standard_form_orthography_name_index ON public.annotation_standard_form_orthography (name);CREATE TABLE IF NOT EXISTS annotation_subordination_content (
	annotation_subordination_content_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_subordination_content_pk PRIMARY KEY (annotation_subordination_content_id)
);

CREATE UNIQUE INDEX annotation_subordination_content_name_index ON public.annotation_subordination_content (name);CREATE TABLE IF NOT EXISTS annotation_subordination_context (
	annotation_subordination_context_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_subordination_context_pk PRIMARY KEY (annotation_subordination_context_id)
);

CREATE UNIQUE INDEX annotation_subordination_context_name_index ON public.annotation_subordination_context (name);CREATE TABLE IF NOT EXISTS annotation_subordination_form (
	annotation_subordination_form_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_subordination_form_pk PRIMARY KEY (annotation_subordination_form_id)
);

CREATE UNIQUE INDEX annotation_subordination_form_name_index ON public.annotation_subordination_form (name);CREATE TABLE IF NOT EXISTS annotation_subtype_lexis (
	annotation_subtype_lexis_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_subtype_lexis_pk PRIMARY KEY (annotation_subtype_lexis_id)
);

CREATE UNIQUE INDEX annotation_subtype_lexis_name_index ON public.annotation_subtype_lexis (name);CREATE TABLE IF NOT EXISTS annotation_subtype_morphology (
	annotation_subtype_morphology_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_subtype_morphology_pk PRIMARY KEY (annotation_subtype_morphology_id)
);

CREATE UNIQUE INDEX annotation_subtype_morphology_name_index ON public.annotation_subtype_morphology (name);CREATE TABLE IF NOT EXISTS annotation_subtype_orthography (
	annotation_subtype_orthography_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_subtype_orthography_pk PRIMARY KEY (annotation_subtype_orthography_id)
);

CREATE UNIQUE INDEX annotation_subtype_orthography_name_index ON public.annotation_subtype_orthography (name);CREATE TABLE IF NOT EXISTS annotation_symbol (
	annotation_symbol_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_symbol_pk PRIMARY KEY (annotation_symbol_id)
);

CREATE UNIQUE INDEX annotation_symbol_name_index ON public.annotation_symbol (name);CREATE TABLE IF NOT EXISTS annotation_type_formulaicity (
	annotation_type_formulaicity_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_type_formulaicity_pk PRIMARY KEY (annotation_type_formulaicity_id)
);

CREATE UNIQUE INDEX annotation_type_formulaicity_name_index ON public.annotation_type_formulaicity (name);CREATE TABLE IF NOT EXISTS annotation_type_lexis (
	annotation_type_lexis_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_type_lexis_pk PRIMARY KEY (annotation_type_lexis_id)
);

CREATE UNIQUE INDEX annotation_type_lexis_name_index ON public.annotation_type_lexis (name);CREATE TABLE IF NOT EXISTS annotation_type_morphology (
	annotation_type_morphology_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_type_morphology_pk PRIMARY KEY (annotation_type_morphology_id)
);

CREATE UNIQUE INDEX annotation_type_morphology_name_index ON public.annotation_type_morphology (name);CREATE TABLE IF NOT EXISTS annotation_type_orthography (
	annotation_type_orthography_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_type_orthography_pk PRIMARY KEY (annotation_type_orthography_id)
);

CREATE UNIQUE INDEX annotation_type_orthography_name_index ON public.annotation_type_orthography (name);CREATE TABLE IF NOT EXISTS annotation_type_reconstruction (
	annotation_type_reconstruction_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_type_reconstruction_pk PRIMARY KEY (annotation_type_reconstruction_id)
);

CREATE UNIQUE INDEX annotation_type_reconstruction_name_index ON public.annotation_type_reconstruction (name);CREATE TABLE IF NOT EXISTS annotation_vacat (
	annotation_vacat_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_vacat_pk PRIMARY KEY (annotation_vacat_id)
);

CREATE UNIQUE INDEX annotation_vacat_name_index ON public.annotation_vacat (name);CREATE TABLE IF NOT EXISTS annotation_weight (
	annotation_weight_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_weight_pk PRIMARY KEY (annotation_weight_id)
);

CREATE UNIQUE INDEX annotation_weight_name_index ON public.annotation_weight (name);CREATE TABLE IF NOT EXISTS annotation_word_class (
	annotation_word_class_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_word_class_pk PRIMARY KEY (annotation_word_class_id)
);

CREATE UNIQUE INDEX annotation_word_class_name_index ON public.annotation_word_class (name);CREATE TABLE IF NOT EXISTS annotation_word_splitting (
	annotation_word_splitting_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_word_splitting_pk PRIMARY KEY (annotation_word_splitting_id)
);

CREATE UNIQUE INDEX annotation_word_splitting_name_index ON public.annotation_word_splitting (name);CREATE TABLE IF NOT EXISTS annotation_wordclass_lexis (
	annotation_wordclass_lexis_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_wordclass_lexis_pk PRIMARY KEY (annotation_wordclass_lexis_id)
);

CREATE UNIQUE INDEX annotation_wordclass_lexis_name_index ON public.annotation_wordclass_lexis (name);CREATE TABLE IF NOT EXISTS annotation_wordclass_morphology (
	annotation_wordclass_morphology_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_wordclass_morphology_pk PRIMARY KEY (annotation_wordclass_morphology_id)
);

CREATE UNIQUE INDEX annotation_wordclass_morphology_name_index ON public.annotation_wordclass_morphology (name);CREATE TABLE IF NOT EXISTS annotation_wordclass_orthography (
	annotation_wordclass_orthography_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT annotation_wordclass_orthography_pk PRIMARY KEY (annotation_wordclass_orthography_id)
);

CREATE UNIQUE INDEX annotation_wordclass_orthography_name_index ON public.annotation_wordclass_orthography (name);CREATE TABLE IF NOT EXISTS text_selection (
	text_selection_id serial NOT NULL,
	text_id integer NOT NULL,
	text text NOT NULL,
	text_edited text NOT NULL,
	line_number_start integer NULL,
	line_number_end integer NULL,
	selection_start integer NOT NULL,
	selection_length integer NOT NULL,
	selection_end integer NOT NULL,
	CONSTRAINT text_selection_pk PRIMARY KEY (text_selection_id)
);

ALTER TABLE public.text_selection ADD CONSTRAINT text_id_fk FOREIGN KEY (text_id) REFERENCES public.text(text_id);CREATE TABLE IF NOT EXISTS handshift_annotation (
	handshift_annotation_id serial NOT NULL,
	text_selection_id integer NOT NULL,
	annotation_abbreviation_id integer NULL,
	annotation_accentuation_id integer NULL,
	annotation_connectivity_id integer NULL,
	annotation_correction_id integer NULL,
	annotation_curvature_id integer NULL,
	annotation_degree_of_formality_id integer NULL,
	annotation_expansion_id integer NULL,
	annotation_lineation_id integer NULL,
	annotation_orientation_id integer NULL,
	annotation_punctuation_id integer NULL,
	annotation_regularity_id integer NULL,
	annotation_script_type_id integer NULL,
	annotation_slope_id integer NULL,
	annotation_word_splitting_id integer NULL,
	internal_hand_num varchar NULL,
	attestation_id integer NULL,
	comment text NULL,
	status text NULL,
	created timestamp NULL,
	created_by varchar NULL,
	modified timestamp NULL,
	modified_by varchar NULL,
	revision_status varchar NULL,
	CONSTRAINT handshift_annotation_pk PRIMARY KEY (handshift_annotation_id)
);

ALTER TABLE public.handshift_annotation ADD CONSTRAINT text_selection_id_fk FOREIGN KEY (text_selection_id) REFERENCES public.text_selection(text_selection_id);
ALTER TABLE public.handshift_annotation ADD CONSTRAINT attestation_id_fk FOREIGN KEY (attestation_id) REFERENCES public.attestation(attestation_id);
ALTER TABLE public.handshift_annotation ADD CONSTRAINT annotation_abbreviation_id_fk FOREIGN KEY (annotation_abbreviation_id) REFERENCES public.annotation_abbreviation(annotation_abbreviation_id);
ALTER TABLE public.handshift_annotation ADD CONSTRAINT annotation_accentuation_id_fk FOREIGN KEY (annotation_accentuation_id) REFERENCES public.annotation_accentuation(annotation_accentuation_id);
ALTER TABLE public.handshift_annotation ADD CONSTRAINT annotation_connectivity_id_fk FOREIGN KEY (annotation_connectivity_id) REFERENCES public.annotation_connectivity(annotation_connectivity_id);
ALTER TABLE public.handshift_annotation ADD CONSTRAINT annotation_correction_id_fk FOREIGN KEY (annotation_correction_id) REFERENCES public.annotation_correction(annotation_correction_id);
ALTER TABLE public.handshift_annotation ADD CONSTRAINT annotation_curvature_id_fk FOREIGN KEY (annotation_curvature_id) REFERENCES public.annotation_curvature(annotation_curvature_id);
ALTER TABLE public.handshift_annotation ADD CONSTRAINT annotation_degree_of_formality_id_fk FOREIGN KEY (annotation_degree_of_formality_id) REFERENCES public.annotation_degree_of_formality(annotation_degree_of_formality_id);
ALTER TABLE public.handshift_annotation ADD CONSTRAINT annotation_expansion_id_fk FOREIGN KEY (annotation_expansion_id) REFERENCES public.annotation_expansion(annotation_expansion_id);
ALTER TABLE public.handshift_annotation ADD CONSTRAINT annotation_lineation_id_fk FOREIGN KEY (annotation_lineation_id) REFERENCES public.annotation_lineation(annotation_lineation_id);
ALTER TABLE public.handshift_annotation ADD CONSTRAINT annotation_orientation_id_fk FOREIGN KEY (annotation_orientation_id) REFERENCES public.annotation_orientation(annotation_orientation_id);
ALTER TABLE public.handshift_annotation ADD CONSTRAINT annotation_punctuation_id_fk FOREIGN KEY (annotation_punctuation_id) REFERENCES public.annotation_punctuation(annotation_punctuation_id);
ALTER TABLE public.handshift_annotation ADD CONSTRAINT annotation_regularity_id_fk FOREIGN KEY (annotation_regularity_id) REFERENCES public.annotation_regularity(annotation_regularity_id);
ALTER TABLE public.handshift_annotation ADD CONSTRAINT annotation_script_type_id_fk FOREIGN KEY (annotation_script_type_id) REFERENCES public.annotation_script_type(annotation_script_type_id);
ALTER TABLE public.handshift_annotation ADD CONSTRAINT annotation_slope_id_fk FOREIGN KEY (annotation_slope_id) REFERENCES public.annotation_slope(annotation_slope_id);
ALTER TABLE public.handshift_annotation ADD CONSTRAINT annotation_word_splitting_id_fk FOREIGN KEY (annotation_word_splitting_id) REFERENCES public.annotation_word_splitting(annotation_word_splitting_id);CREATE TABLE IF NOT EXISTS language_annotation (
	language_annotation_id serial NOT NULL,
	text_selection_id integer NOT NULL,
	annotation_bigraphism_comments_id integer NULL,
	annotation_bigraphism_domain_id integer NULL,
	annotation_bigraphism_formulaicity_id integer NULL,
	annotation_bigraphism_rank_id integer NULL,
	annotation_bigraphism_type_id integer NULL,
	annotation_codeswitching_comments_id integer NULL,
	annotation_codeswitching_domain_id integer NULL,
	annotation_codeswitching_formulaicity_id integer NULL,
	annotation_codeswitching_rank_id integer NULL,
	annotation_codeswitching_type_id integer NULL,
	annotation_other_comments_id integer NULL,
	annotation_other_domain_id integer NULL,
	annotation_other_formulaicity_id integer NULL,
	annotation_other_rank_id integer NULL,
	annotation_other_type_id integer NULL,
	created timestamp NULL,
	created_by varchar NULL,
	modified timestamp NULL,
	modified_by varchar NULL,
	revision_status varchar NULL,
	CONSTRAINT language_annotation_pk PRIMARY KEY (language_annotation_id)
);

ALTER TABLE public.language_annotation ADD CONSTRAINT text_selection_id_fk FOREIGN KEY (text_selection_id) REFERENCES public.text_selection(text_selection_id);
ALTER TABLE public.language_annotation ADD CONSTRAINT annotation_bigraphism_comments_id_fk FOREIGN KEY (annotation_bigraphism_comments_id) REFERENCES public.annotation_bigraphism_comments(annotation_bigraphism_comments_id);
ALTER TABLE public.language_annotation ADD CONSTRAINT annotation_bigraphism_domain_id_fk FOREIGN KEY (annotation_bigraphism_domain_id) REFERENCES public.annotation_bigraphism_domain(annotation_bigraphism_domain_id);
ALTER TABLE public.language_annotation ADD CONSTRAINT annotation_bigraphism_formulaicity_id_fk FOREIGN KEY (annotation_bigraphism_formulaicity_id) REFERENCES public.annotation_bigraphism_formulaicity(annotation_bigraphism_formulaicity_id);
ALTER TABLE public.language_annotation ADD CONSTRAINT annotation_bigraphism_rank_id_fk FOREIGN KEY (annotation_bigraphism_rank_id) REFERENCES public.annotation_bigraphism_rank(annotation_bigraphism_rank_id);
ALTER TABLE public.language_annotation ADD CONSTRAINT annotation_bigraphism_type_id_fk FOREIGN KEY (annotation_bigraphism_type_id) REFERENCES public.annotation_bigraphism_type(annotation_bigraphism_type_id);
ALTER TABLE public.language_annotation ADD CONSTRAINT annotation_codeswitching_comments_id_fk FOREIGN KEY (annotation_codeswitching_comments_id) REFERENCES public.annotation_codeswitching_comments(annotation_codeswitching_comments_id);
ALTER TABLE public.language_annotation ADD CONSTRAINT annotation_codeswitching_domain_id_fk FOREIGN KEY (annotation_codeswitching_domain_id) REFERENCES public.annotation_codeswitching_domain(annotation_codeswitching_domain_id);
ALTER TABLE public.language_annotation ADD CONSTRAINT annotation_codeswitching_formulaicity_id_fk FOREIGN KEY (annotation_codeswitching_formulaicity_id) REFERENCES public.annotation_codeswitching_formulaicity(annotation_codeswitching_formulaicity_id);
ALTER TABLE public.language_annotation ADD CONSTRAINT annotation_codeswitching_rank_id_fk FOREIGN KEY (annotation_codeswitching_rank_id) REFERENCES public.annotation_codeswitching_rank(annotation_codeswitching_rank_id);
ALTER TABLE public.language_annotation ADD CONSTRAINT annotation_codeswitching_type_id_fk FOREIGN KEY (annotation_codeswitching_type_id) REFERENCES public.annotation_codeswitching_type(annotation_codeswitching_type_id);
ALTER TABLE public.language_annotation ADD CONSTRAINT annotation_other_comments_id_fk FOREIGN KEY (annotation_other_comments_id) REFERENCES public.annotation_other_comments(annotation_other_comments_id);
ALTER TABLE public.language_annotation ADD CONSTRAINT annotation_other_domain_id_fk FOREIGN KEY (annotation_other_domain_id) REFERENCES public.annotation_other_domain(annotation_other_domain_id);
ALTER TABLE public.language_annotation ADD CONSTRAINT annotation_other_formulaicity_id_fk FOREIGN KEY (annotation_other_formulaicity_id) REFERENCES public.annotation_other_formulaicity(annotation_other_formulaicity_id);
ALTER TABLE public.language_annotation ADD CONSTRAINT annotation_other_rank_id_fk FOREIGN KEY (annotation_other_rank_id) REFERENCES public.annotation_other_rank(annotation_other_rank_id);
ALTER TABLE public.language_annotation ADD CONSTRAINT annotation_other_type_id_fk FOREIGN KEY (annotation_other_type_id) REFERENCES public.annotation_other_type(annotation_other_type_id);CREATE TABLE IF NOT EXISTS lexis_annotation (
	lexis_annotation_id serial NOT NULL,
	text_selection_id integer NOT NULL,
	annotation_standard_form_lexis_id integer NULL,
	annotation_type_lexis_id integer NULL,
	annotation_subtype_lexis_id integer NULL,
	annotation_wordclass_lexis_id integer NULL,
	annotation_formulaicity_lexis_id integer NULL,
	annotation_prescription_lexis_id integer NULL,
	annotation_proscription_lexis_id integer NULL,
	annotation_position_in_word_lexis_id integer NULL,
	annotation_identifier_lexis_id integer NULL,
	created timestamp NULL,
	created_by varchar NULL,
	modified timestamp NULL,
	modified_by varchar NULL,
	revision_status varchar NULL,
	CONSTRAINT lexis_annotation_pk PRIMARY KEY (lexis_annotation_id)
);

ALTER TABLE public.lexis_annotation ADD CONSTRAINT text_selection_id_fk FOREIGN KEY (text_selection_id) REFERENCES public.text_selection(text_selection_id);
ALTER TABLE public.lexis_annotation ADD CONSTRAINT annotation_standard_form_lexis_id_fk FOREIGN KEY (annotation_standard_form_lexis_id) REFERENCES public.annotation_standard_form_lexis(annotation_standard_form_lexis_id);
ALTER TABLE public.lexis_annotation ADD CONSTRAINT annotation_type_lexis_id_fk FOREIGN KEY (annotation_type_lexis_id) REFERENCES public.annotation_type_lexis(annotation_type_lexis_id);
ALTER TABLE public.lexis_annotation ADD CONSTRAINT annotation_subtype_lexis_id_fk FOREIGN KEY (annotation_subtype_lexis_id) REFERENCES public.annotation_subtype_lexis(annotation_subtype_lexis_id);
ALTER TABLE public.lexis_annotation ADD CONSTRAINT annotation_wordclass_lexis_id_fk FOREIGN KEY (annotation_wordclass_lexis_id) REFERENCES public.annotation_wordclass_lexis(annotation_wordclass_lexis_id);
ALTER TABLE public.lexis_annotation ADD CONSTRAINT annotation_formulaicity_lexis_id_fk FOREIGN KEY (annotation_formulaicity_lexis_id) REFERENCES public.annotation_formulaicity_lexis(annotation_formulaicity_lexis_id);
ALTER TABLE public.lexis_annotation ADD CONSTRAINT annotation_prescription_lexis_id_fk FOREIGN KEY (annotation_prescription_lexis_id) REFERENCES public.annotation_prescription_lexis(annotation_prescription_lexis_id);
ALTER TABLE public.lexis_annotation ADD CONSTRAINT annotation_proscription_lexis_id_fk FOREIGN KEY (annotation_proscription_lexis_id) REFERENCES public.annotation_proscription_lexis(annotation_proscription_lexis_id);
ALTER TABLE public.lexis_annotation ADD CONSTRAINT annotation_position_in_word_lexis_id_fk FOREIGN KEY (annotation_position_in_word_lexis_id) REFERENCES public.annotation_position_in_word_lexis(annotation_position_in_word_lexis_id);
ALTER TABLE public.lexis_annotation ADD CONSTRAINT annotation_identifier_lexis_id_fk FOREIGN KEY (annotation_identifier_lexis_id) REFERENCES public.annotation_identifier_lexis(annotation_identifier_lexis_id);CREATE TABLE IF NOT EXISTS morpho_syntactical_annotation (
	morpho_syntactical_annotation_id serial NOT NULL,
	text_selection_id integer NOT NULL,
	annotation_aspect_content_id integer NULL,
	annotation_aspect_context_id integer NULL,
	annotation_aspect_form_id integer NULL,
	annotation_complementation_content_id integer NULL,
	annotation_complementation_context_id integer NULL,
	annotation_complementation_form_id integer NULL,
	annotation_modality_content_id integer NULL,
	annotation_modality_context_id integer NULL,
	annotation_modality_for_id integer NULL,
	annotation_coherence_content_id integer NULL,
	annotation_coherence_context_id integer NULL,
	annotation_coherence_form_id integer NULL,
	annotation_clitic_content_id integer NULL,
	annotation_clitic_context_id integer NULL,
	annotation_clitic_form_id integer NULL,
	annotation_case_content_id integer NULL,
	annotation_case_context_id integer NULL,
	annotation_case_form_id integer NULL,
	annotation_subordination_content_id integer NULL,
	annotation_subordination_form_id integer NULL,
	annotation_subordination_context_id integer NULL,
	annotation_relativisation_content_id integer NULL,
	annotation_relativisation_context_id integer NULL,
	annotation_relativisation_form_id integer NULL,
	annotation_type_formulaicity_id integer NULL,
	annotation_type_reconstruction_id integer NULL,
	created timestamp NULL,
	created_by varchar NULL,
	modified timestamp NULL,
	modified_by varchar NULL,
	revision_status varchar NULL,
	CONSTRAINT morpho_syntactical_annotation_pk PRIMARY KEY (morpho_syntactical_annotation_id)
);

ALTER TABLE public.morpho_syntactical_annotation ADD CONSTRAINT text_selection_id_fk FOREIGN KEY (text_selection_id) REFERENCES public.text_selection(text_selection_id);
ALTER TABLE public.morpho_syntactical_annotation ADD CONSTRAINT annotation_aspect_content_id_fk FOREIGN KEY (annotation_aspect_content_id) REFERENCES public.annotation_aspect_content(annotation_aspect_content_id);
ALTER TABLE public.morpho_syntactical_annotation ADD CONSTRAINT annotation_aspect_context_id_fk FOREIGN KEY (annotation_aspect_context_id) REFERENCES public.annotation_aspect_context(annotation_aspect_context_id);
ALTER TABLE public.morpho_syntactical_annotation ADD CONSTRAINT annotation_aspect_form_id_fk FOREIGN KEY (annotation_aspect_form_id) REFERENCES public.annotation_aspect_form(annotation_aspect_form_id);
ALTER TABLE public.morpho_syntactical_annotation ADD CONSTRAINT annotation_complementation_content_id_fk FOREIGN KEY (annotation_complementation_content_id) REFERENCES public.annotation_complementation_content(annotation_complementation_content_id);
ALTER TABLE public.morpho_syntactical_annotation ADD CONSTRAINT annotation_complementation_context_id_fk FOREIGN KEY (annotation_complementation_context_id) REFERENCES public.annotation_complementation_context(annotation_complementation_context_id);
ALTER TABLE public.morpho_syntactical_annotation ADD CONSTRAINT annotation_complementation_form_id_fk FOREIGN KEY (annotation_complementation_form_id) REFERENCES public.annotation_complementation_form(annotation_complementation_form_id);
ALTER TABLE public.morpho_syntactical_annotation ADD CONSTRAINT annotation_modality_content_id_fk FOREIGN KEY (annotation_modality_content_id) REFERENCES public.annotation_modality_content(annotation_modality_content_id);
ALTER TABLE public.morpho_syntactical_annotation ADD CONSTRAINT annotation_modality_context_id_fk FOREIGN KEY (annotation_modality_context_id) REFERENCES public.annotation_modality_context(annotation_modality_context_id);
ALTER TABLE public.morpho_syntactical_annotation ADD CONSTRAINT annotation_modality_for_id_fk FOREIGN KEY (annotation_modality_for_id) REFERENCES public.annotation_modality_form(annotation_modality_form_id);
ALTER TABLE public.morpho_syntactical_annotation ADD CONSTRAINT annotation_coherence_content_id_fk FOREIGN KEY (annotation_coherence_content_id) REFERENCES public.annotation_coherence_content(annotation_coherence_content_id);
ALTER TABLE public.morpho_syntactical_annotation ADD CONSTRAINT annotation_coherence_context_id_fk FOREIGN KEY (annotation_coherence_context_id) REFERENCES public.annotation_coherence_context(annotation_coherence_context_id);
ALTER TABLE public.morpho_syntactical_annotation ADD CONSTRAINT annotation_coherence_form_id_fk FOREIGN KEY (annotation_coherence_form_id) REFERENCES public.annotation_coherence_form(annotation_coherence_form_id);
ALTER TABLE public.morpho_syntactical_annotation ADD CONSTRAINT annotation_clitic_content_id_fk FOREIGN KEY (annotation_clitic_content_id) REFERENCES public.annotation_clitic_content(annotation_clitic_content_id);
ALTER TABLE public.morpho_syntactical_annotation ADD CONSTRAINT annotation_clitic_context_id_fk FOREIGN KEY (annotation_clitic_context_id) REFERENCES public.annotation_clitic_context(annotation_clitic_context_id);
ALTER TABLE public.morpho_syntactical_annotation ADD CONSTRAINT annotation_clitic_form_id_fk FOREIGN KEY (annotation_clitic_form_id) REFERENCES public.annotation_clitic_form(annotation_clitic_form_id);
ALTER TABLE public.morpho_syntactical_annotation ADD CONSTRAINT annotation_case_content_id_fk FOREIGN KEY (annotation_case_content_id) REFERENCES public.annotation_case_content(annotation_case_content_id);
ALTER TABLE public.morpho_syntactical_annotation ADD CONSTRAINT annotation_case_context_id_fk FOREIGN KEY (annotation_case_context_id) REFERENCES public.annotation_case_context(annotation_case_context_id);
ALTER TABLE public.morpho_syntactical_annotation ADD CONSTRAINT annotation_case_form_id_fk FOREIGN KEY (annotation_case_form_id) REFERENCES public.annotation_case_form(annotation_case_form_id);
ALTER TABLE public.morpho_syntactical_annotation ADD CONSTRAINT annotation_subordination_content_id_fk FOREIGN KEY (annotation_subordination_content_id) REFERENCES public.annotation_subordination_content(annotation_subordination_content_id);
ALTER TABLE public.morpho_syntactical_annotation ADD CONSTRAINT annotation_subordination_form_id_fk FOREIGN KEY (annotation_subordination_form_id) REFERENCES public.annotation_subordination_form(annotation_subordination_form_id);
ALTER TABLE public.morpho_syntactical_annotation ADD CONSTRAINT annotation_subordination_context_id_fk FOREIGN KEY (annotation_subordination_context_id) REFERENCES public.annotation_subordination_context(annotation_subordination_context_id);
ALTER TABLE public.morpho_syntactical_annotation ADD CONSTRAINT annotation_relativisation_content_id_fk FOREIGN KEY (annotation_relativisation_content_id) REFERENCES public.annotation_relativisation_content(annotation_relativisation_content_id);
ALTER TABLE public.morpho_syntactical_annotation ADD CONSTRAINT annotation_relativisation_context_id_fk FOREIGN KEY (annotation_relativisation_context_id) REFERENCES public.annotation_relativisation_context(annotation_relativisation_context_id);
ALTER TABLE public.morpho_syntactical_annotation ADD CONSTRAINT annotation_relativisation_form_id_fk FOREIGN KEY (annotation_relativisation_form_id) REFERENCES public.annotation_relativisation_form(annotation_relativisation_form_id);
ALTER TABLE public.morpho_syntactical_annotation ADD CONSTRAINT annotation_type_formulaicity_id_fk FOREIGN KEY (annotation_type_formulaicity_id) REFERENCES public.annotation_type_formulaicity(annotation_type_formulaicity_id);
ALTER TABLE public.morpho_syntactical_annotation ADD CONSTRAINT annotation_type_reconstruction_id_fk FOREIGN KEY (annotation_type_reconstruction_id) REFERENCES public.annotation_type_reconstruction(annotation_type_reconstruction_id);CREATE TABLE IF NOT EXISTS morphology_annotation (
	morphology_annotation_id serial NOT NULL,
	text_selection_id integer NOT NULL,
	annotation_standard_form_morphology_id integer NULL,
	annotation_type_morphology_id integer NULL,
	annotation_subtype_morphology_id integer NULL,
	annotation_wordclass_morphology_id integer NULL,
	annotation_formulaicity_morphology_id integer NULL,
	annotation_prescription_morphology_id integer NULL,
	annotation_proscription_morphology_id integer NULL,
	annotation_position_in_word_morphology_id integer NULL,
	annotation_identifier_morphology_id integer NULL,
	created timestamp NULL,
	created_by varchar NULL,
	modified timestamp NULL,
	modified_by varchar NULL,
	revision_status varchar NULL,
	CONSTRAINT morphology_annotation_pk PRIMARY KEY (morphology_annotation_id)
);

ALTER TABLE public.morphology_annotation ADD CONSTRAINT text_selection_id_fk FOREIGN KEY (text_selection_id) REFERENCES public.text_selection(text_selection_id);
ALTER TABLE public.morphology_annotation ADD CONSTRAINT annotation_standard_form_morphology_id_fk FOREIGN KEY (annotation_standard_form_morphology_id) REFERENCES public.annotation_standard_form_morphology(annotation_standard_form_morphology_id);
ALTER TABLE public.morphology_annotation ADD CONSTRAINT annotation_type_morphology_id_fk FOREIGN KEY (annotation_type_morphology_id) REFERENCES public.annotation_type_morphology(annotation_type_morphology_id);
ALTER TABLE public.morphology_annotation ADD CONSTRAINT annotation_subtype_morphology_id_fk FOREIGN KEY (annotation_subtype_morphology_id) REFERENCES public.annotation_subtype_morphology(annotation_subtype_morphology_id);
ALTER TABLE public.morphology_annotation ADD CONSTRAINT annotation_wordclass_morphology_id_fk FOREIGN KEY (annotation_wordclass_morphology_id) REFERENCES public.annotation_wordclass_morphology(annotation_wordclass_morphology_id);
ALTER TABLE public.morphology_annotation ADD CONSTRAINT annotation_formulaicity_morphology_id_fk FOREIGN KEY (annotation_formulaicity_morphology_id) REFERENCES public.annotation_formulaicity_morphology(annotation_formulaicity_morphology_id);
ALTER TABLE public.morphology_annotation ADD CONSTRAINT annotation_prescription_morphology_id_fk FOREIGN KEY (annotation_prescription_morphology_id) REFERENCES public.annotation_prescription_morphology(annotation_prescription_morphology_id);
ALTER TABLE public.morphology_annotation ADD CONSTRAINT annotation_proscription_morphology_id_fk FOREIGN KEY (annotation_proscription_morphology_id) REFERENCES public.annotation_proscription_morphology(annotation_proscription_morphology_id);
ALTER TABLE public.morphology_annotation ADD CONSTRAINT annotation_position_in_word_morphology_id_fk FOREIGN KEY (annotation_position_in_word_morphology_id) REFERENCES public.annotation_position_in_word_morphology(annotation_position_in_word_morphology_id);
ALTER TABLE public.morphology_annotation ADD CONSTRAINT annotation_identifier_morphology_id_fk FOREIGN KEY (annotation_identifier_morphology_id) REFERENCES public.annotation_identifier_morphology(annotation_identifier_morphology_id);CREATE TABLE IF NOT EXISTS orthography_annotation (
	orthography_annotation_id serial NOT NULL,
	text_selection_id integer NOT NULL,
	annotation_standard_form_orthography_id integer NULL,
	annotation_type_orthography_id integer NULL,
	annotation_subtype_orthography_id integer NULL,
	annotation_wordclass_orthography_id integer NULL,
	annotation_formulaicity_orthography_id integer NULL,
	annotation_prescription_orthography_id integer NULL,
	annotation_proscription_orthography_id integer NULL,
	annotation_position_in_word_orthography_id integer NULL,
	annotation_identifier_orthography_id integer NULL,
	created timestamp NULL,
	created_by varchar NULL,
	modified timestamp NULL,
	modified_by varchar NULL,
	revision_status varchar NULL,
	CONSTRAINT orthography_annotation_pk PRIMARY KEY (orthography_annotation_id)
);

ALTER TABLE public.orthography_annotation ADD CONSTRAINT text_selection_id_fk FOREIGN KEY (text_selection_id) REFERENCES public.text_selection(text_selection_id);
ALTER TABLE public.orthography_annotation ADD CONSTRAINT annotation_standard_form_orthography_id_fk FOREIGN KEY (annotation_standard_form_orthography_id) REFERENCES public.annotation_standard_form_orthography(annotation_standard_form_orthography_id);
ALTER TABLE public.orthography_annotation ADD CONSTRAINT annotation_type_orthography_id_fk FOREIGN KEY (annotation_type_orthography_id) REFERENCES public.annotation_type_orthography(annotation_type_orthography_id);
ALTER TABLE public.orthography_annotation ADD CONSTRAINT annotation_subtype_orthography_id_fk FOREIGN KEY (annotation_subtype_orthography_id) REFERENCES public.annotation_subtype_orthography(annotation_subtype_orthography_id);
ALTER TABLE public.orthography_annotation ADD CONSTRAINT annotation_wordclass_orthography_id_fk FOREIGN KEY (annotation_wordclass_orthography_id) REFERENCES public.annotation_wordclass_orthography(annotation_wordclass_orthography_id);
ALTER TABLE public.orthography_annotation ADD CONSTRAINT annotation_formulaicity_orthography_id_fk FOREIGN KEY (annotation_formulaicity_orthography_id) REFERENCES public.annotation_formulaicity_orthography(annotation_formulaicity_orthography_id);
ALTER TABLE public.orthography_annotation ADD CONSTRAINT annotation_prescription_orthography_id_fk FOREIGN KEY (annotation_prescription_orthography_id) REFERENCES public.annotation_prescription_orthography(annotation_prescription_orthography_id);
ALTER TABLE public.orthography_annotation ADD CONSTRAINT annotation_proscription_orthography_id_fk FOREIGN KEY (annotation_proscription_orthography_id) REFERENCES public.annotation_proscription_orthography(annotation_proscription_orthography_id);
ALTER TABLE public.orthography_annotation ADD CONSTRAINT annotation_position_in_word_orthography_id_fk FOREIGN KEY (annotation_position_in_word_orthography_id) REFERENCES public.annotation_position_in_word_orthography(annotation_position_in_word_orthography_id);
ALTER TABLE public.orthography_annotation ADD CONSTRAINT annotation_identifier_orthography_id_fk FOREIGN KEY (annotation_identifier_orthography_id) REFERENCES public.annotation_identifier_orthography(annotation_identifier_orthography_id);CREATE TABLE IF NOT EXISTS typography_annotation (
	typography_annotation_id serial NOT NULL,
	text_selection_id integer NOT NULL,
	annotation_abbreviation_id integer NULL,
	annotation_accentuation_id integer NULL,
	annotation_accronym_id integer NULL,
	annotation_insertion_id integer NULL,
	annotation_expansion_id integer NULL,
	annotation_connectivity_id integer NULL,
	annotation_correction_id integer NULL,
	annotation_curvature_id integer NULL,
	annotation_deletion_id integer NULL,
	annotation_orientation_id integer NULL,
	annotation_vacat_id integer NULL,
	annotation_weight_id integer NULL,
	annotation_symbol_id integer NULL,
	annotation_word_splitting_id integer NULL,
	annotation_word_class_id integer NULL,
	annotation_punctuation_id integer NULL,
	annotation_position_in_text_id integer NULL,
	annotation_regularity_id integer NULL,
	annotation_slope_id integer NULL,
	annotation_script_type_id integer NULL,
	comment text NULL,
	created timestamp NULL,
	created_by varchar NULL,
	modified timestamp NULL,
	modified_by varchar NULL,
	revision_status varchar NULL,
	CONSTRAINT typography_annotation_pk PRIMARY KEY (typography_annotation_id)
);

ALTER TABLE public.typography_annotation ADD CONSTRAINT text_selection_id_fk FOREIGN KEY (text_selection_id) REFERENCES public.text_selection(text_selection_id);
ALTER TABLE public.typography_annotation ADD CONSTRAINT annotation_abbreviation_id_fk FOREIGN KEY (annotation_abbreviation_id) REFERENCES public.annotation_abbreviation(annotation_abbreviation_id);
ALTER TABLE public.typography_annotation ADD CONSTRAINT annotation_accentuation_id_fk FOREIGN KEY (annotation_accentuation_id) REFERENCES public.annotation_accentuation(annotation_accentuation_id);
ALTER TABLE public.typography_annotation ADD CONSTRAINT annotation_accronym_id_fk FOREIGN KEY (annotation_accronym_id) REFERENCES public.annotation_accronym(annotation_accronym_id);
ALTER TABLE public.typography_annotation ADD CONSTRAINT annotation_insertion_id_fk FOREIGN KEY (annotation_insertion_id) REFERENCES public.annotation_insertion(annotation_insertion_id);
ALTER TABLE public.typography_annotation ADD CONSTRAINT annotation_expansion_id_fk FOREIGN KEY (annotation_expansion_id) REFERENCES public.annotation_expansion(annotation_expansion_id);
ALTER TABLE public.typography_annotation ADD CONSTRAINT annotation_connectivity_id_fk FOREIGN KEY (annotation_connectivity_id) REFERENCES public.annotation_connectivity(annotation_connectivity_id);
ALTER TABLE public.typography_annotation ADD CONSTRAINT annotation_correction_id_fk FOREIGN KEY (annotation_correction_id) REFERENCES public.annotation_correction(annotation_correction_id);
ALTER TABLE public.typography_annotation ADD CONSTRAINT annotation_curvature_id_fk FOREIGN KEY (annotation_curvature_id) REFERENCES public.annotation_curvature(annotation_curvature_id);
ALTER TABLE public.typography_annotation ADD CONSTRAINT annotation_deletion_id_fk FOREIGN KEY (annotation_deletion_id) REFERENCES public.annotation_deletion(annotation_deletion_id);
ALTER TABLE public.typography_annotation ADD CONSTRAINT annotation_orientation_id_fk FOREIGN KEY (annotation_orientation_id) REFERENCES public.annotation_orientation(annotation_orientation_id);
ALTER TABLE public.typography_annotation ADD CONSTRAINT annotation_vacat_id_fk FOREIGN KEY (annotation_vacat_id) REFERENCES public.annotation_vacat(annotation_vacat_id);
ALTER TABLE public.typography_annotation ADD CONSTRAINT annotation_weight_id_fk FOREIGN KEY (annotation_weight_id) REFERENCES public.annotation_weight(annotation_weight_id);
ALTER TABLE public.typography_annotation ADD CONSTRAINT annotation_symbol_id_fk FOREIGN KEY (annotation_symbol_id) REFERENCES public.annotation_symbol(annotation_symbol_id);
ALTER TABLE public.typography_annotation ADD CONSTRAINT annotation_word_splitting_id_fk FOREIGN KEY (annotation_word_splitting_id) REFERENCES public.annotation_word_splitting(annotation_word_splitting_id);
ALTER TABLE public.typography_annotation ADD CONSTRAINT annotation_word_class_id_fk FOREIGN KEY (annotation_word_class_id) REFERENCES public.annotation_word_class(annotation_word_class_id);
ALTER TABLE public.typography_annotation ADD CONSTRAINT annotation_punctuation_id_fk FOREIGN KEY (annotation_punctuation_id) REFERENCES public.annotation_punctuation(annotation_punctuation_id);
ALTER TABLE public.typography_annotation ADD CONSTRAINT annotation_position_in_text_id_fk FOREIGN KEY (annotation_position_in_text_id) REFERENCES public.annotation_position_in_text(annotation_position_in_text_id);
ALTER TABLE public.typography_annotation ADD CONSTRAINT annotation_regularity_id_fk FOREIGN KEY (annotation_regularity_id) REFERENCES public.annotation_regularity(annotation_regularity_id);
ALTER TABLE public.typography_annotation ADD CONSTRAINT annotation_slope_id_fk FOREIGN KEY (annotation_slope_id) REFERENCES public.annotation_slope(annotation_slope_id);
ALTER TABLE public.typography_annotation ADD CONSTRAINT annotation_script_type_id_fk FOREIGN KEY (annotation_script_type_id) REFERENCES public.annotation_script_type(annotation_script_type_id);CREATE TABLE IF NOT EXISTS generic_text_structure_part (
	generic_text_structure_part_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT generic_text_structure_part_pk PRIMARY KEY (generic_text_structure_part_id)
);

CREATE UNIQUE INDEX generic_text_structure_part_name_index ON public.generic_text_structure_part (name);CREATE TABLE IF NOT EXISTS layout_text_structure_part (
	layout_text_structure_part_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT layout_text_structure_part_pk PRIMARY KEY (layout_text_structure_part_id)
);

CREATE UNIQUE INDEX layout_text_structure_part_name_index ON public.layout_text_structure_part (name);CREATE TABLE IF NOT EXISTS text_structure_alignment (
	text_structure_alignment_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT text_structure_alignment_pk PRIMARY KEY (text_structure_alignment_id)
);

CREATE UNIQUE INDEX text_structure_alignment_name_index ON public.text_structure_alignment (name);CREATE TABLE IF NOT EXISTS text_structure_indentation (
	text_structure_indentation_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT text_structure_indentation_pk PRIMARY KEY (text_structure_indentation_id)
);

CREATE UNIQUE INDEX text_structure_indentation_name_index ON public.text_structure_indentation (name);CREATE TABLE IF NOT EXISTS text_structure_lectional_signs (
	text_structure_lectional_signs_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT text_structure_lectional_signs_pk PRIMARY KEY (text_structure_lectional_signs_id)
);

CREATE UNIQUE INDEX text_structure_lectional_signs_name_index ON public.text_structure_lectional_signs (name);CREATE TABLE IF NOT EXISTS text_structure_lineation (
	text_structure_lineation_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT text_structure_lineation_pk PRIMARY KEY (text_structure_lineation_id)
);

CREATE UNIQUE INDEX text_structure_lineation_name_index ON public.text_structure_lineation (name);CREATE TABLE IF NOT EXISTS text_structure_orientation (
	text_structure_orientation_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT text_structure_orientation_pk PRIMARY KEY (text_structure_orientation_id)
);

CREATE UNIQUE INDEX text_structure_orientation_name_index ON public.text_structure_orientation (name);CREATE TABLE IF NOT EXISTS text_structure_pagination (
	text_structure_pagination_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT text_structure_pagination_pk PRIMARY KEY (text_structure_pagination_id)
);

CREATE UNIQUE INDEX text_structure_pagination_name_index ON public.text_structure_pagination (name);CREATE TABLE IF NOT EXISTS text_structure_separation (
	text_structure_separation_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT text_structure_separation_pk PRIMARY KEY (text_structure_separation_id)
);

CREATE UNIQUE INDEX text_structure_separation_name_index ON public.text_structure_separation (name);CREATE TABLE IF NOT EXISTS text_structure_spacing (
	text_structure_spacing_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT text_structure_spacing_pk PRIMARY KEY (text_structure_spacing_id)
);

CREATE UNIQUE INDEX text_structure_spacing_name_index ON public.text_structure_spacing (name);CREATE TABLE IF NOT EXISTS text_structure_annotation_subtype (
	text_structure_annotation_subtype_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT text_structure_annotation_subtype_pk PRIMARY KEY (text_structure_annotation_subtype_id)
);

CREATE UNIQUE INDEX text_structure_annotation_subtype_name_index ON public.text_structure_annotation_subtype (name);CREATE TABLE IF NOT EXISTS text_structure_annotation_type (
	text_structure_annotation_type_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT text_structure_annotation_type_pk PRIMARY KEY (text_structure_annotation_type_id)
);

CREATE UNIQUE INDEX text_structure_annotation_type_name_index ON public.text_structure_annotation_type (name);CREATE TABLE IF NOT EXISTS text_structure_attached_to (
	text_structure_attached_to_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT text_structure_attached_to_pk PRIMARY KEY (text_structure_attached_to_id)
);

CREATE UNIQUE INDEX text_structure_attached_to_name_index ON public.text_structure_attached_to (name);CREATE TABLE IF NOT EXISTS text_structure_attachment_type (
	text_structure_attachment_type_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT text_structure_attachment_type_pk PRIMARY KEY (text_structure_attachment_type_id)
);

CREATE UNIQUE INDEX text_structure_attachment_type_name_index ON public.text_structure_attachment_type (name);CREATE TABLE IF NOT EXISTS text_structure_information_status (
	text_structure_information_status_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT text_structure_information_status_pk PRIMARY KEY (text_structure_information_status_id)
);

CREATE UNIQUE INDEX text_structure_information_status_name_index ON public.text_structure_information_status (name);CREATE TABLE IF NOT EXISTS text_structure_speech_act (
	text_structure_speech_act_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT text_structure_speech_act_pk PRIMARY KEY (text_structure_speech_act_id)
);

CREATE UNIQUE INDEX text_structure_speech_act_name_index ON public.text_structure_speech_act (name);CREATE TABLE IF NOT EXISTS text_structure_standard_form (
	text_structure_standard_form_id serial NOT NULL,
	name varchar(250) NULL,
	CONSTRAINT text_structure_standard_form_pk PRIMARY KEY (text_structure_standard_form_id)
);

CREATE UNIQUE INDEX text_structure_standard_form_name_index ON public.text_structure_standard_form (name);CREATE TABLE IF NOT EXISTS generic_text_structure (
	generic_text_structure_id integer NOT NULL,
	text_selection_id integer NOT NULL,
	generic_text_structure_part_id integer NULL,
	generic_text_structure_part_number varchar(10) NULL,
	level_id integer NULL,
	components varchar(250) NULL,
	preservation_status varchar(250) NULL,
	comment text NULL,
	CONSTRAINT generic_text_structure_pk PRIMARY KEY (generic_text_structure_id)
);

ALTER TABLE public.generic_text_structure ADD CONSTRAINT text_selection_id_fk FOREIGN KEY (text_selection_id) REFERENCES public.text_selection(text_selection_id);
ALTER TABLE public.generic_text_structure ADD CONSTRAINT level_id_fk FOREIGN KEY (level_id) REFERENCES public.level(level_id);
ALTER TABLE public.generic_text_structure ADD CONSTRAINT generic_text_structure_part_id_fk FOREIGN KEY (generic_text_structure_part_id) REFERENCES public.generic_text_structure_part(generic_text_structure_part_id);CREATE TABLE IF NOT EXISTS layout_text_structure (
	layout_text_structure_id integer NOT NULL,
	text_selection_id integer NOT NULL,
	layout_text_structure_part_id integer NULL,
	layout_text_structure_part_number varchar(10) NULL,
	text_structure_spacing_id integer NULL,
	text_structure_separation_id integer NULL,
	text_structure_orientation_id integer NULL,
	text_structure_alignment_id integer NULL,
	text_structure_indentation_id integer NULL,
	text_structure_lectional_signs_id integer NULL,
	text_structure_lineation_id integer NULL,
	text_structure_pagination_id integer NULL,
	comment text NULL,
	preservation_status varchar(250) NULL,
	CONSTRAINT layout_text_structure_pk PRIMARY KEY (layout_text_structure_id)
);

ALTER TABLE public.layout_text_structure ADD CONSTRAINT text_selection_id_fk FOREIGN KEY (text_selection_id) REFERENCES public.text_selection(text_selection_id);
ALTER TABLE public.layout_text_structure ADD CONSTRAINT layout_text_structure_part_id_fk FOREIGN KEY (layout_text_structure_part_id) REFERENCES public.layout_text_structure_part(layout_text_structure_part_id);
ALTER TABLE public.layout_text_structure ADD CONSTRAINT text_structure_spacing_id_fk FOREIGN KEY (text_structure_spacing_id) REFERENCES public.text_structure_spacing(text_structure_spacing_id);
ALTER TABLE public.layout_text_structure ADD CONSTRAINT text_structure_separation_id_fk FOREIGN KEY (text_structure_separation_id) REFERENCES public.text_structure_separation(text_structure_separation_id);
ALTER TABLE public.layout_text_structure ADD CONSTRAINT text_structure_orientation_id_fk FOREIGN KEY (text_structure_orientation_id) REFERENCES public.text_structure_orientation(text_structure_orientation_id);
ALTER TABLE public.layout_text_structure ADD CONSTRAINT text_structure_alignment_id_fk FOREIGN KEY (text_structure_alignment_id) REFERENCES public.text_structure_alignment(text_structure_alignment_id);
ALTER TABLE public.layout_text_structure ADD CONSTRAINT text_structure_indentation_id_fk FOREIGN KEY (text_structure_indentation_id) REFERENCES public.text_structure_indentation(text_structure_indentation_id);
ALTER TABLE public.layout_text_structure ADD CONSTRAINT text_structure_lectional_signs_id_fk FOREIGN KEY (text_structure_lectional_signs_id) REFERENCES public.text_structure_lectional_signs(text_structure_lectional_signs_id);
ALTER TABLE public.layout_text_structure ADD CONSTRAINT text_structure_lineation_id_fk FOREIGN KEY (text_structure_lineation_id) REFERENCES public.text_structure_lineation(text_structure_lineation_id);
ALTER TABLE public.layout_text_structure ADD CONSTRAINT text_structure_pagination_id_fk FOREIGN KEY (text_structure_pagination_id) REFERENCES public.text_structure_pagination(text_structure_pagination_id);CREATE TABLE IF NOT EXISTS generic_text_structure_annotation (
	generic_text_structure_annotation_id integer NOT NULL,
	generic_text_structure_id integer NOT NULL,
	text_selection_id integer NOT NULL,
	text_structure_annotation_type_id integer NULL,
	text_structure_annotation_subtype_id integer NULL,
	text_structure_standard_form_id integer NULL,
	text_structure_attached_to_id integer NULL,
	text_structure_attachment_type_id integer NULL,
	text_structure_speech_act_id integer NULL,
	text_structure_information_status_id integer NULL,
	CONSTRAINT generic_text_structure_annotation_pk PRIMARY KEY (generic_text_structure_annotation_id)
);

ALTER TABLE public.generic_text_structure_annotation ADD CONSTRAINT generic_text_structure_id_fk FOREIGN KEY (generic_text_structure_id) REFERENCES public.generic_text_structure(generic_text_structure_id);
ALTER TABLE public.generic_text_structure_annotation ADD CONSTRAINT text_selection_id_fk FOREIGN KEY (text_selection_id) REFERENCES public.text_selection(text_selection_id);
ALTER TABLE public.generic_text_structure_annotation ADD CONSTRAINT text_structure_annotation_type_id_fk FOREIGN KEY (text_structure_annotation_type_id) REFERENCES public.text_structure_annotation_type(text_structure_annotation_type_id);
ALTER TABLE public.generic_text_structure_annotation ADD CONSTRAINT text_structure_annotation_subtype_id_fk FOREIGN KEY (text_structure_annotation_subtype_id) REFERENCES public.text_structure_annotation_subtype(text_structure_annotation_subtype_id);
ALTER TABLE public.generic_text_structure_annotation ADD CONSTRAINT text_structure_standard_form_id_fk FOREIGN KEY (text_structure_standard_form_id) REFERENCES public.text_structure_standard_form(text_structure_standard_form_id);
ALTER TABLE public.generic_text_structure_annotation ADD CONSTRAINT text_structure_attached_to_id_fk FOREIGN KEY (text_structure_attached_to_id) REFERENCES public.text_structure_attached_to(text_structure_attached_to_id);
ALTER TABLE public.generic_text_structure_annotation ADD CONSTRAINT text_structure_attachment_type_id_fk FOREIGN KEY (text_structure_attachment_type_id) REFERENCES public.text_structure_attachment_type(text_structure_attachment_type_id);
ALTER TABLE public.generic_text_structure_annotation ADD CONSTRAINT text_structure_speech_act_id_fk FOREIGN KEY (text_structure_speech_act_id) REFERENCES public.text_structure_speech_act(text_structure_speech_act_id);
ALTER TABLE public.generic_text_structure_annotation ADD CONSTRAINT text_structure_information_status_id_fk FOREIGN KEY (text_structure_information_status_id) REFERENCES public.text_structure_information_status(text_structure_information_status_id);CREATE TABLE IF NOT EXISTS layout_text_structure_annotation (
	layout_text_structure_annotation_id integer NOT NULL,
	layout_text_structure_id integer NOT NULL,
	text_selection_id integer NOT NULL,
	text_structure_annotation_type_id integer NULL,
	text_structure_annotation_subtype_id integer NULL,
	text_structure_spacing_id integer NULL,
	text_structure_separation_id integer NULL,
	text_structure_orientation_id integer NULL,
	text_structure_alignment_id integer NULL,
	text_structure_indentation_id integer NULL,
	text_structure_lectional_signs_id integer NULL,
	text_structure_lineation_id integer NULL,
	text_structure_pagination_id integer NULL,
	CONSTRAINT layout_text_structure_annotation_pk PRIMARY KEY (layout_text_structure_annotation_id)
);

ALTER TABLE public.layout_text_structure_annotation ADD CONSTRAINT layout_text_structure_id_fk FOREIGN KEY (layout_text_structure_id) REFERENCES public.layout_text_structure(layout_text_structure_id);
ALTER TABLE public.layout_text_structure_annotation ADD CONSTRAINT text_selection_id_fk FOREIGN KEY (text_selection_id) REFERENCES public.text_selection(text_selection_id);
ALTER TABLE public.layout_text_structure_annotation ADD CONSTRAINT text_structure_annotation_type_id_fk FOREIGN KEY (text_structure_annotation_type_id) REFERENCES public.text_structure_annotation_type(text_structure_annotation_type_id);
ALTER TABLE public.layout_text_structure_annotation ADD CONSTRAINT text_structure_annotation_subtype_id_fk FOREIGN KEY (text_structure_annotation_subtype_id) REFERENCES public.text_structure_annotation_subtype(text_structure_annotation_subtype_id);
ALTER TABLE public.layout_text_structure_annotation ADD CONSTRAINT text_structure_spacing_id_fk FOREIGN KEY (text_structure_spacing_id) REFERENCES public.text_structure_spacing(text_structure_spacing_id);
ALTER TABLE public.layout_text_structure_annotation ADD CONSTRAINT text_structure_separation_id_fk FOREIGN KEY (text_structure_separation_id) REFERENCES public.text_structure_separation(text_structure_separation_id);
ALTER TABLE public.layout_text_structure_annotation ADD CONSTRAINT text_structure_orientation_id_fk FOREIGN KEY (text_structure_orientation_id) REFERENCES public.text_structure_orientation(text_structure_orientation_id);
ALTER TABLE public.layout_text_structure_annotation ADD CONSTRAINT text_structure_alignment_id_fk FOREIGN KEY (text_structure_alignment_id) REFERENCES public.text_structure_alignment(text_structure_alignment_id);
ALTER TABLE public.layout_text_structure_annotation ADD CONSTRAINT text_structure_indentation_id_fk FOREIGN KEY (text_structure_indentation_id) REFERENCES public.text_structure_indentation(text_structure_indentation_id);
ALTER TABLE public.layout_text_structure_annotation ADD CONSTRAINT text_structure_lectional_signs_id_fk FOREIGN KEY (text_structure_lectional_signs_id) REFERENCES public.text_structure_lectional_signs(text_structure_lectional_signs_id);
ALTER TABLE public.layout_text_structure_annotation ADD CONSTRAINT text_structure_lineation_id_fk FOREIGN KEY (text_structure_lineation_id) REFERENCES public.text_structure_lineation(text_structure_lineation_id);
ALTER TABLE public.layout_text_structure_annotation ADD CONSTRAINT text_structure_pagination_id_fk FOREIGN KEY (text_structure_pagination_id) REFERENCES public.text_structure_pagination(text_structure_pagination_id);