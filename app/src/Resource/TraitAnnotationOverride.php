<?php
namespace App\Resource;

use Illuminate\Database\Eloquent\Model;
trait TraitAnnotationOverride
{
    protected function override(array $ret, Model $resource): array
    {
        $ret['hasOverride'] = false;
        $ret['isDeleted'] = false;

        if ($resource->relationLoaded('override')) {
            $override = $resource->override;
            if (!$override) {
                return $ret;
            }
            // override text selection properties
            $textSelection = $ret['text_selection'];
            $textSelection['selection_start'] = $override->selection_start;
            $textSelection['selection_end'] = $override->selection_end;
            $textSelection['selection_length'] = $override->selection_length;
            // update annotation
            $ret['text_selection'] = $textSelection;
            $ret['hasOverride'] = true;
            $ret['isDeleted'] = $override->is_deleted;
        }

        return $ret;
    }
}