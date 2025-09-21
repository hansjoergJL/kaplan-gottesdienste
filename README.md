# 🕊️ KaPlan Gottesdienste WordPress Plugin

**Verbinden Sie Ihr KaPlan-System nahtlos mit Ihrer Website** • Zeigen Sie aktuelle Gottesdienste und Gemeindeveranstaltungen automatisch auf Ihrer WordPress-Website an.

[![Version](https://img.shields.io/badge/Version-1.8.5-blue.svg)](https://github.com/hansjoergJL/kaplan-gottesdienste/releases)
[![WordPress](https://img.shields.io/badge/WordPress-2.7+-green.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-5.5+-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2-orange.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Downloads](https://img.shields.io/github/downloads/hansjoergJL/kaplan-gottesdienste/total.svg)](https://github.com/hansjoergJL/kaplan-gottesdienste/releases)

---

## ✨ Was macht dieses Plugin?

Das **KaPlan Gottesdienste Plugin** holt automatisch Ihre aktuellen Gottesdienste, Veranstaltungen und Termine aus Ihrem KaPlan-System und zeigt sie schön formatiert auf Ihrer WordPress-Website an.

### 🎯 **Perfekt für:**
- **Gemeindeleitung** - Automatische Website-Updates ohne manuelle Arbeit
- **Webmaster** - Einfache Integration ohne Programmierkenntnisse
- **Gemeindeglieder** - Immer aktuelle Informationen zu Gottesdiensten

---

## 🚀 **Hauptfunktionen**

### 📅 **Automatische Synchronisation**
- Zeigt immer die neuesten Gottesdienste aus KaPlan
- Keine manuelle Pflege der Website nötig
- Änderungen in KaPlan erscheinen sofort online

### 🎨 **Flexible Darstellung**
✅ **3 verschiedene Layouts** für unterschiedliche Bedürfnisse  
✅ **Deutsche Datumsformate** - Sonntag, 21. September 2025  
✅ **Mobile-optimiert** - Sieht auf allen Geräten gut aus  
✅ **Anpassbare Ausgabe** - Zeigen Sie nur was Sie möchten  

### 🔧 **Einfache Verwendung**
\`\`\`
[ausgabe_kaplan server="ihr-server.de" arbeitsgruppe="gemeinde" code="ihr-code"]
\`\`\`
*Einfach als "Shortcode" einfügen.*

### ⚡ **Automatische Updates**
- Updates erscheinen direkt in WordPress
- Ein Klick - fertig aktualisiert
- Keine FTP oder technischen Kenntnisse erforderlich

---

## 📸 **So sieht es aus**

### Standard-Layout
\`\`\`
📅 Sonntag, 22. September 2025
    🕘 09:30  Heilige Messe (Pfarrkirche)
    🕚 11:00  Familiengottesdienst mit Kinderchor
    
📅 Montag, 23. September 2025
    🕘 18:00  Abendmesse (Kapelle)
\`\`\`

### Tabellen-Layout (Template 2)
| **Sonntag, 22. September 2025** |
|---|
| **🕘 09:30** | Heilige Messe (Pfarrkirche) |
| **🕚 11:00** | Familiengottesdienst mit Kinderchor |

### 3-Spalten-Layout (Template 3) - NEU!
| Zeit | Gottesdienst | Ort |
|------|-------------|-----|
| **🕘 09:30** | Heilige Messe<br>*mit Kirchenchor* | Pfarrkirche |
| **🕚 11:00** | Familiengottesdienst | Gemeindehaus |

---

## 📦 **Installation**

### **Schritt 1: Plugin herunterladen**
[📥 **Neueste Version herunterladen**](https://github.com/hansjoergJL/kaplan-gottesdienste/releases/latest)

### **Schritt 2: In WordPress installieren**
1. WordPress Admin öffnen
2. **Plugins** → **Installieren** → **Plugin hochladen**
3. ZIP-Datei auswählen
4. **Jetzt installieren** und **Aktivieren**

### **Schritt 3: Auf Website einfügen**
Fügen Sie den Shortcode in der Ausgabeseite für Gottesdienste (oder Gemeindetermine) ein:

\`\`\`
[ausgabe_kaplan server="ihr-kaplan-server.de" arbeitsgruppe="gemeinde" code="ihr-zugriffscode"]
\`\`\`

*Die benötigten Zugangsdaten erhalten Sie von Ihrem KaPlan-Administrator!*

---

## ⚙️ **Anpassungsmöglichkeiten**

### **Basis-Parameter**
| Parameter | Beschreibung | Beispiel |
|-----------|-------------|----------|
| \`server\` | Ihre KaPlan-Server-Adresse | \`server="web.kaplanserver.de"\` |
| \`arbeitsgruppe\` | KaPlan-Arbeitsgruppe | \`arbeitsgruppe="test"\` |
| \`code\` | Ihr Zugriffscode | \`code="1234"\` |

### **Erweiterte Optionen**
| Parameter | Was es macht | Optionen |
|-----------|-------------|----------|
| \`template\` | **Layout-Stil** | \`"1"\` Standard, \`"2"\` Tabelle, \`"3"\` 3-Spalten |
| \`days\` | **Zeitraum** | \`"14"\` für 14 Tage, \`"30"\` für 30 Tage |
| \`mode\` | **Anzeigemodus** | \`"B"\` Chronologisch, \`"VT"\` Veranstaltungen |
| \`leitung\` | **Zelebrant anzeigen** | \`"TVN"\` Titel + Name |
| \`options\` | **Zusatzoptionen** | \`"U"\` zeigt "Uhr" an |

### **Beispiele für verschiedene Anwendungen**

**🔸 Gottesdienste für die nächsten 2 Wochen:**
\`\`\`
[ausgabe_kaplan server="kaplanserver" arbeitsgruppe="test" code="1234" days="14"]
\`\`\`

**🔸 Veranstaltungen im 3-Spalten-Layout:**
\`\`\`
[ausgabe_kaplan server="kaplanserver" arbeitsgruppe="test" code="1234" template="3" mode="VT"]
\`\`\`

**🔸 Mit Zelebrant und Ergänzung " Uhr":**
\`\`\`
[ausgabe_kaplan server="kaplanserver" arbeitsgruppe="test" code="1234" leitung="TVN" options="U"]
\`\`\`

---

## ❓ **Häufige Fragen**

### **"Das Plugin zeigt keine Daten an"**
1. ✅ Prüfen Sie Ihre KaPlan-Zugangsdaten
2. ✅ Stellen Sie sicher, dass Ihr KaPlan-Server erreichbar ist
3. ✅ Kontaktieren Sie Ihren KaPlan-Administrator

### **"Wie ändere ich das Aussehen?"**
- Verwenden Sie verschiedene \`template\`-Optionen (1, 2, oder 3)
- Passen Sie CSS in Ihrem Theme an
- Nutzen Sie verschiedene \`options\`-Parameter

### **"Wie aktualisiere ich das Plugin?"**
- Updates erscheinen automatisch in WordPress unter **Plugins**
- Einfach auf **Aktualisieren** klicken
- Alle Einstellungen bleiben erhalten

### **"Smart Quotes Problem"**
⚠️ **Wichtig:** Wenn Sie Anführungszeichen kopieren/einfügen, können "smarte Anführungszeichen" (wie diese: \`"14"\`) Probleme verursachen. Das Plugin normalisiert diese automatisch seit Version 1.8.5.

---

## 🆕 **Was ist neu?**

### **Version 1.8.5 (Aktuell)**
🔧 **Kritischer Fix:** Smart Quotes Normalisierung  
✅ Plugin funktioniert jetzt auch mit kopierten Shortcodes  
✅ Bessere Fehlerbehandlung bei Attributen  
✅ Verbesserte URL-Parameter-Validierung  

### **Version 1.8.4**
🆕 **Neues 3-Spalten-Layout** (Template 3)  
📱 **Verbesserte mobile Darstellung**  
🎨 **Modernisierte CSS-Styles**  

[📋 **Alle Versionshinweise anzeigen**](https://github.com/hansjoergJL/kaplan-gottesdienste/releases)

---

## 💡 **Support & Hilfe**

### **🆘 Benötigen Sie Hilfe?**
- 📧 **Technischer Support:** Über GitHub Issues
- 🌐 **Plugin-Website:** [jlsoftware.de](http://www.jlsoftware.de/software/kaplan-plugin/)
- 📖 **KaPlan-Support:** Kontaktieren Sie Ihren KaPlan-Anbieter

### **🤝 Beitragen**
Sind Sie Entwickler? Contributions sind willkommen!
- 🐛 **Fehler melden:** [GitHub Issues](https://github.com/hansjoergJL/kaplan-gottesdienste/issues)
- 🔧 **Verbesserungen vorschlagen:** Pull Requests
- ⭐ **Projekt unterstützen:** Geben Sie uns einen Stern!

---

## 📋 **Systemanforderungen**

| Anforderung | Minimum | Empfohlen |
|-------------|---------|-----------|
| **WordPress** | 2.7+ | 6.0+ |
| **PHP** | 5.5+ | 8.0+ |
| **KaPlan** | API-Zugang | Aktuelle Version |
| **Internet** | HTTPS-Verbindung | Stabile Verbindung |

---

## 📄 **Lizenz**

Dieses Plugin ist unter der **GPL v2** lizensiert - Sie können es frei verwenden und ändern. 
Erwerben Sie aber zur Nutzung, Datenabfrage und Supportberechtigung bitte eine Lizenz!

---

## 👥 **Über die Entwickler**

Entwickelt von: **Hans-Jörg Jödike**  
*Entwickelt mit ❤️ für die Kirchengemeinde*

📍 **Deutschland** • 🌐 [jlsoftware.de](http://www.jlsoftware.de)

---

<div align="center">

### 🌟 **Hat Ihnen das Plugin geholfen?**

[⭐ **Geben Sie uns einen Stern auf GitHub!**](https://github.com/hansjoergJL/kaplan-gottesdienste) • [📥 **Neueste Version herunterladen**](https://github.com/hansjoergJL/kaplan-gottesdienste/releases/latest)

*Danke, dass Sie KaPlan Gottesdienste verwenden! 🙏*

</div>
