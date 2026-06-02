# Annotation Service — LOD & W3C Web Annotation Design

## Choosing a Schema / Exchange Format

Three candidates were considered for describing and exchanging annotation data with other services.

### Apache UIMA / CAS TypeSystem

- Designed for NLP processing pipelines (GATE, cTAKES, DKPro)
- Good if interop with those specific tools is required
- Heavy: XML-based type descriptors, serializes as XMI or CAS JSON
- Not LOD-native, not web-friendly — it is a pipeline format, not an exchange format

**Verdict:** only relevant as an output format if NLP pipeline integration is needed, not as a primary exchange model.

### JSON Schema

- A *validation* language, not a data format
- Describes shape and constraints but carries no semantic meaning
- `"uri": "vocab:curvature:5"` is opaque to any consumer without out-of-band documentation
- Good for API contracts and tooling

**Verdict:** still useful at the API boundary to validate the *shape* of documents, but not sufficient as a standalone exchange format.

### W3C Web Annotation + JSON-LD (recommended)

- Built specifically for text annotations: `target` = text selection, `body` = annotation content
- JSON-LD is plain JSON for consumers that don't care about LOD, but full RDF for those that do
- Vocabulary terms become first-class URIs that dereference to SKOS concept definitions
- W3C standard — annotation editors (Hypothesis, Recogito, etc.) already speak it

**Verdict:** recommended primary exchange format. JSON Schema and JSON-LD are complementary, not competing: JSON-LD gives semantic meaning to the data; JSON Schema enforces its structure at the API boundary.

---

## Dereferenceable URLs vs Opaque URIs

In Linked Open Data, concept identifiers must be dereferenceable HTTP(S) URLs — not custom URI schemes.

| Type | Example | Problem |
|---|---|---|
| Opaque URI | `vocab:annotation_curvature:5` | Custom scheme, nothing can follow it |
| Dereferenceable URL | `https://evwrit.ugent.be/vocab/annotation_curvature/5` | Any HTTP client can GET it and receive RDF/JSON-LD back |

A dereferenceable URL is a URI, but not all URIs are dereferenceable. When a LOD client sees `"id": "https://evwrit.ugent.be/vocab/annotation_curvature/5"` it can:

1. GET that URL
2. Receive a SKOS concept document (content negotiation: Turtle, JSON-LD, or HTML via `Accept` header)
3. Discover `skos:prefLabel`, `skos:broader`, `skos:inScheme`, translations, etc.

This is one of the four core Linked Data principles (Tim Berners-Lee): use HTTP URIs so people can look up the things they identify.

**Practical implication:** the vocabulary endpoints must actually serve content at those URLs. `GET https://evwrit.ugent.be/vocab/annotation_curvature/5` should return the SKOS concept. The lookup service already knows about these concepts — it just needs to expose them at stable, dereferenceable URLs.

---

## Multiple Bodies vs Single Structured Body

The W3C Web Annotation spec allows both patterns.

### Multiple bodies — one per property

```json
"body": [
  { "id": "https://evwrit.ugent.be/vocab/annotation_curvature/5",  "type": "skos:Concept", "label": "italic",      "purpose": "evwrit:classifying/curvature"  },
  { "id": "https://evwrit.ugent.be/vocab/annotation_typography/3", "type": "skos:Concept", "label": "cursive",     "purpose": "evwrit:classifying/typography" },
  { "id": "https://evwrit.ugent.be/vocab/annotation_type/1",       "type": "skos:Concept", "label": "handwriting", "purpose": "evwrit:classifying/type"       }
]
```

**Pros:** each body is independently typed, queryable, and replaceable. A SPARQL query for "all annotations with curvature=italic" is clean.

**Cons:** with 10+ properties per annotation (as in TypographyAnnotation or HandshiftAnnotation), the body array becomes very verbose.

### Single structured body

```json
"body": {
  "type":  "Dataset",
  "@type": "evwrit:TypographyAnnotation",
  "evwrit:curvature":  { "id": "https://evwrit.ugent.be/vocab/annotation_curvature/5",  "label": "italic"      },
  "evwrit:typography": { "id": "https://evwrit.ugent.be/vocab/annotation_typography/3", "label": "cursive"     },
  "evwrit:type":       { "id": "https://evwrit.ugent.be/vocab/annotation_type/1",        "label": "handwriting" }
}
```

**Pros:** mirrors the internal domain model directly, easier to read, natural fit for the service's JSON responses.

**Cons:** less granular for LOD consumers — you cannot cleanly query a single property without understanding the custom `evwrit:` vocabulary.

### Recommendation: single structured body

Annotation properties (curvature, typography, type, etc.) are aspects of the *same observation* and only make sense together. Splitting them into independent bodies implies they could exist independently, which they cannot. A `Dataset` body typed as `evwrit:TypographyAnnotation` maps cleanly to the existing domain model.

The multiple-body pattern is better suited when bodies are truly independent (e.g. a comment + a tag + a link on the same target).

---

## Full W3C Web Annotation Example

An annotation with a comment and three controlled vocabulary properties, using a single structured body.

```json
{
  "@context": [
    "http://www.w3.org/ns/anno.jsonld",
    {
      "evwrit": "https://evwrit.ugent.be/vocab/",
      "skos":   "http://www.w3.org/2004/02/skos/core#",
      "label":  "skos:prefLabel"
    }
  ],
  "type": "Annotation",
  "id":   "https://evwrit.ugent.be/annotation/42",
  "motivation": "classifying",
  "body": [
    {
      "type":    "TextualBody",
      "value":   "cursive hand, ligatures present",
      "purpose": "commenting"
    },
    {
      "type":  "Dataset",
      "@type": "evwrit:TypographyAnnotation",
      "evwrit:curvature":  { "id": "https://evwrit.ugent.be/vocab/annotation_curvature/5",  "label": "italic"      },
      "evwrit:typography": { "id": "https://evwrit.ugent.be/vocab/annotation_typography/3", "label": "cursive"     },
      "evwrit:type":       { "id": "https://evwrit.ugent.be/vocab/annotation_type/1",        "label": "handwriting" }
    }
  ],
  "target": {
    "source": "https://evwrit.ugent.be/text/document/1",
    "selector": {
      "type":  "TextPositionSelector",
      "start": 10,
      "end":   50
    }
  }
}
```

The `purpose` field on the `TextualBody` uses the standard W3C motivation `commenting`. The structured body uses your own `evwrit:` vocabulary to name the properties.

---

## SKOS Controlled Vocabulary Schema

Each lookup table becomes a `skos:ConceptScheme`; each row becomes a `skos:Concept`. This is the LOD layer that makes your vocabulary term URLs dereferenceable.

```turtle
@prefix skos:   <http://www.w3.org/2004/02/skos/core#> .
@prefix evwrit: <https://evwrit.ugent.be/vocab/> .
@prefix dct:    <http://purl.org/dc/terms/> .

# --- Concept Schemes (one per lookup table) ---

evwrit:annotation_curvature
  a               skos:ConceptScheme ;
  skos:prefLabel  "Annotation Curvature"@en ;
  dct:description "Describes the curvature of letterforms in a handwritten text."@en .

evwrit:annotation_typography
  a               skos:ConceptScheme ;
  skos:prefLabel  "Annotation Typography"@en .

evwrit:annotation_type
  a               skos:ConceptScheme ;
  skos:prefLabel  "Annotation Type"@en .

# --- Concepts (rows in annotation_curvature) ---

evwrit:annotation_curvature/1
  a              skos:Concept ;
  skos:inScheme  evwrit:annotation_curvature ;
  skos:prefLabel "upright"@en .

evwrit:annotation_curvature/5
  a              skos:Concept ;
  skos:inScheme  evwrit:annotation_curvature ;
  skos:prefLabel "italic"@en ;
  skos:broader   evwrit:annotation_curvature/1 .

# --- Concepts (rows in annotation_typography) ---

evwrit:annotation_typography/3
  a              skos:Concept ;
  skos:inScheme  evwrit:annotation_typography ;
  skos:prefLabel "cursive"@en .

# --- Concepts (rows in annotation_type) ---

evwrit:annotation_type/1
  a              skos:Concept ;
  skos:inScheme  evwrit:annotation_type ;
  skos:prefLabel "handwriting"@en .
```

---

## How the Layers Connect

```
DB row:  annotation_curvature_id = 5
           │
           ▼
Service:   resolves to URL + label at response time
           │
           ▼
URL:       https://evwrit.ugent.be/vocab/annotation_curvature/5
           │  ├─ skos:prefLabel  "italic"
           │  ├─ skos:broader    evwrit:annotation_curvature/1
           │  └─ skos:inScheme   evwrit:annotation_curvature
           │
           ▼
W3C Annotation body carries the URL + label inline (self-contained),
but any LOD client can follow the URL for richer context.
```

- **Database** holds integer FKs
- **Service** resolves them to dereferenceable URLs + labels at response time
- **SKOS graph** makes those URLs meaningful to LOD clients
- **W3C Annotation** wraps everything in a standard exchange format
- **JSON Schema** validates the shape of the JSON-LD at the API boundary
