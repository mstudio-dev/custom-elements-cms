# Custom Elements CMS

Ein flexibles Content Management System basierend auf Symfony 6.4 und EasyAdmin, das dynamische Element-Typen mit individuellen Formularfeldern ermöglicht.

## Features

- 🎨 **Dynamische Element-Typen** - Erstelle eigene Content-Elemente mit JSON-Definition
- 📝 **Rich-Text-Editor** - Trix-Integration mit Formatierungen (Fett, Kursiv, Links)
- 🖼️ **Mediathek** - Ordnerbasierte Dateiverwaltung mit Drag & Drop
- 📰 **News-System** - Mit Kategorien, Teaser-Bildern und Listenansichten
- 👤 **Personen-Verwaltung** - Für Team-Seiten und Autorenprofile
- 🔒 **Benutzer-Authentifizierung** - Sicheres Login-System

## Installation

```bash
# Dependencies installieren
composer install

# Datenbank erstellen
php bin/console doctrine:database:create

# Migrations ausführen
php bin/console doctrine:migrations:migrate

# Admin-User erstellen
php bin/console app:create-admin-user

# Development-Server starten
php -S localhost:8000 -t public
```

Admin-Login: http://localhost:8000/admin

## Element-Typen

Element-Typen definieren die Struktur deiner Content-Elemente. Jeder Element-Typ hat:
- **Name**: Technischer Identifier (z.B. `text_with_image`)
- **Label**: Anzeigename im Backend
- **Fields**: JSON-Array mit Felddefinitionen
- **Template**: Twig-Datei für die Frontend-Ausgabe

### Verfügbare Feldtypen

#### 1. Text (Einzeiliges Eingabefeld)
```json
{
  "name": "headline",
  "type": "text",
  "label": "Überschrift",
  "required": false
}
```

#### 2. Textarea (Mehrzeiliges Textfeld)
```json
{
  "name": "description",
  "type": "textarea",
  "label": "Beschreibung",
  "required": false
}
```

#### 3. Rich Text (Formatierter Text mit Trix-Editor)
```json
{
  "name": "content",
  "type": "richtext",
  "label": "Inhalt",
  "required": false
}
```
**Verfügbare Formatierungen:**
- Fett
- Kursiv
- Links

#### 4. Checkbox / Boolean
```json
{
  "name": "show_border",
  "type": "checkbox",
  "label": "Rahmen anzeigen",
  "required": false
}
```
Alternative: `"type": "boolean"`

#### 5. Choice (Dropdown-Auswahl)
```json
{
  "name": "alignment",
  "type": "choice",
  "label": "Ausrichtung",
  "required": false,
  "choices": {
    "left": "Linksbündig",
    "center": "Zentriert",
    "right": "Rechtsbündig"
  }
}
```

#### 6. Media / Image (Medien-Auswahl)
```json
{
  "name": "image",
  "type": "media",
  "label": "Bild",
  "required": false
}
```
Alternative: `"type": "image"`

Zeigt ein Dropdown mit allen Dateien aus der Mediathek an (inkl. Ordnerpfad).

## Beispiel: Element-Typ "Text mit Bild"

### 1. Element-Typ anlegen

**Admin** → **Element Types** → **Add Element Type**

```json
[
  {
    "name": "headline",
    "type": "text",
    "label": "Überschrift"
  },
  {
    "name": "text",
    "type": "richtext",
    "label": "Text"
  },
  {
    "name": "image",
    "type": "media",
    "label": "Bild"
  },
  {
    "name": "image_position",
    "type": "choice",
    "label": "Bildposition",
    "choices": {
      "left": "Links vom Text",
      "right": "Rechts vom Text",
      "top": "Über dem Text",
      "bottom": "Unter dem Text"
    }
  }
]
```

**Template**: `text_with_image.html.twig`

### 2. Template erstellen

`templates/element_types/text_with_image.html.twig`:

```twig
{# Template für Element Type: Text mit Bild #}
{% if data.image %}
  {% set media = repository('Media').find(data.image) %}
{% endif %}

<div class="text-with-image my-4 position-{{ data.image_position ?? 'left' }}">
  {% if data.headline %}
    <h2>{{ data.headline }}</h2>
  {% endif %}
  
  <div class="content-wrapper">
    {% if media and data.image_position in ['left', 'top'] %}
      <div class="image-container">
        <img src="{{ media.url }}" alt="{{ media.name }}" class="img-fluid">
      </div>
    {% endif %}
    
    {% if data.text %}
      <div class="text-container">
        {{ data.text | raw }}
      </div>
    {% endif %}
    
    {% if media and data.image_position in ['right', 'bottom'] %}
      <div class="image-container">
        <img src="{{ media.url }}" alt="{{ media.name }}" class="img-fluid">
      </div>
    {% endif %}
  </div>
</div>
```

### 3. Element verwenden

1. **Seite bearbeiten** → Tab **Elements**
2. **Add Element**
3. **Element Type** auswählen: "Text mit Bild"
4. Felder ausfüllen
5. Speichern

## Beispiel: Accordion

Das Accordion-Element hat 5 feste Item-Felder (keine JSON-Struktur):

```json
[
  {
    "name": "headline",
    "type": "text",
    "label": "Überschrift (optional)",
    "required": false
  },
  {
    "name": "keep_open",
    "type": "checkbox",
    "label": "Mehrere Panels gleichzeitig öffnen",
    "required": false
  },
  {
    "name": "item1_title",
    "type": "text",
    "label": "Item 1 - Titel",
    "required": false
  },
  {
    "name": "item1_content",
    "type": "textarea",
    "label": "Item 1 - Inhalt",
    "required": false
  },
  {
    "name": "item2_title",
    "type": "text",
    "label": "Item 2 - Titel",
    "required": false
  },
  {
    "name": "item2_content",
    "type": "textarea",
    "label": "Item 2 - Inhalt",
    "required": false
  }
  // ... bis item5
]
```

## Zugriff auf Daten im Template

### Einfache Felder
```twig
{{ data.headline }}
{{ data.text }}
{{ data.show_border }}
```

### Media-Felder
```twig
{% if data.image %}
  {% set media = repository('Media').find(data.image) %}
  <img src="{{ media.url }}" alt="{{ media.name }}">
{% endif %}
```

### Repository-Zugriff
```twig
{# In Page/News-Templates verfügbar #}
{% set allPages = repository('Page').findAll() %}
{% set publishedNews = repository('News').findBy({'status': 'published'}) %}
```

## Struktur

```
src/
├── Controller/
│   └── Admin/              # EasyAdmin CRUD-Controller
├── Entity/                 # Doctrine Entities
├── EventListener/          # Event Subscribers
├── Form/                   # Custom Form Types
└── Repository/             # Doctrine Repositories

templates/
├── element_types/          # Element-Templates
├── page/                   # Seiten-Templates
├── news/                   # News-Templates
└── admin/                  # Admin-Customizations

public/
├── uploads/                # Hochgeladene Dateien
├── trix-integration.js     # Trix-Editor Integration
└── admin-media-dragdrop.js # Mediathek Drag & Drop
```

## Technische Details

### System
- **PHP**: 8.3.6
- **Symfony**: 6.4
- **EasyAdmin**: 4.27
- **Datenbank**: SQLite
- **Rich-Text-Editor**: Trix 2.0.8

### Datenstruktur

**Element.data** (JSON):
```json
{
  "headline": "Meine Überschrift",
  "text": "<p>Formatierter Text</p>",
  "image": 42,
  "alignment": "center"
}
```

Media-IDs werden als Integer gespeichert und im Template mit `repository('Media').find(id)` aufgelöst.

## Tipps & Best Practices

### 1. Feld-Namen
- Verwende `snake_case` für technische Namen
- Sprechende Labels für die Nutzer

### 2. Required-Felder
- Nur wirklich notwendige Felder als `required: true` markieren
- Fehlende Felder im Template mit `?? ''` oder `?? null` abfangen

### 3. Media-Felder
- Immer prüfen ob Media-ID existiert: `{% if data.image %}`
- Fallback-Bild oder Platzhalter vorsehen

### 4. Choice-Felder
- Kurze Werte als Keys (werden im JSON gespeichert)
- Sprechende Labels als Values

### 5. Templates
- Template-Name sollte dem Element-Type entsprechen
- Konsistente CSS-Klassen verwenden
- Bootstrap 5 ist bereits eingebunden

## Support

Bei Fragen oder Problemen:
1. Cache löschen: `php bin/console cache:clear`
2. Log-Dateien prüfen: `var/log/`
3. Browser-Konsole auf JavaScript-Fehler prüfen
