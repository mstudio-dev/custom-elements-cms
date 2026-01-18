# Anleitung: Element Type "Text mit Bild" erstellen

## 1. Neuen Element Type im Backend anlegen

Gehe zu **Element Types** → **Add Element Type** und fülle folgende Felder aus:

### Name
```
text_with_image
```

### Label
```
Text mit Bild
```

### Fields (JSON)
```json
[
  {
    "name": "headline",
    "label": "Überschrift",
    "type": "text"
  },
  {
    "name": "text",
    "label": "Text",
    "type": "textarea"
  },
  {
    "name": "image_id",
    "label": "Bild (Media-ID)",
    "type": "number"
  },
  {
    "name": "image_position",
    "label": "Bildposition",
    "type": "choice",
    "choices": {
      "Links": "left",
      "Rechts": "right"
    }
  },
  {
    "name": "alt_text",
    "label": "Alt-Text",
    "type": "text"
  }
]
```

### Template (Twig)
Kopiere den Inhalt aus: `templates/element_types/text_with_image.html.twig`

## 2. Element erstellen

Gehe zu **Elements** → **Add Element** und fülle aus:

### Element Typ
Wähle: **Text mit Bild**

### Daten (JSON)
```json
{
  "headline": "Meine Überschrift",
  "text": "Dies ist ein Beispieltext.\n\nMit mehreren Absätzen.",
  "image_id": 1,
  "image_position": "left",
  "alt_text": "Beschreibung des Bildes"
}
```

**Hinweis:** Die `image_id` entspricht der ID aus der Mediathek. 
Schau in **Mediathek** nach der ID des gewünschten Bildes.

## 3. Bildpositionen

- `"left"` - Bild links, Text rechts
- `"right"` - Bild rechts, Text links

## 4. Responsive Verhalten

Auf mobilen Geräten (< 768px) wird das Layout automatisch 
zu einer vertikalen Darstellung (Bild oben, Text unten).
