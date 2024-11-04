\connect evwrit

CREATE TABLE public.annotation_override (
                                            annotation_override_id serial4 NOT NULL,
                                            annotation_id int4 NOT NULL,
                                            annotation_type varchar(50) NOT NULL,
                                            selection_start int4 NOT NULL,
                                            selection_length int4 NOT NULL,
                                            selection_end int4 NOT NULL,
                                            is_deleted bool NOT NULL,
                                            created timestamp NULL,
                                            modified timestamp NULL,
                                            CONSTRAINT annotation_override_pk PRIMARY KEY (annotation_override_id),
                                            CONSTRAINT annotation_override_unique UNIQUE (annotation_id, annotation_type)
);
CREATE INDEX annotation_override_annotation_id_idx ON public.annotation_override USING btree (annotation_id, annotation_type);