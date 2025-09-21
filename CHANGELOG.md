# 📋 Changelog

Alle wichtigen Änderungen am KaPlan Gottesdienste Plugin werden in dieser Datei dokumentiert.

Das Format basiert auf [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
und dieses Projekt folgt [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.8.5] - 2025-09-21

### 🔧 Behoben
- **KRITISCH**: Smart Quotes Normalisierung in Shortcode-Attributen
  - Plugin funktioniert jetzt auch mit kopierten Shortcodes aus Word/anderen Editoren
  - Automatische Konvertierung von "smart quotes" zu regulären Anführungszeichen
  - Bessere Sanitization von numerischen Feldern (`days`, `template`)
- PHP 8+ Attribute-Syntax korrigiert: `#[ReturnTypeWillChange]` statt `#["ReturnTypeWillChange"]`

### 🚀 Verbessert
- Robustere URL-Parameter-Validierung
- Erweiterte Fehlerbehandlung bei malformed Shortcodes
- Debug-Output für bessere Problemdiagnose

### 📚 Dokumentation
- Benutzerfreundliches README mit deutschen Anweisungen
- Neue GitHub Issue-Templates
- Erweiterte Troubleshooting-Sektion

---

## [1.8.4] - 2025-01-21

### 🆕 Neu
- **3-Spalten-Layout** (Template 3) für bessere Übersichtlichkeit
- Verbesserte mobile Darstellung
- Optimierte CSS-Styles

### 🔧 Technisch
- WordPress 2.7+ und PHP 5.5+ Kompatibilität
- Aktualisierte Versionsnummern-Verwaltung

---

## [1.8.0] - 2025-01-09

### 🆕 Major Features
- **Automatisches Update-System** über GitHub Releases
- Ein-Klick-Updates direkt aus WordPress Admin
- Automatische Benachrichtigungen bei neuen Versionen
- Sichere Update-Installation ohne FTP

### 🎨 UI/UX
- Neues **Template-System** mit mehreren Layout-Optionen
- Verbesserte CSS-Klassen für Theme-Integration
- Mobile-First Responsive Design

---

## [1.0.0] - 2021

### 🎉 Initial Release
- Grundfunktionalität für KaPlan-Integration
- Shortcode-System implementiert
- Deutsche Lokalisierung
- Basis-Templates für Gottesdienst-Ausgabe

---

## Legende

- 🆕 **Neu** - Neue Features
- 🔧 **Behoben** - Bug Fixes
- 🚀 **Verbessert** - Verbesserungen
- 📚 **Dokumentation** - Dokumentations-Updates
- 🔒 **Sicherheit** - Sicherheits-Fixes
- ⚡ **Performance** - Performance-Optimierungen
- 🎨 **UI/UX** - User Interface Verbesserungen

---

**Hinweis**: Für die neueste Version besuchen Sie die [GitHub Releases](https://github.com/hansjoergJL/kaplan-gottesdienste/releases).
