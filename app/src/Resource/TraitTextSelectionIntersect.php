<?php


namespace App\Resource;


trait TraitTextSelectionIntersect {

    private function annotationIntersect(&$annotationSource, $annotations, $limitProperties= []): void
    {
        $additionalProperties = [];
        foreach( $annotations as $annotationTest ) {
            $type = $annotationTest['type'];
            if ($this->textSelectionIntersect($annotationSource['text_selection'], $annotationTest['text_selection'])) {
                $properties = count($limitProperties) ? array_intersect_key($annotationTest['properties'], array_flip($limitProperties)) : array_filter($annotationTest['properties'], fn($v,$k) => $v && strpos( $k , $type ) === 0, ARRAY_FILTER_USE_BOTH);
                foreach ($properties as $propertyKey => $propertyValue) {
                    if ( $propertyValue['id'] ?? $propertyValue['number'] ?? null) { // todo: dirty!
                        $additionalProperties[$propertyKey][$propertyValue['id'] ?? $propertyValue['number']] = $propertyValue;
                    }
                }
            }
        }
        // remove keys of additional properties
        foreach ($additionalProperties as $propertyKey => $propertyValues) {
            $additionalProperties[$propertyKey] = array_values($propertyValues);
        }
        if (!array_key_exists('intersect_properties', $annotationSource)){
            $annotationSource = array_merge($annotationSource, ['intersect_properties' => array_map(fn($value)=>[$value], $annotationSource['properties'])]);
        }
        // merge additional properties with existing properties
        foreach ($additionalProperties as $propertyKey => $propertyValues) {
            if (!array_key_exists($propertyKey, $annotationSource['intersect_properties'])){
                $annotationSource['intersect_properties'] = array_merge($annotationSource['intersect_properties'], [$propertyKey => $propertyValues]);
            }
            $annotationSource['intersect_properties'][$propertyKey] = array_merge($propertyValues, $annotationSource['intersect_properties'][$propertyKey]);
        }
    }

    private function textSelectionIntersect($a, $b): ?array
    {
        $a = $a['text_selection'] ?? $a;
        $b = $b['text_selection'] ?? $b;

        $min = $a['selection_start'] < $b['selection_start'] ? $a : $b;
        $max = $min['id'] == $a['id'] ? $b : $a;

        //min ends before max starts -> no intersection
        if ($min['selection_end'] < $max['selection_start']) return null; //the ranges don't intersect

        return [$max['selection_start'], min($min['selection_end'], $max['selection_end'])];
    }

}