<?php
namespace App\Service\ElasticSearch;

interface SearchConfigInterface
{
    const FILTER_NUMERIC = "numeric"; // numeric term filter
    const FILTER_BOOLEAN = "boolean"; // boolean term filter
    const FILTER_KEYWORD = "keyword"; // term filter
    const FILTER_WILDCARD = "wildcard"; // wildcard term filter
    const FILTER_EXISTS = "exists"; 

    const FILTER_TEXT = "text";
    const FILTER_TEXT_MULTIPLE = "text_multiple";

    const FILTER_OBJECT_ID = "object_id";
    const FILTER_NESTED_ID = "nested_id";

    const FILTER_NESTED_MULTIPLE = "nested_multiple";
    const FILTER_DATE_RANGE = "date_range";
    const FILTER_DMY_RANGE = "dmy_range";
    const FILTER_NUMERIC_RANGE_SLIDER = "numeric_range";

    const AGG_NUMERIC = "numeric";
    const AGG_KEYWORD = "exact_text";
    const AGG_NESTED_KEYWORD = "nested_term";
    const AGG_BOOLEAN = "bool";
    const AGG_GLOBAL_STATS = "stats";

    const AGG_NESTED_ID_NAME = "nested_id_name";
    const AGG_OBJECT_ID_NAME = "object_id_name";
}
