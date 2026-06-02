# Annotation Service — JSON Schema Design

## Context

Annotation models have properties that reference controlled vocabulary lookup tables internally (integer foreign keys, e.g. `annotation_curvature_id`). When published or served via the annotation service, each FK is converted to a URI plus a human-readable label. This document describes how to model the JSON schemas for view and create operations in a self-describing annotation service.

---

## The URI + Label Problem

| Context | What you need |
|---|---|
| Create / update | A URI is sufficient to identify the vocabulary term |
| View / fetch | URI + label together, so the response is human-readable without a second lookup |

Using `oneOf: [string, object]` creates unnecessary client complexity. The recommended approach is to **always use an object**, but define separate `$defs` for the create vs. view variant.

---

## Reusable `$defs` — `VocabularyRef` and `VocabularyView`

`VocabularyRef` and `VocabularyView` are generic, field-agnostic definitions. Any controlled vocabulary field (`curvature`, `typography`, `type`, `connectivity`, etc.) references one of these instead of defining its own type.

```json
"$defs": {
  "VocabularyRef": {
    "type": "object",
    "properties": {
      "uri": { "type": "string" }
    },
    "required": ["uri"],
    "additionalProperties": false
  },
  "VocabularyView": {
    "allOf": [
      { "$ref": "#/$defs/VocabularyRef" },
      {
        "properties": {
          "label": { "type": "string" }
        },
        "required": ["label"]
      }
    ]
  }
}
```

Adding a new vocabulary field costs one line — `"connectivity": { "$ref": "#/$defs/VocabularyView" }` — with no new type definitions.

---

## Example Annotation Schema

The annotation has 5 properties: a string `id`, an optional string `comment`, and three controlled vocabulary fields: `curvature`, `typography`, `type`.

### Create schema

What an editor sends when creating an annotation. Vocabulary fields require only a URI; `comment` is optional.

```json
{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "$defs": {
    "VocabularyRef": {
      "type": "object",
      "properties": {
        "uri": { "type": "string" }
      },
      "required": ["uri"],
      "additionalProperties": false
    }
  },
  "type": "object",
  "properties": {
    "id":         { "type": "string" },
    "comment":    { "type": "string" },
    "curvature":  { "$ref": "#/$defs/VocabularyRef" },
    "typography": { "$ref": "#/$defs/VocabularyRef" },
    "type":       { "$ref": "#/$defs/VocabularyRef" }
  },
  "required": ["id", "curvature", "typography", "type"]
}
```

### View schema

What the service returns when fetching an annotation. All vocabulary fields carry both `uri` and `label`.

```json
{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "$defs": {
    "VocabularyRef": {
      "type": "object",
      "properties": {
        "uri": { "type": "string" }
      },
      "required": ["uri"],
      "additionalProperties": false
    },
    "VocabularyView": {
      "allOf": [
        { "$ref": "#/$defs/VocabularyRef" },
        {
          "properties": {
            "label": { "type": "string" }
          },
          "required": ["label"]
        }
      ]
    }
  },
  "type": "object",
  "properties": {
    "id":         { "type": "string" },
    "comment":    { "type": "string" },
    "curvature":  { "$ref": "#/$defs/VocabularyView" },
    "typography": { "$ref": "#/$defs/VocabularyView" },
    "type":       { "$ref": "#/$defs/VocabularyView" }
  },
  "required": ["id", "curvature", "typography", "type"]
}
```

---

## Example Instances

### View response

```json
{
  "id": "annotation:42",
  "comment": "cursive hand, ligatures present",
  "curvature":  { "uri": "vocab:annotation_curvature:5",  "label": "italic" },
  "typography": { "uri": "vocab:annotation_typography:3", "label": "cursive" },
  "type":       { "uri": "vocab:annotation_type:1",       "label": "handwriting" }
}
```

### Create request

```json
{
  "id": "annotation:42",
  "comment": "cursive hand, ligatures present",
  "curvature":  { "uri": "vocab:annotation_curvature:5" },
  "typography": { "uri": "vocab:annotation_typography:3" },
  "type":       { "uri": "vocab:annotation_type:1" }
}
```

---

## Self-Describing Service

The annotation service exposes a descriptor so clients know which schema applies to each operation:

```json
{
  "id": "annotation_service_typography",
  "capabilities": ["findOne", "findAll", "create", "update"],
  "schemas": {
    "findOne":  { "response": { "$ref": "schemas/annotation.view.json"   } },
    "findAll":  { "response": { "$ref": "schemas/annotation.view.json"   } },
    "create":   { "request":  { "$ref": "schemas/annotation.create.json" },
                  "response": { "$ref": "schemas/annotation.view.json"   } },
    "update":   { "request":  { "$ref": "schemas/annotation.update.json" },
                  "response": { "$ref": "schemas/annotation.view.json"   } }
  }
}
```

`response` is always the view schema — even after a create or update, the service returns the enriched object with labels so the caller doesn't need a second fetch.
